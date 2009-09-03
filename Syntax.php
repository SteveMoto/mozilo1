<?php

/*
######
INHALT
######
		
		Projekt "Flatfile-basiertes CMS f�r Einsteiger"
		Syntaxersetzung
		Klasse ITF04-1
		Industrieschule Chemnitz

		Ronny Monser
		Arvid Zimmermann
		Oliver Lorenz
		www.mozilo.de

######
*/

class Syntax {
	
	var $CMS_CONF;
	var $LANG;
	var $LINK_REGEX;
	var $MAIL_REGEX;

	
// ------------------------------------------------------------------------------    
// Konstruktor
// ------------------------------------------------------------------------------
	function Syntax(){
		$this->CMS_CONF	= new Properties("main.conf");
		$this->LANG	= new Language();
		// Regul�rer Audruck zur �berpr�fung von Links
		// �berpr�fung auf Validit�t >> protokoll :// (username:password@) [(sub.)server.tld|ip-adresse] (:port) (subdirs|files)
					// protokoll 						(https?|t?ftps?|gopher|telnets?|mms|imaps?|irc|pop3s?|rdp|smb|smtps?|sql|ssh):\/\/
					// username:password@		(\w)+\:(\w)+\@
					// (sub.)server.tld 		((\w)+\.)?(\w)+\.[a-zA-Z]{2,4}
					// ip-adresse (ipv4)		([\d]{1,3}\.){3}[\d]{1,3}
					// port									\:[\d]{1,5}
					// subdirs|files				(\w)+
		$this->LINK_REGEX = "/^(https?|t?ftps?|gopher|telnets?|mms|imaps?|irc|pop3s?|rdp|smb|smtps?|sql|ssh)\:\/\/((\w)+\:(\w)+\@)?[((\w)+\.)?(\w)+\.[a-zA-Z]{2,4}|([\d]{1,3}\.){3}[\d]{1,3}](\:[\d]{1,5})?((\w)+)?$/";
		$this->MAIL_REGEX = "/^\w[\w|\.|\-]+@\w[\w|\.|\-]+\.[a-zA-Z]{2,4}$/";
	}
	

// ------------------------------------------------------------------------------
// Umsetzung der �bergebenen CMS-Syntax in HTML, R�ckgabe als String
// ------------------------------------------------------------------------------
	function convertContent($content, $firstrecursion){
		global $CONTENT_DIR_ABS;
		global $CONTENT_DIR_REL;
		global $CONTENT_FILES_DIR;
		global $GALLERIES_DIR;
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
//		preg_match_all("/\[([\w|=]+)\|([^\[\]]+)\]/U", $content, $matches);
		preg_match_all("/\[([^\[\]]+)\|([^\[\]]+)\]/U", $content, $matches);
		$i = 0;
		// F�r jeden Treffer...
		foreach ($matches[0] as $match) {
			// ...Auswertung und Verarbeitung der Informationen
			$attribute = $matches[1][$i];
			$value = $matches[2][$i];
			
			// externer Link
			if ($attribute == "link") {
				if (preg_match($this->LINK_REGEX, $value)) {
					$shortenendlink = $value;
					switch ($this->CMS_CONF->get("shortenlinks")) {
						// mit "http://www." beginnende Links ohne das "http://www." anzeigen
						case 2: { 
							if (substr($value, 0, 11) == "http://www.")
								$shortenendlink = substr($value, 11, strlen($value)-11);
							// zus�tzlich: mit "http://" beginnende Links ohne das "http://" anzeigen
							elseif (substr($value, 0, 7) == "http://")
								$shortenendlink = substr($value, 7, strlen($value)-7);
							break;
						}
						// mit "http://" beginnende Links ohne das "http://" anzeigen
						case 1: { 
							if (substr($value, 0, 7) == "http://")
								$shortenendlink = substr($value, 7, strlen($value)-7);
							break;
						}
						default: { 
						}
					}
					$content = str_replace ($match, "<a class=\"link\" href=\"$value\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_extern_1", $value)."\" target=\"_blank\">$shortenendlink</a>", $content);
				}
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_extern_error_1", $value)."\">$value</em>", $content);
			}

			// externer Link mit eigenem Text
			elseif (substr($attribute,0,5) == "link=") {
				// �berpr�fung auf korrekten Link
				if (preg_match($this->LINK_REGEX, $value))
					$content = str_replace ($match, "<a class=\"link\" href=\"$value\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_extern_1", $value)."\" target=\"_blank\">".substr($attribute, 5, strlen($attribute)-5)."</a>", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_extern_error_1", $value)."\">".substr($attribute, 5, strlen($attribute)-5)."</em>", $content);
			}

			// Mail-Link mit eigenem Text
			elseif (substr($attribute,0,5) == "mail=") {
				// �berpr�fung auf korrekten Link
				if (preg_match($this->MAIL_REGEX, $value))
					$content = str_replace ($match, "<a class=\"mail\" href=\"".obfuscateAdress("mailto:$value", 3)."\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_mail_1", obfuscateAdress("$value", 3))."\">".substr($attribute, 5, strlen($attribute)-5)."</a>", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_mail_error_1", $value)."\">".substr($attribute, 5, strlen($attribute)-5)."</em>", $content);
			}
			elseif ($attribute == "mail"){
				// �berpr�fung auf Validit�t
				if (preg_match("/^\w[\w|\.|\-]+@\w[\w|\.|\-]+\.[a-zA-Z]{2,4}$/", $value))
					$content = str_replace ($match, "<a class=\"mail\" href=\"".obfuscateAdress("mailto:$value", 3)."\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_mail_1", obfuscateAdress("$value", 3))."\">".obfuscateAdress("$value", 3)."</a>", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_mail_error_1", $value)."\">$value</em>", $content);
			}

			// Kategorie-Link (�berpr�fen, ob Kategorie existiert)
			elseif ($attribute == "kategorie"){
				$requestedcat = nameToCategory($specialchars->deleteSpecialChars(html_entity_decode($value)));
				if ((!$requestedcat=="") && (file_exists("./$CONTENT_DIR_REL/$requestedcat")))
					$content = str_replace ($match, "<a class=\"category\" href=\"index.php?cat=$requestedcat\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_category_1", $value)."\">$value</a>", $content);
				else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_category_error_1", $value)."\">$value</em>", $content);
			}

			// Link auf Inhaltsseite in aktueller oder anderer Kategorie (�berpr�fen, ob Inhaltsseite existiert)
			elseif ($attribute == "seite"){
				$valuearray = explode(":", $value);
				// Inhaltsseite in aktueller Kategorie
				if (count($valuearray) == 1) {
					$requestedpage = nameToPage($specialchars->deleteSpecialChars(html_entity_decode($value)), $CAT_REQUEST);
					if ((!$requestedpage=="") && (file_exists("./$CONTENT_DIR_REL/$CAT_REQUEST/$requestedpage")))
						$content = str_replace ($match, "<a class=\"page\" href=\"index.php?cat=$CAT_REQUEST&amp;page=".substr($requestedpage, 0, strlen($requestedpage) - strlen($CONTENT_EXTENSION))."\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_page_1", $value)."\">$value</a>", $content);
					else
						$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_page_error_1", $value)."\">$value</em>", $content);
				}
				// Inhaltsseite in anderer Kategorie
				else {
					$requestedcat = nameToCategory($specialchars->deleteSpecialChars(html_entity_decode($valuearray[0])));
					if ((!$requestedcat=="") && (file_exists("./$CONTENT_DIR_REL/$requestedcat"))) {
						$requestedpage = nameToPage($specialchars->deleteSpecialChars(html_entity_decode($valuearray[1])), $requestedcat);
						if ((!$requestedpage=="") && (file_exists("./$CONTENT_DIR_REL/$requestedcat/$requestedpage")))
							$content = str_replace ($match, "<a class=\"page\" href=\"index.php?cat=$requestedcat&amp;page=".substr($requestedpage, 0, strlen($requestedpage) - strlen($CONTENT_EXTENSION))."\" title=\"".$this->LANG->getLanguageValue2("tooltip_link_page_2", $valuearray[1], $valuearray[0])."\">".$valuearray[1]."</a>", $content);
						else
							$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue2("tooltip_link_page_error_2", $valuearray[1], $valuearray[0])."\">".$valuearray[1]."</em>", $content);	
					}
					else
						$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_category_error_1", $valuearray[0])."\">".$valuearray[1]."</em>", $content);
				}
			}

			// Datei aus dem Dateiverzeichnis (�berpr�fen, ob Datei existiert)
			elseif ($attribute == "datei"){
				$valuearray = explode(":", $value);
				// Datei in aktueller Kategorie
				if (count($valuearray) == 1) {
					if (file_exists("./$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/$value"))
						$content = str_replace ($match, "<a class=\"file\" href=\"$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/".preg_replace("'\s'", "%20", $value)."\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_file_1", $value)."\" target=\"_blank\">$value</a>", $content);
					else
						$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_file_error_1", $value)."\">$value</em>", $content);
				}
				// Datei in anderer Kategorie
				else {
					$requestedcat = nameToCategory($specialchars->deleteSpecialChars(html_entity_decode($valuearray[0])));
					if ((!$requestedcat=="") && (file_exists("./$CONTENT_DIR_REL/$requestedcat"))) {
						if (file_exists("./$CONTENT_DIR_REL/$requestedcat/$CONTENT_FILES_DIR/$valuearray[1]"))
							$content = str_replace ($match, "<a class=\"file\" href=\"$CONTENT_DIR_REL/$requestedcat/$CONTENT_FILES_DIR/".preg_replace("'\s'", "%20", $valuearray[1])."\" title=\"".$this->LANG->getLanguageValue2("tooltip_link_file_2", $valuearray[1], $valuearray[0])."\" target=\"_blank\">".$valuearray[1]."</a>", $content);
						else
							$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue2("tooltip_link_file_error_2", $valuearray[1], $valuearray[0])."\">".$valuearray[1]."</em>", $content);
					}
					else
						$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_category_error_1", $valuearray[0])."\">".$valuearray[1]."</em>", $content);
				}
			}

			// Galerielink mit eigenem Text
			elseif (substr($attribute,0,8) == "galerie=") {
				$cleanedvalue = $specialchars->deleteSpecialChars($value);
				if (file_exists("./$GALLERIES_DIR/$cleanedvalue")) {
					$handle = opendir("./$GALLERIES_DIR/$cleanedvalue");
					$j=0;
					while ($file = readdir($handle)) {
						if (is_file("./$GALLERIES_DIR/$cleanedvalue/".$file) && ($file <> "texte.conf")) {
		    			$j++;
		    		}
					}
					$content = str_replace ($match, "<a class=\"gallery\" href=\"gallery.php?gal=$cleanedvalue\" title=\"".$this->LANG->getLanguageValue2("tooltip_link_gallery_2", $value, $j)."\" target=\"_blank\">".substr($attribute, 8, strlen($attribute)-8)."</a>", $content);
				}
				// Galerie nicht vorhanden
				else {
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_gallery_error_1", $value)."\">".substr($attribute, 8, strlen($attribute)-8)."</em>", $content);
				}
			}
			// Galerie
			elseif ($attribute == "galerie") {
				$cleanedvalue = $specialchars->deleteSpecialChars($value);
				if (file_exists("./$GALLERIES_DIR/$cleanedvalue")) {
					$handle = opendir("./$GALLERIES_DIR/$cleanedvalue");
					$j=0;
					while ($file = readdir($handle)) {
						if (is_file("./$GALLERIES_DIR/$cleanedvalue/".$file) && ($file <> "texte.conf")) {
		    			$j++;
		    		}
					}
					$content = str_replace ($match, "<a class=\"gallery\" href=\"gallery.php?gal=$cleanedvalue\" title=\"".$this->LANG->getLanguageValue2("tooltip_link_gallery_2", $value, $j)."\" target=\"_blank\">$value</a>", $content);
				}
				// Galerie nicht vorhanden
				else {
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_gallery_error_1", $value)."\">$value</em>", $content);
				}
			}

			// Bild aus dem Dateiverzeichnis oder externes Bild
			elseif (($attribute == "bild") || ($attribute == "bildlinks") ||($attribute == "bildrechts")) {
				$cssclass = "";
				if ($attribute == "bildlinks")
					$cssclass = " class=\"leftcontentimage\"";
				elseif ($attribute == "bildrechts")
					$cssclass = " class=\"rightcontentimage\"";
				// Bei Links: NICHT nach ":" aufsplitten!
				if (preg_match($this->LINK_REGEX, $value))
					$valuearray = $value;
				// Ansonsten: Nach ":" aufsplitten
				else
					$valuearray = explode(":", $value);
				// Bild in aktueller Kategorie
				if (count($valuearray) == 1) {
					if (file_exists("./$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/$value"))
						$content = str_replace ($match, "<img src=\"$CONTENT_DIR_REL/$CAT_REQUEST/$CONTENT_FILES_DIR/".preg_replace("'\s'", "%20", $value)."\" alt=\"".$this->LANG->getLanguageValue1("alttext_image_1", $value)."\"$cssclass />", $content);
					elseif (preg_match($this->LINK_REGEX, $value))
						$content = str_replace ($match, "<img src=\"$value\" alt=\"".$this->LANG->getLanguageValue1("alttext_image_1", $value)."\"$cssclass />", $content);
					else
						$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_image_error_1", $value)."\">$value</em>", $content);
				}
				// Bild in anderer Kategorie
				else {
					$requestedcat = nameToCategory($specialchars->deleteSpecialChars(html_entity_decode($valuearray[0])));
					if ((!$requestedcat=="") && (file_exists("./$CONTENT_DIR_REL/$requestedcat"))) {
						if (file_exists("./$CONTENT_DIR_REL/$requestedcat/$CONTENT_FILES_DIR/".$valuearray[1]))
							$content = str_replace ($match, "<img src=\"$CONTENT_DIR_REL/$requestedcat/$CONTENT_FILES_DIR/".preg_replace("'\s'", "%20", $valuearray[1])."\" alt=\"".$this->LANG->getLanguageValue1("alttext_image_1", $valuearray[1])."\"$cssclass />", $content);
						else
							$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue2("tooltip_image_error_2", $valuearray[1], $valuearray[0])."\">".$valuearray[1]."</em>", $content);
					}
					else
						$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_link_category_error_1", $valuearray[0])."\">".$valuearray[1]."</em>", $content);
				}
			}

			// linksb�ndiger Text
			if ($attribute == "links"){
				$content = str_replace ("$match", "<div style=\"text-align:left;\">".$value."</div>", $content);
			}

			// zentrierter Text
			elseif ($attribute == "zentriert"){
				$content = str_replace ("$match", "<div style=\"text-align:center;\">".$value."</div>", $content);
			}

			// Text im Blocksatz
			elseif ($attribute == "block"){
				$content = str_replace ("$match", "<div style=\"text-align:justified;\">".$value."</div>", $content);
			}

			// rechtsb�ndiger Text
			elseif ($attribute == "rechts"){
				$content = str_replace ("$match", "<div style=\"text-align:right;\">".$value."</div>", $content);
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
			// (veraltet seit Version 1.7 - nur aus Gr�nden der Abw�rtskompatibilit�t noch mitgef�hrt)
			elseif ($attribute == "fettkursiv"){
				$content = str_replace ($match, "<em class=\"bolditalic\">$value</em>", $content);
			}

			// Text unterstrichen
			elseif ($attribute == "unter"){
				$content = str_replace ($match, "<em class=\"underlined\">$value</em>", $content);
			}

			// Text durchgestrichen
			elseif ($attribute == "durch"){
				$content = str_replace ($match, "<em class=\"crossed\">$value</em>", $content);
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
			
			// HTML
			elseif ($attribute == "html"){
				$nobrvalue = preg_replace('/(\r\n|\r|\n)?/m', '', $value);
				$content = str_replace ("$match", html_entity_decode($nobrvalue), $content);
			}

			// Farbige Elemente
			elseif (substr($attribute,0,6) == "farbe=") {
				// �berpr�fung auf korrekten Hexadezimalwert
				if (preg_match("/^([a-f]|\d){6}$/i", substr($attribute, 6, strlen($attribute)-6))) 
					$content = str_replace ("$match", "<em style=\"color:#".substr($attribute, 6, strlen($attribute)-6).";\">".$value."</em>", $content);
				else
					$content = str_replace ("$match", "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_color_error_1", substr($attribute, 6, strlen($attribute)-6))."\">$value</em>", $content);
			}

			// Attribute, die nicht zugeordnet werden k�nnen
			else
					$content = str_replace ($match, "<em class=\"deadlink\" title=\"".$this->LANG->getLanguageValue1("tooltip_attribute_error_1", $attribute)."\">$value</em>", $content);

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
			$content = $this->convertContent($content, false);
			
		// Konvertierten Seiteninhalt zur�ckgeben
    return $content;
	}


}

?>