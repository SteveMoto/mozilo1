<?php

/* 
* 
* $Revision$
* $LastChangedDate$
* $Author$
*
*/

class Syntax {
    
    var $LANG;
    var $LINK_REGEX;
    var $MAIL_REGEX;
    var $TARGETBLANK_LINK;
#    var $TARGETBLANK_DOWNLOAD;
    var $anchorcounter;
    var $headlineinfos;
#    var $firstconvertContent;
// ------------------------------------------------------------------------------    
// Konstruktor
// ------------------------------------------------------------------------------
    function Syntax(){
        global $CMS_CONF;
        global $USER_SYNTAX;
        global $activ_plugins;
        global $deactiv_plugins;

        // Regulärer Audruck zur überprüfung von Links
        // überprüfung auf Validität >> protokoll :// (username:password@) [(sub.)server.tld|ip-adresse] (:port) (subdirs|files)
                    // protokoll                (https?|t?ftps?|gopher|telnets?|mms|imaps?|irc|pop3s?|rdp|smb|smtps?|sql|ssh):\/\/
                    // username:password@       (\w)+\:(\w)+\@
                    // (sub.)server.tld         ((\w)+\.)?(\w)+\.[a-zA-Z]{2,4}
                    // ip-adresse (ipv4)        ([\d]{1,3}\.){3}[\d]{1,3}
                    // port                     \:[\d]{1,5}
                    // subdirs|files            (\w)+
        $this->LINK_REGEX   = "/^(https?|t?ftps?|gopher|telnets?|mms|imaps?|irc|pop3s?|rdp|smb|smtps?|sql|ssh|svn)\:\/\/((\w)+\:(\w)+\@)?[((\w)+\.)?(\w)+\.[a-zA-Z]{2,4}|([\d]{1,3}\.){3}[\d]{1,3}](\:[\d]{1,5})?((\w)+)?$/";
        // Punycode-URLs können beliebige Zeichen im Domainnamen enthalten!
        // $this->MAIL_REGEX   = "/^\w[\w|\.|\-]+@\w[\w|\.|\-]+\.[a-zA-Z]{2,4}$/";
        $this->MAIL_REGEX   = "/^.+@.+\..+$/";
        
        // Externe Links in neuem Fenster öffnen?
        if ($CMS_CONF->get("targetblank_link") == "true") {
            $this->TARGETBLANK_LINK = " target=\"_blank\"";
        }
        else {
            $this->TARGETBLANK_LINK = "";
        }
        // Download-Links in neuem Fenster öffnen?
/*        if ($CMS_CONF->get("targetblank_download") == "true") {
            $this->TARGETBLANK_DOWNLOAD = " target=\"_blank\"";
        }
        else {
            $this->TARGETBLANK_DOWNLOAD = "";
        }*/
        $this->anchorcounter            = 1;

        $syntax_elemente = get_class_methods($this);
        $syntax_array = array();
        foreach($syntax_elemente as $element) {
            if(substr($element,0,strlen("syntax_")) == "syntax_")
                $syntax_array[] = substr($element,strlen("syntax_"));
        }

        $this->syntax_user = $USER_SYNTAX->toArray();
        foreach($this->syntax_user as $user => $inhalt) {
            $syntax_array[] = $user;
        }
        # Damit zuerst z.B. nach links und dann nach link gesucht wird sonst wird links als link gefunden
        rsort($syntax_array);

        $syntax_such = "/\[(".implode("=|",$syntax_array)."=|".implode("|",$syntax_array).")([^\[\]\{\}]*)\|([^\[\]\{\}]*)\]/Um";
        $syntax_such_rest = "/\[(".implode("=|",$syntax_array)."=|".implode("|",$syntax_array).")([^\|]*)\|(.*)\]/m";

        $this->activ_plugins = $activ_plugins;
        $this->deactiv_plugins = $deactiv_plugins;
        $plugin = array_merge($this->activ_plugins, $this->deactiv_plugins);
        # Das gleiche hier mit Plugins siehe rsort weiter oben
        rsort($plugin);

        $plugin_such = "/\{(".implode("|",$plugin).")\|([^\[\]\{\}]*)\}/Um";
        $plugin_such_rest = "/\{(".implode("|",$plugin).")\|(.*)\}/m";
        $plugin_such_ohne = "/\{(".implode("|",$plugin).")\}/Um";
        $this->SYNTAX_SEARCH = $syntax_such;
        $this->PLUGIN_SEARCH = $plugin_such;
        $this->SYNTAX_SEARCH_REST = $syntax_such_rest;
        $this->PLUGIN_SEARCH_REST = $plugin_such_rest;
        $this->PLUGIN_SEARCH_OHNE = $plugin_such_ohne;
        $this->script_search = array();
        $this->script_replace = array();
        $this->pluginself['placeholder'] = array();
        $this->pluginself['replace'] = array();
#        $this->firstconvertContent = false;
    }

    function match_syntax_plugins() {
        /*
        Der array aufbau der zurückgegeben wird ist immer so
        0 = match
        1 = plugin syntax
        2 = description
        3 = value
        */
        # die { und } bei denn Platzhalter ändern nach -platz~ und -platzend~
        $this->change_placeholder();

        preg_match_all($this->SYNTAX_SEARCH, $this->content, $matches_syntax);
        $syntax = false;
        if(isset($matches_syntax[0]) and count($matches_syntax[0]) > 0) {
            $syntax = true;
        }
        preg_match_all($this->PLUGIN_SEARCH, $this->content, $matches_plugins);
        # hier stimt der array aufbau nicht deshalb passen wir in an
        $matches_plugins[3] = $matches_plugins[2];

        preg_match_all($this->PLUGIN_SEARCH_OHNE, $this->content, $matches_plugins_ohne);
        if(isset($matches_plugins_ohne[0]) and count($matches_plugins_ohne[0]) > 0) {
            # hier stimt der array aufbau nicht deshalb passen wir in an
            $matches_plugins_ohne[2] = array_fill(0, count($matches_plugins_ohne[0]), '');
            $matches_plugins_ohne[3] = array_fill(0, count($matches_plugins_ohne[0]), '');
            $matches_plugins[0] = array_merge($matches_plugins[0], $matches_plugins_ohne[0]);
            $matches_plugins[1] = array_merge($matches_plugins[1], $matches_plugins_ohne[1]);
            $matches_plugins[2] = array_merge($matches_plugins[2], $matches_plugins_ohne[2]);
            $matches_plugins[3] = array_merge($matches_plugins[3], $matches_plugins_ohne[3]);
        }

        $plugins = false;
        if(isset($matches_plugins[0]) and count($matches_plugins[0]) > 0) {
            $plugins = true;
        }
        # syntax gefunden plugins nicht
        if($syntax and !$plugins) {
            return $matches_syntax;
        }
        # plugins gefunden syntax nicht
        if(!$syntax and $plugins) {
            return $matches_plugins;
        }
        # plugins und syntax gefunden
        if($syntax and $plugins) {
            # aus gefunden syntax und plugins ein array machen
            $result[0] = array_merge($matches_syntax[0], $matches_plugins[0]);
            $result[1] = array_merge($matches_syntax[1], $matches_plugins[1]);
            $result[2] = array_merge($matches_syntax[2], $matches_plugins[2]);
            $result[3] = array_merge($matches_syntax[3], $matches_plugins[3]);
            return $result;
        }
        # mit den "SYNTAX_SEARCH, PLUGIN_SEARCH, PLUGIN_SEARCH_OHNE"
        # such parametern wurde nichts gefunden
        # dann versuchen wir es noch mit den "SYNTAX_SEARCH_REST, PLUGIN_SEARCH_REST"
        preg_match_all($this->SYNTAX_SEARCH_REST, $this->content, $matches_syntax);
        $syntax = false;
        if(isset($matches_syntax[0]) and count($matches_syntax[0]) > 0) {
            $syntax = true;
        }
        preg_match_all($this->PLUGIN_SEARCH_REST, $this->content, $matches_plugins);
        # hier stimt der array aufbau nicht deshalb passen wir in an
        $matches_plugins[3] = $matches_plugins[2];

        $plugins = false;
        if(isset($matches_plugins[0]) and count($matches_plugins[0]) > 0) {
            $plugins = true;
        }
        # syntax gefunden plugins nicht
        if($syntax and !$plugins) {
            return $matches_syntax;
        }
        # plugins gefunden syntax nicht
        if(!$syntax and $plugins) {
            return $matches_plugins;
        }
        # plugins und syntax gefunden
        if($syntax and $plugins) {
            # aus gefunden syntax und plugins ein array machen
            $result[0] = array_merge($matches_syntax[0], $matches_plugins[0]);
            $result[1] = array_merge($matches_syntax[1], $matches_plugins[1]);
            $result[2] = array_merge($matches_syntax[2], $matches_plugins[2]);
            $result[3] = array_merge($matches_syntax[3], $matches_plugins[3]);
            return $result;
        }
    }
// ------------------------------------------------------------------------------
// Umsetzung der übergebenen CMS-Syntax in HTML, Rückgabe als String
// ------------------------------------------------------------------------------
    function convertContent($content, $cat, $firstrecursion) {
#        if($this->firstconvertContent)
#            die("convertContent darf nur einmal aufgerufen werden");
#        $this->firstconvertContent = true;
        $this->content = $content;
        $this->cat = $cat;
        if ($firstrecursion) {
            $this->content = $this->prepareContent($this->content);
            // Überschriften einlesen
            $this->headlineinfos = $this->getHeadlineInfos($this->content);
        }

        // Erstmal mit dummy ersetzen: Horizontale Linen
#        $this->content = str_replace('[----]', '~hr-', $this->content);
        $matches = $this->match_syntax_plugins();
$not_exit = 0;
$not_exit_max = 20;

        while(isset($matches[0]) and count($matches[0]) > 0) {
if($not_exit >= $not_exit_max)
    break;
            foreach($matches[1] as $pos => $function) {
                # weil nach syntax= gesucht wird enthält das array auch syntax=
                if(substr($function,-1) == "=")
                    $function = substr($function,0,-1);
                $replace = NULL;
                # alle <script und <style sachen raus
                $this->find_script_style();
                # Plugin
                if(in_array($function,$this->activ_plugins) or in_array($function,$this->deactiv_plugins)) {
                    $replace = $this->plugin_replace($function,$matches[3][$pos]);
                # Syntax
                } elseif(method_exists($this, "syntax_".$function)) {
                    $tmp_syntax = "syntax_".$function;
                    $replace = $this->$tmp_syntax($matches[2][$pos],$matches[3][$pos]);
                # User Syntax
                } elseif(isset($this->syntax_user[$function])) {
                    $replace = $this->syntax_user($matches[2][$pos],$matches[3][$pos],$function);
                # unbekant
#!!!!!!! hier fehlermeldung ?????????????
                } else {
                    $special_search = array('[',']','{','}','|');
                    $special_replace = array('&#091;','&#093;','&#123;','&#125;','&#124;');
                    $match = str_replace($special_search,$special_replace,$matches[0][$pos]);
                    $replace = '<span style="color:red;font-weight:bold;text-decoration:line-through;">'.$match.'</span>';
                }
                $this->content = str_replace($matches[0][$pos],$replace,$this->content);
                # wenn ein Plugin an sich was übergeben hat
                $this->replacePluginSelfPlaceholderData();
            }
$not_exit++;
            $matches = $this->match_syntax_plugins();
        }
if($not_exit >= $not_exit_max)
    echo "ACHTUNG NOT EXIT PRÜFEN<br>\n";

        # script und style sachen wieder einsetzen
        $this->find_script_style(false);

        # Platzhalter wieder herstellen
        $this->change_placeholder(false);

        # Horizontale Linen ersetzen
        $this->content = str_replace('[----]', '<hr class="horizontalrule" />', $this->content);
        // dummy mit Horizontale Linen ersetzen
#        $this->content = preg_replace('/\~hr-/', '<hr class="horizontalrule" />', $this->content);

        # Zeilenümbrüche sind in pages später html umbrüche
        $this->content = str_replace("-br~","<br />",$this->content);
#        $content = preg_replace('/(\r\n|\r|\n)/', '$1<br />', $content);

        $this->content = str_replace("-nbsp~","&nbsp;",$this->content);

        // Zeilenwechsel nach Blockelementen entfernen
        // Tag-Beginn                                       <
        // optional: Slash bei schließenden Tags            (\/?)
        // Blockelemente                                    (address|blockquote|div|dl|fieldset|form|h[123456]|hr|noframes|noscript|ol|p|pre|table|ul|center|dir|isindex|menu)
        // optional: sonstige Zeichen (z.B. Attribute)      ([^>]*)
        // Tag-Ende                                         >
        // optional: Zeilenwechsel                          (\r\n|\r|\n)?
        // <br /> mit oder ohne Slash (das, was raus muß!)  <br \/? >
/*preg_match_all('/<(\/?)(address|blockquote|div|dl|fieldset|form|h[123456]|hr|noframes|noscript|ol|p|pre|table|th|tr|td|ul|center|dir|isindex|menu)([^>]*)>(\r\n|\r|\n)?<br \/?>/',$this->content,$test);
echo "<pre>";
print_r($test);
echo "</pre><br>\n";*/
        $this->content = preg_replace('/<(\/?)(address|blockquote|div|dl|fieldset|form|h[123456]|hr|noframes|noscript|ol|p|pre|table|th|tr|td|ul|center|dir|isindex|menu)([^>]*)>(\r\n|\r|\n)?<br \/?>/', "<$1$2$3>$4",$this->content);
        // direkt aufeinanderfolgende Listen zusammenführen
        $this->content = preg_replace('/<\/ul>(\r\n|\r|\n)?<ul class="unorderedlist">/', '', $this->content);
        // direkt aufeinanderfolgende numerierte Listen zusammenführen
        $this->content = preg_replace('/<\/ol>(\r\n|\r|\n)?<ol class="orderedlist">/', '', $this->content);
        # Table Hack recursive Table
#        $this->content = str_replace('&#38;', '&', $this->content);

        // Zeilenwechsel in Include-Tags wiederherstellen    
#        $this->content = preg_replace('/{newline_in_include_tag}/', "\n", $this->content);
        // Zeilenwechsel in HTML-Tags wiederherstellen    
#        $this->content = preg_replace('/{newline_in_html_tag}/', "\n", $this->content);

$this->content = str_replace(array("-html_lt~","-html_gt~"),array("&lt;","&gt;"),$this->content);
global $specialchars;
$this->content = $specialchars->decodeProtectedChr($this->content);

#echo "return content";
        return $this->content;
    }


    # wenn ein Plugin einen Platzhalter erstelt um in später mit eigenen
    # inhalt ersetzen möchte. Siehe z.B. das SideBar Plugin
    function pluginSelfPlaceholderData($placeholder,$replace) {
        $this->pluginself['placeholder'][] = $placeholder;
        $this->pluginself['replace'][] = $replace;
    }

    # ersetze vom Plugin selbst erzeugte Platzhalter mit dem Inhalt vom Plugin
    function replacePluginSelfPlaceholderData() {
        # einlesen des arrays mit dem von Plugins übergeben inhalt
        foreach($this->pluginself['placeholder'] as $pos => $placeholder) {
            # nur ersetzen wenn im content auch der zu ersetzende platzhalter enthalten ist
            # ist nötig fals das Plugin den ersetzende platzhalter noch nicht gesetzt hat
            if(strstr($this->content,$placeholder)) {
                $this->content = str_replace($placeholder,$this->pluginself['replace'][$pos],$this->content);
                # der platzhalter wurde ersetzt aus dem array entfernen
                unset($this->pluginself['placeholder'][$pos],$this->pluginself['replace'][$pos]);
            }
        }
    }

    function change_placeholder($find = true) {
        if($find) {
            # alle {????} mussen raus auser {PLUGIN}
            preg_match_all("/\{([^\[\]\{\}\|]*)\}/Um", $this->content, $placeholder);
            if(isset($placeholder[0]) and count($placeholder[0]) > 0) {
                foreach($placeholder[0] as $pos => $search) {
                    if(in_array($placeholder[1][$pos],$this->activ_plugins) or in_array($placeholder[1][$pos],$this->deactiv_plugins))
                        continue;
                    $replace = '-platz~'.$placeholder[1][$pos].'-platzend~';
                    $this->content = str_replace($search,$replace,$this->content);
                }
            }
        } else {
            $this->content = str_replace(array('-platz~','-platzend~'),array('{','}'),$this->content);
        }
    }

    function find_script_style($find = true) {
        if($find) {
            # script und style einträge suchen
            preg_match_all("/\<script(.*)\<\/script>/Umsi", $this->content, $script);
            preg_match_all("/\<style(.*)\<\/style>/Umsi", $this->content, $style);
            $script_style = array_merge($script[0], $style[0]);

            # aufräumen und $this->script_???? erzeugen
            foreach($script_style as $script_match) {
                # wenn lehr nächsten nehmen
                if(empty($script_match))
                    continue;
                $dummy = '<!-- dummy script style '.count($this->script_search).' -->';
                # bei den styles sind gleiche einträge unötig
                if(substr($script_match,0,6) == "<style" and in_array($script_match,$this->script_replace)) {
                    # deshalb dummy lehr
                    $dummy = NULL;
                }
                # script und style ersetzen mit dummy
                $this->content = str_replace($script_match,$dummy,$this->content);
                # wenn dummy nicht lehr arrays füllen
                if(!empty($dummy)) {
                    $this->script_search[] = $dummy;
                    $this->script_replace[] = $script_match;
                }
            }
        } else {
            # script und style sachen wieder einsetzen
            $this->content = str_replace($this->script_search,$this->script_replace,$this->content);
        }
    }

    function syntax_link($desciption,$value) {
        // externer Link
        global $CMS_CONF;
        global $language;
        global $specialchars;
        // überprüfung auf korrekten Link
        if(preg_match($this->LINK_REGEX, $value)) {
            if(empty($desciption)) {
                $desciption = $value;
                switch ($CMS_CONF->get("shortenlinks")) {
                    // mit "http://www." beginnende Links ohne das "http://www." anzeigen
                    case 2: { 
                        if (substr($value, 0, 11) == "http://www.")
                            $desciption = substr($value, 11, strlen($value)-11);
                        // zusätzlich: mit "http://" beginnende Links ohne das "http://" anzeigen
                        elseif (substr($value, 0, 7) == "http://")
                            $desciption = substr($value, 7, strlen($value)-7);
                        break;
                    }
                    // mit "http://" beginnende Links ohne das "http://" anzeigen
                    case 1: { 
                        if (substr($value, 0, 7) == "http://")
                            $desciption = substr($value, 7, strlen($value)-7);
                        break;
                    }
                    default: { 
                    }
                }
            }
            # erstmal alle HTML Zeichen wandeln
            $link = $specialchars->getHtmlEntityDecode($value);
            # alle url encodete Zeichen wandeln
            $link = $specialchars->rebuildSpecialChars($link,false,false);
            # alles url encodeten
            $link = $specialchars->replaceSpecialChars($link,false);
            # alle :,?,&,;,= zurück wandeln
            $link = str_replace(array('%3A','%3F','%26','%3B','%3D'),array(':','?','&amp;',';','='),$link);
            return "<a class=\"link\" href=\"$link\"".$this->getTitleAttribute($language->getLanguageValue1("tooltip_link_extern_1", $value)).$this->TARGETBLANK_LINK.">".$desciption."</a>";
        } else {
            if(empty($desciption))
                $desciption = $value;
            return $this->createDeadlink($desciption, $language->getLanguageValue1("tooltip_link_extern_error_1", $value));
        }
    }

    function syntax_mail($desciption,$value) {
        // Mail-Link mit eigenem Text
        global $language;
        global $specialchars;
        $dead = $desciption;
        if(empty($desciption)) {
            $desciption = obfuscateAdress("$value", 3);
            $dead = $value;
        }
        // überprüfung auf korrekten Link
        if (preg_match($this->MAIL_REGEX, $value)) {
            return "<a class=\"mail\" href=\"".obfuscateAdress("mailto:$value", 3)."\"".$this->getTitleAttribute($language->getLanguageValue1("tooltip_link_mail_1", obfuscateAdress("$value", 3))).">".$desciption."</a>";
        } else {
            return $this->createDeadlink($dead, $language->getLanguageValue1("tooltip_link_mail_error_1", $value));
        }

    }

    function syntax_kategorie($desciption,$value) {
        // Kategorie-Link (überprüfen, ob Kategorie existiert)
        // Kategorie-Link mit eigenem Text
        global $language;
        global $CatPage;

        $cat = $CatPage->get_AsKeyName($value, true);

        $link_text = $desciption;
        if(empty($desciption)) {
            $link_text = $CatPage->get_HrefText($cat,false);
        }

        if($CatPage->exists_CatPage($cat,false)) {
            return $CatPage->create_LinkTag($CatPage->get_Href($cat,false)
                    ,$link_text
                    ,"category"
                    ,$language->getLanguageValue1("tooltip_link_category_1", $value)
                    );
        } else {
            return $this->createDeadlink($value, $language->getLanguageValue1("tooltip_link_category_error_1", $value));
        }
    }

    function syntax_seite($desciption,$value) {
        // Link auf Inhaltsseite in aktueller oder anderer Kategorie (überprüfen, ob Inhaltsseite existiert)
        // Link auf Inhaltsseite in aktueller oder anderer Kategorie mit beliebigem Text
        global $specialchars;
        global $language;
        global $CatPage;

        list($cat,$page) = $CatPage->split_CatPage_fromSyntax($value,$this->cat);

        if(!$CatPage->exists_CatPage($cat,false)) {
            $cat_text = $specialchars->rebuildSpecialChars($cat,true,true);
            return $this->createDeadlink($cat_text, $language->getLanguageValue1("tooltip_link_category_error_1", $cat_text));
        }
        if(!$CatPage->exists_CatPage($cat,$page)) {
            $cat_text = $specialchars->rebuildSpecialChars($cat,true,true);
            $page_text = $specialchars->rebuildSpecialChars($page,true,true);
            return $this->createDeadlink($page_text, $language->getLanguageValue2("tooltip_link_page_error_2", $page_text, $cat_text));
        }
        $link_text = $desciption;
        if(empty($desciption)) {
            $link_text = $CatPage->get_HrefText($cat,$page);
        }
        return $CatPage->create_LinkTag($CatPage->get_Href($cat,$page)
                    ,$link_text
                    ,"page"
                    ,$language->getLanguageValue2("tooltip_link_page_2", $specialchars->rebuildSpecialChars($page,true,true), $specialchars->rebuildSpecialChars($cat,true,true))
                    );
    }

    function syntax_absatz($desciption,$value) {
        // Verweise auf Absätze innerhalb der Inhaltsseite
        global $language;
        // Beschreibungstext extrahieren
        if(!empty($desciption)) {
            $link_text = $desciption;
        }
        else {
            $link_text = $value;
        } 
        $pos = 0;
        foreach ($this->headlineinfos as $headline_info) {
            // $headline_info besteht aus Überschriftstyp (1/2/3) und Wert
            if ($headline_info[1] == $value) {
                // "Nach oben"-Verweis
                if ($pos == 0)
                    return "<a class=\"paragraph\" href=\"#a$pos\"".$this->getTitleAttribute($language->getLanguageValue0("tooltip_anchor_gototop_0")).">$link_text</a>";
                // sonstige Anker-Verweise
                else
                    return "<a class=\"paragraph\" href=\"#a$pos\"".$this->getTitleAttribute($language->getLanguageValue1("tooltip_anchor_goto_1", $value)).">$link_text</a>";
            }
            $pos++;
        }
        return $this->createDeadlink($value, $language->getLanguageValue1("tooltip_anchor_error_1", $value));
    }
        

    function syntax_datei($desciption,$value) {
        // Datei aus dem Dateiverzeichnis (überprüfen, ob Datei existiert)
        // Datei aus dem Dateiverzeichnis mit beliebigem Text
        global $specialchars;
        global $language;
        global $CatPage;
        global $CMS_CONF;

        list($cat,$datei) = $CatPage->split_CatPage_fromSyntax($value,$this->cat,true);

        if(!$CatPage->exists_File($cat,$datei)) {
            $cat_text = $specialchars->rebuildSpecialChars($cat,true,true);
            $datei_text = $specialchars->rebuildSpecialChars($datei,true,true);
            return $this->createDeadlink($datei_text, $language->getLanguageValue2("tooltip_link_file_error_2", $datei_text, $cat_text));
        }
        $link_text = $desciption;
        if(empty($desciption)) {
            $link_text = $specialchars->rebuildSpecialChars($datei,true,true);
        }
        // Download-Links in neuem Fenster öffnen?
        $target = false;
        if ($CMS_CONF->get("targetblank_download") == "true")
            $target = "_blank";
        return $CatPage->create_LinkTag($CatPage->get_HrefFile($cat,$datei)
                    ,$link_text
                    ,"file"
                    ,$language->getLanguageValue2("tooltip_link_file_2", $specialchars->rebuildSpecialChars($datei,true,true), $specialchars->rebuildSpecialChars($cat,true,true))
                    ,$target);
    }

    function syntax_galerie($desciption,$value) {
        // Galerie
        global $specialchars;
        $cleanedvalue = $specialchars->replaceSpecialChars($specialchars->getHtmlEntityDecode($value),false);
        $link_text = "";
        if(!empty($desciption)) {
            $link_text = ",".$desciption;
        }
        return '{Galerie|'.$cleanedvalue.$link_text.'}';
    }

    function syntax_bildlinks($desciption,$value) {
        return $this->syntax_bild($desciption,$value,"bildlinks");
    }
    function syntax_bildrechts($desciption,$value) {
        return $this->syntax_bild($desciption,$value,"bildrechts");
    }
    function syntax_bild($desciption,$value,$syntax = "bild") {
        // Bild aus dem Dateiverzeichnis oder externes Bild
        global $specialchars;
        global $CONTENT_DIR_REL;
        global $CONTENT_FILES_DIR_NAME;
        global $URL_BASE;
        global $CONTENT_DIR_NAME;
        global $language;
        // Bildunterschrift merken, wenn vorhanden
        $subtitle = "";
        if(!empty($desciption))
            $subtitle = $desciption;

        $imgsrc = false;

        $value = $specialchars->getHtmlEntityDecode($value);
        // Bei externen Bildern: $value NICHT nach ":" aufsplitten!
        if (preg_match($this->LINK_REGEX, $value)) {
            $imgsrc = $value;
        }

        // Ansonsten: Nach ":" aufsplitten
        else {
            global $CatPage;
            global $CMS_CONF;

            list($cat,$datei) = $CatPage->split_CatPage_fromSyntax($value,$this->cat,true);

            if(!$CatPage->exists_File($cat,$datei)) {
                $cat_text = $specialchars->rebuildSpecialChars($cat,true,true);
                $datei_text = $specialchars->rebuildSpecialChars($datei,true,true);
                return $this->createDeadlink($datei_text, $language->getLanguageValue2("tooltip_image_error_2", $datei_text, $cat_text));
            }
            $imgsrc = $CatPage->get_srcFile($cat,$datei);
        }

        // Nun aber das Bild ersetzen!
        if ($imgsrc) {
            $alt = $specialchars->rebuildSpecialChars($value,true,true);
            $cssclass = "";
            if ($syntax == "bild") {
                $cssclass = "contentimage";
            }
            if ($syntax == "bildlinks") {
                $cssclass = "leftcontentimage";
            }
            elseif ($syntax == "bildrechts") {
                $cssclass = "rightcontentimage";
            }
            // ohne Untertitel
            if ($subtitle == "") {
                // normales Bild: ohne <span> rundrum
                if ($syntax == "bild") {
                    return "<img src=\"$imgsrc\" alt=\"".$language->getLanguageValue1("alttext_image_1", $alt)."\" class=\"$cssclass\" />";
                }
                else {
                    return "<span class=\"$cssclass\"><img src=\"$imgsrc\" alt=\"".$language->getLanguageValue1("alttext_image_1", $alt)."\" class=\"$cssclass\" /></span>";
                }
            }
            // mit Untertitel
            else {
                return "<span class=\"$cssclass\"><img src=\"$imgsrc\" alt=\"".$language->getLanguageValue1("alttext_image_1", $alt)."\" class=\"$cssclass\" /><br /><span class=\"imagesubtitle\">$subtitle</span></span>";
            }
        }
    }

    function syntax_links($desciption,$value) {
        // linksbündiger Text
        return "<p class=\"alignleft\">".$value."</p>";
    }

    function syntax_zentriert($desciption,$value) {
        // zentrierter Text
        return "<p class=\"aligncenter\">".$value."</p>";
    }

    function syntax_block($desciption,$value) {
        // Text im Blocksatz
        return "<p class=\"alignjustify\">".$value."</p>";
    }

    function syntax_rechts($desciption,$value) {
        // rechtsbündiger Text
        return "<p class=\"alignright\">".$value."</p>";
    }

    function syntax_fett($desciption,$value) {
        // Text fett
        return "<b class=\"contentbold\">$value</b>";
    }

    function syntax_kursiv($desciption,$value) {
        // Text kursiv
        return "<i class=\"contentitalic\">$value</i>";
    }

    function syntax_fettkursiv($desciption,$value) {
        // Text fettkursiv 
        // (VERALTET seit Version 1.7 - nur aus Gründen der Abwärtskompatibilität noch mitgeführt)
        return "<b class=\"contentbold\"><i class=\"contentitalic\">$value</i></b>";
    }

    function syntax_unter($desciption,$value) {
        // Text unterstrichen
        return "<u class=\"contentunderlined\">$value</u>";
    }

    function syntax_durch($desciption,$value) {
        // Text durchgestrichen
        return "<s class=\"contentstrikethrough \">$value</s>";
    }

    function syntax_ueber1($desciption,$value) {
        // Überschrift groß
        return "<h1 id=\"a".$this->anchorcounter++."\" class=\"heading1\">$value</h1>";
    }

    function syntax_ueber2($desciption,$value) {
        // Überschrift mittel
        return "<h2 id=\"a".$this->anchorcounter++."\" class=\"heading2\">$value</h2>";
    }

    function syntax_ueber3($desciption,$value) {
        // Überschrift normal
        return "<h3 id=\"a".$this->anchorcounter++."\" class=\"heading3\">$value</h3>";
    }

    function syntax_liste($desciption,$value) {
        // Listenpunkt unorderedlist listitem
        return '<ul class="unorderedlist"><li class="listitem">'.$value.'</li></ul>';
    }

    function syntax_numliste($desciption,$value) {
        // numerierter Listenpunkt orderedlist
        return '<ol class="orderedlist"><li class="listitem">'.$value.'</li></ol>';
    }

    function syntax_liste1($desciption,$value) {
        // Liste, einfache Einrückung
        // (VERALTET seit Version 1.10 - nur aus Gründen der Abwärtskompatibilität noch mitgeführt)
        return "<ul><li>$value</li></ul>";
    }

    function syntax_liste2($desciption,$value) {
        // Liste, doppelte Einrückung
        // (VERALTET seit Version 1.10 - nur aus Gründen der Abwärtskompatibilität noch mitgeführt)
        return "<ul><ul><li>$value</li></ul></ul>";
    }

    function syntax_liste3($desciption,$value) {
        // Liste, dreifache Einrückung
        // (VERALTET seit Version 1.10 - nur aus Gründen der Abwärtskompatibilität noch mitgeführt)
        return "<ul><ul><ul><li>$value</li></ul></ul></ul>";
    }

    function syntax_html($desciption,$value) {
#        global $specialchars;
#        $nobrvalue = preg_replace('/(\r\n|\r|\n)/m', '{newline_in_html_tag}', $value);
        # Wichtig alle &#???; (sind die zeichen mit ^ dafor) nach &amp;#???; wandel damit
        # getHtmlEntityDecode nicht das Zeichen herstellt
#       $nobrvalue = preg_replace("/\&\#(\d+)\;/Umsie", "'&amp;#\\1;'", $nobrvalue);
#       $nobrvalue = $specialchars->getHtmlEntityDecode($nobrvalue);

        $value = str_replace("-nbsp~","",$value);
        $value = str_replace("-br~","",$value);
        # alle < und > im html code wieder herstellen
        $value = str_replace(array("&lt;","&gt;"),array("<",">"),$value);

        return $value;
    }

    function syntax_tabelle($desciption,$value) {
        // Tabellen
        $tabellecss = "contenttable";
        if(!empty($desciption))
            # was nach dem = steht wird als class name verwendet
            $tabellecss = $desciption;
        // Tabelleninhalt aufbauen
        $tablecontent = "";
        // Tabellenzeilen

        preg_match_all("/(&lt;|&lt;&lt;)(.*)(&gt;|&gt;&gt;)/Umsie", $value, $tablelines);
        foreach ($tablelines[0] as $j => $tablematch) {
            // Kopfzeilen
            if (preg_match("/&lt;&lt;([^&gt;]*)/Umsi", $tablematch)) {
                $linecontent = preg_replace('/\|/', '</th><th class="'.$tabellecss.'">', $tablelines[2][$j]);
                $linecontent = preg_replace('/&lt;(.*)/', "$1", $linecontent);
                $tablecontent .= '<tr><th class="'.$tabellecss.'">'.$linecontent.'</th></tr>';
            }
            // normale Tabellenzeilen
            else {
                // CSS-Klasse immer im Wechsel
                $cssline = $tabellecss."1";
                if ($j%2 == 0) {
                    $cssline = $tabellecss."2";
                }
                // Pipes durch TD-Wechsel ersetzen
                $linecontent = explode("|",$tablelines[2][$j]);
                $tablecontent .= "<tr>";
                foreach($linecontent as $pos => $td_content) {
                    # td css vortlaufend nummerieren mit 1 anfangen
                    $tablecontent .= '<td class="'.$cssline.' '.$tabellecss."cell".($pos + 1).'">'.$td_content.'</td>';
                }
                $tablecontent .= "</tr>";
            }
        }

        return '<table class="'.$tabellecss.'" cellspacing="0" border="0" cellpadding="0" summary="">'.$tablecontent.'</table>';
    }

    function syntax_include($desciption,$value) {
        // Includes
        global $CONTENT_DIR_REL;
        global $EXT_PAGE, $EXT_HIDDEN;
        global $PAGE_REQUEST;
        global $specialchars;
        global $language;
        global $CatPage;

        list($cat,$page) = $CatPage->split_CatPage_fromSyntax($value,$this->cat);

        if(!$CatPage->exists_CatPage($cat,false)) {
            $cat_text = $specialchars->rebuildSpecialChars($cat,true,true);
            return $this->createDeadlink($cat_text, $language->getLanguageValue1("tooltip_link_category_error_1", $cat_text));
        }
        if(!$CatPage->exists_CatPage($cat,$page)) {
            $cat_text = $specialchars->rebuildSpecialChars($cat,true,true);
            $page_text = $specialchars->rebuildSpecialChars($page,true,true);
            return $this->createDeadlink($page_text, $language->getLanguageValue2("tooltip_link_page_error_2", $page_text, $cat_text));
        }
        $link_text = $desciption;
        if(empty($desciption)) {
            $link_text = $CatPage->get_HrefText($cat,$page);
        }

        // Seite darf sich nicht selbst includen!
        if (($cat == substr($this->cat,3)) and ($page == substr($PAGE_REQUEST,3))) {
            return $this->createDeadlink($value, $language->getLanguageValue0("tooltip_include_recursion_error_0"));
        }
        // Includierte Inhaltsseite parsen
        else {
            # wenn cat page schonn im merker ist fehlermeldung weil sonst
            # include endlosschleife
            $incl_catpage = $CatPage->get_AsKeyName($cat).":".$CatPage->get_AsKeyName($page);
            if(isset($CatPage->SyntaxIncludeRemember[$incl_catpage]))
                return $this->createDeadlink($CatPage->get_HrefText($cat,false).":".$CatPage->get_HrefText($cat,$page), $language->getLanguageValue0("tooltip_include_recursion_error_0"));
            else {
                if(false !== ($pagecontent = $CatPage->get_PageContent($cat,$page))) {
                    # include merker setzen
                    $CatPage->SyntaxIncludeRemember[$incl_catpage] = $CatPage->get_AsKeyName($page);
                    # ist eine Inhaltseite also inhalt vorbereiten
                    $pagecontent = $this->prepareContent($pagecontent);
                    return $pagecontent;
                }
            }
        }
    }

    function syntax_farbe($desciption,$value) {
        // Farbige Elemente
        global $language;
        // Überprüfung auf korrekten Hexadezimalwert
        if (preg_match("/^([a-f]|\d){6}$/i", $desciption)) {
            return "<span style=\"color:#".$desciption.";\">".$value."</span>";
        }
        else {
            return $this->createDeadlink($value, $language->getLanguageValue1("tooltip_color_error_1", $desciption));
        }
    }

    function syntax_user($desciption,$value,$syntax) {
        global $USER_SYNTAX;
        // Platzhalter {VALUE} im definierten Syntaxelement ersetzen
        $replacetext = str_replace("{VALUE}", $value, $USER_SYNTAX->get($syntax));
        // Platzhalter {DESCRIPTION} im definierten Syntaxelement durch die Beschreibung ersetzen
        $replacetext = str_replace("{DESCRIPTION}", $desciption, $replacetext);
        return $replacetext;
    }

    function plugin_replace($plugin,$plugin_parameter) {
        global $PLUGIN_DIR_REL;
        global $URL_BASE;
        global $PLUGIN_DIR_NAME;
        global $language;
        if(in_array($plugin, $this->activ_plugins)) {
            $replacement = NULL;
            // ...ueberpruefen, ob es eine zugehörige Plugin-PHP-Datei gibt
            if(file_exists($PLUGIN_DIR_REL.$plugin."/index.php")) {
                // Plugin-Code includieren
                require_once($PLUGIN_DIR_REL.$plugin."/index.php");
            }
            $plugin_true = true;
            // Enthaelt der Code eine Klasse mit dem Namen des Plugins?
            if(class_exists($plugin)) {
                // Objekt instanziieren und Inhalt holen!
                if(!isset($this->$plugin))
                    $this->$plugin = new $plugin();
                $replacement = $this->$plugin->getPluginContent($plugin_parameter);
            } else {
                $plugin_true = false;
                $replacement = $this->createDeadlink($plugin, $language->getLanguageValue1("plugin_error_1", $plugin));
            }
            if($plugin_true and !in_array($plugin, $this->deactiv_plugins)
                and file_exists($PLUGIN_DIR_REL.$plugin."/plugin.css")
                ) {
                $css = '<style type="text/css"> @import "'.$URL_BASE.$PLUGIN_DIR_NAME.'/'.$plugin.'/plugin.css"; </style>';
                if(strpos($this->content,$css) < 1 and !in_array($css,$this->script_replace)) {
                    $dummy = '<!-- dummy script style '.count($this->script_search).' -->';
                    $this->script_search[] = $dummy;
                    $this->script_replace[] = $css;
                    $this->content = str_replace(array("</head>","</HEAD>"),$dummy."\n</head>",$this->content);
                }
            }
            # return Plugin inhalt
            return $replacement;
        } elseif(in_array($plugin, $this->deactiv_plugins)) {
            # Deactiviertes Plugin mit nichts ersetzen
            return NULL;
        }
        return NULL;
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: "title"-Attribut zusammenbauen (oder nicht, wenn nicht konfiguriert)
// ------------------------------------------------------------------------------
    function getTitleAttribute($value) {
        global $CMS_CONF;
        if ($CMS_CONF->get("showsyntaxtooltips") == "true") {
            return " title=\"".$value."\"";
        }
        return "";
    }

// ------------------------------------------------------------------------------
// Inhaltsverzeichnis aus den übergebenen Überschrift-Infos aufbauen
// ------------------------------------------------------------------------------
    function getToC($pagerequest) {
        global $language;
        $tableofcontents = "<div class=\"tableofcontents\">";
        if (count($this->headlineinfos) > 1) {
            $tableofcontents .= "<ul>";
            // Schleife über Überschriften-Array (0 ist der Seitenanfang - auslassen)
            for ($toc_counter=1; $toc_counter < count($this->headlineinfos); $toc_counter++) {
                $link = "<a class=\"page\" href=\"#a$toc_counter\"".$this->getTitleAttribute($language->getLanguageValue1("tooltip_anchor_goto_1", $this->headlineinfos[$toc_counter][1])).">".$this->headlineinfos[$toc_counter][1]."</a>";
                if ($this->headlineinfos[$toc_counter][0] >= "2") {
                    $tableofcontents .= "<li class=\"blind\"><ul>";
                }
                if ($this->headlineinfos[$toc_counter][0] >= "3") {
                    $tableofcontents .= "<li class=\"blind\"><ul>";
                }
                $tableofcontents .= "<li class=\"toc_".$this->headlineinfos[$toc_counter][0]."\">".$link."</li>";
                if ($this->headlineinfos[$toc_counter][0] >= "2") {
                    $tableofcontents .= "</ul></li>";
                }
                if ($this->headlineinfos[$toc_counter][0] >= "3") {
                    $tableofcontents .= "</ul></li>";
                }
            }
            $tableofcontents .= "</ul>";
        }
        $tableofcontents .= "</div>";
        return $tableofcontents;
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: Überschrift-Infos einlesen
// ------------------------------------------------------------------------------
    function getHeadlineInfos($content) {
        global $language;
        // "absatz"-Links vorbereiten: Alle Überschriften einlesen
        preg_match_all("/\[(ueber([\d]))\|([^\[\]]+)\]/", $content, $matches);
        // $headlines besteht aus Arrays, die zwei Werte beinhalten: Überschriftstyp (1/2/3) und Wert
        $headlines = array();
        $headlines[0] = array("0", $language->getLanguageValue0("anchor_top_0"));

        $i = 0;
        foreach ($matches[0] as $match) {
            // gefundene Überschriften im Array $headlines merken
            $headlines[$i+1] = (array($matches[2][$i], $matches[3][$i]));
            //echo ($i+1) ." >>> ". $matches[2][$i].", ".$matches[3][$i]."<hr>";
            $i++;
        }
        return $headlines;
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: content heraus filtern und ersetzen
// ------------------------------------------------------------------------------
    function getReplaceContent() {
        echo "!!!!!!!!!!getReplaceContent solten wir einfüren";
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: sachen im head einfügen aber nur wenn sie nocht drin sind
// ------------------------------------------------------------------------------
    function getReplaceHead() {
        echo "!!!!!!!!!!getReplaceContent solten wir einfüren";
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: Inhalte vorbereiten
// ------------------------------------------------------------------------------
    function prepareContent($content) {
        global $specialchars;

        $content_search = false;
        if(strstr($content,'---content~~~') and strstr($content,'~~~content---')) {
            $tmp_content = $content;
            $start = strpos($content,"---content~~~");
            $length = (strpos($content,"~~~content---") + strlen("~~~content---")) - $start;
            $content = substr($content,$start,$length);
            $content_search = $content;
        }

        // Inhaltsformatierungen
        # alle &lt; und &gt; die in einer page sind sollen so sein
        $content = str_replace(array("&lt;","&gt;"),array("-html_lt~","-html_gt~"),$content);
        # alle < und > in &lt; und &gt; wandeln damit sie nicht als html tags angezeigt werden
        $content = str_replace(array("<",">"),array("&lt;","&gt;"),$content);

#        $content = preg_replace("/&amp;#036;/Umsi", "&#036;", $content);
#        $content = preg_replace("/&amp;#092;/Umsi", "&#092;", $content);
#        $content = preg_replace("/\^(.)/Umsie", "'&#'.ord('\\1').';'", $content);
#        $content = $specialchars->numeric_entities_decode($content); 
        # alle zeichen die ein ^ davor sind geschützte zeichen
        $content = $specialchars->encodeProtectedChr($content);
        // Für Einrückungen
        $content = str_replace("  ","-nbsp~-nbsp~",$content);
        # Zeilenümbrüche sind in pages später html umbrüche
#        $content = preg_replace('/(\r\n|\r|\n)/', '$1<br />', $content);
        $content = preg_replace('/(\r\n|\r|\n)/', '$1-br~', $content);
/*        $content = preg_replace('/<(\/?)(address|blockquote|div|dl|fieldset|form|h[123456]|hr|noframes|noscript|ol|p|pre|table|ul|center|dir|isindex|menu)([^>]*)>(\r\n|\r|\n)?<br \/?>/', "<$1$2$3>$4",$content);*/
        if($content_search) {
            $content = str_replace($content_search,$content,$tmp_content);
        }
        // Platzhalter ersetzen
        $content = replacePlaceholders($content, "", "");

        return $content;
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: Deadlink erstellen
// ------------------------------------------------------------------------------
    function createDeadlink($content, $title) {
        return "<span class=\"deadlink\"".$this->getTitleAttribute($title).">$content</span>";
    }

}

?>