<?php

/* 
* 
* $Revision$
* $LastChangedDate$
* $Author$
*
*/



require_once("../SpecialChars.php");

$specialchars = new SpecialChars();
require_once("../Properties.php");
/* Variablen */

/**--------------------------------------------------------------------------------
 @author: Oliver Lorenz
 Liest aus dem Language-File eine Bistimmte Variable    aus.
 --------------------------------------------------------------------------------*/
function getLanguageValue($confpara)
{
    global $BASIC_LANGUAGE;
    global $CHARSET;

    $text = htmlentities($BASIC_LANGUAGE->get($confpara),ENT_COMPAT,$CHARSET);
    if(empty($text)) {
        return '<b style="color:#ff0000;">'.$confpara."</b> ".$BASIC_LANGUAGE->get('languagefile_error');
    }
    $text = str_replace(array("&lt;","&gt;"),array("<",">"), $text);
    return $text;
}

/**--------------------------------------------------------------------------------
 @author: Oliver Lorenz
 Gibt alle enthaltenen Ordner in ein Array aus
 --------------------------------------------------------------------------------*/
function getDirs($dir,$complet = false,$exclude_link = false)
{

    $vergeben = array();
    if (is_dir($dir))
    {
        $handle = opendir($dir);
        while($file = readdir($handle))
        {
            if($exclude_link !== false and preg_match('/-_blank-|-_self-/', $file)) {
                continue;
            }
            if(isValidDirOrFile($file) && !is_file("$dir/$file"))
            {
                if($complet === false)
                    array_push($vergeben, substr($file,0,2));
                else
                    array_push($vergeben,$file);
            }
        }
        closedir($handle);
    }
    sort($vergeben);
    return $vergeben;
}

/**--------------------------------------------------------------------------------
 @author: Arvid Zimmermann
 Gibt alle enthaltenen Dateien in ein Array aus
 --------------------------------------------------------------------------------*/
function getFiles($dir, $excludeextension)
{
    $dir = stripslashes($dir);
    $files = array();
    $handle = opendir($dir);
    while($file = readdir($handle)) {
        if(isValidDirOrFile($file) && ($file != "dateien")) {
            // auszuschließende Extensions nicht berücksichtigen
            if ($excludeextension != "") {
                if (substr($file, strlen($file)-4, strlen($file)) != "$excludeextension")
                array_push($files, $file);
            }
            else
                array_push($files, $file);
        }
    }
    closedir($handle);
    return $files;
}

/*--------------------------------------------------------------------------------
 @author: Oliver Lorenz
 Sucht nach einem Ordner der mit einer Bestimmten Nummern-Praefix beginnt
 --------------------------------------------------------------------------------*/
function specialNrDir($dir, $nr)
{
    $dir = stripslashes($dir);
    if (is_dir($dir)){
        $handle = opendir($dir);
        while($file = readdir($handle))
        {
            if(isValidDirOrFile($file) and is_dir("$dir/$file"))
            {
                if(substr($file,0,2)==$nr)
                {
                    closedir($handle);
                    return substr($file,3);
                }
            }
        }
    }
}

/*--------------------------------------------------------------------------------
 @author: Oliver Lorenz
 Legt die Ordnerstuktur für eine neue Kategorie an
 --------------------------------------------------------------------------------*/
function createCategory($new_cat) {
    global $specialchars;
    global $ADMIN_CONF;

    @mkdir ("../kategorien/".$new_cat);
    $line_error = __LINE__ - 1;
    $last_error = @error_get_last();
    if($last_error['line'] == $line_error) {
        $error['php_error'][] = $last_error['message'];
    } elseif(!is_dir("../kategorien/".$new_cat)) {
        $error['category_error_new'][] = $new_cat;
    }
    # ist kein Link
    if(!preg_match('/-_blank-|-_self-/', $new_cat)) {
        @mkdir ("../kategorien/".$new_cat."/dateien");
        $line_error = __LINE__ - 1;
        $last_error = @error_get_last();
        if($last_error['line'] == $line_error) {
            $error['php_error'][] = $last_error['message'];
        } elseif(!is_dir("../kategorien/".$new_cat."/dateien")) {
            $error['category_error_new'][] = $new_cat."/dateien";
        }
    }
    if(isset($error['php_error']) or isset($error['category_error_new'])) {
        # wenns hier schonn ne meldung gibt dann gleich Raus
        return $error;
    }
    # bis hier kein fehler dann solte das chmod auch fehlerfrei gehen
    useChmod("../kategorien/".$new_cat);
    # ist kein Link
    if(!preg_match('/-_blank-|-_self-/', $new_cat)) {
        useChmod("../kategorien/".$new_cat."/dateien");
    }
}

function getGalleriesAsSelect($selectedgallery) {
    global $specialchars;
    $dirs = array();
    $handle = opendir('../galerien');
    while (($file = readdir($handle))) {
        if (isValidDirOrFile($file))
        array_push($dirs, $file);
    }
    closedir($handle);
    sort($dirs);
    $select = "<select name=\"gal\">";
    foreach ($dirs as $file) {
        if (($selectedgallery <> "") && ($file == $selectedgallery))
        $select .= "<option selected=\"selected\" value=\"".$file."\">".$specialchars->rebuildSpecialChars($file, true, true)."</option>";
        else
        $select .= "<option value=\"".$file."\">".$specialchars->rebuildSpecialChars($file, true, true)."</option>";
    }
    $select .= "</select>";
    return $select;
}

// gibt Verzeichnisinhalte als Array zurück (ignoriert dabei Dateien, wenn $includefiles == true)
#function getDirContentAsArray($dir, $includefiles, $position = true) {
function getDirContentAsArray($dir, $hiddeposition = true) {
    $dircontent = array();
    if (is_dir($dir)) {
        $handle = opendir($dir);
        while($file = readdir($handle)) {
            if(isValidDirOrFile($file)) {
                // wenn $includefiles true ist, werden auch Dateien ins Array gesteckt; sonst nur Verzeichnisse
                if (!is_file("$dir/$file")) {
                    # wenn $hiddeposition = true keine Position
                    if($hiddeposition === true)
                        array_push($dircontent, substr($file,3));
                    else
                        array_push($dircontent, $file);
                }
            }
        }
        closedir($handle);
    }
    natcasesort($dircontent);
    return $dircontent;
}

function dirsize($dir) {
   if (!is_dir($dir) or !is_readable($dir)) return FALSE;
   $size = 0;
   $dh = opendir($dir);
   while(($entry = readdir($dh)) !== false) {
      if(!isValidDirOrFile($entry)) 
         continue;
      if(is_dir( $dir . "/" . $entry))
         $size += dirsize($dir . "/" . $entry);
      else
         $size += filesize($dir . "/" . $entry);
   }
   closedir($dh);
   return $size;
}

function convertFileSizeUnit($filesize){
    if ($filesize < 1024)
        return $filesize . "&nbsp;B";
    elseif ($filesize < 1048576)
        return round(($filesize/1024) , 2) . "&nbsp;KB";
    else
        return round(($filesize/1024/1024) , 2) . "&nbsp;MB";
}

// ------------------------------------------------------------------------------
// Handelt es sich um ein valides Verzeichnis / eine valide Datei?
// ------------------------------------------------------------------------------
function isValidDirOrFile($file) {
    # Alles was einen Punkt vor der Datei hat
    if(strpos($file,".") === 0) {
        return false;
    }
    # alle php Dateien
    if(substr($file,-4) == ".php") {
        return false;
    }
    # und der Rest
    if(in_array($file, array(
            "Thumbs.db", // Windows-spezifisch
            "__MACOSX", // Mac-spezifisch
            "settings" // Eclipse
            ))) {
        return false;
    }
    return true;
}


// ------------------------------------------------------------------------------
// Ändert Referenzen auf eine Inhaltsseite in allen anderen Inhaltsseiten
// ------------------------------------------------------------------------------
function updateReferencesInAllContentPages($oldCategory, $oldPage, $newCategory, $newPage) {
    # Wichtig !!!!!!
    # Rename CAT: $oldPage und $newPage müssen leer sein, $oldCategory und $newCategory aber gesetzt
    # Rename PAGE: $newCategory muss leer sein, $oldCategory, $oldPage und $newPage aber gesetzt
    # Move PAGE: Alle müssen gefüllt sein
    global $CONTENT_DIR_REL;

    $error = NULL;
    // Alle Kategorien einlesen
    $contentdirhandle = opendir($CONTENT_DIR_REL);
    while($currentcategory = readdir($contentdirhandle)) {
        if(isValidDirOrFile($currentcategory)) {
            // Alle Inhaltseiten der aktuellen Kategorie einlesen 
            $cathandle = opendir($CONTENT_DIR_REL."/".$currentcategory);
            while($currentpage = readdir($cathandle)) {
                if(isValidDirOrFile($currentpage) && is_file($CONTENT_DIR_REL."/".$currentcategory."/".$currentpage)) {
                    // Datei öffnen
                    $pagehandle = @fopen($CONTENT_DIR_REL."/".$currentcategory."/".$currentpage, "r");
                    // Inhalt auslesen
                    $pagecontent = @fread($pagehandle, @filesize($CONTENT_DIR_REL."/".$currentcategory."/".$currentpage));
                    // Datei schließen
                    @fclose($pagehandle);
                    // Referenzen im Inhalt ersetzen
                    $result = updateReferencesInText($pagecontent, $currentcategory, $currentpage, $oldCategory, $oldPage, $newCategory, $newPage);
                    // Ersetzung nur vornehmen, wenn überhaupt Referenzen auftauchen
                    if ($result[0]) {
                        // Inhaltsseite speichern
/*                        $error['updateReferences'] = saveContentToPage($result[1], $CONTENT_DIR_REL."/".$currentcategory."/".$currentpage);*/
                        $error_tmp = saveContentToPage($result[1], $CONTENT_DIR_REL."/".$currentcategory."/".$currentpage);
                        if(!empty($error_tmp)) {
                            if(is_array($error)) {
                                $error = array_merge_recursive($error,$error_tmp);
                            } else {
                                $error = $error_tmp;
                            }
                        }
                    }
                }
            }
            closedir($cathandle);
        }
    }
    closedir($contentdirhandle);
    return $error;
}
    
// ------------------------------------------------------------------------------
// Ändert Referenzen auf eine Inhaltsseite in einem übergebenen Text
// ------------------------------------------------------------------------------
function updateReferencesInText($currentPagesContent, $currentPagesCategory, $movedPage, $oldCategory, $oldPage, $newCategory, $newPage) {
    global $specialchars;
    global $CONTENT_DIR_REL;
    global $CHARSET;

    $pos_currentPagesCategory     = $specialchars->rebuildSpecialChars($currentPagesCategory,false,false);
    $pos_oldCategory        = $specialchars->rebuildSpecialChars($oldCategory,false,false);
    $pos_oldPage            = $specialchars->rebuildSpecialChars($oldPage,false,false);
    $pos_newCategory         = $specialchars->rebuildSpecialChars($newCategory,false,false);
    $pos_newPage             = $specialchars->rebuildSpecialChars($newPage,false,false);
    $movedPage             = $specialchars->rebuildSpecialChars($movedPage,false,false);

    $changesmade = false;

    # ein Hack weil in Inhaltsete ein ^ vor [ und ] ist im Dateinamen aber nicht
    $hack_eckigeklamern = str_replace(array("[","]"),array("&#94;[","&#94;]"),array($pos_oldCategory,$pos_oldPage,$pos_newCategory,$pos_newPage));

    $oldCategory    = html_entity_decode(substr($hack_eckigeklamern[0],3),ENT_COMPAT,$CHARSET);
    $oldPage    = html_entity_decode(substr($hack_eckigeklamern[1],3,-4),ENT_COMPAT,$CHARSET);
    $newCategory     = html_entity_decode(substr($hack_eckigeklamern[2],3),ENT_COMPAT,$CHARSET);
    $newPage     = html_entity_decode(substr($hack_eckigeklamern[3],3,-4),ENT_COMPAT,$CHARSET);

    # ein Hack weil dieses preg_match_all nicht mit ^, [ und ] im attribut umgehen kann
    $currentPagesContentmatches = str_replace(array("^[","^]"),array("&#94;&#091;","&#94;&#093;"),$currentPagesContent);
    // Nach Texten in eckigen Klammern suchen
    preg_match_all("/\[([^\[\]]+)\|([^\[\]]*)\]/Um", $currentPagesContentmatches, $matches);
    $i = 0;

    $allowed_attributes = array("seite","kategorie","datei","bild","bildlinks","bildrechts","include");

    // Für jeden Treffer...
$debug = true;
    foreach ($matches[0] as $match) {
if($debug) echo "alle matches = $match -----------<br>\n";
        # ein Hack weil dieses preg_match_all nicht mit ^, [ und ] im attribut umgehen kann
        $match = str_replace(array("&#94;&#091;","&#94;&#093;"),array("^[","^]"),$match);
        // ...Auswertung und Verarbeitung der Informationen
        $attribute = $matches[1][$i];
        $replace_match = "";
        if(strstr($attribute,"=")) {
            $allowed_test = substr($attribute,0,strpos($attribute,"="));

        } else {
            $allowed_test = $attribute;
        }
        if(in_array($allowed_test,$allowed_attributes))
        {
if($debug) echo "match = $match -----------<br>\n";
if($debug) echo "datei = $pos_currentPagesCategory/$movedPage<br>\n";
            # weil oldPage und newPage lehr sind Kategorie rename
            if(!empty($oldCategory) and !empty($newCategory) and empty($oldPage) and empty($newPage))
            {
                # einfach alle oldCategory -> newCategory
                if(strstr($match,"|".$oldCategory.":") or strstr($match,"|".$oldCategory."]"))
                {
                    $replace_match = str_replace($oldCategory,$newCategory,$match);
if($debug) echo "cat = $match -> $replace_match<br>\n";
                }
            }
            # weil newCategory lehr Inhaltseite rename
            if(!empty($oldCategory) and empty($newCategory) and !empty($oldPage) and !empty($newPage))
            {
                # ist [attribut|oldCategory:oldPage] dann oldPage -> newPage
                # oder ist [attribut|oldPage] und die untersuchende datei in oldCategory dann oldPage -> newPage
                if((strstr($match,"|$oldCategory:$oldPage]") or (strstr($match,"|$oldPage]")
                and $pos_oldCategory == $pos_currentPagesCategory )))
                {
                    $replace_match = str_replace($oldPage,$newPage,$match);
if($debug) echo "page = $match -> $replace_match<br>\n";
                }
            }
            # alles voll dann move Inhaltseite in andere Kategorie
            if(!empty($oldCategory) and !empty($newCategory) and !empty($oldPage) and !empty($newPage))
            {
                # weil in der zu bearbeitende Inhaltseite ein Object ist
                # das in alten Kategorie liegt neue Kategorie einfügen
                if($movedPage == $pos_newPage
                and !strstr($match,":")
                and $oldCategory != $newCategory)
                {
                    $replace_match = str_replace("|","|$oldCategory:",$match);
if($debug) echo "+++cat = $match -> $replace_match<br>\n";
                    }
                # weil in der zu bearbeitende Inhaltseite ein Object ist
                # das in der Kategorie liegt in die die Inhaltseite verschoben wird,
                # Kategorie entfernen
                elseif($movedPage == $pos_newPage
                and strstr($match,":")
                and $pos_currentPagesCategory == $pos_newCategory)
                {
                    $replace_match = str_replace("|$newCategory:","|",$match);
if($debug) echo "---cat = $match -> $replace_match<br>\n";
                }
                # alle andern Inhaltseiten die [attribut|oldCategory:oldPage] enthalten ändern
                elseif(strstr($match,"|$oldCategory:$oldPage]"))
                {
                    $replace_match = str_replace("$oldCategory:$oldPage","$newCategory:$newPage",$match);
if($debug) echo "cat_page = $match -> $replace_match<br>\n";
                }
            }
            # änderung nur wenn was geändert wurde
            if(!empty($replace_match) and $matches[0][$i] != $replace_match) {
                # ein Hack weil dieses preg_match_all nicht mit ^, [ und ] im attribut umgehen kann
                $matches[0][$i] = str_replace(array("&#94;&#091;","&#94;&#093;"),array("^[","^]"),$matches[0][$i]);
                $currentPagesContent = str_replace ($matches[0][$i], $replace_match, $currentPagesContent);
if($debug) echo "diff == match = ".$matches[0][$i]." | replace_match = $replace_match<br>\n";
                $changesmade = true;
            }
if($debug) echo "<br>\n";
        }    
    $i++;
    }
    // Konvertierten Seiteninhalt zurückgeben
    return array($changesmade, $currentPagesContent);
}
/**/
function getChmod($dir = false) {
    global $ADMIN_CONF;
    $mode = $ADMIN_CONF->get("chmodnewfilesatts");
    if(strlen($mode) > 0) {
        if($dir === true) {
            // X-Bit setzen, um Verzeichniszugriff zu garantieren
            if(substr($mode,0,1) >= 2 and substr($mode,0,1) <= 6) $mode = $mode + 100;
            if(substr($mode,1,1) >= 2 and substr($mode,1,1) <= 6) $mode = $mode + 10;
            if(substr($mode,2,1) >= 2 and substr($mode,2,1) <= 6) $mode = $mode + 1;
        }
        return octdec($mode);
    }
    # Der server Vergibt die Rechte
    return false;
}

function changeChmod($file) {
    $error_new = NULL;
    $dir = NULL;
    if(is_dir($file)) {
        $dir = true;
    }
    # nicht zu tuhn
    if(getChmod() === false) {
        return $error_new;
    }
    @chmod($file, getChmod($dir));
    $line_error = __LINE__ - 1; # wichtig direckt nach Befehl
    $last_error = @error_get_last();
    # clearstatcache() damit fileperms() sauber Arbeitet
    clearstatcache();
    if($last_error['line'] == $line_error) {
        # dummy fehlermeldung erzeugen
        @chmod();
        $error_new['php_error'] = $file." - ".$last_error['message'];
    } elseif(substr(decoct(fileperms($file)), -3) != decoct(getChmod($dir))) {
        $error_new['chmod_error'] = $file;
    }
    return $error_new;
}

function useChmod($dir = false, $error = NULL) {
    global $error;

    if($dir === false) {
        $ordner = array("conf",
                        "../conf",
                        "../kategorien","../galerien");
        foreach($ordner as $dirs) {
            $error_tmp = useChmod($dirs,$error);
            if(is_array($error_tmp)) {
                $error[key($error_tmp)] = $error_tmp[key($error_tmp)];
            }
        }
        return $error;
    } else {
        # nicht zu tuhn
        if(getChmod() === false) {
            return;
        }
        if(is_dir($dir)) {
            $error_tmp = changeChmod($dir);
            if(is_array($error_tmp)) {
                $error[key($error_tmp)][] = $error_tmp[key($error_tmp)];
            }
            $handle = opendir($dir);
            while($file = readdir($handle)) {
                if(isValidDirOrFile($file)) {
                    if(is_dir($dir.'/'.$file)) {
                        $error_tmp = useChmod($dir.'/'.$file,$error);
                        if(is_array($error_tmp)) {
                            $error[key($error_tmp)] = $error_tmp[key($error_tmp)];
                        }
                    } elseif(is_file($dir.'/'.$file)) {
                        $error_tmp = changeChmod($dir.'/'.$file);
                        if(is_array($error_tmp)) {
                            $error[key($error_tmp)][] = $error_tmp[key($error_tmp)];
                        }
                    }
                }
            }
            closedir($handle);
        } elseif(is_file($dir)) {
            $error_tmp = changeChmod($dir);
            if(is_array($error_tmp)) {
                $error[key($error_tmp)][] = $error_tmp[key($error_tmp)];
            }
        }
        return $error;
    }
}

# $conf_datei = voller pfad und conf Dateiname oder nur Array Name
function makeDefaultConf($conf_datei) {
    $basic = array(
                    'text' => array(
                        'adminmail' => 'admin%40mozilo.cms',
                        'language' => 'deDE',
                        'noupload' => 'php,php3,php4,php5'),
                    'digit' => array(
                        'backupmsgintervall' => '30',
                        'chmodnewfilesatts' => '',
                        'lastbackup' => time(),
                        'maximageheight' => '',
                        'maximagewidth' => '',
                        'maxnumberofuploadfiles' => '5',
                        'textareaheight' => '270'),
                    'checkbox' => array(
                        'overwriteuploadfiles' => 'false',
                        'sendadminmail' => 'true',
                        'showTooltips' => 'true',
                        'usebigactionicons' => 'false',
                        'showexpert' => 'false'),
                    # das sind die Expert Parameter von basic
                    'expert' => array(
                        'noupload',
                        'backupmsgintervall',
                        'lastbackup',
                        'maxnumberofuploadfiles',
                        'showTooltips',
                        'textareaheight',
                        'usebigactionicons',
                        'overwriteuploadfiles')
                    );

    $main = array(
                    'text' => array(
                        'shortenlinks' => '0',
                        'titlebarseparator' => '%20%3A%3A%20',
                        'usesubmenu' => '1',
                        'websitedescription' => '',
                        'websitekeywords' => '',
                        'websitetitle' => 'moziloCMS%20-%20Das%20CMS%20f%FCr%20Einsteiger'),
                    'select' => array(
                        'cmslanguage' => 'Deutsch',
                        'cmslayout' => 'moziloCMS%202009',
                        'defaultcat' => '',
                        'menu2' => 'no_menu2',
                        'titlebarformat' => '%7BWEBSITE%7D'),
                    'checkbox' => array(
                        'hidecatnamedpages' => 'false',
                        'modrewrite' => 'false',
                        'replaceemoticons' => 'true',
                        'showhiddenpagesinlastchanged' => 'false',
                        'showhiddenpagesinsearch' => 'false',
                        'showhiddenpagesinsitemap' => 'false',
                        'showsyntaxtooltips' => 'true',
                        'targetblank_download' => 'true',
                        'targetblank_gallery' => 'true',
                        'targetblank_link' => 'true',
                        'usecmssyntax' => 'true'),
                    # das sind die Expert Parameter von main
                    'expert' => array(
                        'hidecatnamedpages',
                        'modrewrite',
                        'showhiddenpagesinlastchanged',
                        'showhiddenpagesinsearch',
                        'showhiddenpagesinsitemap',
                        'targetblank_download',
                        'targetblank_link',
                        'showsyntaxtooltips',
                        'replaceemoticons',
                        'shortenlinks',
                        'usecmssyntax',
                        'usesubmenu')
                    );

    $syntax = array('wikipedia' => '[link={DESCRIPTION}|http://de.wikipedia.org/wiki/{VALUE}]');

    $formular = array('formularmail' => '',
                        'contactformusespamprotection' => 'true',
                        'contactformwaittime' => '15',
                        'mail' => ',true,true',
                        'message' => ',true,true',
                        'name' => ',true,true',
                        'website' => ',true,true');

    $logindata = array('falselogincount' => '0',
                        'falselogincounttemp' => '0',
                        'initialpw' => 'true',
                        'initialsetup' => 'true',
                        'loginlockstarttime' => '',
                        'name' => 'admin',
                        'pw' => '19ad89bc3e3c9d7ef68b89523eff1987');

    $downloads = array('_downloadcounterstarttime' => time());

    $version = array('cmsversion' => '1.12',
                        'cmsname' => 'Amalia');

    $gallery = array('digit' => array(
                        'maxheight' => '',
                        'maxwidth' => '',
                        'maxthumbheight' => '100',
                        'maxthumbwidth' => '100',
                        'gallerypicsperrow' => '4'),
                    'checkbox' => array(
                        'usethumbs' => 'true', # reihen folge ist wichtig
                        'usedfgallery' => 'false'), # reihen folge ist wichtig
                    'text' => array(
                        'target' => '_blank'),
                    'expert' => array(
                        'usedfgallery' => 'false',
                        'usethumbs' => 'true',
                        'maxthumbheight' => '100',
                        'maxthumbwidth' => '100',
                        'gallerypicsperrow' => '4')
                    );

    $aufgaben = array('3 + 7' => '10',
                        '5 - 3' => '2',
                        '1 plus 1' => '2',
                        '17 minus 7' => '10',
                        '4 * 2' => '8',
                        '3x3' => '9',
                        '2 durch 2' => '1',
                        'Elvis Presleys Vorname' => 'Elvis',
                        'Angela Merkels Nachname' => 'Merkel',
                        'Bronze, Silber, ...?' => 'Gold');

    $passwords = array('# Kategorie/Inhaltsseite' => 'password');


    if(strpos($conf_datei,".conf") > 0) {
        $name = substr(basename($conf_datei),0,-(strlen(".conf")));
        # beim erzeugen duerfen sub arrays nicht mit rein
        foreach($$name as $key => $value) {
            if($key == "expert") continue;
            if(is_array($value)) {
                foreach($value as $key => $value) {
                    $return_array[$key] = $value;
                }
            } else {
                $return_array = $$name;
                break;
            }
        }
        return $return_array;
    } else {
        return $$conf_datei;
    }
}


?>
