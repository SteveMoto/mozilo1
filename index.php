<?php

/*
######
INHALT
######
		
		Projekt "Flatfile-basiertes CMS f�r Einsteiger"
		Mai 2006
		Klasse ITF04-1
		Industrieschule Chemnitz

		Ronny Monser
		Arvid Zimmermann
		Oliver Lorenz
		-> mozilo

		Dieses Dokument stellt ein simples dateibasiertes
		Content Management System dar.
		
		Funktion:
		Siehe /admin/readme.htm

######
*/

	require_once("SpecialChars.php");
	require_once("Properties.php");
	$specialchars = new SpecialChars();
	$mainconfig = new Properties("main.conf");
	
	// Config-Parameter auslesen
	
	$WEBSITE_TITLE			= $mainconfig->get("websitetitle");
	if ($WEBSITE_TITLE == "")
		$WEBSITE_TITLE = "Titel der Website";
	
	$TEMPLATE_FILE			= $mainconfig->get("templatefile");
	if ($TEMPLATE_FILE == "")
		$TEMPLATE_FILE = "template.html";

	$CSS_FILE						= $mainconfig->get("cssfile");
	if ($CSS_FILE == "")
		$CSS_FILE = "css/style.css";

	$DEFAULT_CATEGORY		= $mainconfig->get("defaultcat");
	if ($DEFAULT_CATEGORY == "")
		$DEFAULT_CATEGORY = "10_Home";

	$DEFAULT_PAGE				= $mainconfig->get("defaultpage");
	if ($DEFAULT_PAGE == "")
		$DEFAULT_PAGE = "10_Home";

	$FAVICON_FILE				= $mainconfig->get("faviconfile");
	if ($FAVICON_FILE == "")
		$FAVICON_FILE = "favicon.ico";

	$USE_CMS_SYNTAX			= true;
	if ($mainconfig->get("usecmssyntax") == "false")
		$USE_CMS_SYNTAX = false;


	$CONTENT_DIR_REL		= "inhalt";
	$CONTENT_DIR_ABS 		= getcwd() . "/$CONTENT_DIR_REL";
	$CONTENT_FILES_DIR	= "dateien";
	$CONTENT_GALLERY_DIR= "galerie";
	$CONTENT_EXTENSION	= ".txt";
	$CAT_REQUEST 				= $_GET['cat'];
	$PAGE_REQUEST 			= $_GET['page'];
	
	$CONTENT 						= "";
	$HTML								= "";
	
	
	// Zuerst: �bergebene Parameter �berpr�fen
	checkParameters();
	// Dann: HTML-Template einlesen und mit Inhalt f�llen
	readTemplate();
	// Zum Schlu�: Ausgabe des fertigen HTML-Dokuments
  echo $HTML;

	
// ------------------------------------------------------------------------------
// Parameter auf Korrektheit pr�fen
// ------------------------------------------------------------------------------
	function checkParameters() {
		global $CONTENT_DIR_ABS;
		global $CONTENT_FILES_DIR;
		global $CONTENT_GALLERY_DIR;
		global $CONTENT_EXTENSION;
		global $DEFAULT_CATEGORY;
		global $CAT_REQUEST;
		global $PAGE_REQUEST;
		// �berpr�fung der gegebenen Parameter
		if (
				// Wenn keine Kategorie �bergeben wurde...
				($CAT_REQUEST == "") 
				// ...oder eine nicht existente Kategorie...
				|| (!file_exists("$CONTENT_DIR_ABS/$CAT_REQUEST")) 
				// ...oder eine Kategorie ohne Contentseiten...
				|| (getDirContentAsArray("$CONTENT_DIR_ABS/$CAT_REQUEST", array($CONTENT_FILES_DIR, $CONTENT_GALLERY_DIR)) == "")
				// ...oder eine nicht existente Content-Seite...
				|| (($PAGE_REQUEST <> "") && (!file_exists("$CONTENT_DIR_ABS/$CAT_REQUEST/$PAGE_REQUEST$CONTENT_EXTENSION")))
			)
			// ...dann verwende die Standardkategorie
			$CAT_REQUEST = $DEFAULT_CATEGORY;
		
		// Kategorie-Verzeichnis einlesen
		$pagesarray = getDirContentAsArray("$CONTENT_DIR_ABS/$CAT_REQUEST/", array($CONTENT_FILES_DIR, $CONTENT_GALLERY_DIR));
		// Wenn Contentseite nicht explizit angefordert wurde oder nicht vorhanden ist...
		if (($PAGE_REQUEST == "") || (!file_exists("$CONTENT_DIR_ABS/$CAT_REQUEST/$PAGE_REQUEST$CONTENT_EXTENSION")))
			//...erste Contentseite der Kategorie setzen
			$PAGE_REQUEST = substr($pagesarray[0], 0, strlen($pagesarray[0]) - strlen($CONTENT_EXTENSION));
	}
	
	
// ------------------------------------------------------------------------------
// HTML-Template einlesen und verarbeiten
// ------------------------------------------------------------------------------
	function readTemplate() {
		global $CSS_FILE;
		global $HTML;
		global $FAVICON_FILE;
		global $TEMPLATE_FILE;
		global $USE_CMS_SYNTAX;
		global $WEBSITE_TITLE;
		// Template-Datei auslesen
    if (!$file = @fopen($TEMPLATE_FILE, "r"))
        die("'$TEMPLATE_FILE' fehlt! Bitte kontaktieren Sie den Administrator.");
    $template = fread($file, filesize($TEMPLATE_FILE));
    fclose($file);
    
		// Platzhalter des Templates mit Inhalt f�llen
    $HTML = preg_replace('/{CSS_FILE}/', $CSS_FILE, $template);
    $HTML = preg_replace('/{FAVICON_FILE}/', $FAVICON_FILE, $HTML);
    $HTML = preg_replace('/{WEBSITE_TITLE}/', $WEBSITE_TITLE, $HTML);
    if ($USE_CMS_SYNTAX)
    	$HTML = preg_replace('/{CONTENT}/', convertContent(getContent(), true), $HTML);
    else
    	$HTML = preg_replace('/{CONTENT}/', getContent(), $HTML);
    $HTML = preg_replace('/{MAINMENU}/', getMainMenu(), $HTML);
    $HTML = preg_replace('/{DETAILMENU}/', getDetailMenu(), $HTML);
    $HTML = preg_replace('/{LASTCHANGE}/', getLastChangedContentPage(), $HTML);
	}


// ------------------------------------------------------------------------------    
// Zu einem Kategorienamen passendes Kategorieverzeichnis suchen und zur�ckgeben
// ------------------------------------------------------------------------------
	function nameToCategory($catname) {
		global $CONTENT_DIR_ABS;
		// Content-Verzeichnis einlesen
		$dircontent = getDirContentAsArray("$CONTENT_DIR_ABS", array());
		// alle vorhandenen Kategorien durchgehen...
		foreach ($dircontent as $currentelement) {
			// ...und wenn eine auf den Namen pa�t...
			if (substr($currentelement, 3, strlen($currentelement)-3) == $catname){
				// ...den Kategorie zur�ckgeben
				return $currentelement;
			}
		}
		// Wenn kein Verzeichnis pa�t: Leerstring zur�ckgeben
		return "";
	}


// ------------------------------------------------------------------------------    
// Zu einer Inhaltsseite passende Datei suchen und zur�ckgeben
// ------------------------------------------------------------------------------
	function nameToPage($pagename, $currentcat) {
		global $CONTENT_DIR_ABS;
		global $CONTENT_FILES_DIR;
		global $CONTENT_GALLERY_DIR;
		global $CONTENT_EXTENSION;
		// Kategorie-Verzeichnis einlesen
		$dircontent = getDirContentAsArray("$CONTENT_DIR_ABS/$currentcat", array($CONTENT_FILES_DIR, $CONTENT_GALLERY_DIR));
		// alle vorhandenen Inhaltsdateien durchgehen...
		foreach ($dircontent as $currentelement) {
			// ...und wenn eine auf den Namen pa�t...
			if (substr($currentelement, 3, strlen($currentelement) - 3 - strlen($CONTENT_EXTENSION)) == $pagename) {
			//if (substr($currentelement, 3, strlen($currentelement)-3) == $pagename){
				// ...den Kategorie zur�ckgeben
				return $currentelement;
			}
		}
		// Wenn keine Datei pa�t: Leerstring zur�ckgeben
		return "";
	}


// ------------------------------------------------------------------------------
// Inhalt einer Content-Datei einlesen, R�ckgabe als String
// ------------------------------------------------------------------------------
	function getContent() {
		global $CONTENT_DIR_ABS;
		global $CONTENT_FILES_DIR;
		global $CONTENT_GALLERY_DIR;
		global $CONTENT_EXTENSION;
		global $CAT_REQUEST;
		global $PAGE_REQUEST;
		// Contentseiten der angeforderten Kategorie in Array einlesen
		$pagesarray = getDirContentAsArray("$CONTENT_DIR_ABS/$CAT_REQUEST/", array($CONTENT_FILES_DIR, $CONTENT_GALLERY_DIR));
		// Das Array der Contentseiten elementweise pr�fen...
		foreach ($pagesarray as $currentelement) {
			// ...und bei einem Treffer den Inhalt der Content-Datei zur�ckgeben
			if ($currentelement == "$PAGE_REQUEST$CONTENT_EXTENSION"){
				return implode("", file("$CONTENT_DIR_ABS/$CAT_REQUEST/$PAGE_REQUEST$CONTENT_EXTENSION"));
			}
		}
	}
	
	
// ------------------------------------------------------------------------------
// Auslesen des Content-Verzeichnisses unter Ber�cksichtigung 
// des auszuschlie�enden File-Verzeichnisses, R�ckgabe als Array
// ------------------------------------------------------------------------------
	function getDirContentAsArray($dir, $excludes) {
		$currentdir = opendir($dir);
		$i=0;
		// Einlesen des gesamten Content-Verzeichnisses au�er dem 
		// auszuschlie�enden Verzeichnis und den Elementen . und ..
		while ($file = readdir($currentdir)) {
			if (($file <> ".") && ($file <> "..") && (!in_array($file, $excludes))) {
	    	$files[$i] = $file;
	    	$i++;
	    }
		}
		// R�ckgabe des sortierten Arrays
		if ($files <> "")
			sort($files);
		return $files;
	}


// ------------------------------------------------------------------------------
// Aufbau des Hauptmen�s, R�ckgabe als String
// ------------------------------------------------------------------------------
	function getMainMenu() {
		global $CONTENT_DIR_ABS;
		global $CONTENT_FILES_DIR;
		global $CONTENT_GALLERY_DIR;
		global $CAT_REQUEST;
		global $PAGE_REQUEST;
		global $specialchars;
		$mainmenu = "";
		// Kategorien-Verzeichnis einlesen
		$categoriesarray = getDirContentAsArray($CONTENT_DIR_ABS, array());
		// Jedes Element des Arrays ans Men� anh�ngen
		foreach ($categoriesarray as $currentcategory) {
			// Wenn die Kategorie keine Contentseiten hat, zeige sie nicht an
			if (getDirContentAsArray("$CONTENT_DIR_ABS/$currentcategory", array($CONTENT_FILES_DIR, $CONTENT_GALLERY_DIR)) == "")
				$mainmenu .= "";
			// Aktuelle Kategorie als aktiven Men�punkt anzeigen...
			elseif ($currentcategory == $CAT_REQUEST)
				$mainmenu .= "<a href=\"index.php?cat=$currentcategory\" class=\"menuactive\">".substr($specialchars->rebuildSpecialChars($currentcategory), 3, strlen($currentcategory))."</a>";
			// ...alle anderen als normalen Men�punkt.
			else
					$mainmenu .= "<a href=\"index.php?cat=$currentcategory\" class=\"menu\">".substr($specialchars->rebuildSpecialChars($currentcategory), 3, strlen($currentcategory))."</a>";
		}
		// R�ckgabe des Men�s
		return $mainmenu;
	}


// ------------------------------------------------------------------------------
// Aufbau des Detailmen�s, R�ckgabe als String
// ------------------------------------------------------------------------------
	function getDetailMenu(){
		global $CONTENT_DIR_ABS;
		global $CONTENT_FILES_DIR;
		global $CONTENT_GALLERY_DIR;
		global $CAT_REQUEST;
		global $PAGE_REQUEST;
		global $CONTENT_EXTENSION;
		global $specialchars;
		$detailmenu = "";
		// Content-Verzeichnis der aktuellen Kategorie einlesen
		$contentarray = getDirContentAsArray("$CONTENT_DIR_ABS/$CAT_REQUEST", array($CONTENT_FILES_DIR, $CONTENT_GALLERY_DIR));
		// Jedes Element des Arrays ans Men� anh�ngen
		foreach ($contentarray as $currentcontent) {
			// Aktuelle Kategorie als aktiven Men�punkt anzeigen...
			if (substr($currentcontent, 0, strlen($currentcontent) - strlen($CONTENT_EXTENSION)) == $PAGE_REQUEST)
				$detailmenu .= "<a href=\"index.php?cat=$CAT_REQUEST&amp;page=".
												substr($currentcontent, 0, strlen($currentcontent) - strlen($CONTENT_EXTENSION)).
												"\" class=\"detailmenuactive\">".
												substr($specialchars->rebuildSpecialChars($currentcontent), 3, strlen($currentcontent) - strlen($CONTENT_EXTENSION) - 3).
												"</a> ";
			// ...alle anderen als normalen Men�punkt.
			else
				$detailmenu .= "<a href=\"index.php?cat=$CAT_REQUEST&amp;page=".
												substr($currentcontent, 0, strlen($currentcontent) - strlen($CONTENT_EXTENSION)).
												"\" class=\"detailmenu\">".
												substr($specialchars->rebuildSpecialChars($currentcontent), 3, strlen($currentcontent) - strlen($CONTENT_EXTENSION) - 3).
												"</a> ";
		}
		// R�ckgabe des Men�s
		return $detailmenu;
	}



// ------------------------------------------------------------------------------
// Einlesen des Inhalts-Verzeichnisses, R�ckgabe der zuletzt ge�nderten Datei
// ------------------------------------------------------------------------------
	function getLastChangedContentPage(){
		global $specialchars;
		$latestchanged = array("cat" => "catname", "file" => "filename", "time" => 0);
		$currentdir = opendir("inhalt");
		while ($file = readdir($currentdir)) {
			if (($file <> ".") && ($file <> "..")) {
				$latestofdir = getLastChangeOfCat("inhalt/".$file);
				if ($latestofdir['time'] > $latestchanged['time']) {
					$latestchanged['cat'] = $file;
					$latestchanged['file'] = $latestofdir['file'];
					$latestchanged['time'] = $latestofdir['time'];
				}
	    }
		}
		return "<a href=\"index.php?cat=".$latestchanged['cat']."&amp;page=".substr($latestchanged['file'], 0, strlen($latestchanged['file'])-4)."\" title=\"Inhaltsseite &quot;".$specialchars->rebuildSpecialChars(substr($latestchanged['file'], 3, strlen($latestchanged['file'])-7))."&quot; anzeigen\" class=\"latestchangedlink\">".$specialchars->rebuildSpecialChars(substr($latestchanged['file'], 3, strlen($latestchanged['file'])-7))."</a> (".strftime("%d.%m.%Y, %H:%M:%S", date($latestchanged['time'])).")";
	}



// ------------------------------------------------------------------------------
// Einlesen eines Kategorie-Verzeichnisses, R�ckgabe der zuletzt ge�nderten Datei
// ------------------------------------------------------------------------------
	function getLastChangeOfCat($dir){
		$latestchanged = array("file" => "filename", "time" => 0);
		$currentdir = opendir($dir);
		while ($file = readdir($currentdir)) {
			if (is_file($dir."/".$file)) {
				if (filemtime($dir."/".$file) > $latestchanged['time']) {
					$latestchanged['file'] = $file;
					$latestchanged['time'] = filemtime($dir."/".$file);
				}
	    }
		}
		return $latestchanged;
	}



// ------------------------------------------------------------------------------
// Umsetzung der �bergebenen CMS-Syntax in HTML, R�ckgabe als String
// ------------------------------------------------------------------------------
	function convertContent($content, $firstrecursion){
		global $CONTENT_DIR_ABS;
		global $CONTENT_DIR_REL;
		global $CONTENT_FILES_DIR;
		global $CONTENT_GALLERY_DIR;
		global $CAT_REQUEST;
		global $CONTENT_EXTENSION;
		global $specialchars;
		
		if ($firstrecursion) {
			// Inhaltsformatierungen
	    $content = htmlentities($content);
			$content = preg_replace("/&amp;#036;/Umsi", "&#036;", $content);
			$content = preg_replace("/&amp;#092;/Umsi", "&#092;", $content);
			$content = preg_replace("/\^(.)/Umsie", "'&#'.ord('\\1').';'", $content);
		}
		
		// Nach Texten in eckigen Klammern suchen
		preg_match_all("/\[([\w]+)\|([^\[\]\|]+)\]/", $content, $matches);
		$i = 0;
		// F�r jeden Treffer...
		foreach ($matches[0] as $match) {
			// ...Auswertung und Verarbeitung der Informationen
			$attribute = $matches[1][$i];
			$value = $matches[2][$i];

			// externer Link
			if ($attribute == "link"){
				// �berpr�fung auf Validit�t >> protokoll :// (username:password@) [(sub.)server.tld|ip-adresse] (:port) (subdirs|files)
						// protokoll 						(http|https|ftp|gopher|telnet|mms):\/\/
						// username:password@		(\w)+\:(\w)+\@
						// (sub.)server.tld 		((\w)+\.)?(\w)+\.[a-zA-Z]{2,4}
						// ip-adresse (ipv4)		([\d]{1,3}\.){3}[\d]{1,3}
						// port									\:[\d]{1,5}
						// subdirs|files				(\w)+
			if (preg_match("/(http|https|ftp|gopher|telnet|mms)\:\/\/((\w)+\:(\w)+\@)?[((\w)+\.)?(\w)+\.[a-zA-Z]{2,4}|([\d]{1,3}\.){3}[\d]{1,3}](\:[\d]{1,5})?((\w)+)?$/", $value))
					$content = str_replace ($match, "<a href=\"$value\" title=\"Webseite &quot;$value&quot; besuchen\" target=\"_blank\">$value</a>", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"Fehlerhafte Link-Adresse &quot;$value&quot;\">$value</em>", $content);
			}

			// Mail-Link
			elseif ($attribute == "mail"){
				// �berpr�fung auf Validit�t
				if (preg_match("/^\w[\w|\.|\-]+@\w[\w|\.|\-]+\.[a-zA-Z]{2,4}$/", $value))
					$content = str_replace ($match, "<a href=\"mailto:$value\" title=\"Mail an &quot;$value&quot; schreiben\">$value</a>", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"Fehlerhafte E-Mail-Adresse &quot;$value&quot;\">$value</em>", $content);
			}

			// Kategorie-Link (�berpr�fen, ob Kategorie existiert)
			elseif ($attribute == "kategorie"){
				$requestedcat = nameToCategory($specialchars->deleteSpecialChars($value));
				if ((!$requestedcat=="") && (file_exists("./$CONTENT_DIR_REL/$requestedcat")))
					$content = str_replace ($match, "<a href=\"index.php?cat=$requestedcat\" title=\"Zur Kategorie &quot;$value&quot; wechseln\">$value</a>", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"Kategorie &quot;$value&quot; nicht vorhanden\">$value</em>", $content);
			}

			// Link auf Inhaltsseite in aktueller Kategorie (�berpr�fen, ob Inhaltsseite existiert)
			elseif ($attribute == "seite"){
				$requestedpage = nameToPage($specialchars->deleteSpecialChars($value), $CAT_REQUEST);
				if ((!$requestedpage=="") && (file_exists("./$CONTENT_DIR_REL/$CAT_REQUEST/$requestedpage")))
					$content = str_replace ($match, "<a href=\"index.php?cat=$CAT_REQUEST&amp;page=".substr($requestedpage, 0, strlen($requestedpage) - strlen($CONTENT_EXTENSION))."\" title=\"Inhaltsseite &quot;$value&quot; anzeigen\">$value</a>", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"Inhaltsseite &quot;$value&quot; nicht vorhanden\">$value</em>", $content);
			}

			// Datei aus dem Dateiverzeichnis (�berpr�fen, ob Datei existiert)
			elseif ($attribute == "datei"){
				if (file_exists("./$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/$value"))
					$content = str_replace ($match, "<a href=\"$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/$value\" title=\"Datei &quot;$value&quot; herunterladen\" target=\"_blank\">$value</a>", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"Datei &quot;$value&quot; nicht vorhanden\">$value</em>", $content);
			}

			// Galerie mit Bildern aus dem Galerieverzeichnis
			elseif ($attribute == "galerie"){
				$handle = opendir("./$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_GALLERY_DIR");
				$j=0;
				while ($file = readdir($handle)) {
					if (is_file("./$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_GALLERY_DIR/".$file) && ($file <> "texte.conf")) {
	    			$j++;
	    		}
				}
				$content = str_replace ($match, "<a href=\"galerie.php?cat=$CAT_REQUEST\" title=\"Galerie &quot;$value&quot; ($j Bilder) ansehen\" target=\"_blank\">$value</a>", $content);
			}

			// Bild aus dem Dateiverzeichnis (�berpr�fen, ob Bilddatei existiert)
			elseif ($attribute == "bild"){
				if (file_exists("./$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/$value"))
					$content = str_replace ($match, "<img src=\"$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/$value\" alt=\"Bild &quot;$value&quot;\" />", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"Bilddatei &quot;$value&quot; nicht vorhanden\">$value</em>", $content);
			}

			// Bild links ausgerichtet
			elseif ($attribute == "bildlinks"){
				if (file_exists("./$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/$value"))
					$content = str_replace ($match, "<img src=\"$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/$value\" class=\"leftcontentimage\" alt=\"Bild &quot;$value&quot;\" />", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"Bilddatei &quot;$value&quot; nicht vorhanden\">$value</em>", $content);
			}

			// Bild rechts ausgerichtet
			elseif ($attribute == "bildrechts"){
				if (file_exists("./$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/$value"))
					$content = str_replace ($match, "<img src=\"$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/$value\" class=\"rightcontentimage\" alt=\"Bild &quot;$value&quot;\" />", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"Bilddatei &quot;$value&quot; nicht vorhanden\">$value</em>", $content);
			}

			// Text fett
			elseif ($attribute == "fett"){
				$content = str_replace ($match, "<em class=\"bold\">$value</em>", $content);
			}

			// Text kursiv
			elseif ($attribute == "kursiv"){
				$content = str_replace ($match, "<em class=\"italic\">$value</em>", $content);
			}

			// Text fettkursiv
			elseif ($attribute == "fettkursiv"){
				$content = str_replace ($match, "<em class=\"bolditalic\">$value</em>", $content);
			}

			// �berschrift gro�
			elseif ($attribute == "ueber1"){
				$content = str_replace ("$match", "<h1>$value</h1>", $content);
			}

			// �berschrift mittel
			elseif ($attribute == "ueber2"){
				$content = str_replace ("$match", "<h2>$value</h2>", $content);
			}

			// �berschrift normal
			elseif ($attribute == "ueber3"){
				$content = str_replace ("$match", "<h3>$value</h3>", $content);
			}

			// Liste, einfache Einr�ckung
			elseif ($attribute == "liste1"){
				$content = str_replace ("$match", "<ul><li>$value</li></ul>", $content);
			}

			// Liste, doppelte Einr�ckung
			elseif ($attribute == "liste2"){
				$content = str_replace ("$match", "<ul><ul><li>$value</li></ul></ul>", $content);
			}

			// Liste, dreifache Einr�ckung
			elseif ($attribute == "liste3"){
				$content = str_replace ("$match", "<ul><ul><ul><li>$value</li></ul></ul></ul>", $content);
			}
			
			// Attribute, die nicht zugeordnet werden k�nnen
			else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"Falsche Syntax: Unbekanntes Attribut &quot;$attribute&quot;\">$value</em>", $content);

			$i++;
		}

		// Immer ersetzen: Horizontale Linen
		$content = preg_replace('/\[----\](\r\n|\r|\n)?/m', '<hr />', $content);
		// Zeilenwechsel setzen
		$content = preg_replace('/\n/', '<br />', $content);
		// Zeilenwechsel vor und nach Blockelementen wieder herausnehmen
		//$content = preg_replace('/<br \/><hr \/>/', "<hr />", $content);
		$content = preg_replace('/<\/ul>(\r\n|\r|\n)<br \/>/', "</ul>", $content);
		$content = preg_replace('/<\/ol>(\r\n|\r|\n)<br \/>/', "</ol>", $content);
		$content = preg_replace('/(<\/h[123]>)(\r\n|\r|\n)<br \/>/', "$1", $content);

		// Rekursion, wenn noch Fundstellen
		if ($i > 0)
			$content = convertContent($content, false);
			
		// Konvertierten Seiteninhalt zur�ckgeben
    return $content;
	}
?>