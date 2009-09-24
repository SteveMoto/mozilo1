<?php

/***************************************************************
* Die Funktion mu� exakt "get[NAME_DER_VARIABLE]" hei�en.
* Sie bekommt einen String-Parameter �bergeben.
***************************************************************/

function getDEMO_VARIABLE($value) {
    
    
    /***************************************************************
    * Es kann auf s�mtliche Variablen und Funktionen der index.php 
    * zugegriffen werden.
    * 
    * Der Wert, mit dem die Variable ersetzt werden soll, mu� per
    * "return" zur�ckgegeben werden.
    * 
    * Der String-Parameter $value ist der Wert bei erweiterten
    * Variablen: {VARIABLE|wert}
    * Ist die Variable nicht erweitert ( {VARIABLE} ), wird $value
    * als Leerstring ("") �bergeben.
    * Man kann den $value-Parameter nutzen, mu� es aber nicht.
    * 
    * Beispiele:
    ***************************************************************/
    
    
    
    // Nutzung des Parameters mit mehreren kommaseparierten Werten
    // (werden in das Array $values gepackt)
    // - Nutzung: {DEMO_VARIABLE|Wert1,Wert2,Wert3,...}
    // - Ausgabe: Der erste Wert ist Wert1
    $values = explode(",", $value);
    // return ("Der erste Wert ist ".$values[0]); // zum Testen entkommentieren!
    
    
    
    // Nutzung des Parameters mit CMS-Variablen - Namen aktueller 
    // Inhaltsseite in Gro�buchstaben zur�ckgeben:
    // - Nutzung: {DEMO_VARIABLE|{PAGE_NAME}}
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
    else if ($stunde <= 23) {
        $begruessung ="Guten Abend!";
    }
    else {
        $begruessung ="Hallo!";
    }
    return $begruessung; // zum Testen entkommentieren!
    
}

?>