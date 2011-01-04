<?php

/*
*
* $Revision$
* $LastChangedDate$
* $Author$
*
*/

session_start();

/* 
echo "<pre style=\"position:fixed;background-color:#000;color:#0f0;padding:5px;font-family:monospace;border:2px solid #777;\">";
print_r($_REQUEST);
echo "</pre>"; 
*/

$BASE_DIR = substr($_SERVER["SCRIPT_FILENAME"],0,strpos($_SERVER["SCRIPT_FILENAME"],'index.php'));
$CMS_DIR_NAME = "cms";
$BASE_DIR_CMS = $BASE_DIR.$CMS_DIR_NAME."/";
$tmp_getDirContentAsArray = NULL;

if(is_file($BASE_DIR_CMS."DefaultConf.php")) {
    require_once($BASE_DIR_CMS."DefaultConf.php");
} else {
    die("Fatal Error ".$BASE_DIR_CMS."DefaultConf.php Datei existiert nicht");
}

# Um Cross-Site Scripting-Schwachstellen zu verhindern
$_SERVER["PHP_SELF"] = htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, $CHARSET);
$_SERVER["REQUEST_URI"] = htmlspecialchars($_SERVER["REQUEST_URI"], ENT_QUOTES, $CHARSET);
if(isset($_SERVER["SCRIPT_URL"]))
    $_SERVER["SCRIPT_URL"] = htmlspecialchars($_SERVER["SCRIPT_URL"], ENT_QUOTES, $CHARSET);
if(isset($_SERVER["SCRIPT_URI"]))
    $_SERVER["SCRIPT_URI"] = htmlspecialchars($_SERVER["SCRIPT_URI"], ENT_QUOTES, $CHARSET);

$_GET = cleanREQUEST($_GET);
$_REQUEST = cleanREQUEST($_REQUEST);
$_POST = cleanREQUEST($_POST);

    # ab php > 5.2.0 hat preg_* ein default pcre.backtrack_limit von 100000 zeichen
    # deshalb der versuch mit ini_set
    @ini_set('pcre.backtrack_limit', 1000000);

    require_once($BASE_DIR_CMS."SpecialChars.php");
    require_once($BASE_DIR_CMS."Properties.php");
    
    // Initial: Fehlerausgabe unterdruecken, um Path-Disclosure-Attacken ins Leere laufen zu lassen
    @ini_set("display_errors", 1);

    $specialchars   = new SpecialChars();
    $CMS_CONF     = new Properties($BASE_DIR_CMS."conf/main.conf",true);
    $VERSION_CONF  = new Properties($BASE_DIR_CMS."conf/version.conf",true);
##    $GALLERY_CONF  = new Properties($BASE_DIR_CMS."conf/gallery.conf",true); // Ticket 64
    $USER_SYNTAX  = new Properties($BASE_DIR_CMS."conf/syntax.conf",true);
    $URL_BASE = substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],"index.php"));
    $CONTENT_DIR_REL        = $BASE_DIR.$CONTENT_DIR_NAME."/";
    $PLUGIN_DIR_REL         = $BASE_DIR.$PLUGIN_DIR_NAME."/";

    require_once($BASE_DIR_CMS."Language.php");
    $language       = new Language();
    $activ_plugins = array();
    $deactiv_plugins = array();
    # Vorhandene Plugins finden und in array $activ_plugins und $deactiv_plugins einsetzen
    # wird für Search und Pluginplatzhaltern verwendet
    list($activ_plugins,$deactiv_plugins) = findPlugins();
    require_once($BASE_DIR_CMS."Syntax.php");
    require_once($BASE_DIR_CMS."Smileys.php");
    $syntax         = new Syntax();
    $smileys        = new Smileys($BASE_DIR_CMS."smileys");

    require_once($BASE_DIR_CMS."Plugin.php");

    // Dateiendungen fuer Inhaltsseiten
    # Achtung die endungen muessen alle gleich lang sein
    $EXT_PAGE       = ".txt";
    $EXT_HIDDEN     = ".hid";
    $EXT_DRAFT      = ".tmp";
    $EXT_LINK       = ".lnk";

    $LAYOUT_DIR     = "layouts/".$CMS_CONF->get("cmslayout");
    $TEMPLATE_FILE  = $LAYOUT_DIR."/template.html";

    # wenn ein Plugin die gallerytemplate.html benutzten möchte und sie blank ist 
    if (getRequestParam("galtemplate", false) == "true") {
        $TEMPLATE_FILE  = $LAYOUT_DIR."/gallerytemplate.html";
    }

    $LAYOUT_DIR_URL = $specialchars->replaceSpecialChars($URL_BASE.$LAYOUT_DIR,true);
    $CSS_FILE       = $LAYOUT_DIR_URL."/css/style.css";
    $FAVICON_FILE   = $LAYOUT_DIR_URL."/favicon.ico";
    // Einstellungen fuer Kontaktformular
/*    $contactformconfig  = new Properties($BASE_DIR_CMS."formular/formular.conf",true);

    // Spamschutz-Aufgaben lt. frontend sprache laden
    if (is_file($BASE_DIR_CMS."formular/aufgaben_".$CMS_CONF->get("cmslanguage").".conf")) {
        $contactformcalcs = new Properties($BASE_DIR_CMS."formular/aufgaben_".$CMS_CONF->get("cmslanguage").".conf",true);
    } elseif (is_file($BASE_DIR_CMS."formular/aufgaben_enEN.conf")) {
    // wenn nicht vorhanden als default enEN laden
        $contactformcalcs = new Properties($BASE_DIR_CMS."formular/aufgaben_enEN.conf",true);
    } else {
    // wenn enEN auch nicht vorhanden - die()
        die("Fatal Error: ".$BASE_DIR_CMS."formular/aufgaben_enEN.conf existiert nicht!");
    }
*/
    $WEBSITE_NAME = $specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"),false,true);
    if ($WEBSITE_NAME == "")
        $WEBSITE_NAME = "Titel der Website";

    $USE_CMS_SYNTAX = true;
    if ($CMS_CONF->get("usecmssyntax") == "false")
        $USE_CMS_SYNTAX = false;
        
    // Request-Parameter einlesen und dabei absichern
    $CAT_REQUEST_URL = $specialchars->replaceSpecialChars(getRequestParam('cat', false),false);
    $PAGE_REQUEST_URL = $specialchars->replaceSpecialChars(getRequestParam('page', false),false);
    $ACTION_REQUEST = getRequestParam('action', false);
    $QUERY_REQUEST = stripcslashes(getRequestParam('query', false));
    $HIGHLIGHT_REQUEST = getRequestParam('highlight', false);

    $HTML                   = "";

    $DEFAULT_CATEGORY = $CMS_CONF->get("defaultcat");
    // Ueberpruefen: Ist die Startkategorie vorhanden? Wenn nicht, nimm einfach die allererste als Standardkategorie
    if (!file_exists($CONTENT_DIR_REL.$DEFAULT_CATEGORY)) {
        $contentdir = opendir($CONTENT_DIR_REL);
        while ($cat = readdir($contentdir)) {
            if (isValidDirOrFile($cat)) {
                $DEFAULT_CATEGORY = $cat;
                break;
            }
        }
        closedir($contentdir);
    }
   
    $CAT_REQUEST = nameToCategory($CAT_REQUEST_URL);
    if ($CAT_REQUEST == "") {
    	$CAT_REQUEST = $DEFAULT_CATEGORY;
    }
    $PAGE_REQUEST = nameToPage($PAGE_REQUEST_URL, $CAT_REQUEST,false);

    // Dateiname der aktuellen Inhaltsseite (wird in getContent() gesetzt)
    $PAGE_FILE = "";

    // Zuerst: Uebergebene Parameter ueberpruefen
    checkParameters();
    // Dann: HTML-Template einlesen und mit Inhalt fuellen
    readTemplate();
    # manche Provider sind auf iso eingestelt
    header('content-type: text/html; charset='.$CHARSET.'');

#echo memory_get_peak_usage(true) / 1024 / 1024 . " MB<br>\n";
#echo memory_get_peak_usage() / 1024 / 1024 . " MB<br>\n";
    // Zum Schluß: Ausgabe des fertigen HTML-Dokuments
    echo $HTML;


// ------------------------------------------------------------------------------
// Parameter auf Korrektheit pruefen
// ------------------------------------------------------------------------------
    function checkParameters() {
        global $CONTENT_DIR_REL;
        global $DEFAULT_CATEGORY;
        global $ACTION_REQUEST;
        global $CAT_REQUEST;
        global $PAGE_REQUEST;
        global $EXT_DRAFT;
        global $EXT_HIDDEN;
        global $EXT_PAGE;
        global $CMS_CONF;

        // Ueberpruefung der gegebenen Parameter
        if (
                // Wenn keine Kategorie uebergeben wurde...
                ($CAT_REQUEST == "")
                // ...oder eine nicht existente Kategorie...
                || (!file_exists($CONTENT_DIR_REL.$CAT_REQUEST))
                // ...oder eine Kategorie ohne Contentseiten...
                || (count(getDirContentAsArray($CONTENT_DIR_REL.$CAT_REQUEST, true, true)) == 0)
            )
            // ...dann verwende die Standardkategorie
            $CAT_REQUEST = $DEFAULT_CATEGORY;


        // Kategorie-Verzeichnis einlesen
        $pagesarray = getDirContentAsArray($CONTENT_DIR_REL.$CAT_REQUEST, true, $CMS_CONF->get("showhiddenpagesasdefaultpage") == "true");

        // Wenn Contentseite nicht explizit angefordert wurde oder nicht vorhanden ist...
        if (
            ($PAGE_REQUEST == "")
            || (!file_exists($CONTENT_DIR_REL.$CAT_REQUEST."/".$PAGE_REQUEST.$EXT_PAGE) && !file_exists($CONTENT_DIR_REL.$CAT_REQUEST."/".$PAGE_REQUEST.$EXT_HIDDEN) && !file_exists($CONTENT_DIR_REL.$CAT_REQUEST."/".$PAGE_REQUEST.$EXT_DRAFT))
            ) {
            //...erste Contentseite der Kategorie setzen
            $PAGE_REQUEST = substr($pagesarray[0], 0, strlen($pagesarray[0]) - 4);
        }

        // Wenn ein Action-Parameter uebergeben wurde: keine aktiven Kat./Inhaltts. anzeigen
        if (($ACTION_REQUEST == "sitemap") || ($ACTION_REQUEST == "search")) {
            $CAT_REQUEST = "";
            $PAGE_REQUEST = "";
        }
    }


// ------------------------------------------------------------------------------
// HTML-Template einlesen und verarbeiten
// ------------------------------------------------------------------------------
    function readTemplate() {
        global $CSS_FILE;
        global $HTML;
        global $FAVICON_FILE;
        global $LAYOUT_DIR_URL;
        global $TEMPLATE_FILE;
        global $USE_CMS_SYNTAX;
        global $WEBSITE_NAME;
        global $ACTION_REQUEST;
        global $HIGHLIGHT_REQUEST;
        global $CAT_REQUEST;
        global $PAGE_REQUEST;
        global $language;
        global $syntax;
        global $CMS_CONF;
        global $smileys;
        global $specialchars;
        global $URL_BASE;
        global $CHARSET;
        global $BASE_DIR_CMS;
        global $activ_plugins;
        global $deactiv_plugins;

    if (!$file = @fopen($TEMPLATE_FILE, "r"))
        die($language->getLanguageValue1("message_template_error_1", $TEMPLATE_FILE));
    $template = fread($file, filesize($TEMPLATE_FILE));
    fclose($file);
        // Platzhalter des Templates mit Inhalt fuellen
        $pagecontentarray = array();
       // getSiteMap, getSearchResult und getContent liefern jeweils ein Array:
       // [0] = Inhalt
       // [1] = Name der Kategorie (leer bei getSiteMap, getSearchResult)
       // [2] = Name des Inhalts
    $pagecontent = "";
    $cattitle = "";
    $pagetitle = "";
     
    # ist nur true wenn Inhaltseite eingelesen wird
    $is_syntax = false;
    if ($ACTION_REQUEST == "sitemap") {
        $pagecontentarray = getSiteMap();
        $pagecontent    = $pagecontentarray[0];
        $cattitle         = $pagecontentarray[1];
        $pagetitle         = $pagecontentarray[2];
    }
    elseif ($ACTION_REQUEST == "search") {
        require_once($BASE_DIR_CMS."Search.php");
        $pagecontentarray = searchInPages();
        $pagecontent    = $pagecontentarray[0];
        $cattitle         = $pagecontentarray[1];
        $pagetitle         = $pagecontentarray[2];
    }
    // Inhalte aus Inhaltsseiten durch Passwort schuetzen
    else { 
        // zunaechst Passwort als gesetzt und nicht eingegeben annehmen
        $passwordok = false;
        if (file_exists($BASE_DIR_CMS."conf/passwords.conf")) {
            $passwords = new Properties($BASE_DIR_CMS."conf/passwords.conf", true); // alle Passwörter laden
            if ($passwords->keyExists($CAT_REQUEST.'/'.$PAGE_REQUEST)) { // nach Passwort fuer diese Seite suchen
                $cattitle    = catToName($CAT_REQUEST, true);
                $pagetitle   = $language->getLanguageValue0("passwordform_title_0");
                if (!isset($_POST) || ($_POST == array())) // sofern kein Passwort eingegeben, nach einem Fragen
                    $pagecontent = getPasswordForm();
                else {
                    if (md5(getRequestParam("password", false)) == $passwords->get($CAT_REQUEST.'/'.$PAGE_REQUEST))
                    // richtiges Passwort eingegeben
                        $passwordok = true;
                    else
                    // falsches Passwort eingegeben - Zugriff verweigern
                        $pagecontent = $language->getLanguageValue0("passwordform_message_passwordwrong_0");
                }
            }
            else
            // diese Seite hat ein Passwort - lasse Zugriff zu
                $passwordok = true;
        }
        else
        // keine Seite hat ein Passwort - lasse Zugriff zu
            $passwordok = true;
        if ($passwordok) {
            if ($USE_CMS_SYNTAX) {
                $pagecontentarray = getContent();
                # Inhaltseite wurde eingelesen also true
                $is_syntax = true;
                $pagecontent    = $pagecontentarray[0];
                $cattitle         = $pagecontentarray[1];
                $pagetitle         = $pagecontentarray[2];
              }
            else {
                $pagecontentarray = getContent();
                $pagecontent    = $pagecontentarray[0];
                $cattitle         = $pagecontentarray[1];
                $pagetitle         = $pagecontentarray[2];
            }
        }
    }
    if(strstr($TEMPLATE_FILE,"gallerytemplate.html"))
        $is_syntax = false;

    $HTML = str_replace('{CONTENT}','---content~~~'.$pagecontent.'~~~content---',$template);
    $HTML = $syntax->convertContent($HTML, $CAT_REQUEST, $is_syntax);
    unset($pagecontent);

    // Smileys ersetzen
    if ($CMS_CONF->get("replaceemoticons") == "true") {
        $HTML = $smileys->replaceEmoticons($HTML);
    }

    // Gesuchte Phrasen hervorheben
    if ($HIGHLIGHT_REQUEST <> "") {
        require_once($BASE_DIR_CMS."Search.php");
        # wir suchen nur im content teil
        preg_match("/---content~~~(.*)~~~content---/Umsi", $HTML,$content);
        $tmp = highlightSearch($content[0], $HIGHLIGHT_REQUEST);
        $HTML = str_replace($content[0],$tmp,$HTML);
        unset($tmp);
    }

    $HTML = str_replace('{CSS_FILE}', $CSS_FILE, $HTML);
    $HTML = str_replace('{CHARSET}', $CHARSET, $HTML);
    $HTML = str_replace('{FAVICON_FILE}', $FAVICON_FILE, $HTML);
    $HTML = str_replace('{LAYOUT_DIR}', $LAYOUT_DIR_URL, $HTML);
    $HTML = str_replace('{BASE_URL}', $URL_BASE, $HTML);

    // Platzhalter ersetzen
    $HTML = replacePlaceholders($HTML, $cattitle, $pagetitle);

    if(strpos($HTML,'{WEBSITE_TITLE}') !== false)
        $HTML = str_replace('{WEBSITE_TITLE}', getWebsiteTitle($WEBSITE_NAME, $cattitle, $pagetitle), $HTML);

    // Meta-Tag "keywords"
    $HTML = str_replace('{WEBSITE_KEYWORDS}', $specialchars->rebuildSpecialChars($CMS_CONF->get("websitekeywords"),false,true), $HTML);
    // Meta-Tag "description"
    $HTML = str_replace('{WEBSITE_DESCRIPTION}', $specialchars->rebuildSpecialChars($CMS_CONF->get("websitedescription"),false,true), $HTML);

    if(strpos($HTML,'{MAINMENU}') !== false)
        $HTML = str_replace('{MAINMENU}', getMainMenu(), $HTML);

    if(strpos($HTML,'{DETAILMENU}') !== false) {
        // Detailmenue (nicht zeigen, wenn Submenues aktiviert sind)
        if ($CMS_CONF->get("usesubmenu") > 0) {
            $HTML = str_replace('{DETAILMENU}', "", $HTML);
        }
        else {
            $HTML = str_replace('{DETAILMENU}', getDetailMenu($CAT_REQUEST), $HTML);
        }
    }
    // Suchformular
    if(strpos($HTML,'{SEARCH}') !== false)
        $HTML = str_replace('{SEARCH}', getSearchForm(), $HTML);
    
    // Sitemap-Link
    $HTML = str_replace('{SITEMAPLINK}', "<a href=\"".$URL_BASE."index.php?action=sitemap\" id=\"sitemaplink\"".getTitleAttribute($language->getLanguageValue0("tooltip_showsitemap_0")).">".$language->getLanguageValue0("message_sitemap_0")."</a>", $HTML);
    
    // CMS-Info-Link
    if(strpos($HTML,'{CMSINFO}') !== false)
        $HTML = str_replace('{CMSINFO}', getCmsInfo(), $HTML);
/*      
    // Kontaktformular
    if(strpos($HTML,'{CONTACT}') !== false)
        $HTML = str_replace('{CONTACT}', buildContactForm(), $HTML);
*/
    // Kontaktformular
    if(strpos($HTML,'{TABLEOFCONTENTS}') !== false)
        $HTML = str_replace('{TABLEOFCONTENTS}', $syntax->getToC($pagecontent), $HTML);

    $HTML = str_replace(array('&#123;','&#125;','&#91;','&#93;'),array('{','}','[',']'),$HTML);
    $HTML = str_replace(array('---content~~~','~~~content---'),"",$HTML);
    }

// ------------------------------------------------------------------------------
// Formular zur Passworteingabe anzeigen
// ------------------------------------------------------------------------------
    function getPasswordForm() {
        global $language;
        global $CMS_CONF;
        global $CAT_REQUEST;
        global $PAGE_REQUEST;
        global $URL_BASE;

        $url = "index.php?cat=".substr($CAT_REQUEST,3)."&amp;page=".substr($PAGE_REQUEST,3);
        if($CMS_CONF->get("modrewrite") == "true") {
            $url = $URL_BASE.substr($CAT_REQUEST,3)."/".substr($PAGE_REQUEST,3).".html";
        }
        // TODO: sollte auch wahlweise ueber ein Template gehen
        return '<form action="'.$url.'" method="post" class="contentpassword">
        '.$language->getLanguageValue0("passwordform_pagepasswordplease_0").' 
        <input type="password" name="password" class="contentpassword_input" />
        <input type="submit" value="'.$language->getLanguageValue0("passwordform_send_0").'" class="contentpassword_button" />
        </form>';
    }

// ------------------------------------------------------------------------------
// Zu einem Kategorienamen passendes Kategorieverzeichnis suchen und zurueckgeben
// Alle Kuehe => 00_Alle-nbsp-K-uuml-he
// ------------------------------------------------------------------------------
    function nameToCategory($catname) {
        global $CONTENT_DIR_REL;

        // Content-Verzeichnis einlesen
        $dircontent = getDirContentAsArray($CONTENT_DIR_REL, false, false);
        // alle vorhandenen Kategorien durchgehen...
        foreach ($dircontent as $currentelement) {
            // ...und wenn eine auf den Namen paßt...
            if (substr($currentelement, 3, strlen($currentelement)-3) == $catname) {
                // ...den vollen Kategorienamen zurueckgeben
                return $currentelement;
            # bei alten links ist die Position noch dabei
            } elseif($currentelement == $catname) {
                return $currentelement;
            }
        }
        // Wenn kein Verzeichnis paßt: Leerstring zurueckgeben
        return "";
    }


// ------------------------------------------------------------------------------
// Zu einer Inhaltsseite passende Datei suchen und zurueckgeben
// Muellers Kuh => 00_M-uuml-llers-nbsp-Kuh.txt
// ------------------------------------------------------------------------------
    function nameToPage($pagename, $currentcat, $ext = true) {
        global $CONTENT_DIR_REL;
        global $EXT_PAGE;
        global $ACTION_REQUEST;

        $showdraft = false;
        if($ACTION_REQUEST == "draft")
            $showdraft = true;
        // Kategorie-Verzeichnis einlesen
        $dircontent = getDirContentAsArray($CONTENT_DIR_REL.$currentcat, true, true, $showdraft);
        if(!is_array($dircontent))
            return "";
        // alle vorhandenen Inhaltsdateien durchgehen...
        foreach ($dircontent as $currentelement) {
            // ...und wenn eine auf den Namen paßt...
            if (substr($currentelement, 3, strlen($currentelement) - 3 - strlen($EXT_PAGE)) == $pagename) {
                // ...den vollen Seitennamen zurueckgeben mit extension
                if($ext) {
                    return $currentelement;
                } else {
                // ...den vollen Seitennamen zurueckgeben ohne extension
                    return substr($currentelement, 0, strlen($currentelement) - strlen($EXT_PAGE));
                }
            # bei alten links ist die Positon noch im Namen
            } elseif (substr($currentelement, 0, strlen($currentelement) - strlen($EXT_PAGE)) == $pagename) {
                // ...den vollen Seitennamen zurueckgeben mit extension
                if($ext) {
                    return $currentelement;
                } else {
                // ...den vollen Seitennamen zurueckgeben ohne extension
                    return substr($currentelement, 0, strlen($currentelement) - strlen($EXT_PAGE));
                }
            }
        }
        // Wenn keine Datei paßt: Leerstring zurueckgeben
        return "";
    }


// ------------------------------------------------------------------------------
// Kategorienamen aus komplettem Verzeichnisnamen einer Kategorie zurueckgeben
// 00_Alle-nbsp-K-uuml-he => Alle Kuehe
// ------------------------------------------------------------------------------
    function catToName($cat, $rebuildnbsp) {
        global $specialchars;
        return $specialchars->rebuildSpecialChars(substr($cat, 3, strlen($cat)), $rebuildnbsp, true);
    }


// ------------------------------------------------------------------------------
// Seitennamen aus komplettem Dateinamen einer Inhaltsseite zurueckgeben
// 00_M-uuml-llers-nbsp-Kuh.txt => Muellers Kuh
// ------------------------------------------------------------------------------
    function pageToName($page, $rebuildnbsp) {
        global $specialchars;
        return $specialchars->rebuildSpecialChars(substr($page, 3, strlen($page) - 7), $rebuildnbsp, true);
    }


// ------------------------------------------------------------------------------
// Inhalt einer Content-Datei einlesen, Rueckgabe als String
// ------------------------------------------------------------------------------
    function getContent() {
        global $CONTENT_DIR_REL;
        global $CAT_REQUEST;
        global $PAGE_REQUEST;
        global $EXT_HIDDEN;
        global $EXT_PAGE;
        global $EXT_DRAFT;
        global $PAGE_FILE;
        global $ACTION_REQUEST;
        global $specialchars;

        // Entwurf
        if (
                ($ACTION_REQUEST == "draft") &&
                (file_exists($CONTENT_DIR_REL.$CAT_REQUEST."/".$PAGE_REQUEST.$EXT_DRAFT))
            ) {
            $PAGE_FILE = $PAGE_REQUEST.$EXT_HIDDEN;
            return array (
                                        implode("", file($CONTENT_DIR_REL.$CAT_REQUEST."/".$PAGE_REQUEST.$EXT_DRAFT)),
                                        catToName($CAT_REQUEST, true),
                                        pageToName($PAGE_REQUEST.$EXT_DRAFT, true)
                                        );
        }
        // normale Inhaltsseite
        elseif (file_exists($CONTENT_DIR_REL.$CAT_REQUEST."/".$PAGE_REQUEST.$EXT_PAGE)) {
            $PAGE_FILE = $PAGE_REQUEST.$EXT_PAGE;
            return array (
                                        implode("", file($CONTENT_DIR_REL.$CAT_REQUEST."/".$PAGE_REQUEST.$EXT_PAGE)),
                                        catToName($CAT_REQUEST, true),
                                        pageToName($PAGE_REQUEST.$EXT_PAGE, true)
                                        );
        }
        // Versteckte Inhaltsseite
        elseif (file_exists($CONTENT_DIR_REL.$CAT_REQUEST."/".$PAGE_REQUEST.$EXT_HIDDEN)) {
            $PAGE_FILE = $PAGE_REQUEST.$EXT_HIDDEN;
            return array (
                                        implode("", file($CONTENT_DIR_REL.$CAT_REQUEST."/".$PAGE_REQUEST.$EXT_HIDDEN)),
                                        catToName($CAT_REQUEST, true),
                                        pageToName($PAGE_REQUEST.$EXT_HIDDEN, true)
                                        );
        }
        else
            return array("","","");
    }


// ------------------------------------------------------------------------------
// Auslesen des Content-Verzeichnisses unter Beruecksichtigung
// des auszuschließenden File-Verzeichnisses, Rueckgabe als Array
// ------------------------------------------------------------------------------
    function getDirContentAsArray($dir, $iscatdir, $showhidden, $showdraft = false) {
        global $CONTENT_FILES_DIR_NAME;
        global $EXT_DRAFT;
        global $EXT_HIDDEN;
        global $EXT_PAGE;
        global $EXT_LINK;
        global $tmp_getDirContentAsArray;

        $files_read = array();
        if(!isset($tmp_getDirContentAsArray[$dir])) {
            $currentdir = opendir($dir);
            while (false !== ($file = readdir($currentdir))) {
                if (
                    // ...und nicht $CONTENT_FILES_DIR_NAME
                    (($file <> $CONTENT_FILES_DIR_NAME) || (!$iscatdir))
                    // nicht "." und ".."
                    && isValidDirOrFile($file)
                    ) {
                $files_read[] = $file;
                }
            }
            closedir($currentdir);
            $tmp_getDirContentAsArray[$dir] = $files_read;
        } else {
            $files_read = $tmp_getDirContentAsArray[$dir];
        }
#        $currentdir = opendir($dir);
#        $files = "";
        $files = array();
        // Einlesen des gesamten Content-Verzeichnisses außer dem
        // auszuschließenden Verzeichnis und den Elementen . und ..
#        while ($file = readdir($currentdir)) {
        foreach ($files_read as $file) {
            if (
                    // wenn Kategorieverzeichnis: Alle Dateien auslesen, die auf $EXT_PAGE oder $EXT_HIDDEN enden...
#                    (
                        (!$iscatdir)
                        || (substr($file, strlen($file)-4, strlen($file)) == $EXT_PAGE)
                        || (substr($file, strlen($file)-4, strlen($file)) == $EXT_LINK)
                        || ($showhidden && (substr($file, strlen($file)-4, strlen($file)) == $EXT_HIDDEN))
                        || ($showdraft && (substr($file, strlen($file)-4, strlen($file)) == $EXT_DRAFT))
#                    )
                    // ...und nicht $CONTENT_FILES_DIR_NAME
#                    && (($file <> $CONTENT_FILES_DIR_NAME) || (!$iscatdir))
                    // nicht "." und ".."
#                    && isValidDirOrFile($file)
                    ) {
            $files[] = $file;
            }
        }
#        closedir($currentdir);
        // Rueckgabe des sortierten Arrays
#        if ($files <> "")
            sort($files);
        return $files;
    }


// ------------------------------------------------------------------------------
// Aufbau des Hauptmenues, Rueckgabe als String
// ------------------------------------------------------------------------------
    function getMainMenu() {
        global $CONTENT_DIR_REL;
        global $CAT_REQUEST;
        global $PAGE_REQUEST;
        global $specialchars;
        global $CMS_CONF;
        global $language;
        global $syntax;
        global $URL_BASE;
        global $EXT_LINK;

        $mainmenu = "<ul class=\"mainmenu\">";
        // Kategorien-Verzeichnis einlesen
        $categoriesarray = getDirContentAsArray($CONTENT_DIR_REL, false, false);
        // Jedes Element des Arrays ans Menue anhaengen
        foreach ($categoriesarray as $currentcategory) {
            # Mod Rewrite
            $url = $URL_BASE."index.php?cat=".substr($currentcategory,3);
            if($CMS_CONF->get("modrewrite") == "true") {
                $url = $URL_BASE.substr($currentcategory,3).".html";
            }
            if(substr($currentcategory,-(strlen($EXT_LINK))) == $EXT_LINK) {
               $mainmenu .= '<li class="mainmenu">'.menuLink($currentcategory,"menu")."</li>";
            }
            // Wenn die Kategorie keine Contentseiten hat, zeige sie nicht an
            elseif (count(getDirContentAsArray($CONTENT_DIR_REL.$currentcategory, true, false)) == 0) {
                $mainmenu .= "";
            }
            // Aktuelle Kategorie als aktiven Menuepunkt anzeigen...
            elseif ($currentcategory == $CAT_REQUEST) {
                $mainmenu .= "<li class=\"mainmenu\">".
                    "<a href=\"".$url."\" class=\"menuactive\"".
                    $syntax->getTitleAttribute($language->getLanguageValue1("tooltip_link_category_1", catToName($currentcategory, false))).
                    ">".catToName($currentcategory, false)."</a>";
                if ($CMS_CONF->get("usesubmenu") > 0) {
                    $mainmenu .= getDetailMenu($currentcategory);
                }
                $mainmenu .= "</li>";
            }
            // ...alle anderen als normalen Menuepunkt.
            else {
                $mainmenu .= "<li class=\"mainmenu\">".
                    "<a href=\"".$url."\" class=\"menu\"".
                     $syntax->getTitleAttribute($language->getLanguageValue1("tooltip_link_category_1", catToName($currentcategory, false))).
                     ">".catToName($currentcategory, false)."</a>";
                if ($CMS_CONF->get("usesubmenu") == 2) {
                    $mainmenu .= getDetailMenu($currentcategory);
                }
                $mainmenu .= "</li>";
            }
        }
        // Rueckgabe des Menues
        return $mainmenu . "</ul>";
    }


// ------------------------------------------------------------------------------
// Aufbau des Detailmenues, Rueckgabe als String
// ------------------------------------------------------------------------------
    function getDetailMenu($cat){
        global $ACTION_REQUEST;
        global $QUERY_REQUEST;
        global $CONTENT_DIR_REL;
        global $CAT_REQUEST;
        global $PAGE_REQUEST;
        global $EXT_DRAFT;
        global $language;
        global $specialchars;
        global $CMS_CONF;
        global $syntax;
        global $URL_BASE;
        global $EXT_LINK;
        global $CHARSET;

        if ($CMS_CONF->get("usesubmenu") > 0)
            $cssprefix = "submenu";
        else
            $cssprefix = "detailmenu";

        # Mod Rewrite
        $url_draft = $URL_BASE."index.php?cat=".substr($cat,3)."&amp;page=".substr($PAGE_REQUEST, 3)."&amp;";
        $modrewrite_dumy = NULL;
        if($CMS_CONF->get("modrewrite") == "true") {
            $url_draft = $URL_BASE.substr($cat,3)."/".substr($PAGE_REQUEST, 3).".html?";
            $modrewrite_dumy = ".html";
        }
        $detailmenu = "<ul class=\"detailmenu\">";
        // Sitemap
        if (($ACTION_REQUEST == "sitemap") && ($CMS_CONF->get("usesubmenu") == 0))
            $detailmenu .= "<li class=\"detailmenu\"><a href=\"".$URL_BASE."index.php".$modrewrite_dumy."?action=sitemap\" class=\"".$cssprefix."active\">".$language->getLanguageValue0("message_sitemap_0")."</a></li>";
        // Suchergebnis
        elseif (($ACTION_REQUEST == "search") && ($CMS_CONF->get("usesubmenu") == 0))
            $detailmenu .= "<li class=\"detailmenu\"><a href=\"".$URL_BASE."index.php".$modrewrite_dumy."?action=search&amp;query=".$specialchars->replaceSpecialChars($QUERY_REQUEST, false)."\" class=\"".$cssprefix."active\">".$language->getLanguageValue1("message_searchresult_1", $specialchars->getHtmlEntityDecode($QUERY_REQUEST))."</a></li>";
        // Entwurfsansicht
        elseif (($ACTION_REQUEST == "draft") && ($CMS_CONF->get("usesubmenu") == 0))
            $detailmenu .= "<li class=\"detailmenu\"><a href=\"".$url_draft."action=draft\" class=\"".$cssprefix."active\">".pageToName($PAGE_REQUEST.$EXT_DRAFT, false)." (".$language->getLanguageValue0("message_draft_0").")</a></li>";
        // "ganz normales" Detailmenue einer Kategorie
        else {
            // Content-Verzeichnis der aktuellen Kategorie einlesen
            $contentarray = getDirContentAsArray($CONTENT_DIR_REL.$cat, true, false);

            // Kategorie, die nur versteckte Seiten enthaelt: kein Detailmenue zeigen
            if ($contentarray == "") {
                return "";
            }

            // Jedes Element des Arrays ans Menue anhaengen
            foreach ($contentarray as $currentcontent) {
                // Inhaltsseite nicht anzeigen, wenn sie genauso heißt wie die Kategorie
                if ($CMS_CONF->get("hidecatnamedpages") == "true") {
                    if(substr($currentcontent, 3, strlen($currentcontent) - 7) == substr($cat, 3) and substr($currentcontent,-(strlen($EXT_LINK))) != $EXT_LINK) {
                        // Wenn es in der Kategorie nur diese eine (dank hidecatnamedpages eh nicht angezeigte) Seite gibt,
                        // dann gib als Detailmenue gleich einen Leerstring zurueck
                        if (count($contentarray) == 1) {
                            return "";
                        } 
                        // ...ansonsten auf zur naechsten Inhaltsseite!
                        else {
                            continue;
                        }
                    }
                }
                # Mod Rewrite
                $url = $URL_BASE."index.php?cat=".substr($cat,3)."&amp;page=".substr($currentcontent, 3, strlen($currentcontent) - 7);
                if($CMS_CONF->get("modrewrite") == "true") {
                    $url = $URL_BASE.substr($cat,3)."/".substr($currentcontent, 3, strlen($currentcontent) - 7).".html";
                }
                // Aktuelle Inhaltsseite als aktiven Menuepunkt anzeigen...
                if (
                    ($CAT_REQUEST == $cat) // aktive Kategorie
                    && (substr($currentcontent, 0, strlen($currentcontent) - 4) == $PAGE_REQUEST) // aktive Seite
                    && (substr($currentcontent, -(strlen($EXT_LINK))) != $EXT_LINK) // aktive Seite
                ) {
                    $detailmenu .= "<li class=\"detailmenu\"><a href=\"".$url.
                                                    "\" class=\"".$cssprefix."active\"".
                                                    $syntax->getTitleAttribute($language->getLanguageValue2("tooltip_link_page_2", pageToName($currentcontent, false), catToName($cat, false))).
                                                    ">".
                                                    pageToName($currentcontent, false).
                                                    "</a></li>";
                }
                // ...alle anderen als normalen Menuepunkt.
                else {
                    if(substr($currentcontent,-(strlen($EXT_LINK))) == $EXT_LINK) {
                        $detailmenu .= '<li class="detailmenu">'.menuLink($currentcontent,$cssprefix)."</li>";
                    } else {
                        $detailmenu .= "<li class=\"detailmenu\"><a href=\"".$url.
                                                    "\" class=\"".$cssprefix."\"".
                                                    $syntax->getTitleAttribute($language->getLanguageValue2("tooltip_link_page_2", pageToName($currentcontent, false), catToName($cat, false))).
                                                    ">".
                                                    pageToName($currentcontent, false).
                                                    "</a></li>";
            }
                }
            }
        }
        // Rueckgabe des Menues
        return $detailmenu . "</ul>";
    }


// ------------------------------------------------------------------------------
// Rueckgabe des Suchfeldes
// ------------------------------------------------------------------------------
    function getSearchForm(){
        global $language;
        global $CMS_CONF;
        global $specialchars;
        global $CHARSET;
        global $LAYOUT_DIR_URL;

        $modrewrite_dumy = NULL;
        if($CMS_CONF->get("modrewrite") == "true") {
            $modrewrite_dumy = ".html";
        }
        $form = "<form accept-charset=\"$CHARSET\" method=\"get\" action=\"index.php$modrewrite_dumy\" class=\"searchform\"><fieldset id=\"searchfieldset\">"
        ."<input type=\"hidden\" name=\"action\" value=\"search\" />"
        ."<input type=\"text\" name=\"query\" value=\"\" class=\"searchtextfield\" />"
        ."<input type=\"image\" name=\"action\" value=\"search\" src=\"".$LAYOUT_DIR_URL."/grafiken/searchicon.gif\" alt=\"".$language->getLanguageValue0("message_search_0")."\" class=\"searchbutton\"".getTitleAttribute($language->getLanguageValue0("message_search_0"))." />"
        ."</fieldset></form>";
        return $form;
    }


// ------------------------------------------------------------------------------
// Erzeugung einer Sitemap
// ------------------------------------------------------------------------------
    function getSiteMap() {
        global $CONTENT_DIR_REL;
        global $language;
        global $specialchars;
        global $CMS_CONF;
        global $EXT_LINK;
        global $URL_BASE;
        
        $showhiddenpages = ($CMS_CONF->get("showhiddenpagesinsitemap") == "true");
        
        $sitemap = "<h1>".$language->getLanguageValue0("message_sitemap_0")."</h1>"
        ."<div class=\"sitemap\">";
        // Kategorien-Verzeichnis einlesen
        $categoriesarray = getDirContentAsArray($CONTENT_DIR_REL, false, false);
        // Jedes Element des Arrays an die Sitemap anhaengen
        foreach ($categoriesarray as $currentcategory) {
            # ist ein link
            if(substr($currentcategory,-(strlen($EXT_LINK))) == $EXT_LINK) {
                continue;
            }
            // Wenn die Kategorie keine Contentseiten hat, zeige sie nicht an
            $contentarray = getDirContentAsArray($CONTENT_DIR_REL.$currentcategory, true, $showhiddenpages);
            if ($contentarray == "")
                continue;

            $sitemap .= "<h2>".catToName($currentcategory, false)."</h2><ul>";
            // Alle Inhaltsseiten der aktuellen Kategorie auflisten...
            // Jedes Element des Arrays an die Sitemap anhaengen
            foreach ($contentarray as $currentcontent) {
                # ist ein link
                if(substr($currentcontent,-(strlen($EXT_LINK))) == $EXT_LINK) {
                    continue;
                }
                $url = "index.php?cat=".substr($currentcategory,3)."&amp;page=".substr($currentcontent, 3, strlen($currentcontent) - 7);
                if($CMS_CONF->get("modrewrite") == "true") {
                    $url = $URL_BASE.substr($currentcategory,3)."/".substr($currentcontent, 3, strlen($currentcontent) - 7).".html";
                }
                $sitemap .= "<li><a href=\"$url\"".getTitleAttribute($language->getLanguageValue2("tooltip_link_page_2", pageToName($currentcontent, false), catToName($currentcategory, false))).">".
                                                    pageToName($currentcontent, false).
                                                    "</a></li>";
            }
            $sitemap .= "</ul>";
        }
        $sitemap .= "</div>";
        // Rueckgabe der Sitemap
        return array($sitemap, $language->getLanguageValue0("message_sitemap_0"), $language->getLanguageValue0("message_sitemap_0"));
    }

// ------------------------------------------------------------------------------
// E-Mail-Adressen verschleiern
// ------------------------------------------------------------------------------
// Dank fuer spam-me-not.php an Rolf Offermanns!
// Spam-me-not in JavaScript: http://www.zapyon.de
    function obfuscateAdress($originalString, $mode) {
        // $mode == 1            dezimales ASCII
        // $mode == 2            hexadezimales ASCII
        // $mode == 3            zufaellig gemischt
        $encodedString = "";
        $nowCodeString = "";
        $randomNumber = -1;

        $originalLength = strlen($originalString);
        $encodeMode = $mode;

        for ( $i = 0; $i < $originalLength; $i++) {
            if ($mode == 3) $encodeMode = rand(1,2);
            switch ($encodeMode) {
                case 1: // Decimal code
                    $nowCodeString = "&#" . ord($originalString[$i]) . ";";
                    break;
                case 2: // Hexadecimal code
                    $nowCodeString = "&#x" . dechex(ord($originalString[$i])) . ";";
                    break;
                default:
                    return "ERROR: wrong encoding mode.";
            }
            $encodedString .= $nowCodeString;
        }
        return $encodedString;
    }

// ------------------------------------------------------------------------------
// Rueckgabe des Website-Titels
// ------------------------------------------------------------------------------
    function getWebsiteTitle($websitetitle, $cattitle, $pagetitle) {
        global $CMS_CONF;
        global $specialchars;

        $title = $specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarformat"),true,true);
        $sep = $specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarseparator"),true,true);
        $title = str_replace('{WEBSITE}', $websitetitle, $title);
        if ($cattitle == "") {
            $title = str_replace('{CATEGORY}', "", $title);
        }
        else {
            $title = str_replace('{CATEGORY}', $cattitle, $title);
        }
        $title = str_replace('{PAGE}', $pagetitle, $title);
        $title = str_replace('{SEP}', $sep, $title);
        return $title;
    }



// ------------------------------------------------------------------------------
// Anzeige der Informationen zum System
// ------------------------------------------------------------------------------
    function getCmsInfo() {
        global $CMS_CONF;
        global $language;
        global $VERSION_CONF;
        return "<a href=\"http://cms.mozilo.de/\" target=\"_blank\" id=\"cmsinfolink\"".getTitleAttribute($language->getLanguageValue1("tooltip_link_extern_1", "http://cms.mozilo.de")).">moziloCMS ".$VERSION_CONF->get("cmsversion")."</a>";
    }


// ------------------------------------------------------------------------------
// Platzhalter im uebergebenen String ersetzen
// ------------------------------------------------------------------------------
    function replacePlaceholders($content, $cattitle, $pagetitle) {
        global $CMS_CONF;
        global $specialchars;
        global $CAT_REQUEST;
        global $PAGE_REQUEST;
        global $PAGE_FILE;
        global $EXT_PAGE;
        global $LAYOUT_DIR_URL;

        // Titel der Website
        $content = str_replace('{WEBSITE_NAME}', $specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"),false,true), $content);
        // Layout-Verzeichnis
        $content = str_replace('{LAYOUT_DIR}', $LAYOUT_DIR_URL, $content);

        if ($CAT_REQUEST != "") {
            // "unbehandelter" Name der aktuellen Kategorie ("10_M%FCllers%20Kuh")
            $content = str_replace('{CATEGORY}', $CAT_REQUEST, $content);
            // Aus dem "unbehandelter" Name der aktuellen Kategorie werden für die URL die % zu %25
            $content = str_replace('{CATEGORY_URL}', $specialchars->replaceSpecialChars($CAT_REQUEST,true), $content);
            // "sauberer" Name der aktuellen Kategorie ("Muellers Kuh")
            if(strpos("tmp".$content,'{CATEGORY_NAME}') !== false)
                $content = str_replace('{CATEGORY_NAME}', catToName($CAT_REQUEST, true), $content);
        }
        // Suche, Sitemap
        else {
            // "unbehandelter" Name der aktuellen Kategorie ("10_M%FCllers%20Kuh")
            $content = str_replace('{CATEGORY}', $cattitle, $content);
            // Aus dem "unbehandelter" Name der aktuellen Kategorie werden für die URL die % zu %25
            $content = str_replace('{CATEGORY_URL}', $specialchars->replaceSpecialChars($cattitle,true), $content);
            // "sauberer" Name der aktuellen Kategorie ("Muellers Kuh")
            $content = str_replace('{CATEGORY_NAME}', $cattitle, $content);
        }

        if ($PAGE_REQUEST != "") {
            // "unbehandelter" Name der aktuellen Inhaltsseite ("10_M%FCllers%20Kuh")
            $content = str_replace('{PAGE}', $PAGE_REQUEST, $content);
            // Aus dem "unbehandelter" Name der aktuellen Inhaltsseite werden für die URL die % zu %25
            $content = str_replace('{PAGE_URL}', $specialchars->replaceSpecialChars($PAGE_REQUEST,true), $content);
            // Dateiname der aktuellen Inhaltsseite ("10_M%FCllers%20Kuh.txt")
            $content = str_replace('{PAGE_FILE}', $PAGE_FILE, $content);
            // "sauberer" Name der aktuellen Inhaltsseite ("Muellers Kuh")
            if(strpos("tmp".$content,'{PAGE_NAME}') !== false)
                $content = str_replace('{PAGE_NAME}', pageToName($PAGE_FILE, true), $content);
            
        }
        // Suche, Sitemap
        else {
            // "unbehandelter" Name der aktuellen Inhaltsseite ("10_M-uuml-llers-nbsp-Kuh")
            $content = str_replace('{PAGE}', $pagetitle, $content);
            // Aus dem "unbehandelter" Name der aktuellen Inhaltsseite werden für die URL die % zu %25
            $content = str_replace('{PAGE_URL}', $specialchars->replaceSpecialChars($pagetitle,true), $content);
            // Dateiname der aktuellen Inhaltsseite ("10_M-uuml-llers-nbsp-Kuh.txt")
            $content = str_replace('{PAGE_FILE}', $pagetitle, $content);
            // "sauberer" Name der aktuellen Inhaltsseite ("Muellers Kuh")
            $content = str_replace('{PAGE_NAME}', $pagetitle, $content);
        }
        // ...und zurueckgeben
        return $content;
    }
    
// ------------------------------------------------------------------------------
// Handelt es sich um ein valides Verzeichnis / eine valide Datei?
// ------------------------------------------------------------------------------
    function isValidDirOrFile($file) {
	    # Alles, was einen Punkt vor der Datei hat
	    if(strpos($file,".") === 0) {
	        return false;
	    }
	    # alle PHP-Dateien
	    if(substr($file,-4) == ".php") {
	        return false;
	    }
	    # ...und der Rest
	    if(in_array($file, array(
	            "Thumbs.db", // Windows-spezifisch
	            "__MACOSX", // Mac-spezifisch
	            "settings" // Eclipse
	            ))) {
	        return false;
	    }
	    return true;
    }
/*
// ------------------------------------------------------------------------------
// Gibt das Kontaktformular zurueck
// ------------------------------------------------------------------------------
    function buildContactForm() {
        global $contactformconfig;
        global $language;
        global $CMS_CONF;
        global $WEBSITE_NAME;
        global $CAT_REQUEST;
        global $PAGE_REQUEST;
        global $CHARSET;
        global $specialchars;
        global $BASE_DIR_CMS;
        
        require_once($BASE_DIR_CMS."Mail.php");
        // existiert eine Mailadresse? Wenn nicht: Das Kontaktformular gar nicht anzeigen!
        if (strlen($contactformconfig->get("formularmail")) < 1) {
            return "<span class=\"deadlink\"".getTitleAttribute($language->getLanguageValue0("tooltip_no_mail_error_0")).">{CONTACT}</span>";
        }
        
        // Sollen die Spamschutz-Aufgaben verwendet werden?
        $usespamprotection = $contactformconfig->get("contactformusespamprotection") == "true";

        $config_name = explode(",", ($contactformconfig->get("name")));
        $config_mail = explode(",", ($contactformconfig->get("mail")));
        $config_website = explode(",", ($contactformconfig->get("website")));
        $config_message = explode(",", ($contactformconfig->get("message")));
        
        $mandatory = false;
        if(($config_name[2] == "true") or ($config_mail[2] == "true") or ($config_website[2] == "true") or ($config_message[2] == "true"))
            $mandatory = true;

        $errormessage = "";
        $form = "";
        
        if (isset($_SESSION['contactform_name'])) {
            $name       = getRequestParam($_SESSION['contactform_name'], false);
            $mail       = getRequestParam($_SESSION['contactform_mail'], false);
            $website    = getRequestParam($_SESSION['contactform_website'], false);
            $message    = getRequestParam($_SESSION['contactform_message'], false);
            $calcresult = getRequestParam($_SESSION['contactform_calculation'], false);
        }
        else {
            $name       = "";
            $mail       = "";
            $website    = "";
            $message    = "";
            $calcresult = "";
        }
        // Das Formular wurde abgesendet
        if (getRequestParam('submit', false) <> "") { 

            // Bot-Schutz: Wurde das Formular innerhalb von x Sekunden abgeschickt?
            $sendtime = $contactformconfig->get("contactformwaittime");
            if (($sendtime == "") || !preg_match("/^[\d+]+$/", $sendtime)) {
                $sendtime = 15;
            }
            if (time() - $_SESSION['contactform_loadtime'] < $sendtime) {
                $errormessage = $language->getLanguageValue1("contactform_senttoofast_1", $sendtime);
            }
            if ($usespamprotection) {
                // Nochmal Spamschutz: Ergebnis der Spamschutz-Aufgabe auswerten
                if (strtolower($calcresult) != strtolower($_SESSION['calculation_result'])) {
                    $errormessage = $language->getLanguageValue0("contactform_wrongresult_0");
                }
            }
            // Es ist ein Fehler aufgetreten!
            if ($errormessage == "") {
                // Eines der Pflichtfelder leer?
                if (($config_name[2] == "true") && ($name == "")) {
                    $errormessage = $language->getLanguageValue0("contactform_fieldnotset_0")." ".$language->getLanguageValue0("contactform_name_0");
                }
                else if (($config_mail[2] == "true") && ($mail == "")) {
                    $errormessage = $language->getLanguageValue0("contactform_fieldnotset_0")." ".$language->getLanguageValue0("contactform_mail_0");
                }
                else if (($config_website[2] == "true") && ($website == "")) {
                    $errormessage = $language->getLanguageValue0("contactform_fieldnotset_0")." ".$language->getLanguageValue0("contactform_website_0");
                }
                else if (($config_message[2] == "true") && ($message == "")) {
                    $errormessage = $language->getLanguageValue0("contactform_fieldnotset_0")." ".$language->getLanguageValue0("contactform_message_0");
                }
            }
            // Es ist ein Fehler aufgetreten!
            if ($errormessage <> "") {
                $form .= "<span id=\"contact_errormessage\">".$errormessage."</span>";
            }
            else {
                $mailcontent = "";
                if ($config_name[1] == "true") {
                    $mailcontent .= $language->getLanguageValue0("contactform_name_0").":\t".$name."\r\n";
                }
                if ($config_mail[1] == "true") {
                    $mailcontent .= $language->getLanguageValue0("contactform_mail_0").":\t".$mail."\r\n";
                }
                if ($config_website[1] == "true") {
                    $mailcontent .= $language->getLanguageValue0("contactform_website_0").":\t".$website."\r\n";
                }
                if ($config_message[1] == "true") {
                    $mailcontent .= "\r\n".$language->getLanguageValue0("contactform_message_0").":\r\n".$message."\r\n";
                }
                $mailsubject = $language->getLanguageValue1("contactform_mailsubject_1", $specialchars->getHtmlEntityDecode($WEBSITE_NAME));
                $mailsubject_confirm = $language->getLanguageValue1("contactform_mailsubject_confirm_1", $specialchars->getHtmlEntityDecode($WEBSITE_NAME));
                
                // Wenn Mail-Adresse im Formular gesetzt ist - versuchen Kopie dorthin zu senden
                if ($mail <> "") {
                    sendMail($mailsubject_confirm, $mailcontent, $contactformconfig->get("formularmail"), $mail, $contactformconfig->get("formularmail"));
                }
                // Mail an eingestellte Mail-Adresse (Mail-Absender muss auch diese Adresse sein,
                // sonst gibts kein Mail wenn der keine oder ungültige Adresse eingibt..
                sendMail($mailsubject, $mailcontent, $contactformconfig->get("formularmail"), $contactformconfig->get("formularmail"), $mail);
                $form .= "<span id=\"contact_successmessage\">".$language->getLanguageValue0("contactform_confirmation_0")."</span>";
                
                // Felder leeren
                $name = "";
                $mail = "";
                $website = "";
                $message = "";
            }
        }

        // Wenn das Formular nicht abgesendet wurde: die Feldnamen neu bestimmen
        else {
            renameContactInputs();
        }
        
        // aktuelle Zeit merken
        $_SESSION['contactform_loadtime'] = time();
        $action_para = "index.php";
        if($CMS_CONF->get("modrewrite") == "true") {
            $action_para = substr($PAGE_REQUEST,3).".html";
        }
        $form .= "<form accept-charset=\"$CHARSET\" method=\"post\" action=\"$action_para\" name=\"contact_form\" id=\"contact_form\">"
        ."<input type=\"hidden\" name=\"cat\" value=\"".substr($CAT_REQUEST,3)."\" />"
        ."<input type=\"hidden\" name=\"page\" value=\"".substr($PAGE_REQUEST,3)."\" />"
        ."<table id=\"contact_table\" summary=\"contact form table\">";
        if ($config_name[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            if ($config_name[0] != "") {
                $form .= "<tr><td style=\"padding-right:10px;\">".$specialchars->rebuildSpecialChars($config_name[0],false,true);
            } else {
                $form .= "<tr><td style=\"padding-right:10px;\">".$language->getLanguageValue0("contactform_name_0");
            }
            if ($config_name[2] == "true") {
                $form .= "*";
            }
            $form .= "</td><td><input type=\"text\" id=\"contact_name\" name=\"".$_SESSION['contactform_name']."\" value=\"".$name."\" /></td></tr>";
        }
        if ($config_mail[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            if ($config_mail[0] != "") {
                $form .= "<tr><td style=\"padding-right:10px;\">".$specialchars->rebuildSpecialChars($config_mail[0],false,true);
            } else {
                $form .= "<tr><td style=\"padding-right:10px;\">".$language->getLanguageValue0("contactform_mail_0");
            }
            if ($config_mail[2] == "true") {
                $form .= "*";
            }
            $form .= "</td><td><input type=\"text\" id=\"contact_mail\" name=\"".$_SESSION['contactform_mail']."\" value=\"".$mail."\" /></td></tr>";
        }
        if ($config_website[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            if ($config_website[0] != "") {
                $form .= "<tr><td style=\"padding-right:10px;\">".$specialchars->rebuildSpecialChars($config_website[0],false,true);
            } else {
                $form .= "<tr><td style=\"padding-right:10px;\">".$language->getLanguageValue0("contactform_website_0");
            }
            if ($config_website[2] == "true") {
                $form .= "*";
            }
            $form .= "</td><td><input type=\"text\" id=\"contact_website\" name=\"".$_SESSION['contactform_website']."\" value=\"".$website."\" /></td></tr>";
        }
        if ($config_message[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            if ($config_message[0] != "") {
                $form .= "<tr><td style=\"padding-right:10px;\">".$specialchars->rebuildSpecialChars($config_message[0],false,true);
            } else {
                $form .= "<tr><td style=\"padding-right:10px;\">".$language->getLanguageValue0("contactform_message_0");
            }
            if ($config_message[2] == "true") {
                $form .= "*";
            }
            $form .= "</td><td><textarea rows=\"10\" cols=\"50\" id=\"contact_message\" name=\"".$_SESSION['contactform_message']."\">".$message."</textarea></td></tr>";
        }
        if ($usespamprotection) {
            $mandatory = true;
            // Spamschutz-Aufgabe
            $calculation_data = getRandomCalculationData();
            $_SESSION['calculation_result'] = $calculation_data[1];
            $form .= "<tr><td colspan=\"2\">".$language->getLanguageValue0("contactform_spamprotection_text_0")."</td></tr>"
                ."<tr><td style=\"padding-right:10px;\">".$calculation_data[0]."*</td>"
                ."<td><input type=\"text\" id=\"contact_calculation\" name=\"".$_SESSION['contactform_calculation']."\" value=\"\" /></td></tr>";
            
        }
        if($mandatory)
            $form .= "<tr><td style=\"padding-right:10px;\">&nbsp;</td><td>".$language->getLanguageValue0("contactform_mandatory_fields_0")."</td></tr>";
        $form .= "<tr><td style=\"padding-right:10px;\">&nbsp;</td><td><input type=\"submit\" class=\"submit\" id=\"contact_submit\" name=\"submit\" value=\"".$language->getLanguageValue0("contactform_submit_0")."\" /></td></tr>";
        $form .= "</table>"
        ."</form>";
        
        return $form;
    }
*/
// ------------------------------------------------------------------------------
// Hilfsfunktion: Sichert einen Input-Wert
// ------------------------------------------------------------------------------
    function cleanInput($input) {
        global $CHARSET;
        if (function_exists("mb_convert_encoding")) {
            $input = @mb_convert_encoding($input, $CHARSET);
        }
        return $input;
    }

// ------------------------------------------------------------------------------    
// Alte Url wandeln
// ------------------------------------------------------------------------------
    function rebuildOldSpecialChars($oldurl) {
        global $specialchars;
        global $CMS_CONF;

        # wenn die numeriung im cat page ist weg damit
        if(preg_match("/\d\d_/", substr($oldurl,0,3)))
            $oldurl = substr($oldurl,3);
        # wenn keine alte -????~ sachen im cat page sind gleich raus hier
        if(!preg_match("/-\D+~/", $oldurl))
            return rawurldecode($oldurl);
        // Leerzeichen
        $oldurl = str_replace("-nbsp~", " ", $oldurl);
        // @, ?
        $oldurl = str_replace("-at~", "@", $oldurl);
        $oldurl = str_replace("-ques~", "?", $oldurl);
        // Alle mozilo-Entities in HTML-Entities umwandeln!
        $oldurl = preg_replace("/-([^-~]+)~/U", "&$1;", $oldurl);
        $oldurl = rawurldecode($specialchars->getHtmlEntityDecode($oldurl));
        return $oldurl;
    }
// ------------------------------------------------------------------------------
// Hilfsfunktion: Prueft einen Requestparameter
// ------------------------------------------------------------------------------
    function getRequestParam($param, $clean) {
        global $URL_BASE;
        global $CMS_CONF;

        # wenn in der url z.B. cat[]=Kategorie übergeben wurde
        if(isset($_REQUEST[$param]) and is_array($_REQUEST[$param]))
            return NULL;

        # auf Alte Url testen und gewandelt zurück geben
        if((isset($_REQUEST[$param])) and ($param == "cat" or $param == "page"))
            $_REQUEST[$param] = rebuildOldSpecialChars($_REQUEST[$param]);

        if(($CMS_CONF->get("modrewrite") == "true") and ($param == "cat" or $param == "page")) {
            $request = NULL;
            # ein hack für alte links
            if (isset($_REQUEST[$param])) {
                return $_REQUEST[$param];
            }

            # ein tmp dafor weil wenn $URL_BASE = / ist werden alle / ersetzt durch nichts
            $url_get = str_replace("tmp".$URL_BASE,"","tmp".$_SERVER['REQUEST_URI']);
            $url_get = str_replace("&amp;","&",$url_get);
            $QUERY_STRING = str_replace("&amp;","&",$_SERVER['QUERY_STRING']);
            $url_get = str_replace("?".$QUERY_STRING,"",$url_get);
            if($param == "cat") {
                $url_para = explode("/",$url_get);
                if(count($url_para) > 1) {
                    $request = $url_para[0];
                } else {
                    $request = substr($url_get,0,-5);
                }
            } elseif($param == "page") {
                $url_para = explode("/",$url_get);
                if(count($url_para) > 1) {
                    $request = substr($url_para[1],0,-5);
                } else {
                    $request = NULL;
                }
            }
            return $request;
        }
        if (isset($_REQUEST[$param])) {
            if ($clean) {
                return cleanInput(rawurldecode($_REQUEST[$param]));
            }
            else {
                return rawurldecode($_REQUEST[$param]);
            }
        }
        // Parameter ist nicht im Request vorhanden
        else {
            return "";
        }
    }
    

// ------------------------------------------------------------------------------
// Hilfsfunktion: "title"-Attribut zusammenbauen (oder nicht, wenn nicht konfiguriert)
// ------------------------------------------------------------------------------
    function getTitleAttribute($value) {
        global $CMS_CONF;
        if ($CMS_CONF->get("showsyntaxtooltips") == "true") {
            return " title=\"".$value."\"";
        }
        return "";
    }


// ------------------------------------------------------------------------------
// Rueckgabe der Dateinamen der vorigen und naechsten Seite
// ------------------------------------------------------------------------------
    function getNeighbourPages($page) {
        global $CONTENT_DIR_REL;
        global $CAT_REQUEST;
        global $CMS_CONF;
        global $EXT_LINK;
        
        // leer initialisieren
        $neighbourPages = array("", "");
        // aktuelle Kategorie einlesen
        $pagesarray = getDirContentAsArray($CONTENT_DIR_REL.$CAT_REQUEST, true, $CMS_CONF->get("showhiddenpagesincmsvariables") == "true");
        // Schleife ueber alle Seiten
        for ($i = 0; $i < count($pagesarray); $i++) {
            if(substr($pagesarray[$i], -(strlen($EXT_LINK))) == $EXT_LINK)
                continue;
            if ($page == substr($pagesarray[$i], 0, strlen($pagesarray[$i]) - 4)) {
                // vorige Seite (nur setzen, wenn aktuelle nicht die erste ist)
                if ($i > 0) {
                    $neighbourPages[0] = $pagesarray[$i-1];
                }
                // naechste Seite (nur setzen, wenn aktuelle nicht die letzte ist)
                if($i < count($pagesarray)-1) {
                    $neighbourPages[1] = $pagesarray[$i+1];
                }
                // Schleife kann abgebrochen werden
                break;
            }
        }

        return $neighbourPages;
    }

/*
// ------------------------------------------------------------------------------
// Hilfsfunktion: Zufaellige Spamschutz-Rechenaufgabe und deren Ergebnis zurueckgeben
// ------------------------------------------------------------------------------
    function getRandomCalculationData() {
        global $contactformcalcs;
        $confarray = $contactformcalcs->toArray();
        unset($confarray['readonly']);
        $tmp = array_keys($confarray);
        $randnum = rand(0, count($confarray)-1);
        return array($tmp[$randnum],$confarray[$tmp[$randnum]]);
    }
// ------------------------------------------------------------------------------
// Hilfsfunktion: Bestimmt die Inputnamen neu
// ------------------------------------------------------------------------------    
    function renameContactInputs() {
        $_SESSION['contactform_name'] = time()-rand(30, 40);
        $_SESSION['contactform_mail'] = time()-rand(10, 20);
        $_SESSION['contactform_website'] = time()-rand(0, 10);
        $_SESSION['contactform_message'] = time()-rand(40, 50);
        $_SESSION['contactform_calculation'] = time()-rand(50, 60);
    }
*/
    function findPlugins() {
        global $PLUGIN_DIR_REL;
        # Damit ein Platzhalter der als erste kommt erkant wierd
        $activ_plugins = array();
        $deactiv_plugins = array();
        // alle Plugins einlesen
        $dircontent = getDirContentAsArray($PLUGIN_DIR_REL, false, false);
        foreach ($dircontent as $currentelement) {
            # nach schauen ob das Plugin active ist
            if(file_exists($PLUGIN_DIR_REL.$currentelement."/plugin.conf")
                and file_exists($PLUGIN_DIR_REL.$currentelement."/index.php")) {
                $conf_plugin = new Properties($PLUGIN_DIR_REL.$currentelement."/plugin.conf",true);
                if($conf_plugin->get("active") == "false") {
                    # array fuehlen mit deactivierte Plugin Platzhalter
                    $deactiv_plugins[] = $currentelement;
                } elseif($conf_plugin->get("active") == "true") {
                    $activ_plugins[] = $currentelement;
                }
                unset($conf_plugin);
            }
        }
        return array($activ_plugins,$deactiv_plugins);
    }


/*
// ------------------------------------------------------------------------------
// Hilfsfunktion: Plugin-Variablen ersetzen
// ------------------------------------------------------------------------------    
    function replacePluginVariables($content,$activ_plugins,$deactiv_plugins) {
        global $PLUGIN_DIR_REL;
        global $syntax;
        global $language;
        global $URL_BASE;
        global $PLUGIN_DIR_NAME;

        # ab php > 5.2.0 hat preg_* ein default pcre.backtrack_limit von 100000 zeichen
        # deshalb der versuch mit ini_set
        @ini_set('pcre.backtrack_limit', 1000000);
        # alle script sachen rausnemen da könten verschachtelungen mit {} drin sein
        preg_match_all("/\<script(.*)\<\/script>/Umsi", $content, $java_script);

        if(count($java_script[0]) > 0) {
            foreach($java_script[0] as $pos => $script_match) {
                $content = str_replace($script_match,'<!-- plugin script '.$pos.'start -->',$content);
                $script[] = array('<!-- plugin script '.$pos.'start -->' => $script_match);
            }
        }
        # alle style sachen rausnemen da könten verschachtelungen mit {} drin sein
        preg_match_all("/\<style(.*)\<\/style>/Umsi", $content, $style);
        if(count($style[0]) > 0) {
            foreach($style[0] as $pos => $style_match) {
                $content = str_replace($style_match,'<!-- plugin style '.$pos.'start -->',$content);
                $script[] = array('<!-- plugin style '.$pos.'start -->' => $style_match);
            }
        }
        # alle Platzhalter mit Parameter suchen die in einer Verschachtelung sind
        preg_match_all("/\{([^\{\}]+)\|([^\{\}]*)\}/Um", $content, $matches);
        # wenn keine gefunden die ohne Parameter suchen
        if(count($matches[0]) <= 0) {
            preg_match_all("/\{([^\|\{]+)\}/Umsi", $content, $matches);
        }
        $notexit = 0;
        $css = array();
        while (count($matches[0]) > 0 and $notexit < 10) {
            # $matches[0] = {Plugin|Parameter}
            # $matches[1] = Plugin name
            # $matches[2] = Plugin Parameter
            foreach($matches[0] as $pos => $halter) {
                $match = $matches[0][$pos];
                $plugin = $matches[1][$pos];
                $plugin_parameter = "";
                if(isset($matches[2][$pos]))
                    $plugin_parameter = $matches[2][$pos];
                // ...ueberpruefen, ob es eine zugehörige Plugin-PHP-Datei gibt
                if(in_array($plugin, $activ_plugins)) {
                    $replacement = "";
                    if(file_exists($PLUGIN_DIR_REL.$plugin."/index.php")) {
                        // Plugin-Code includieren
                        require_once($PLUGIN_DIR_REL.$plugin."/index.php");
                    }
                    // Enthaelt der Code eine Klasse mit dem Namen des Plugins?
                    if(class_exists($plugin)) {
                        if(!in_array($plugin, $deactiv_plugins)) {
                            // Objekt instanziieren und Inhalt holen!
                            $currentpluginobject = new $plugin();
                            $replacement = $currentpluginobject->getPluginContent($plugin_parameter);
                        }
                    } else {
                        $replacement = str_replace(array('{','}','|'),array('~noplugin_start-','-noplugin_end~','-noplugin_grade~'),$match);
                    }
                    // Variable durch Plugin-Inhalt (oder Fehlermeldung) ersetzen
                    $content = str_replace($match,$replacement,$content);
                    if(!in_array($plugin, $deactiv_plugins)
                        and file_exists($PLUGIN_DIR_REL.$plugin."/plugin.css")
                        ) {
                        $css[] = '<style type="text/css"> @import "'.$URL_BASE.$PLUGIN_DIR_NAME.'/'.$plugin.'/plugin.css"; </style>';
                    }
                } elseif(in_array($plugin, $deactiv_plugins)) {
                    # Deactiviertes Plugin mit nichts ersetzen
                    $content = str_replace($match,"",$content);
                } else {
                    # Platzhalter nicht bekant
                    $noplugin = str_replace(array('{','}','|'),array('~noplugin_start-','-noplugin_end~','-noplugin_grade~'),$match);
                    $content = str_replace($match,$noplugin,$content);
                }

                # alle script sachen rausnemen da könten verschachtelungen mit {} drin sein
                preg_match_all("/\<script(.*)\<\/script>/Umsi", $content, $java_script);
                if(count($java_script[0]) > 0) {
                    foreach($java_script[0] as $scriptpos => $script_match) {
                        $content = str_replace($script_match,'<!-- plugin script '.$scriptpos.'drin'.$pos.' -->',$content);
                        $script[] = array('<!-- plugin script '.$scriptpos.'drin'.$pos.' -->' => $script_match);
                    }
                }
                # alle style sachen rausnemen da könten verschachtelungen mit {} drin sein
                preg_match_all("/\<style(.*)\<\/style>/Umsi", $content, $style);
                if(count($style[0]) > 0) {
                    foreach($style[0] as $stylepos => $style_match) {
                        $content = str_replace($style_match,'<!-- plugin style '.$stylepos.'drin'.$pos.' -->',$content);
                        $script[] = array('<!-- plugin style '.$stylepos.'drin'.$pos.' -->' => $style_match);
                    }
                }
            }
            # noch mal alle Platzhalter mit Parameter suchen die in einer Verschachtelung sind
            preg_match_all("/\{([^\{\}]+)\|([^\{\}]*)\}/Um", $content, $matches);
            # wenn keine gefunden die ohne Parameter suchen
            if(count($matches[0]) <= 0) {
                preg_match_all("/\{([^\|\{]+)\}/Umsi", $content, $matches);
            }
            $notexit++;
        }
        # alle script und style sachen wieder einsetzen
        if(isset($script) and is_array($script)) {
            foreach($script as $script_tmp) {
                $content = str_replace(key($script_tmp),$script_tmp[key($script_tmp)],$content);
            }
        }
        # alle nicht bekanten wieder herstellen
        $content = str_replace(array('~noplugin_start-','-noplugin_end~','-noplugin_grade~'),array('{','}','|'),$content);
        return array($content,$css);
    }
*/

    function menuLink($link,$css) {
        global $EXT_LINK;
        global $specialchars;
        global $syntax;
        global $language;

        if(!empty($css)) {
             $css = ' class="'.$css.'"';
        }
        $target = "_blank";
        if(strstr($link,"-_blank-")) {
            $tmp_link = explode("-_blank-",$link);
        }
        if(strstr($link,"-_self-")) {
            $tmp_link = explode("-_self-",$link);
            $target = "_self";
        }

        $tmp_link[1] = substr($tmp_link[1],0,-(strlen($EXT_LINK)));
        $titel = $syntax->getTitleAttribute($language->getLanguageValue1("tooltip_link_extern_1",$specialchars->rebuildSpecialChars($tmp_link[1], true, true)));
        return '<a href="'.$specialchars->rebuildSpecialChars($tmp_link[1], true, true).'"'.$css.' target="'.$target.'"'.$titel.'>'.$specialchars->rebuildSpecialChars(substr($tmp_link[0],3), true, true).'</a> ';
    }
    
?>