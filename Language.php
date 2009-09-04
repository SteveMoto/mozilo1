<?php

/* 
* 
* $Revision$
* $LastChangedDate$
* $Author$
*
*/



/*
######
INHALT
######
		
		Projekt "Flatfile-basiertes CMS f�r Einsteiger"
		Sprachunterst�tzung
		Klasse ITF04-1
		Industrieschule Chemnitz

		Ronny Monser
		Arvid Zimmermann
		Oliver Lorenz
		www.mozilo.de

######
*/

class Language {
	
	var $LANG_CONF;

	
// ------------------------------------------------------------------------------    
// Konstruktor
// ------------------------------------------------------------------------------
	function Language() {
		$cms_conf	= new Properties("conf/main.conf");
		$currentlanguage = $cms_conf->get("cmslanguage");
		if (($currentlanguage == "") || (!file_exists("sprachen/$currentlanguage.conf")))
			$currentlanguage = "Deutsch";
		$this->LANG_CONF = new Properties("sprachen/$currentlanguage.conf");
	}
	

// ------------------------------------------------------------------------------
// Sprachelement ohne Zusatz aus Sprachdatei holen
// ------------------------------------------------------------------------------
	function getLanguageValue0($phrase) {
		return htmlentities($this->LANG_CONF->get($phrase),ENT_COMPAT,'ISO-8859-1');
	}


// ------------------------------------------------------------------------------
// Sprachelement mit einem zus�tzlichen Parameter aus Sprachdatei holen
// ------------------------------------------------------------------------------
	function getLanguageValue1($phrase, $param1) {
		$text = htmlentities($this->LANG_CONF->get($phrase),ENT_COMPAT,'ISO-8859-1');
		$text = preg_replace("/{PARAM1}/", $param1, $text);
		return $text;
	}


// ------------------------------------------------------------------------------
// Sprachelement mit zwei zus�tzlichen Parametern aus Sprachdatei holen
// ------------------------------------------------------------------------------
	function getLanguageValue2($phrase, $param1, $param2) {
		$text = htmlentities($this->LANG_CONF->get($phrase),ENT_COMPAT,'ISO-8859-1');
		$text = preg_replace("/{PARAM1}/", $param1, $text);
		$text = preg_replace("/{PARAM2}/", $param2, $text);
		return $text;
	}


}
?>
