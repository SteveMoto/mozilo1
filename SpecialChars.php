<?php

/* 
* 
* $Revision: 115 $
* $LastChangedDate: 2009-01-27 21:14:39 +0100 (Di, 27 Jan 2009) $
* $Author: arvid $
*
*/



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
		www.mozilo.de

######
*/

class SpecialChars {
	
// ------------------------------------------------------------------------------    
// Konstruktor
// ------------------------------------------------------------------------------
	function SpecialChars(){
	}

// ------------------------------------------------------------------------------    
// Erlaubte Sonderzeichen als RegEx zur�ckgeben
// ------------------------------------------------------------------------------
	function getSpecialCharsRegex() {
		$regex = "/^[a-zA-Z0-9_\-\s\?\!\@\.�".addslashes(html_entity_decode(implode("", get_html_translation_table(HTML_ENTITIES, ENT_QUOTES))))."]+$/";
		$regex = preg_replace("/&#39;/", "\'", $regex);
		return $regex;
	}
	
// ------------------------------------------------------------------------------    
// Erlaubte Sonderzeichen userlesbar als String zur�ckgeben
// ------------------------------------------------------------------------------
	function getSpecialCharsString($sep, $charsperline) {
		$specialcharsstring = "";
		$specialcharshtml = "";
		for ($i=65; $i<=90;$i++)
			$specialcharsstring .= chr($i);
		for ($i=97; $i<=122;$i++)
			$specialcharsstring .= chr($i);
		for ($i=48; $i<=57;$i++)
			$specialcharsstring .= chr($i);
		$specialcharsstring .= html_entity_decode("_- ?!�@.".stripslashes(preg_replace("/&#39;/", "\'", implode(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)))));
		for ($i=0; $i<=strlen($specialcharsstring); $i+=$charsperline) {
			$specialcharshtml .= htmlentities(substr($specialcharsstring, $i, $charsperline))."<br />";
		}
		return $specialcharshtml;
	}
	
// ------------------------------------------------------------------------------    
// Inhaltsseiten/Kategorien f�r Speicherung umlaut- und sonderzeichenbereinigen 
// ------------------------------------------------------------------------------
	function replaceSpecialChars($text) {
		$text = htmlentities(stripslashes($text));
		// Leerzeichen
		$text = preg_replace("/(\s| |\240|&nbsp;)/", "-nbsp~", $text);
		$text = preg_replace("/\"/", "-quot~", $text);
		// �, @, ?
		$text = preg_replace("/�/", "-euro~", $text);
		$text = preg_replace("/@/", "-at~", $text);
		$text = preg_replace("/\?/", "-ques~", $text);
		// Alle HTML-Entities in mozilo-Entities umwandeln
		$text = preg_replace("/&(.*);/U", "-$1~", $text);
		return $text;
	}


// ------------------------------------------------------------------------------    
// Umlaute in Inhaltsseiten/Kategorien f�r Anzeige 
// ------------------------------------------------------------------------------
	function rebuildSpecialChars($text, $rebuildnbsp) {
		// Leerzeichen
		if ($rebuildnbsp)
			$text = preg_replace("/-nbsp~/", "&nbsp;", $text);
		else
			$text = preg_replace("/-nbsp~/", " ", $text);
		// @, ?
		$text = preg_replace("/-at~/", "@", $text);
		$text = preg_replace("/-ques~/", "?", $text);
		// Alle mozilo-Entities in HTML-Entities umwandeln!
		$text = preg_replace("/-([^-~]+)~/U", "&$1;", $text);
		// & escapen 
		//$text = preg_replace("/&+(?!(.+);)/U", "&amp;", $text);
		return $text;
	}


// ------------------------------------------------------------------------------    
// F�r Datei-Uploads erlaubte Sonderzeichen als RegEx zur�ckgeben
// ------------------------------------------------------------------------------
	function getFileCharsRegex() {
		$regex = "/^[a-zA-Z0-9_\-\.]+$/";
		return $regex;
	}
	
// ------------------------------------------------------------------------------    
// F�r Datei-Uploads erlaubte Sonderzeichen userlesbar als String zur�ckgeben
// ------------------------------------------------------------------------------
	function getFileCharsString($sep, $charsperline) {
		$filecharsstring = "";
		$filecharshtml = "";
		for ($i=65; $i<=90;$i++)
			$filecharsstring .= chr($i);
		for ($i=97; $i<=122;$i++)
			$filecharsstring .= chr($i);
		for ($i=48; $i<=57;$i++)
			$filecharsstring .= chr($i);
		$filecharsstring .= "_-.";
		for ($i=0; $i<=strlen($filecharsstring); $i+=$charsperline) {
			$filecharshtml .= htmlentities(substr($filecharsstring, $i, $charsperline))."<br />";
		}
		return $filecharshtml;
	}

	
// ------------------------------------------------------------------------------    
// String f�r SEO-Links umlaut- und sonderzeichenbereinigen 
// ------------------------------------------------------------------------------
	function replaceSeoSpecialChars($text) {
		$text = preg_replace("/�/", "ae", $text);
		$text = preg_replace("/�/", "oe", $text);
		$text = preg_replace("/�/", "ue", $text);
		$text = preg_replace("/�/", "Ae", $text);
		$text = preg_replace("/�/", "Oe", $text);
		$text = preg_replace("/�/", "Ue", $text);
		$text = preg_replace("/�/", "ss", $text);
		$text = preg_replace("/\s/", "+", $text);
		$text = preg_replace("/ /", "+", $text);
		$text = preg_replace("/\240/", "+", $text); // f�r den IE: Zeichen "�", warum auch immer -.-
		$text = preg_replace("/\?/", "", $text);
		$text = preg_replace("/&/", "+", $text);
		$text = preg_replace("/�/", "euro", $text);
		$text = preg_replace("/</", "+", $text);
		$text = preg_replace("/>/", "+", $text);
		$text = preg_replace("/@/", "at", $text);
		return $text;
	}
}
?>