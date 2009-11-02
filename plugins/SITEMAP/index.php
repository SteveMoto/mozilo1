<?php

/***************************************************************
*
* Sitemap-Plugin f�r moziloCMS.
* 
* Mit der Variablen {SITEMAP} kann an beliebiger Stelle des CMS
* (Template oder Inhaltsseiten) die aktuelle Sitemap eingef�gt 
* werden.
* 
***************************************************************/

class SITEMAP extends Plugin {

    /***************************************************************
    * 
    * Gibt den HTML-Code zur�ck, mit dem die Plugin-Variable ersetzt 
    * wird. Der String-Parameter $value ist Pflicht, kann aber leer 
    * sein.
    * 
    ***************************************************************/
    function getContent($value) {
        $sitemapinfo = getSitemap();
        return $sitemapinfo[0];
    } // function getContent
    
    
    
    /***************************************************************
    * 
    * Gibt die Konfigurationsoptionen als Array zur�ck.
    * Ist keine Konfiguration n�tig, gibt die Funktion false zur�ck.
    * 
    ***************************************************************/
    function getConfig() {
        return false; // keine Konfiguration n�tig
    } // function getConfig
    
    
    
    /***************************************************************
    * 
    * Gibt die Plugin-Infos als Array zur�ck - in dieser 
    * Reihenfolge:
    *   - Name des Plugins
    *   - Version des Plugins
    *   - Kurzbeschreibung
    *   - Name des Autors
    *   - Download-URL
    * 
    ***************************************************************/
    function getInfo() {
        return array(
            // Plugin-Name
            "Sitemap",
            // Plugin-Version
            "1.0",
            // Kurzbeschreibung
            "Standard-Sitemap zum Einf�gen an beliebiger Stelle",
            // Name des Autors
            "mozilo",
            // Download-URL
            "http://cms.mozilo.de"
            );
    } // function getInfo

} // class SITEMAP

?>