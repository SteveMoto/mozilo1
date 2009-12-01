<?php

/* 
* 
* $Revision$
* $LastChangedDate$
* $Author$
*
*/

#$CHARSET = 'ISO-8859-1';
$CHARSET = 'UTF-8';

require_once("Crypt.php");
require_once("../Mail.php");
require_once("filesystem.php");

// Session starten!
session_start();

// Initial: Fehlerausgabe unterdr�cken, um Path-Disclosure-Attacken ins Leere laufen zu lassen
@ini_set("display_errors", 0);

// Initialisierungen
$logindataconf = new Properties("conf/logindata.conf");
if(!isset($logindataconf->properties['readonly'])) {
    die($logindataconf->properties['error']);
}

$basicconf = new Properties("conf/basic.conf");
$pwcrypt = new Crypt();
$mailfunctions = new Mail();
$BASIC_LANGUAGE = new Properties("conf/language_".$basicconf->get("language").".conf");

// MAXIMALE ANZAHL FALSCHER LOGINS
$FALSELOGINLIMIT = 3;
// DAUER DER SPERRE NACH FALSCHEN LOGINS IN MINUTEN
$LOGINLOCKTIME = 10;


// �berpr�fen: Existiert ein Benutzer? Wenn nicht: admin:install anlegen
if (($logindataconf->get("name") == "") || ($logindataconf->get("pw") == "")) {
    $logindataconf->set("name", "admin");
    $logindataconf->set("pw", $pwcrypt->encrypt("install"));
    $logindataconf->set("initialpw", "true");
}

$HTML = "<!doctype html public \"-//W3C//DTD HTML 4.0 //EN\"><html>";
// User hat sich ausgeloggt
if (isset($_POST['logout'])) {
    // Session beenden und die Sessiondaten l�schen
    session_destroy();
    unset($_SESSION);
}

// Wurde das Anmeldeformular verschickt?
if  (isset($_POST['login'])) {
    // Zugangsdaten pr�fen
        if (checkLoginData($_POST['username'], $_POST['password'])) {
            // Daten in der Session merken
      $_SESSION['username'] = $_POST['username'];
      $_SESSION['login_okay'] = true;
    }
}

// Anmeldung erfolgreich
if (isset($_SESSION['login_okay']) and $_SESSION['login_okay']) {
    // Counter f�r falsche Logins innerhalb der Sperrzeit zur�cksetzen
    $logindataconf->set("falselogincounttemp", 0);
    // ...ab in den Admin!
    header("location:index.php");
}

// Anmeldung fehlerhaft
elseif  (isset($_POST['login'])) {
    // Counter hochz�hlen
    $falselogincounttemp = ($logindataconf->get("falselogincounttemp"))+1;
    $logindataconf->set("falselogincounttemp", $falselogincounttemp); // Z�hler f�r die aktuelle Sperrzeit
    $falselogincount = ($logindataconf->get("falselogincount"))+1;
    $logindataconf->set("falselogincount", $falselogincount); // Gesamtz�hler
    $HTML .= "<head>"
        ."<link rel=\"stylesheet\" href=\"adminstyle.css\" type=\"text/css\" />"
        ."<title>".getLanguageValue("incorrect_login")."</title>"
        ."</head>"
        ."<body onLoad=\"document.loginform.username.focus();document.loginform.username.select()\" >"
        ."<div class=\"message_fehler\">".getLanguageValue("incorrect_login")."</div>";
    // maximale Anzahl falscher Logins erreicht?
    if ($falselogincounttemp >= $FALSELOGINLIMIT) {
        // Sperrzeit starten
        $logindataconf->set("loginlockstarttime", time());
        // Mail an Admin
        if ($basicconf->get("sendadminmail") == "true") {
            $mailcontent = getLanguageValue("loginlocked_mailcontent")."\r\n\r\n"
                .strftime(getLanguageValue("_dateformat"), time())."\r\n"
                .$_SERVER['REMOTE_ADDR']." / ".gethostbyaddr($_SERVER['REMOTE_ADDR'])."\r\n"
                .getLanguageValue("username").": ".$_POST['username'];
                
                // Pr�fen ob die Mail-Funktion vorhanden ist
                if($mailfunctions->isMailAvailable())
                {
                    $mailfunctions->sendMailToAdmin(getLanguageValue("loginlocked_mailsubject"), $mailcontent);
                }
        }
        // Formular ausgrauen
        $HTML .= login_formular(false);
    }
    else {
        // Formular nochmal normal anzeigen
        $HTML .= login_formular(true);
    }
}

// Formular noch nicht abgeschickt? Dann wurde die Seite zum ersten Mal aufgerufen.
else {
    $HTML .= "<head>"
        ."<link rel=\"stylesheet\" href=\"adminstyle.css\" type=\"text/css\">"
        ."<title>".getLanguageValue("loginplease")."</title>"
        ."</head>"
        ."<body onLoad=\"document.loginform.username.focus();document.loginform.username.select()\">";
        // Login noch gesperrt?
        if (($logindataconf->get("falselogincounttemp") > 0) and (time() - $logindataconf->get("loginlockstarttime")) <= $LOGINLOCKTIME * 60) {
#        if (($logindataconf->get("falselogincounttemp") > 0) && (time() - $logindataconf->get("loginlockstarttime")) <= $LOGINLOCKTIME*60) {
            // gesperrtes Formular anzeigen
            $HTML .= login_formular(false);
        } else {
            // Z�hler zur�cksetzen
            $logindataconf->set("falselogincounttemp", 0);
            // normales Formular anzeigen
            $HTML .= login_formular(true);
        }
} 

$HTML .= "</body></html>";

echo $HTML;

// Aufbau des Login-Formulars
function login_formular($enabled) {
    $form = "<div id=\"loginform_shadowdiv\"></div>";
  if ($enabled)
        $form .= "<div id=\"loginform_maindiv\">";
    else
        $form .= "<div id=\"loginform_maindiv_disabled\">";
    if ($enabled)
        $form .= "<form accept-charset=\"$CHARSET\" name=\"loginform\" action=\"".htmlentities($_SERVER['PHP_SELF'],ENT_COMPAT,$CHARSET)."\" method=\"POST\">";
  $form .= "<table id=\"table_loginform\" width=\"100%\" cellspacing=\"10\" border=\"0\" cellpadding=\"0\">"
      ."<tr>"
      ."<td width=\"5%\" rowspan=\"2\" align=\"center\" valign=\"middle\">"
      ."<img src=\"gfx/login.gif\" alt=\"Login\"/>"
      ."</td>"
      ."<td width=\"5%\" class=\"description\">"
      .getLanguageValue("username").":"
      ."</td>"
      ."<td>";
  if ($enabled)
        $form .= "<input type=\"text\" name=\"username\" size=\"15\" maxlength=\"20\" class=\"login_input\">";
    else
        $form .= "<input class=\"login_input\" type=\"text\" size=\"15\" name=\"username\" readonly=\"readonly\">";
  $form .= "</td>"
      ."</tr>"
      ."<tr>"
      ."<td class=\"description\">"
      .getLanguageValue("password").":"
      ."</td>"
      ."<td>";
  if ($enabled)
        $form .= "<input class=\"login_input\" size=\"15\" maxlength=\"20\" type=\"password\" name=\"password\">";
    else
        $form .= "<input class=\"login_input\" size=\"15\" type=\"password\" name=\"password\" readonly=\"readonly\">";
  $form .= "</td>"
      ."</tr>"
      ."<tr>"
      ."<td colspan=\"3\" style=\"text-align: center;\">";
  if ($enabled)
      $form .= "<input name=\"login\" value=\"Login\" class=\"login_submit\" type=\"submit\">";
  else
      $form .= "<input name=\"login\" value=\"Login\" class=\"login_submit\" type=\"submit\" readonly=\"readonly\">";
  $form .= "</td>"
      ."</tr>"
      ."</table>";
  if ($enabled)
      $form .= "</form>";
    $form .= "</div>";
    return $form;
}

// Logindaten �berpr�fen
function checkLoginData($user, $pass)
{
    global $logindataconf;
    global $pwcrypt;
    if ( ($user == $logindataconf->get("name")) and ($pwcrypt->encrypt($pass) == $logindataconf->get("pw")) )
    {
        return true;
    } else {
        return false;
    }
}

?>