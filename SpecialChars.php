<?php

/*
######
INHALT
######
		
		Projekt "Flatfile-basiertes CMS f�r Einsteiger"
		Umlautersetzung
		Mai 2006
		Klasse ITF04-1
		Industrieschule Chemnitz

		Ronny Monser
		Arvid Zimmermann
		Oliver Lorenz
		-> mozilo

######
*/

class SpecialChars {
	
// ------------------------------------------------------------------------------    
// Konstruktor
// ------------------------------------------------------------------------------
	function SpecialChars(){
	}

// ------------------------------------------------------------------------------    
// Inhaltsseiten/Kategorien f�r Speicherung umlaut- und sonderzeichenbereinigen 
// ------------------------------------------------------------------------------
	function deleteSpecialChars($text) {
		$text = preg_replace("/&auml;/", "-auml-", $text);
		$text = preg_replace("/&ouml;/", "-ouml-", $text);
		$text = preg_replace("/&uuml;/", "-uuml-", $text);
		$text = preg_replace("/&Auml;/", "-Auml-", $text);
		$text = preg_replace("/&Ouml;/", "-Ouml-", $text);
		$text = preg_replace("/&Uuml;/", "-Uuml-", $text);
		$text = preg_replace("/&szlig;/", "-szlig-", $text);
		$text = preg_replace("/\s/", "-nbsp-", $text);
		$text = preg_replace("/�/", "-auml-", $text);
		$text = preg_replace("/�/", "-ouml-", $text);
		$text = preg_replace("/�/", "-uuml-", $text);
		$text = preg_replace("/�/", "-Auml-", $text);
		$text = preg_replace("/�/", "-Ouml-", $text);
		$text = preg_replace("/�/", "-Uuml-", $text);
		$text = preg_replace("/�/", "-szlig-", $text);
		$text = preg_replace("/ /", "-nbsp-", $text);
		return $text;
	}


// ------------------------------------------------------------------------------    
// Umlaute in Inhaltsseiten/Kategorien f�r Anzeige 
// ------------------------------------------------------------------------------
	function rebuildSpecialChars($text, $rebuildnbsp) {
		$text = preg_replace("/-auml-/", "&auml;", $text);
		$text = preg_replace("/-ouml-/", "&ouml;", $text);
		$text = preg_replace("/-uuml-/", "&uuml;", $text);
		$text = preg_replace("/-Auml-/", "&Auml;", $text);
		$text = preg_replace("/-Ouml-/", "&Ouml;", $text);
		$text = preg_replace("/-Uuml-/", "&Uuml;", $text);
		$text = preg_replace("/-szlig-/", "&szlig;", $text);
		if ($rebuildnbsp)
			$text = preg_replace("/-nbsp-/", "&nbsp;", $text);
		else
			$text = preg_replace("/-nbsp-/", " ", $text);
		return $text;
	}


}
?>