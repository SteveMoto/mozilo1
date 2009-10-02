<?php

/***************************************************************
* Demo-Plugin f�r moziloCMS.
* 
* 
* Jedes moziloCMS-Plugin mu�...
* - als Verzeichnis [PLUGINNAME] unterhalb von "plugins" liegen.
* - eine index.php enthalten, in der eine Klasse "[PLUGINNAME]" 
*   definiert ist.
* 
* Diese Plugin-Klasse mu�...
* - von der Klasse "Plugin" erben ("class [PLUGINNAME] extends Plugin")
* - folgende Funktionen enthalten:
*   - getContent($value)
*     -> gibt die HTML-Ersetzung der Plugin-Variable zur�ck.
*     -> der String-Parameter ist Pflicht
*   - getConfig()
*     -> gibt den HTML-Code zum Einf�gen der Plugin-Settings im Admin zur�ck.
*   - getInfo()
*     -> gibt ein Array von Plugin-Infos zur�ck (in dieser Reihenfolge):
*        - Name des Plugins
*        - Version des Plugins
*        - Kurzbeschreibung
*        - Name des Autors
*        - Download-URL
* 
***************************************************************/
class DEMOPLUGIN extends Plugin {

    /***************************************************************
    * 
    * Gibt den HTML-Code zur�ck, mit dem die Plugin-Variable ersetzt wird.
    * 
    ***************************************************************/
    function getContent($value) {
    
        /***************************************************************
        * Es kann auf s�mtliche Variablen und Funktionen der index.php 
        * zugegriffen werden.
        * 
        * Der Wert, mit dem die Plugin-Variable ersetzt werden soll, mu�
        * per "return" zur�ckgegeben werden.
        * 
        * Der String-Parameter $value ist der Wert bei erweiterten
        * Plugin-Variablen: {VARIABLE|wert}
        * Ist die Variable nicht erweitert ( {VARIABLE} ), wird $value
        * als Leerstring ("") �bergeben.
        * Man kann den $value-Parameter nutzen, mu� es aber nicht.
        * 
        * Beispiele:
        ***************************************************************/
    
    
        // Nutzung des Parameters mit mehreren kommaseparierten Werten
        // (werden in das Array $values gepackt)
        // - Nutzung: {DEMOPLUGIN|Wert1,Wert2,Wert3,...}
        // - Ausgabe: Der erste Wert ist Wert1
        $values = explode(",", $value);
        // return ("Der erste Wert ist ".$values[0]); // zum Testen entkommentieren!
    
    
        // Nutzung des Parameters mit CMS-Variablen - Namen aktueller 
        // Inhaltsseite in Gro�buchstaben zur�ckgeben:
        // - Nutzung: {DEMOPLUGIN|{PAGE_NAME}}
        // return (strtoupper($value)); // zum Testen entkommentieren!
    
    
        // Auslesen des Website-Titels aus der CMS-Konfiguration:
        global $mainconfig;
        $titelderseite = $mainconfig->get("websitetitle");
        // return $titelderseite; // zum Testen entkommentieren!
    
    
        // Aufruf der Funktion, die das Hauptmen� erstellt:
        $hauptmenue = getMainMenu();
        // return $hauptmenue; // zum Testen entkommentieren!
    
    
        // Sicheres Auslesen eines �bergebenen POST- bzw. GET-Parameters:
        $anfrage = getRequestParam("parameter", true);
        // return $anfrage; // zum Testen entkommentieren!
    
    
        // Tageszeitabh�ngige Begr��ung:
        $stunde = date("H");
        if ($stunde <= 10) {
            $begruessung ="Guten Morgen!";
        }
        else if ($stunde <= 16) {
            $begruessung ="Guten Tag!";
        }
        else {
            $begruessung ="Guten Abend!";
        }
        return $begruessung; // zum Testen entkommentieren!
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
            "Plugin-Demo",
            // Plugin-Version
            "1.0",
            // Kurzbeschreibung
            "Beispiel-Plugin, das die M�glichkeiten des Plugin-Systems von moziloCMS aufzeigt",
            // Name des Autors
            "mozilo",
            // Download-URL
            "http://cms.mozilo.de"
            );
    } // function getInfo

} // class DEMOPLUGIN

?>