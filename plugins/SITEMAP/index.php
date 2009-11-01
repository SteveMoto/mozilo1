<?php

/***************************************************************
* Sitemap-Plugin f�r moziloCMS.
* 
* Mit der Variablen {SITEMAP} kann an beliebiger Stelle des CMS
* (Template oder Inhaltsseiten) die aktuelle Sitemap eingef�gt 
* werden.
***************************************************************/

class SITEMAP extends Plugin {

    /***************************************************************
    * 
    * Gibt den HTML-Code zur�ck, mit dem die Plugin-Variable ersetzt wird.
    * 
    ***************************************************************/
    function getContent($value) {
        $sitemapinfo = getSitemap();
        return $sitemapinfo[0];
    } // function getContent
    
    
    
    /***************************************************************
    * 
    * Gibt den HTML-Code f�r die Plugin-Settings im Admin zur�ck.
    * 
    ***************************************************************/
    function getConfig() {
        return "Keine Einstellungen m�glich bzw. n�tig. No settings available or required.";
    } // function getConfig
    
    
    
    /***************************************************************
    * 
    * Gibt die Plugin-Infos als Array zur�ck.
    * 
    ***************************************************************/
    function getInfo() {
        return array(
            // Plugin-Name
            "Sitemap",
            // Plugin-Version
            "1.0",
            // Kurzbeschreibung
            "Sitemap zum Einf�gen an beliebiger Stelle",
            // Name des Autors
            "mozilo",
            // Download-URL
            "http://cms.mozilo.de"
            );
    } // function getInfo

} // class SITEMAP

?>