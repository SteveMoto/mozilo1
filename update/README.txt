Update-Scripte für moziloCMS ??? auf 1.12

Diese Anleitung bezieht sich nur auf ein Standard-moziloCMS; 
eigene Erweiterungen müssen u.U. per Hand nachgezogen werden.

Es werden volgende Ordner behandelt kategorien, layouts und galerien

1.  auf dem Webserver ein neues Verzeichnis erstellen 
    (z.B "neumozilo").

2.  moziloCMS 1.12 herunterladen, entpacken und den Inhalt nach 
    "neumozilo" übertragen.

3.  im Verzeichnis "neumozilo" die Verzeichnisse "kategorien" 
    und "galerien" mit diesen Verzeichnissen aus der alten 
    moziloCMS-Installation ersetzen.

4.  das Verzeichnis des bisher verwendeten Layouts in das 
    Verzeichnis "layouts" der neuen CMS-Installation kopieren.

5.  aus dem alten moziloCMS folgende Dateien direkt ins 
    Verzeichnis "neumozilo/update" kopieren:
    - admin/conf/basic.conf
    - admin/conf/logindata.conf
    - conf/downloads.conf
    - conf/main.conf
    - conf/syntax.conf
    - formular/formular.conf

6.  im Browser [neumozilo]/update/update.php aufrufen und den 
    Anweisungen folgen.

7.  prüfen, ob die neue Installation sauber funktionierte siehe log.txt

8.  alte Installation löschen und durch die neue ersetzen

9.  das Verzeichnis "update" in der neuen Installation löschen

10. viel Spass mit moziloCMS 1.12 :-)


Sachen die zu beachten sind !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

Aufbau eines Templates:

    Template name/
                css/
                grafiken/
                template.html
                gallerytemplate.html
                favicon.ico
                layoutsettings.conf

    Es solten nur die Dateien und Ordner die zum Aufbau des Templates gehören enthalten sein.
    Wenn im grafiken/ Ordner Bilder mit Sonderzeichen enthalten sind mus nach dem Update
    das Template entsprechend angepast werden.

Styles die in den css Dateien geändert werden:

    Neu hinzugekommen in Version 1.12:
        /* -------------------------------------------------------- */
        /* [zentriert|...] */
        /* --------------- */
        .aligncenter {
            text-align:center;
        }

        /* -------------------------------------------------------- */
        /* [links|...] */
        /* ----------- */
        .alignleft {
            text-align:left;
        }

        /* -------------------------------------------------------- */
        /* [rechts|...] */
        /* ------------ */
        .alignright {
            text-align:right;
        }

        /* -------------------------------------------------------- */
        /* [block|...] */
        /* ----------- */
        .alignjustify {
            text-align:justify;
        }

        /* -------------------------------------------------------- */
        /* {TABLEOFCONTENTS} */
        /* ----------------- */
        div.tableofcontents ul ul {
            /*padding-left:15px;*/
        }
        div.tableofcontents li.blind {
            list-style-type:none;
            list-style-image:none;
        }

        fieldset#searchfieldset {
           border:none;
           margin:0px;
           padding:0px;
        }


    Neu hinzugekommen in Version 1.11:
        /* -------------------------------------------------------- */
        /* Kontaktformular */
        /* --------------- */
        form#contact_form {
        }
        table#contact_table {
        }
        table#contact_table td {
            vertical-align:top;
            padding:5px;
        }
        span#contact_errormessage{
            color:#880000;
            font-weight:bold;
        }
        span#contact_successmessage{
            color:#008800;
            font-weight:bold;
        }
        input#contact_name, input#contact_mail, input#contact_website {
            width:200px;
        }
        textarea#contact_message {
            width:200px;
        }
        input#contact_submit {
            width:200px;
        }


    Styles die sich ab Version 1.11 geändert haben:

        div.imagesubtitle       nach    span.imagesubtitle
        div.leftcontentimage    nach    span.leftcontentimage
        div.rightcontentimage   nach    span.rightcontentimage
        em.deadlink             nach    span.deadlink
        em.highlight            nach    span.highlight
        b {                     nach    b.contentbold {
        i {                     nach    i.contentitalic {
        u {                     nach    u.contentunderlined {
        s {                     nach    s.contentstrikethrough {

    ACHTUNG !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    Styles die von Hand geändert werden müssen betrieft sehr alte Versionen:

        Suchen sie bitte in ihrer css Datei
        nach [bild|...], [bildlinks|...] und [bildrechts|...]
        und Ersetzen es mit dem hier und passen es an
            /* -------------------------------------------------------- */
            /* [bild|...] */
            /* ---------- */
            img {
                border:none;
            }

            span.imagesubtitle {
                margin:3px 3px;
                text-align:justify;
                font-size:87%;
            }

            /* -------------------------------------------------------- */
            /* [bildlinks|...] */
            /* --------------- */
            span.leftcontentimage {
                margin:6px 20px 6px 0px;
                float:left;
            }

            img.leftcontentimage {
            }

            /* -------------------------------------------------------- */
            /* [bildrechts|...] */
            /* ---------------- */
            span.rightcontentimage {
                margin:6px 0px 6px 20px;
                float:right;
            }

            img.rightcontentimage {
            }


        Suchen sie bitte in ihrer css Datei nach den volgenden Styles
        und Ersetzen es mit dem volgenden und passen es an

        für em.bold das
            b.contentbold {
            }

        für em.italic das
            i.contentitalic {
            }

        für em.underlined das
            u.contentunderlined {
            }

        für em.crossed das
            s.contentstrikethrough {
            }

        Achtung em.bolditalic gibt es nicht mehr sie müssen in den Inhaltseiten [fettkursiv|]
        durch [fett|[kursiv|]] ersetzen und den style em.bolditalic { ???? }
        in css Datei entfernen






