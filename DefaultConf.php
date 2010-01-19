<?php

# Alle Platzhalter als array mit Beschreibung
function makePlatzhalter() {
    $platzhalter = array('{CATEGORY}',
                        '{CATEGORY_NAME}',
                        '{PAGE}',
                        '{PAGE_NAME}',
                        '{PAGE_FILE}',
                        '{PREVIOUS_PAGE}',
                        '{PREVIOUS_PAGE_NAME}',
                        '{PREVIOUS_PAGE_FILE}',
                        '{NEXT_PAGE}',
                        '{NEXT_PAGE_NAME}',
                        '{NEXT_PAGE_FILE}',
                        '{SEARCH}',
                        '{LASTCHANGE}',
                        '{SITEMAPLINK}',
                        '{CMSINFO}',
                        '{CONTACT}',
                        '{TABLEOFCONTENTS}',
                        '{CURRENTGALLERY}');

    return $platzhalter;
}

# $conf_datei = voller pfad und conf Dateiname oder nur Array Name
function makeDefaultConf($conf_datei) {
    $basic = array(
                    'text' => array(
                        'adminmail' => '',
                        'language' => 'deDE',
                        'noupload' => 'php,php3,php4,php5'),
                    'digit' => array(
                        'backupmsgintervall' => '30',
                        'chmodnewfilesatts' => '',
                        'lastbackup' => time(),
                        'maximageheight' => '',
                        'maximagewidth' => '',
                        'maxnumberofuploadfiles' => '5',
                        'textareaheight' => '270'),
                    'checkbox' => array(
                        'overwriteuploadfiles' => 'false',
                        'sendadminmail' => 'false',
                        'showTooltips' => 'true',
                        'usebigactionicons' => 'false',
                        'showexpert' => 'false'),
                    # das sind die Expert Parameter von basic
                    'expert' => array(
                        'noupload',
                        'backupmsgintervall',
                        'lastbackup',
                        'maxnumberofuploadfiles',
                        'showTooltips',
                        'textareaheight',
                        'usebigactionicons',
                        'overwriteuploadfiles')
                    );

    $main = array(
                    'text' => array(
                        'shortenlinks' => '0',
                        'titlebarseparator' => '%20%3A%3A%20',
                        'usesubmenu' => '1',
                        'websitedescription' => '',
                        'websitekeywords' => '',
                        'websitetitle' => 'moziloCMS%20-%20Das%20CMS%20f%FCr%20Einsteiger'),
                    'select' => array(
                        'cmslanguage' => 'Deutsch',
                        'cmslayout' => 'moziloCMS%202009',
                        'defaultcat' => '10_Willkommen',
#                        'menu2' => 'no_menu2',
                        'titlebarformat' => '%7BWEBSITE%7D'),
                    'checkbox' => array(
                        'hidecatnamedpages' => 'false',
                        'modrewrite' => 'false',
                        'replaceemoticons' => 'true',
                        'showhiddenpagesasdefaultpage' => ' false',
                        'showhiddenpagesincmsvariables' => ' false',
                        'showhiddenpagesinlastchanged' => 'false',
                        'showhiddenpagesinsearch' => 'false',
                        'showhiddenpagesinsitemap' => 'false',
                        'showsyntaxtooltips' => 'true',
                        'targetblank_download' => 'true',
                        'targetblank_link' => 'true',
                        'usecmssyntax' => 'true'),
                    # das sind die Expert Parameter von main
                    'expert' => array(
                        'hidecatnamedpages',
                        'modrewrite',
                        'showhiddenpagesasdefaultpage',
                        'showhiddenpagesincmsvariables',
                        'showhiddenpagesinlastchanged',
                        'showhiddenpagesinsearch',
                        'showhiddenpagesinsitemap',
                        'targetblank_download',
                        'targetblank_link',
                        'showsyntaxtooltips',
                        'replaceemoticons',
                        'shortenlinks',
                        'usecmssyntax',
                        'usesubmenu')
                    );

    $syntax = array('wikipedia' => '[link={DESCRIPTION}|http://de.wikipedia.org/wiki/{VALUE}]');

    $formular = array('formularmail' => '',
                        'contactformusespamprotection' => 'true',
                        'contactformwaittime' => '15',
                        'mail' => ',true,true',
                        'message' => ',true,true',
                        'name' => ',true,true',
                        'website' => ',true,true');

    $logindata = array('falselogincount' => '0',
                        'falselogincounttemp' => '0',
                        'initialpw' => 'true',
                        'initialsetup' => 'true',
                        'loginlockstarttime' => '',
                        'name' => 'admin',
                        'pw' => '19ad89bc3e3c9d7ef68b89523eff1987');

    $downloads = array('_downloadcounterstarttime' => time());

    $version = array('cmsversion' => '1.12',
                        'cmsname' => 'Amalia',
                        'revision' => '384');

    $gallery = array('digit' => array(
                        'maxheight' => '',
                        'maxwidth' => '',
                        'maxthumbheight' => '100',
                        'maxthumbwidth' => '100',
                        'gallerypicsperrow' => '4'),
                    'checkbox' => array(
                        'usethumbs' => 'true'), # reihen folge ist wichtig
                    'text' => array(
                        'target' => '_self'),
                    'expert' => array(
                        'usethumbs',
                        'maxthumbheight',
                        'maxthumbwidth',
                        'gallerypicsperrow')
                    );

    $aufgaben = array('3 + 7' => '10',
                        '5 - 3' => '2',
                        '1 plus 1' => '2',
                        '17 minus 7' => '10',
                        '4 * 2' => '8',
                        '3x3' => '9',
                        '2 durch 2' => '1',
                        'Elvis Presleys Vorname' => 'Elvis',
                        'Angela Merkels Nachname' => 'Merkel',
                        'Bronze, Silber, ...?' => 'Gold');

    $passwords = array('# Kategorie/Inhaltsseite' => 'password');

    # ist eine *.conf datei angegeben wird das jeweilige array ohne expert und nur der inhalt der subarrays zurückgegeben
    if(strpos($conf_datei,".conf") > 0) {
        $name = substr(basename($conf_datei),0,-(strlen(".conf")));
        # beim erzeugen duerfen sub arrays nicht mit rein
        foreach($$name as $key => $value) {
            if($key == "expert") continue;
            if(is_array($value)) {
                foreach($value as $key => $value) {
                    $return_array[$key] = $value;
                }
            } else {
                $return_array = $$name;
                break;
            }
        }
        return $return_array;
    # ist es keine *.conf einfach das ganze array zurück
    } else {
        return $$conf_datei;
    }
}
?>