<?php

/* 
* 
* $Revision$
* $LastChangedDate$
* $Author$
*
*/

require_once("Language.php");
require_once("Properties.php");
require_once("SpecialChars.php");

// Initial: Fehlerausgabe unterdr�cken, um Path-Disclosure-Attacken ins Leere laufen zu lassen
# @ini_set("display_errors", 0);

$language = new Language();
$mainconf = new Properties("conf/main.conf");
$specialchars = new SpecialChars();

// Vorschaubilder nach Benutzereinstellung und wenn GDlib installiert
if (!extension_loaded("gd"))
    $mainconf->set("galleryusethumbs", "false");
if ($mainconf->get("galleryusethumbs") == "true")
    $USETHUMBS = true;
else
    $USETHUMBS = false;

$MAX_IMG_WIDTH             = $mainconf->get("gallerymaxwidth");
if ($MAX_IMG_WIDTH == "")
    $MAX_IMG_WIDTH = 500;

$MAX_IMG_HEIGHT         = $mainconf->get("gallerymaxheight");
if ($MAX_IMG_HEIGHT == "")
    $MAX_IMG_HEIGHT = 350;

$WEBSITE_TITLE            = $mainconf->get("websitetitle");
if ($WEBSITE_TITLE == "")
    $WEBSITE_TITLE = "Titel der Website";

$LAYOUT_DIR         = $mainconf->get("cmslayout");
$TEMPLATE_FILE      = "layouts/$LAYOUT_DIR/gallerytemplate.html";
$CSS_FILE           = "layouts/$LAYOUT_DIR/css/style.css";
$FAVICON_FILE       = "layouts/$LAYOUT_DIR/favicon.ico";

// �bergebene Parameter �berpr�fen
$GAL_REQUEST        = $specialchars->replaceSpecialChars($_GET['gal'],false);
$DIR_GALLERY        = "./galerien/".$GAL_REQUEST."/";
$DIR_THUMBS         = $DIR_GALLERY."vorschau/";
if (($GAL_REQUEST == "") || (!file_exists($DIR_GALLERY))) {
    die ($language->getLanguageValue1("message_gallerydir_error_1", $GAL_REQUEST));
}
$GAL_NAME           = $specialchars->rebuildSpecialChars($GAL_REQUEST, true, true);

// Galerieverzeichnis einlesen
$PICARRAY = getPicsAsArray($DIR_GALLERY, array("jpg", "jpeg", "jpe", "gif", "png", "svg"));
$ALLINDEXES = array();
for ($i=1; $i<=count($PICARRAY); $i++) 
    array_push($ALLINDEXES, $i);
// globaler Index
if ((!isset($_GET['index'])) || (!in_array($_GET['index'], $ALLINDEXES)))
    $INDEX = 1;
else
    $INDEX = $_GET['index'];

// Bestimmung der Positionen
$FIRST = 1;
$LAST = count($ALLINDEXES);
if (!in_array($INDEX-1, $ALLINDEXES))
    $BEFORE = $LAST;
else
    $BEFORE = $INDEX-1;
if (!in_array($INDEX+1, $ALLINDEXES))
    $NEXT = 1;
else
    $NEXT = $INDEX+1;
    
if ($USETHUMBS) {
    checkThumbs();
    $THUMBARRAY = getPicsAsArray($DIR_THUMBS, array("jpg", "jpeg", "jpe", "gif", "png", "svg"));
}

// Galerie aufbauen und ausgeben
$HTML = "";
readTemplate();
echo $HTML;




// ------------------------------------------------------------------------------
// HTML-Template einlesen und verarbeiten
// ------------------------------------------------------------------------------
    function readTemplate() {
        global $CSS_FILE;
        global $LAYOUT_DIR;
        global $GAL_NAME;
        global $HTML;
        global $FAVICON_FILE;
        global $PICARRAY;
        global $INDEX;
        global $specialchars;
        global $TEMPLATE_FILE;
        global $USE_CMS_SYNTAX;
        global $USETHUMBS;
        global $WEBSITE_TITLE;
        global $language;
        global $mainconf;
        
        // Template-Datei auslesen
        if (!$file = @fopen($TEMPLATE_FILE, "r")) {
            die($language->getLanguageValue1("message_template_error_1", $TEMPLATE_FILE));
        }
        $template = fread($file, filesize($TEMPLATE_FILE));
        fclose($file);
        
        // Platzhalter des Templates mit Inhalt f�llen
        $HTML = preg_replace('/{CSS_FILE}/', $specialchars->replaceSpecialChars($CSS_FILE, true), $template);
        $HTML = preg_replace('/{FAVICON_FILE}/', $specialchars->replaceSpecialChars($FAVICON_FILE, true), $HTML);
        $HTML = preg_replace('/{LAYOUT_DIR}/', $specialchars->replaceSpecialChars($LAYOUT_DIR, true), $HTML);
        $HTML = preg_replace('/{CMSINFO}/', getCmsInfo(), $HTML);
        $HTML = preg_replace('/{WEBSITE_TITLE}/', getWebsiteTitle($WEBSITE_TITLE, $language->getLanguageValue0("message_galleries_0"), $GAL_NAME), $HTML);
        $HTML = preg_replace('/{CURRENTGALLERY}/', $language->getLanguageValue1("message_gallery_1", $GAL_NAME), $HTML);
        if (count($PICARRAY) == 0) {
            $HTML = preg_replace('/{NUMBERMENU}/', $language->getLanguageValue0("message_galleryempty_0"), $HTML);
        }
        if ($USETHUMBS) {
            $HTML = preg_replace('/{GALLERYMENU}/', "&nbsp;", $HTML);
            $HTML = preg_replace('/{NUMBERMENU}/', getThumbnails(), $HTML);
            $HTML = preg_replace('/{CURRENTPIC}/', "&nbsp;", $HTML);
            $HTML = preg_replace('/{CURRENTDESCRIPTION}/', "&nbsp;", $HTML);
            $HTML = preg_replace('/{XOUTOFY}/', "&nbsp;", $HTML);
        }
        else {
            $HTML = preg_replace('/{GALLERYMENU}/', getGalleryMenu(), $HTML);
            $HTML = preg_replace('/{NUMBERMENU}/', getNumberMenu(), $HTML);
            $HTML = preg_replace('/{CURRENTPIC}/', getCurrentPic(), $HTML);
            if (count($PICARRAY) > 0) {
                $HTML = preg_replace('/{CURRENTDESCRIPTION}/', getCurrentDescription($PICARRAY[$INDEX-1]), $HTML);
            } else {
                $HTML = preg_replace('/{CURRENTDESCRIPTION}/', "", $HTML);
            }
            $HTML = preg_replace('/{XOUTOFY}/', getXoutofY(), $HTML);
        }
    }
    
    
// ------------------------------------------------------------------------------
// Galeriemen� erzeugen
// ------------------------------------------------------------------------------
    function getGalleryMenu() {
        global $ALLINDEXES;
        global $BEFORE;
        global $GAL_REQUEST;
        global $FIRST;
        global $INDEX;
        global $PICARRAY;
        global $LAST;
        global $NEXT;
        global $language;
        
        // Keine Bilder im Galerieverzeichnis?
        if (count($PICARRAY) == 0)
            return "&nbsp;";
        
        $gallerymenu = "<ul class=\"gallerymenu\">";
        
        // Link "Erstes Bild"
        if ($INDEX == $FIRST)
            $linkclass = "gallerymenuactive";
        else
            $linkclass = "gallerymenu";
        $gallerymenu .= "<li class=\"gallerymenu\"><a href=\"gallery.php?gal=$GAL_REQUEST&amp;index=$FIRST\" class=\"$linkclass\">".$language->getLanguageValue0("message_firstimage_0")."</a></li>";
        // Link "Voriges Bild"
        $gallerymenu .= "<li class=\"gallerymenu\"><a href=\"gallery.php?gal=$GAL_REQUEST&amp;index=$BEFORE\" class=\"detailmenu\">".$language->getLanguageValue0("message_previousimage_0")."</a></li>";
        // Link "N�chstes Bild"
        $gallerymenu .= "<li class=\"gallerymenu\"><a href=\"gallery.php?gal=$GAL_REQUEST&amp;index=$NEXT\" class=\"detailmenu\">".$language->getLanguageValue0("message_nextimage_0")."</a></li>";
        // Link "Letztes Bild"
        if ($INDEX == $LAST)
            $linkclass = "gallerymenuactive";
        else
            $linkclass = "gallerymenu";
        $gallerymenu .= "<li class=\"gallerymenu\"><a href=\"gallery.php?gal=$GAL_REQUEST&amp;index=$LAST\" class=\"$linkclass\">".$language->getLanguageValue0("message_lastimage_0")."</a></li>";
        // R�ckgabe des Men�s
        return $gallerymenu."</ul>";
    }
    
    
// ------------------------------------------------------------------------------
// Nummernmen� erzeugen
// ------------------------------------------------------------------------------
    function getNumberMenu() {
        global $mainconf;
        global $GAL_REQUEST;
        global $FIRST;
        global $INDEX;
        global $LAST;
        global $PICARRAY;

        // Keine Bilder im Galerieverzeichnis?
        if (count($PICARRAY) == 0)
            return "&nbsp;";

        $numbermenu = "";
        for ($i=$FIRST; $i<=$LAST; $i++) {
            if ($INDEX == $i)
                    $numbermenu .= "<em class=\"bold\">".$i."</em> | ";
            else
                    $numbermenu .= "<a href=\"gallery.php?gal=".$GAL_REQUEST."&amp;index=".$i."\">".$i."</a> | ";
        }
        // R�ckgabe des Men�s
        return substr($numbermenu, 0, strlen($numbermenu)-2);
    }
    

// ------------------------------------------------------------------------------
// Nummernmen� erzeugen
// ------------------------------------------------------------------------------
    function getThumbnails() {
        global $DIR_GALLERY;
        global $DIR_THUMBS;
        global $PICARRAY;
        global $THUMBARRAY;
        global $language;
        global $mainconf;
        global $specialchars;
        // Aus Config auslesen: Wieviele Bilder pro Tabellenzeile?
        $picsperrow = $mainconf->get("gallerypicsperrow");
        if (($picsperrow == "") || ($picsperrow == 0))
            $picsperrow = 4;

        $thumbs = "<table class=\"gallerytable\" summary=\"gallery table\"><tr>";
        $i = 0;
        for ($i=0; $i<count($THUMBARRAY); $i++) {
            // Bildbeschreibung holen
            $description = getCurrentDescription($THUMBARRAY[$i]);
            if ($description == "")
                $description = "&nbsp;";
            // Neue Tabellenzeile aller picsperrow Zeichen
            if (($i > 0) && ($i % $picsperrow == 0))
                $thumbs .= "</tr><tr>";
            $thumbs .= "<td class=\"gallerytd\" style=\"width:".floor(100 / $picsperrow)."%;\">"
            ."<a href=\"".$specialchars->replaceSpecialChars($DIR_GALLERY.$PICARRAY[$i],true)."\" target=\"_blank\" title=\"".$language->getLanguageValue1("tooltip_gallery_fullscreen_1", $specialchars->rebuildSpecialChars($PICARRAY[$i],true,true))."\">"
            ."<img src=\"".$specialchars->replaceSpecialChars($DIR_THUMBS.$THUMBARRAY[$i],true)."\" alt=\"".$specialchars->rebuildSpecialChars($THUMBARRAY[$i],true,true)."\" class=\"thumbnail\" />"
            ."</a><br />"
            .$description
            ."</td>";
        }
        while ($i % $picsperrow > 0) {
            $thumbs .= "<td class=\"gallerytd\">&nbsp;</td>";
            $i++;
        }
        $thumbs .= "</tr></table>";
        // R�ckgabe der Thumbnails
        return $thumbs;
    }
    
    
// ------------------------------------------------------------------------------
// Aktuelles Bild anzeigen
// ------------------------------------------------------------------------------
    function getCurrentPic() {
        global $DIR_GALLERY;
        global $INDEX;
        global $MAX_IMG_HEIGHT;
        global $MAX_IMG_WIDTH;
        global $PICARRAY;
        global $language;
        global $specialchars;
        // Keine Bilder im Galerieverzeichnis?
        if (count($PICARRAY) == 0)
            return "&nbsp;";
        // Link zur Vollbildansicht �ffnen
        $currentpic = "<a href=\"".$specialchars->replaceSpecialChars($DIR_GALLERY.$PICARRAY[$INDEX-1],true)."\" target=\"_blank\" title=\"".$language->getLanguageValue1("tooltip_gallery_fullscreen_1", $specialchars->rebuildSpecialChars($PICARRAY[$INDEX-1],true,true))."\">";
        // Bilder f�r die Anzeige skalieren
        if (extension_loaded('gd')) {
            $size = getimagesize($DIR_GALLERY.$PICARRAY[$INDEX-1]);
            $w = $size[0];
            $h = $size[1];
            // Breite skalieren
            if ($w > $MAX_IMG_WIDTH) {
                $w=$MAX_IMG_WIDTH;
                $h=round(($MAX_IMG_WIDTH*$size[1])/$size[0]);
            }
            // H�he skalieren
            if ($h > $MAX_IMG_HEIGHT){
                $h=$MAX_IMG_HEIGHT;
                $w=round(($MAX_IMG_HEIGHT*$size[0])/$size[1]);
            }
            $currentpic .= "<img src=\"".$specialchars->replaceSpecialChars($DIR_GALLERY.$PICARRAY[$INDEX-1],true)."\" alt=\"".$language->getLanguageValue1("alttext_galleryimage_1", $specialchars->rebuildSpecialChars($PICARRAY[$INDEX-1],true,true))."\"  style=\"width:".$w."px;height:".$h."px;\" />";
        }
        else
            $currentpic .= "<img src=\"".$specialchars->replaceSpecialChars($DIR_GALLERY.$PICARRAY[$INDEX-1],true)."\" alt=\"".$language->getLanguageValue1("alttext_galleryimage_1", $specialchars->rebuildSpecialChars($PICARRAY[$INDEX-1],true,true))."\"  style=\"max-width:".$MAX_IMG_WIDTH."px;max-height:".$MAX_IMG_HEIGHT."px;\" />";
            // Link zur Vollbildansicht schlie�en
            $currentpic .= "</a>";
        // R�ckgabe des Bildes
        return $currentpic;
    }
    
    
// ------------------------------------------------------------------------------
// Beschreibung zum aktuellen Bild anzeigen
// ------------------------------------------------------------------------------
    function getCurrentDescription($picname) {
        global $DIR_GALLERY;
        global $INDEX;
        global $PICARRAY;
        // Keine Bilder im Galerieverzeichnis?
        if (count($PICARRAY) == 0)
            return "&nbsp;";
        // Texte einlesen
        $alldescriptions = new Properties($DIR_GALLERY."texte.conf");
        $description = $alldescriptions->get($picname);
        if(strlen($description) > 0) {
            return htmlentities($description,ENT_COMPAT,'ISO-8859-1');
        } else {
            return "&nbsp;";
        }
    }


// ------------------------------------------------------------------------------
// Position in der Galerie anzeigen
// ------------------------------------------------------------------------------
    function getXoutofY() {
        global $INDEX;
        global $LAST;
        global $PICARRAY;
        global $language;
        // Keine Bilder im Galerieverzeichnis?
        if (count($PICARRAY) == 0)
            return "&nbsp;";
        return $language->getLanguageValue2("message_gallery_xoutofy_2", $INDEX, $LAST);
    }
    
    
// ------------------------------------------------------------------------------
// Auslesen des �bergebenen Galerieverzeichnisses, R�ckgabe als Array
// ------------------------------------------------------------------------------
function getPicsAsArray($dir, $filetypes) {
    $picarray = array();
    $currentdir = opendir($dir);
    // Alle Dateien des �bergebenen Verzeichnisses einlesen...
    while ($file = readdir($currentdir)){
        // ...�berpr�fen, ob die aktuelle Datei weder "." noch ".." ist und eine erlaubte Endung besitzt (Array "$filetypes")
        if (($file <> ".") && ($file <> "..") && (in_array(strtolower(substr(strrchr($file, "."), 1, strlen(strrchr($file, "."))-1)), $filetypes))) {
            // ...wenn alles pa�t, ans Bilder-Array anh�ngen
            array_push($picarray, $file);
    }
    }
    closedir($currentdir);
    sort($picarray);
    return $picarray;
}


// ------------------------------------------------------------------------------
// Pr�fen, ob alle Thumbnails vorhanden sind; evtl. anlegen
// ------------------------------------------------------------------------------
function checkThumbs() {
    // Thumbnail-Funktionalit�t
    require_once("Thumbnail.php");
    $thumbnailfunction = new Thumbnail();

    global $DIR_GALLERY;
    global $DIR_THUMBS;
    global $PICARRAY;
    global $language;
    
    // Vorschauverzeichnis pr�fen
    if (!file_exists($DIR_THUMBS))
        die ($language->getLanguageValue1("tooltip_link_category_error_1", $DIR_THUMBS));
    // alle Bilder �berpr�fen: Vorschau dazu vorhanden?
    foreach($PICARRAY as $pic) {
        // Vorschaubild anlegen, wenn nicht vorhanden
        if (!file_exists($DIR_THUMBS.$pic))
            $thumbnailfunction->createThumb($pic, $DIR_GALLERY, $DIR_THUMBS);
    }
}


// ------------------------------------------------------------------------------
// R�ckgabe des Website-Titels
// ------------------------------------------------------------------------------
    function getWebsiteTitle($websitetitle, $cattitle, $pagetitle) {
        global $mainconf;

        $title = $mainconf->get("titlebarformat");
        $sep = $mainconf->get("titlebarseparator");
        
    $title = preg_replace('/{WEBSITE}/', $websitetitle, $title);
        if ($cattitle == "")
            $title = preg_replace('/{CATEGORY}/', "", $title);
        else
            $title = preg_replace('/{CATEGORY}/', $cattitle, $title);
    $title = preg_replace('/{PAGE}/', $pagetitle, $title);
    $title = preg_replace('/{SEP}/', $sep, $title);
    return $title;
    }


// ------------------------------------------------------------------------------
// Anzeige der Informationen zum System
// ------------------------------------------------------------------------------
    function getCmsInfo() {
        global $mainconf;
        global $language;
        return "<a href=\"http://cms.mozilo.de/\" target=\"_blank\" id=\"cmsinfolink\"".getTitleAttribute($language->getLanguageValue1("tooltip_link_extern_1", "http://cms.mozilo.de")).">moziloCMS ".$mainconf->get("cmsversion")."</a>";
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: "title"-Attribut zusammenbauen (oder nicht, wenn nicht konfiguriert)
// ------------------------------------------------------------------------------
    function getTitleAttribute($value) {
        global $mainconf;
        if ($mainconf->get("showsyntaxtooltips") == "true") {
            return " title=\"".$value."\"";
        }
        return "";
    }
    
?>