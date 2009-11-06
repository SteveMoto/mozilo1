<?php

/***************************************************************
* 
* Demo-Plugin f�r moziloCMS.
* 
* 
* Jedes moziloCMS-Plugin mu�...
* - als Verzeichnis [PLUGINNAME] unterhalb von "plugins" liegen.
* - im Pluginverzeichnis eine plugin.conf mit den Plugin-
*   Einstellungen enthalten (diese kann auch leer sein).
* - eine index.php enthalten, in der eine Klasse "[PLUGINNAME]" 
*   definiert ist.
* 
* Die Plugin-Klasse mu�...
* - von der Klasse "Plugin" erben ("class [PLUGINNAME] extends Plugin")
* - folgende Funktionen enthalten:
*   getContent($value)
*       -> gibt die HTML-Ersetzung der Plugin-Variable zur�ck
*   getConfig()
*       -> gibt die Konfigurationsoptionen als Array zur�ck
*   getInfo()
*       -> gibt die Plugin-Infos als Array zur�ck
* 
***************************************************************/
class DEMOPLUGIN extends Plugin {


	/***************************************************************
    * 
    * Gibt den HTML-Code zur�ck, mit dem die Plugin-Variable ersetzt 
    * wird. Der String-Parameter $value ist Pflicht, kann aber leer 
    * sein.
    * 
    ***************************************************************/
    function getContent($value) {
    
        /***************************************************************
        * 
        * Details (Beispiele folgen weiter unten):
        * 
        * Es kann auf s�mtliche Variablen und Funktionen der index.php 
        * zugegriffen werden.
        *
        * �ber die lokale Variable $this->settings hat man Zugriff auf 
        * die Werte der Plugin-eigenen plugin.conf.
        * 
        * Der Wert, mit dem die Plugin-Variable letztenendes ersetzt 
        * werden soll, mu� per "return" zur�ckgegeben werden.
        * 
        * Der String-Parameter $value entspricht dem Wert bei 
        * erweiterten Plugin-Variablen: {VARIABLE|wert}
        * Ist die Variable nicht erweitert ( {VARIABLE} ), wird $value
        * als Leerstring ("") �bergeben.
        * Man kann den $value-Parameter nutzen, mu� es aber nicht.
        * 
        ***************************************************************/
        

        /***************************************************************
        * Beispiel: Zugriff auf Werte aus der plugin.conf �ber das 
        * lokale Properties-Objekt $this->settings
        ***************************************************************/

        // Lesend: Der Wert des Schl�ssels "demosetting" wird aus der plugin.conf ausgelesen
        // return $this->settings->get("demosetting"); // zum Testen entkommentieren!
        // Schreibend: Die aktuelle Unixtime wird als "timestring" in die plugin.conf geschrieben ("timestring = 1234567890")
        // $this->settings->set("timestring", time()); // zum Testen entkommentieren!
    
    
        /***************************************************************
        * Beispiel: Nutzung des Parameters $value 
        * - Nutzung: {DEMOPLUGIN|moziloCMS rockt!}
        * - Ausgabe: Yeah! moziloCMS rockt!
        ***************************************************************/
    	
        // return ("Yeah! ".$value); // zum Testen entkommentieren!

    	
    	/***************************************************************
        * Beispiel: Nutzung des Parameters $value (der mehrere 
        * kommaseparierte Werte enth�lt)
        * - Nutzung: {DEMOPLUGIN|Wert1,Wert2,Wert3,...}
        * - Ausgabe: Der erste Wert ist Wert1
        ***************************************************************/
        
    	$values = explode(",", $value);
        // return ("Der erste Wert ist ".$values[0]); // zum Testen entkommentieren!
    
    
        /***************************************************************
        * Beispiel: Nutzung des Parameters $value mit CMS-Variablen 
        * - Nutzung: {DEMOPLUGIN|{PAGE_NAME}}
        * - Ausgabe (Bsp.): WILLKOMMEN
        ***************************************************************/
        
        // Namen aktueller Inhaltsseite in Gro�buchstaben zur�ckgeben
        // return (strtoupper($value)); // zum Testen entkommentieren!
    
    
        /***************************************************************
        * Beispiel: Auslesen des Website-Titels aus der CMS-
        * Konfiguration 
        ***************************************************************/

    	global $mainconfig; // eine globale Variable der index.php!
        // return $mainconfig->get("websitetitle"); // zum Testen entkommentieren!
    
    
        /***************************************************************
        * Beispiel: Anzeige des Hauptmen�s (durch Aufruf der Funktion 
        * der index.php, die das Hauptmen� erstellt) 
        ***************************************************************/
        
        // return getMainMenu(); // zum Testen entkommentieren!
    
    
        /***************************************************************
        * Beispiel: Sicheres Auslesen des POST- bzw. GET-Parameters 
        * "parameter" durch Aufruf der entsprechenden Hilfsfunktion der 
        * index.php 
        ***************************************************************/

        // return getRequestParam("parameter", true); // zum Testen entkommentieren!
    
    
        /***************************************************************
        * Beispiel: Beliebige Logik, hier eine tageszeitabh�ngige 
        * Begr��ung
        ***************************************************************/

        $stunde = date("H");
        if ($stunde <= 10) {
            return "Guten Morgen!";
        }
        else if ($stunde <= 16) {
            return "Guten Tag!";
        }
        else {
            return "Guten Abend!";
        }
    } // function getContent
    
    
    
    /***************************************************************
    * 
    * Gibt die Konfigurationsoptionen als Array zur�ck.
    * Ist keine Konfiguration n�tig, ist dieses Array leer.
    * 
    ***************************************************************/
    function getConfig() {

    	/***************************************************************
        * 
        * Details (Beispiele folgen weiter unten):
        * 
        * Die Funktion liefert ein Array zur�ck. Dieses enth�lt die 
        * Eingabefelder, mit denen der User im moziloAdmin Einstellungen 
        * am Plugin vornehmen kann.
        * Der "type"-Parameter der Eingabefelder bestimmt, um welche Art 
        * Eingabefeld es sich handelt und ist Pflicht. Folgende Werte
        * stehen zur Verf�gung:
        *   text            Textfeld (beliebiger Text)
        *   textarea        mehrzeilige Texteingabe
        *   password        Passwortfeld (Anzeige des Inhalts als ***)
        *   checkbox        Checkbox (ja/nein)
        *   radio           Radio-Buttons (entweder/oder)
        *   select          Auswahlliste
        *   file            Datei-Upload
        * 
        * Die Werte der Eingabefelder werden in die plugin.conf des 
        * Plugins geschrieben - der Name des Eingabefelds ist dabei der 
        * Schl�ssel in der plugin.conf (siehe Beispiele).
        * 
        ***************************************************************/
        
    	
    	// R�ckgabe-Array initialisieren
    	// Das mu� auf jeden Fall geschehen!
        $config = array();
        
        
        /***************************************************************
        * Beispiel: Normales Textfeld, beliebige Eingaben
        * - das Textfeld hei�t "texteingabe"; gibt der Benutzer "abc" 
        *   ein und speichert die Plugin-Einstellungen, wird in der 
        *   plugin.conf folgende Zeile angelegt bzw. �berschrieben:
        *   texteingabe = abc 
        ***************************************************************/
        
        $config['texteingabe']  = array(
            "type" => "text",                           // Pflicht:  Eingabetyp
            "description" => "Bitte Wert eingeben",     // optional: Beschreibung
            "maxlength" => "4",                         // optional: maximale L�nge
            "size" => "4",                              // optional: dargestellte Zeichen
            "regex" => "[a-z]{3}"                       // optional: Erlaubte Werte als regul�rer Ausdruck (hier: drei kleine Buchstaben; wird beim Speichern der Einstellungen �berpr�ft)
            );

            
        /***************************************************************
        * Beispiel: Mehrzeiliges Textfeld, beliebige Eingaben
        ***************************************************************/
            
        $config['mehrzeiligertext'] = array(
            "type" => "textarea",                       // Pflicht:  Eingabetyp 
            "cols" => "4",                              // Pflicht:  Spaltenanzahl 
            "rows" => "4",                              // Pflicht:  Zeilenanzahl
            "description" => "Bitte Text eingeben",     // optional: Beschreibung
            "regex" => ".*"                             // optional: Erlaubte Werte als regul�rer Ausdruck (hier: beliebige Zeichen; wird beim Speichern der Einstellungen �berpr�ft)
            );

            
        /***************************************************************
        * Beispiel: Passwortfeld, beliebige Eingaben
        ***************************************************************/
        
        $config['passwort']  = array(
            "type" => "password",                       // Pflicht:  Eingabetyp
            "saveasmd5" => "true",                      // Pflicht:  soll das Passwort MD5-verschl�sselt in der plugin.conf abgelegt werden? (true/false)
            "description" => "Bitte Passwort eingeben", // optional: Beschreibung
            "maxlength" => "4",                         // optional: maximale L�nge
            "size" => "4",                              // optional: dargestellte Zeichen
            "regex" => "^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$" // optional: Erlaubte Werte als regul�rer Ausdruck (hier: mindestens acht Zeichen, bestehend aus Klein- und Gro�buchstaben sowie Ziffern); wird beim Speichern der Einstellungen �berpr�ft
            );

            
        /***************************************************************
        * Beispiel: Checkbox (aktiv oder nicht aktiv)
        ***************************************************************/
        
        $config['janeinoption'] = array(
            "type" => "checkbox",                       // Pflicht:  Eingabetyp 
            "description" => "Ja oder nein?"            // optional: Beschreibung
            );

        
        /***************************************************************
        * Beispiel: Radio-Buttons (entweder oder)
        ***************************************************************/
        
        $config['entwederoder'] = array(
            "type" => "radio",                          // Pflicht:  Eingabetyp
            "contents" => array(                        // Pflicht:  Werte der einzelnen Optionen als Array
                "blau",
                "rot",
                "gruen"
                ),
            "description" => "Welche Farbe?"           // optional: Beschreibung 
            );
        
        
        /***************************************************************
        * Beispiel: Auswahlliste (Einzel- oder Mehrfachauswahl)
        ***************************************************************/
            
        $config['auswahl'] = array(
            "type" => "select",                         // Pflicht:  Eingabetyp
            "contents" => array(                        // Pflicht:  W�hlbare Elemente (kann auch ein leeres Array() sein)
                "Australien",
                "Venezuela",
                "Chemnitz"),
            "description" => "Ich mache Urlaub in...",  // optional: Beschreibung
            "multiple" => "false",                      // optional: Mehrfachauswahl erlauben
            "size" => "3"                               // optional: Gr��e
            ); 

        
        /***************************************************************
        * Beispiel: Datei-Upload
        ***************************************************************/
        
        $config['datei'] = array(
            "type" => "file",                           // Pflicht:  Eingabetyp
            "uploaddir" => "",                          // Ablageverzeichnis f�r die hochgeladene Datei (relativer Pfad vom Plugin-Verzeichnis aus; leer lassen hei�t, da� Dateien im Plugin-Wurzelverzeichnis abgelegt werden) 
            "description" => "Diese Datei hochladen:"   // optional: Beschreibung
            );

            
        // Nicht vergessen: Das gesamte Array zur�ckgeben
        return $config;
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