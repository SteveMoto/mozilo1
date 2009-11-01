<?php
/* 
* 
* $Revision$
* $LastChangedDate$
* $Author$
*
*/


/* 
* Abstrakte Basisklasse f�r moziloCMS-Plugins.
*
* PHP4 kennt das Prinzip der Abstraktion noch nicht,
* deswegen ist es durch die Hintert�r implementiert:
* Im Konstruktor wird sichergestellt, da� niemand 
* diese abstrakte Klasse hier direkt instanziieren 
* kann; dann wird gepr�ft, ob erbende Klassen auch 
* sauber alle wichtigen Funktionen implementieren.

*/

class Plugin {
    
    var $error;
    
    /*
    * Konstruktor
    */
    function Plugin(){
        // diese (abstrakte) Klasse darf nicht direkt instanziiert werden!
        if (get_class($this) == 'Plugin' || !is_subclass_of ($this, 'Plugin')){
            trigger_error('This class is abstract; it cannot be instantiated.', E_USER_ERROR);
        }

        // pr�fen, ob alle "abstrakten" Methoden implementiert wurden:
        $this->error = null;
        $this->checkForMethod("getContent");
        $this->checkForMethod("getConfig");
        $this->checkForMethod("getInfo");
    }
    
    /*
    * Gibt den Inhalt des Plugins zur�ck
    */
    function getPluginContent($param) {
        // erst pr�fen, ob bei der Initialisierung ein Fehler aufgetreten ist
        if ($this->error == null) {
            return $this->getContent($param);
        }
        // Bei Fehler: Inhalt der Fehlervariablen zur�ckgeben
        else {
            return $this->error;
        }
    }
    
    /*
    * Pr�ft, ob das Objekt eine Methode mit dem �bergebenen Namen besitzt
    */
    function checkForMethod($method) {
        // wenn die Methode nicht existiert, wird die Fehlervariable gef�llt
        if (!method_exists($this, $method)) {
            $syntax = new Syntax();
            $language = new Language();
            $this->error = $syntax->createDeadlink("{".get_class($this)."}", $language->getLanguageValue2("plugin_error_missing_method_2", get_class($this), $method));
        }
    }
    
}
?>