<?php
$ADMIN_TITLE = "moziloAdmin";

/* Login �berpr�fen */
	session_start();
	if (!$_SESSION['login_okay'])
		header("location:login.php?logout=true");

	require_once("filesystem.php");
	require_once("string.php");
	$ADMIN_CONF	= new Properties("conf/basic.conf");
	$CMS_CONF	= new Properties("../main.conf");
	
/* Pfade */
	$CONTENT_DIR_NAME		= "kategorien";
	$CONTENT_DIR_REL		= "../".$CONTENT_DIR_NAME;
	$GALLERIES_DIR_NAME	= "galerien";
	$GALLERIES_DIR_REL	= "../".$GALLERIES_DIR_NAME;
	$PREVIEW_DIR_NAME		= "vorschau";



/* Aktion abh�ngig vom action-Parameter */
	$action = $_GET['action'];
	if ($action == "")
		$action = $_POST['action'];
	$functionreturn = array();
	
	// Kategorien
	if ($action=="category")
		$functionreturn = category();
	elseif ($action=="newcategory")
		$functionreturn = newCategory();
	elseif ($action=="editcategory")
		$functionreturn = editCategory();
	elseif ($action=="deletecategory")
		$functionreturn = deleteCategory();
	// Inhaltsseiten
	elseif ($action=="site")
		$functionreturn = site();
	elseif ($action=="newsite")
		$functionreturn = newSite();
	elseif ($action=="editsite")
		$functionreturn = editSite();
	elseif ($action=="deletesite")
		$functionreturn = deleteSite();
	// Galerien
	elseif ($action=="gallery")
		$functionreturn = gallery();
	elseif ($action=="newgallery")
		$functionreturn = newGallery();
	elseif ($action=="editgallery")
		$functionreturn = editGallery();
	elseif ($action=="deletegallery")
		$functionreturn = deleteGallery();
	// Dateien
	elseif ($action=="file")
		$functionreturn = files();
	elseif ($action=="newfile")
		$functionreturn = newFile();
	elseif ($action=="deletefile")
		$functionreturn = deleteFile();
	// Einstellungen
	elseif ($action=="config")
		$functionreturn = config();
	elseif ($action=="displaycmsconfig")
		$functionreturn = configCmsDisplay();
	elseif ($action=="displayadminconfig")
		$functionreturn = configAdminDisplay();
	elseif ($action=="loginadminconfig")
		$functionreturn = configAdminLogin();
	// Bei unbekanntem oder leerem action-Parameter: Startseite
	else
		$functionreturn = home();

	$pagetitle = $functionreturn[0];
	$pagecontent = $functionreturn[1];
	
	
/* Aufbau der gesamten Seite */
	$html = "<!doctype html public \"-//W3C//DTD HTML 4.01 Transitional//EN\">";
	$html .= "<html>";
	$html .= "<head>";
	$html .= "<script src=\"crossTooltips.js\" type=\"text/javascript\"></script>";
	$html .= "<script src=\"buttons.js\" type=\"text/javascript\"></script>";
	$html .= "<title>$ADMIN_TITLE - $pagetitle</title>";
	$html .= "<link rel=\"stylesheet\" href=\"adminstyle.css\" type=\"text/css\" />";
	$html .= "<link rel=\"stylesheet\" href=\"js_color_picker_v2/js_color_picker_v2.css\" media=\"screen\" type=\"text/css\" />";
	$html .= "<script type=\"text/javascript\" src=\"js_color_picker_v2/color_functions.js\"></script>";
	$html .= "<script type=\"text/javascript\" src=\"js_color_picker_v2/js_color_picker_v2.js\"></script>";
	$html .= "</head>";
	$html .= "<body onload=\"htmlOverlopen(document.documentElement,0)\">";
	
	// DEEEEEEEEEEEBUG ;)
	// Ausgabe aller �bergebenen Werte zu Testzwecken
	/*
	echo "<h2>POST</h2>";
	foreach ($_POST as $a => $b)
		echo $a." -> ".$b."<br />";
	echo "<h2>GET</h2>";
	foreach ($_GET as $a => $b)
		echo $a." -> ".$b."<br />";
	*/
	
	$html .= "<div id=\"mozilo_Logo\"></div>";
	$html .= "<div id=\"main_div\">";
	// Titelleiste
	$html .= "<div id=\"design_Title\">";
	$html .= "<a href=\"login.php?logout=true\" accesskey=\"x\"></a>";
	$html .= "<div id=\"design_Titletext\">$ADMIN_TITLE - $pagetitle</div>";
	$html .= "<a href=\"login.php?logout=true\" accesskey=\"".createNormalTooltip("button_home_logout", "button_home_logout_tooltip", 150)."\"><span id=\"design_Logout\"></span></a>";
	$html .= "</div>";
	// Titelleiste Ende
	$html .= "<div id=\"navi_left\">";
	
/* Men� */
	
	// Men�punkt "Home"
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=home\" accesskey=\"".createNormalTooltip("button_home", "button_home_tooltip", 150)."\"><span id=\"navi_btn_home\">".getLanguageValue("button_home")."</span></a>";
	//Men�punkt "Kategorien"
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=category\" accesskey=\"".createNormalTooltip("button_category", "button_category_tooltip", 150)."\"><span id=\"navi_btn_category\">".getLanguageValue("button_category")."</span></a>";
	// Men�punkt "Seiten"
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=site\" accesskey=\"".createNormalTooltip("button_site", "button_site_tooltip", 150)."\"><span id=\"navi_btn_site\">".getLanguageValue("button_site")."</span></a>";
	// Men�punkt "Galerie"
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=gallery\" accesskey=\"".createNormalTooltip("button_gallery", "button_gallery_tooltip", 150)."\"><span id=\"navi_btn_gallery\">".getLanguageValue("button_gallery")."</span></a>";
	// Men�punkt "Dateien"
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=file\" accesskey=\"".createNormalTooltip("button_data", "button_data_tooltip", 150)."\"><span id=\"navi_btn_upload\">".getLanguageValue("button_data")."</span></a>";
	// Men�punkt "Konfiguration"
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=config\" accesskey=\"".createNormalTooltip("button_config", "button_config_tooltip", 150)."\"><span id=\"navi_btn_help\">".getLanguageValue("button_config")."</span></a>";
	
/* Unterkategorien */	            	
/* Home */
	/* Logout */
	$html .= "<a class=\"leftmenu\" href=\"login.php?logout=true\" accesskey=\"".createNormalTooltip("button_home_logout", "button_home_logout_tooltip", 150)."\"><span id=\"home_logout\"></span></a>";
	            
/* Categories */
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=newcategory\" accesskey=\"".createNormalTooltip("button_category_new", "", 150)."\"><span id=\"kategorie_new\"> </span></a>";
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=editcategory\" accesskey=\"".createNormalTooltip("button_category_edit", "", 150)."\"><span id=\"kategorie_edit\"> </span></a>";
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=deletecategory\" accesskey=\"".createNormalTooltip("button_category_delete", "", 150)."\"><span id=\"kategorie_delete\"> </span></a>";
	            
/* Sites */
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=newsite\" accesskey=\"".createNormalTooltip("button_site_new", "", 150)."\"><span id=\"site_new\"> </span></a>";
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=editsite\" accesskey=\"".createNormalTooltip("button_site_edit", "", 150)."\"><span id=\"site_edit\"> </span></a>";
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=deletesite\" accesskey=\"".createNormalTooltip("button_site_delete", "", 150)."\"><span id=\"site_delete\"> </span></a>";
	            
/* Galleries */
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=newgallery\" accesskey=\"".createNormalTooltip("button_gallery_new", "", 150)."\"><span id=\"gallery_new\"> </span></a>";
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=editgallery\" accesskey=\"".createNormalTooltip("button_gallery_edit", "", 150)."\"><span id=\"gallery_edit\"> </span></a>";
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=deletegallery\" accesskey=\"".createNormalTooltip("button_gallery_delete", "", 150)."\"><span id=\"gallery_delete\"> </span></a>";
	            
/* Config */
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=displaycmsconfig\" accesskey=\"".createNormalTooltip("button_config_cms", "", 150)."\"><span id=\"config_cms\"> </span></a>";
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=displayadminconfig\" accesskey=\"".createNormalTooltip("button_config_admin", "", 150)."\"><span id=\"config_admin\"> </span></a>";
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=loginadminconfig\" accesskey=\"".createNormalTooltip("button_config_pw", "", 150)."\"><span id=\"config_login\"> </span></a>";

/* Files */
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=newfile\" accesskey=\"".createNormalTooltip("button_data_new", "", 150)."\"><span id=\"upload_new\"> </span></a>";
	$html .= "<a class=\"leftmenu\" href=\"index.php?action=deletefile\" accesskey=\"".createNormalTooltip("button_data_delete", "", 150)."\"><span id=\"upload_delete\"> </span></a>";
	$html .= "</div>";
	
/* Seiteninhalt */
	$html .= "<div id=\"div_content\">";

	// Warnung, wenn noch das Initialpa�wort verwendet wird
	$loginconf = new Properties("conf/logindata.conf");
	if (($loginconf->get("initialpw") == "true") && ($action <> "loginadminconfig"))
		$html .= returnMessage(false, getLanguageValue("warning_initial_pw"));

	// Warnung, wenn die letzte Backupwarnung mehr als $intervallsetting Tage her ist
	$intervallsetting = $ADMIN_CONF->get("backupmsgintervall");
	if (($intervallsetting != "") && preg_match("/^[0-9]+$/", $intervallsetting) && ($intervallsetting > 0)) {
		$intervallinseconds = 60 * 60 * 24 * $intervallsetting;
		if (getLastBackup() == 0)
			setLastBackup();
		else {
			$nextbackup = getLastBackup() + $intervallinseconds;
			if($nextbackup <= time())	{
				$html .= returnMessage(false, getLanguageValue("reminder_backup"));
				setLastBackup();
			}
		}
	}
	$html .= $pagecontent;
	$html .= "</div>";

	
	$html .= "</div>";
	$html .= "</body>";
	$html .= "</html>";



/* Ausgabe der kompletten Seite */
echo $html;


/* 	------------------------------
		Zus�tzliche Funktionen
		------------------------------ */
		
	function home() {
		$pagecontent .= "<h2>".getLanguageValue("button_home")."</h2>";
		$pagecontent .= "<p>";
		$pagecontent .= getLanguageValue("welcome_text");
		$pagecontent .= "</p>";
		return array(getLanguageValue("button_home"), $pagecontent);
	}

	function category() {
		$pagecontent = "<h2>".getLanguageValue("button_category")."</h2>";
		$pagecontent .= "<p>".getLanguageValue("category_text")."</p>";
		$pagecontent .= "<h3>".getLanguageValue("choice_text")."</h3>";
		$pagecontent .= "<ul>";
		$pagecontent .= "<li><a href=\"index.php?action=newcategory\">".getLanguageValue("button_category_new")."</a></li>";
		$pagecontent .= "<li><a href=\"index.php?action=editcategory\">".getLanguageValue("button_category_edit")."</a></li>";
		$pagecontent .= "<li><a href=\"index.php?action=deletecategory\">".getLanguageValue("button_category_delete")."</a></li>";
		$pagecontent .= "</ul>";
		return array(getLanguageValue("button_category"), $pagecontent);
	}

	function newCategory() {
		
		global $action;
		global $CONTENT_DIR_REL;
		
		$specialcharregex = "/^[a-zA-Z0-9_\-�������\s\?\!\&\�\<\>\@\.]+$/";
		
		$pagecontent = "";
		
		$title = getLanguageValue("button_category_new");
		$message1 = "";
		$message2 = "";
		$message3 = "";
		$nameconflict = false;
		if(isset($_GET["position"]))
		{
			if(strlen($_GET["name"]) == 0)
			{
				$message3 = getLanguageValue("category_empty");
			}
			elseif(strlen($_GET["position"])>2)
			{
				$message1 = htmlentities($_GET['name']).": ".getLanguageValue("category_exist");
			}
			elseif(!(preg_match($specialcharregex, $_GET["name"])))
			{
				$message4 = htmlentities($_GET['name']).": ".getLanguageValue("category_name_wrong");
				$nameconflict = true;	
			}
			elseif(strlen($_GET["name"])>64)
			{
				$message4 = htmlentities($_GET['name']).": ".getLanguageValue("name_too_long");
				$nameconflict = true;	
			}
			if(strlen($_GET["position"])<3 && strlen($_GET["name"]) != 0 && !$nameconflict)
			{
				createInhalt();
				$message2 = htmlentities($_GET['name']).": ".getLanguageValue("category_created_ok");
			}
		}
		
		$pagecontent = "<h2>".getLanguageValue("button_category_new")."</h2>";
		if( $message1!="")
		{
			$pagecontent .= returnMessage(false, $message1);
		}
		if( $message2!="")
		{
			$pagecontent .= returnMessage(true, $message2);
		}
		if( $message3!="")
		{
			$pagecontent .= returnMessage(false, $message3);
		}
		if( $message4!="")
		{
			$pagecontent .= returnMessage(false, $message4);
		}
		
		
		$pagecontent .= "<p>";
		$pagecontent .= getLanguageValue("category_new_text");
		$pagecontent .= "</p>";
		$pagecontent .= "<h3>".getLanguageValue("button_category_new")."</h3>";
		$pagecontent .= "<form action=\"index.php\" method=\"get\"><input type=\"hidden\" name=\"action\" value=\"".$action."\"><table class=\"data\">";
		// Zeile "NEUER NAME"
		$pagecontent .= "<tr><td class=\"config_row1\">".getLanguageValue("choose_category_name")."</td>";
		if(!(isset($_GET["name"])))
		{
			$pagecontent .= "<td class=\"config_row2\"><input class=\"text1\" name=\"name\" /></td>";
		}
		else
		{
			if($message1 != "" && $message3 != "" && $message4 != "")
			{
				$fill = $_GET["name"];
			}
			else
			{
				$fill = "";
			}
			$pagecontent .= "<td class=\"config_row2\"><input class=\"text1\" name=\"name\" value=\"".$fill."\" /></td>";
		}
		$pagecontent .= "</tr>";
		// Zeile "NEUE POSITION"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\"><a accesskey=\"".createNormalTooltip("category_numbers", "category_number_help", 150)."\"><img class=\"right\" src=\"gfx/information.gif\" alt=\"info\"></a>".getLanguageValue("choose_category_position")."</td>";
		$pagecontent .= "<td class=\"config_row2\">".show_dirs("$CONTENT_DIR_REL/")."</td>";
		$pagecontent .= "</tr>";
		// Zeile "SUBMIT"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">&nbsp;</td>";
		$pagecontent .= "<td class=\"config_row1\"><input type=\"submit\" class=\"submit\"></td>";
		$pagecontent .= "</tr>";
		$pagecontent .= "</table>";
		$pagecontent .= "</form>";
		return array(getLanguageValue("button_category_new"), $pagecontent);
	}

	function editCategory() {
		global $action;
		global $specialchars;
		global $CONTENT_DIR_REL;
		
		$specialcharregex = "/^[a-zA-Z0-9_\-�������\s\?\!\&\�\<\>\@\.]+$/";

		$pagecontent .= "<h2>".getLanguageValue("button_category_edit")."</h2>";
		$goto = "";
		
		if(isset($_GET["submit"])) {
			// Position frei
			if (strlen($_GET["position"])<3) {
				@rename("$CONTENT_DIR_REL/".$_GET["cat"],"$CONTENT_DIR_REL/".$_GET["position"]."_".$specialchars->deleteSpecialChars($_GET["newname"]));
				$pagecontent .= returnMessage(true, htmlentities($_GET["newname"]).": ".getLanguageValue("category_edited"));
				$done = true;
			}
			// Position belegt, aber mit der gleichen Kategorie >> UMBENENNEN
			elseif (substr($_GET["position"],0,2)."_".$specialchars->deleteSpecialChars(substr($_GET["position"],3)) == $_GET["cat"]) {
				if (preg_match($specialcharregex, $_GET["cat"])) {
					if (@rename("$CONTENT_DIR_REL/".$_GET["cat"], "$CONTENT_DIR_REL/".substr($_GET["position"],0,2)."_".$specialchars->deleteSpecialChars($_GET["newname"]))) {
						$pagecontent .= returnMessage(true, htmlentities($_GET["newname"]).": ".getLanguageValue("category_edited"));
						$_GET["cat"] = substr($_GET["position"],0,2)."_".$specialchars->deleteSpecialChars($_GET["newname"]);
						$done = true;
					}
				}
				else
					$pagecontent .= returnMessage(false, htmlentities($_GET["cat"]).": ".getLanguageValue("invalid_values"));
			}
			// Position mit anderer Kategorie belegt
			else
			{
				$pagecontent .= returnMessage(false, htmlentities($_GET["cat"]).": ".getLanguageValue("position_in_use"));
				$goto = "->";
			}
		}
		
		if((!isset($_GET["cat"]) && $goto != "->") || $done)
		{
			// 1. Seite
			$pagecontent .= "<p>";
			$pagecontent .= getLanguageValue("category_edit_text");
			$pagecontent .= "</p>";
			$pagecontent .= "<h3>".getLanguageValue("choice_text")."</h3>";
			$pagecontent .= "<form action=\"index.php\" method=\"GET\"><input type=\"hidden\" name=\"action\" value=\"".$action."\">";
			$pagecontent .= "<input type=\"hidden\" name=\"cat\" value=\"".$_GET["cat"]."\">";
			$pagecontent .= "<table class=\"data\">";
			$pagecontent .= "<tr>";	
			$pagecontent .= "<td class=\"config_row1\">";	
			$pagecontent .= getLanguageValue("choose_category");
			$pagecontent .= "</td>";	
			$pagecontent .= "<td class=\"config_row2\">";	
			$pagecontent .= getCatsAsSelect("");
			$pagecontent .= "</td>";	
			$pagecontent .= "</tr>";	
			$pagecontent .= "<tr>";	
			$pagecontent .= "<td class=\"config_row1\">&nbsp;</td>";	
			$pagecontent .= "<td class=\"config_row2\"><input value=\"".getLanguageValue("choose_category_button")."\" type=\"submit\" class=\"submit\"></td>";	
			$pagecontent .= "</tr>";	
			
			$pagecontent .= "</table>";
			$pagecontent .= "</form>";
		}
		elseif(isset($_GET["cat"]) || $goto == "->")
		{
			$pagecontent .= "<p>";
			$pagecontent .= getLanguageValue("category_choosed");
			$pagecontent .= "<b> ".$specialchars->rebuildSpecialChars(substr($_GET["cat"],3), true)."</b>";
			$pagecontent .= "</p>";
			
			$pagecontent .= "<form action=\"index.php\" method=\"GET\">";
			$pagecontent .= "<input type=\"hidden\" name=\"action\" value=\"".$action."\">";
			$pagecontent .= "<input type=\"hidden\" name=\"cat\" value=\"".$_GET["cat"]."\">";
			$pagecontent .= "<table class=\"data\">";
			// Zeile "NAME �NDERN"
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("current_category_name")."</td>";
			$pagecontent .= "<td class=\"config_row2\"><input class=\"Text1\" value=\"".$specialchars->rebuildSpecialChars( substr($_GET["cat"],3), true )."\" type=\"text\" name=\"newname\"></td>";
			$pagecontent .= "</tr>";
			// Zeile "POSITION �NDERN"
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("current_category_position")."</td>";
			$pagecontent .= "<td class=\"config_row2\">";
			$pagecontent .= show_dirs("$CONTENT_DIR_REL");
			$pagecontent .= "</td>";
			$pagecontent .= "</tr>";
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">&nbsp;</td>";
			$pagecontent .= "<td class=\"config_row2\">";
			$pagecontent .= "<input type=\"Submit\" name=\"submit\" />";
			$pagecontent .= "</td>";
			$pagecontent .= "</tr>";
			$pagecontent .= "</table>";
			$pagecontent .= "</form>";
		}
		return array(getLanguageValue("button_category_edit"), $pagecontent);
	}

	function deleteCategory() {
		global $specialchars;
		global $CONTENT_DIR_REL;
		$pagecontent = "<h2>".getLanguageValue("button_category_delete")."</h2>";
		// L�schen der Kategorie nach Auswertung der �bergebenen Parameter
		if (isset($_GET['cat']) && file_exists("$CONTENT_DIR_REL/".$_GET['cat'])) {
			if (isset($_GET['confirm']) && ($_GET['confirm'] == "true")) {
				if (deleteDir("$CONTENT_DIR_REL/".$_GET['cat']))
					$pagecontent .= returnMessage(true, $specialchars->rebuildSpecialChars(substr($_GET['cat'], 3, strlen($_GET['cat'])), true).": ".getLanguageValue("category_deleted"));
				else
					$pagecontent .= returnMessage(false, $specialchars->rebuildSpecialChars(substr($_GET['cat'], 3, strlen($_GET['cat'])), true).": ".getLanguageValue("category_delete_error"));
			}
			else
				$pagecontent .= returnMessage(false, $specialchars->rebuildSpecialChars(substr($_GET['cat'], 3, strlen($_GET['cat'])), true).": ".getLanguageValue("category_delete_confirm")." <a href=\"index.php?action=deletecategory&amp;cat=".$_GET['cat']."&amp;confirm=true\">".getLanguageValue("yes")."</a> - <a href=\"index.php?action=deletecategory\">".getLanguageValue("no")."</a>");
		}
		
		$pagecontent .= "<p>".getLanguageValue("category_delete_text")."</p>";
		$dirs = getDirs("$CONTENT_DIR_REL");
		$pagecontent .= "<table class=\"data\">";
		foreach ($dirs as $file) {
			$file = $file."_".specialNrDir("$CONTENT_DIR_REL", $file);
			if (($file <> ".") && ($file <> "..") 
				&& ($pageshandle = opendir("$CONTENT_DIR_REL/".$file)) 
				&& ($fileshandle = opendir("$CONTENT_DIR_REL/".$file."/dateien")) 
				) {
				// Anzahl Inhaltsseiten auslesen
				$pagescount = 0;
				while (($currentpage = readdir($pageshandle))) {
					if (is_file("$CONTENT_DIR_REL/".$file."/".$currentpage))
						$pagescount++;
				}
				// Anzahl Dateien auslesen
				$filecount = 0;
				while (($filesdir = readdir($fileshandle))) {
					if (($filesdir <> ".") && ($filesdir <> ".."))
						$filecount++;
				}
				if ($pagescount == 1)
					$pagestext = getLanguageValue("single_page");
				else
					$pagestext = getLanguageValue("many_pages");
				if ($filecount == 1)
					$filestext = getLanguageValue("single_file");
				else
					$filestext = getLanguageValue("many_files");
				$pagecontent .= "<tr><td class=\"config_row1\"><h3>".$specialchars->rebuildSpecialChars(substr($file, 3, strlen($file)-3), true)."</h3> ($pagescount $pagestext, $filecount $filestext)</td>";
				$pagecontent .= "<td class=\"config_row2\"><a href=\"index.php?action=deletecategory&amp;cat=$file".""."\">".getLanguageValue("button_delete")."</a></td></tr>";
			}
		}
		$pagecontent .= "</table>";
		return array(getLanguageValue("button_category_delete"), $pagecontent);
	}

	function site() {
		$pagecontent = "<h2>".getLanguageValue("button_site")."</h2>";
		$pagecontent .= "<p>".getLanguageValue("site_text")."</p>";
		$pagecontent .= "<h3>".getLanguageValue("choice_text")."</h3>";
		$pagecontent .= "<ul>";
		$pagecontent .= "<li><a href=\"index.php?action=newsite\">".getLanguageValue("button_site_new")."</a></li>";
		$pagecontent .= "<li><a href=\"index.php?action=editsite\">".getLanguageValue("button_site_edit")."</a></li>";
		$pagecontent .= "<li><a href=\"index.php?action=deletesite\">".getLanguageValue("button_site_delete")."</a></li>";
		$pagecontent .= "</ul>";
		return array(getLanguageValue("button_site"), $pagecontent);
	}

	function newSite() {
		global $specialchars;
		global $CONTENT_DIR_REL;
		
		$specialcharregex = "/^[a-zA-Z0-9_\-�������\s\?\!\&\�\<\>\@\.]+$/";
		// Wenn nach dem Editieren "Speichern" gedr�ckt wurde
		if (isset($_POST['save'])) {
			// Entwurf speichern
			if ($_POST['draft'] == "on") {
				saveContentToPage($_POST['pagecontent'],"$CONTENT_DIR_REL/".$_POST['cat']."/".substr($_POST['page'], 0, strlen($_POST['page'])-4).".tmp");
			}
			// Ver�ffentlichen
			else {
				saveContentToPage($_POST['pagecontent'],"$CONTENT_DIR_REL/".$_POST['cat']."/".$_POST['page']);
			}
			$pagecontent = returnMessage(true, htmlentities(substr($_POST['page'], 3,strlen($_POST['page'])-7)).": ".getLanguageValue("changes_applied"));
		}
		
		// Wenn nach dem Editieren "Abbrechen" gedr�ckt wurde
		elseif (isset($_POST['cancel']))
			header("location:index.php?action=newsite");

		// Wenn die Kategorie schon gew�hlt wurde oder im n�chsten Schritt ein Fehler war
		if ( isset($_POST['cat']) || 
				(
					isset($_POST['position']) && isset($_POST['name']) 
					&& (strlen($_POST['name']) == 0)
					&& (!preg_match($specialcharregex, $_POST['name']))
					&& (strlen($_POST['position'])>2)
				) 
			) {
			$pagecontent .= "<h2>".getLanguageValue("button_site_new")."</h2>";
			$pagecontent .= "<h3>".getLanguageValue("chosen_category")." ".$specialchars->rebuildSpecialChars(substr($_POST['cat'], 3, strlen($_POST['cat'])-3), true)."</h3>";
			if (isset($_POST['position']) && isset($_POST['name'])) {
				if (strlen($_POST['name']) == 0)
					$pagecontent .= returnMessage(false, getLanguageValue("page_empty"));
				elseif (!preg_match($specialcharregex, $_POST["name"]))
					$pagecontent .= returnMessage(false, htmlentities($_POST['name']).": ".getLanguageValue("invalid_values"));
				elseif (strlen($_POST["position"])>2)
					$pagecontent .= returnMessage(false, htmlentities($_POST['name']).": ".getLanguageValue("page_exist"));	
			}
			$pagecontent .= "<form action=\"index.php\" method=\"POST\"><input type=\"hidden\" name=\"action\" value=\"newsite\"><input type=\"hidden\" name=\"cat\" value=\"".$_POST['cat']."\">";
			$pagecontent .= "<table class=\"data\">";
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("choose_page_name")."</td>";
			$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" name=\"name\"></td>";
			$pagecontent .= "</tr>";
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\"><a accesskey=\"".createNormalTooltip("page_numbers", "page_number_help", 150)."\"><img class=\"right\" src=\"gfx/information.gif\" alt=\"info\"></a>".getLanguageValue("choose_page_position")."</td>";
			$pagecontent .= "<td class=\"config_row2\">".show_files("$CONTENT_DIR_REL/".$_POST['cat'])."</td>";
			$pagecontent .= "</tr>";
			$pagecontent .= "<tr><td class=\"config_row1\">&nbsp;</td>";
			$pagecontent .= "<td class=\"config_row2\"><input type=\"submit\" name=\"chosen\" class=\"submit\" value=\"".getLanguageValue("button_newpage_create")."\" /></td></tr>";
			$pagecontent .= "</table>";
			$pagecontent .= "</form>";
		}
		else {
			// Zuerst: Kategorie w�hlen
			$pagecontent = "<h2>".getLanguageValue("button_site_new")."</h2>";
			$pagecontent .= "<form action=\"index.php\" method=\"POST\"><input type=\"hidden\" name=\"action\" value=\"newsite\">";
			$pagecontent .= "<table class=\"data\">";
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("choose_category_for_page")."</td>";
			$pagecontent .= "<td class=\"config_row2\">".getCatsAsSelect("")."</td></tr>";
			$pagecontent .= "<tr><td class=\"config_row1\">&nbsp;</td>";
			$pagecontent .= "<td class=\"config_row2\"><input type=\"submit\" name=\"chosen\" class=\"submit\" value=\"".getLanguageValue("choose_category_button")."\" /></td></tr>";
			$pagecontent .= "</table>";
			$pagecontent .= "</form>";
		}
		
		// Wenn Name und Position der Seite schon gew�hlt wurde und korrekt sind
		if (
				isset($_POST['position']) 
				&& isset($_POST['name']) 
				&& strlen($_POST['name']) > 0
				&& preg_match($specialcharregex, $_POST['name'])
				&& (strlen($_POST['position'])<=2)
				) {
			$pagecontent = "<h2>".getLanguageValue("button_site_new")."</h2>";
			$pagecontent .= "<form name=\"form\" method=\"post\" action=\"index.php\">";
			$pagecontent .= showEditPageForm($_POST['cat'], $_POST['position']."_".$_POST['name'].".txt", "newsite");
			$pagecontent .= "</form>";
		}
		return array(getLanguageValue("button_site_new"), $pagecontent);
	}

	function editSite() {
		global $specialchars;
		global $CONTENT_DIR_REL;
		$pagecontent = "<h2>".getLanguageValue("button_site_edit")."</h2>";
		// Wenn nach dem Editieren "Speichern" gedr�ckt wurde
		if (isset($_POST['save'])) {
			// Entwurf speichern
			if ($_POST['draft'] == "on") {
				$newpagename = substr($_POST['page'],0,2)."_".$specialchars->deleteSpecialChars($_POST['newpage']).".tmp";
				saveContentToPage($_POST['pagecontent'],"$CONTENT_DIR_REL/".$_POST['cat']."/".$newpagename);
			}
			// Ver�ffentlichen
			else {
				$newpagename = substr($_POST['page'],0,2)."_".$specialchars->deleteSpecialChars($_POST['newpage']).".txt";
				if ($newpagename <> $_POST['page'])
					@unlink("$CONTENT_DIR_REL/".$_POST['cat']."/".$_POST['page']);
				saveContentToPage($_POST['pagecontent'],"$CONTENT_DIR_REL/".$_POST['cat']."/".$newpagename);
			}
			$pagecontent = returnMessage(true, htmlentities($_POST['newpage']).": ".getLanguageValue("changes_applied")).$pagecontent;
		}
		// Wenn nach dem Editieren "Abbrechen" gedr�ckt wurde
		elseif (isset($_POST['cancel']))
			header("location:index.php?action=editsite");
		if (isset($_GET['file']) && isset($_GET['cat'])) {
			$pagecontent .= "<form name=\"form\" method=\"post\" action=\"index.php\">";
			$draft = "";
			if (substr($_GET['file'], strlen($_GET['file'])-4, strlen($_GET['file'])) == ".tmp")
				$draft = " (".getLanguageValue("draft").")";
			$pagecontent .= "<p style=\"margin-bottom:0px;\">".getLanguageValue("site_name").": "."<input class=\"text2\" type=\"text\" name=\"newpage\" value=\"".$specialchars->rebuildSpecialChars(substr($_GET['file'],3,strlen($_GET['file'])-7), true)."\" />$draft</p>";
			$pagecontent .= showEditPageForm($_GET['cat'], $_GET['file'], "editsite");
			$pagecontent .= "</form>";
		}
		
		else {
			$dirs = getDirs("$CONTENT_DIR_REL");
			foreach ($dirs as $file)
			sort($dirs);
			$pagecontent .= "<p>".getLanguageValue("page_edit_text")."</p>";
			foreach ($dirs as $file) {
				$file = $file."_".specialNrDir("$CONTENT_DIR_REL", $file);
					if (($file <> ".") && ($file <> "..") && ($subhandle = opendir("$CONTENT_DIR_REL/".$file))) {
						$pagecontent .= "<h3>".$specialchars->rebuildSpecialChars(substr($file, 3, strlen($file)-3), true)."</h3>";
						$hasdata = false;
						$pagecontent .= "<table class=\"data\">";
						$catcontent = array();
						while (($subfile = readdir($subhandle)))
							if (is_file("$CONTENT_DIR_REL/".$file."/".$subfile))
								array_push($catcontent, $subfile);
						sort($catcontent);
						foreach ($catcontent as $subfile) {
							$draft = "";
							$draftaction = "";
							if (substr($subfile, strlen($subfile)-4, strlen($subfile)) == ".tmp") {
								$draft = " (".getLanguageValue("draft").")";
								$draftaction = "&amp;action=draft";
							}
							$pagecontent .= "<tr><td class=\"config_row1\">".$specialchars->rebuildSpecialChars(substr($subfile, 3, strlen($subfile)-7), true)."$draft</td><td class=\"config_row2\">";
							$pagecontent .= "<a href=\"../index.php?cat=".$file."&amp;page=".substr($subfile, 0, strlen($subfile)-4)."$draftaction\" target=\"_blank\">".getLanguageValue("button_preview")."</a>";
							$pagecontent .= " - <a href=\"index.php?action=editsite&amp;cat=".$file."&amp;file=".$subfile."\">".getLanguageValue("button_edit")."</a>";
							$pagecontent .= "</td></tr>";
							$hasdata = true;
						}
						if (!$hasdata)
						$pagecontent .= "<tr><td class=\"config_row1\">".getLanguageValue("page_no_data")."</td><td class=\"config_row2\">&nbsp;</td></tr>";
						$pagecontent .= "</table>";
					}
			}
		}
		return array(getLanguageValue("button_site_edit"), $pagecontent);
	}

	function deleteSite() {
		global $specialchars;
		global $CONTENT_DIR_REL;
		$pagecontent = "<h2>".getLanguageValue("button_site_delete")."</h2>";
		// L�schen der Inhaltsseite nach Auswertung der �bergebenen Parameter
		if (isset($_GET['cat']) && isset($_GET['file']) && file_exists("$CONTENT_DIR_REL/".$_GET['cat']) && file_exists("$CONTENT_DIR_REL/".$_GET['cat']."/".$_GET['file'])) {
			if (isset($_GET['confirm']) && ($_GET['confirm'] == "true")) {
				if (@unlink("$CONTENT_DIR_REL/".$_GET['cat']."/".$_GET['file']))
					$pagecontent .= returnMessage(true, $specialchars->rebuildSpecialChars(substr($_GET['file'], 3, strlen($_GET['file'])-7), true).": ".getLanguageValue("page_deleted"));
				else
					$pagecontent .= returnMessage(false, $specialchars->rebuildSpecialChars(substr($_GET['file'], 3, strlen($_GET['file'])-7), true).": ".getLanguageValue("page_delete_error"));
			}
			else
				$pagecontent .= returnMessage(false, $specialchars->rebuildSpecialChars(substr($_GET['file'], 3, strlen($_GET['file'])-7), true).": ".getLanguageValue("page_delete_confirm")." <a href=\"index.php?action=deletesite&amp;cat=".$_GET['cat']."&amp;file=".$_GET['file']."&amp;confirm=true\">".getLanguageValue("yes")."</a> - <a href=\"index.php?action=deletesite\">".getLanguageValue("no")."</a>");
		}
		$pagecontent .= "<p>".getLanguageValue("page_delete_text")."</p>";
		$dirs = getDirs("$CONTENT_DIR_REL");
		foreach ($dirs as $file) {
			$file = $file."_".specialNrDir("$CONTENT_DIR_REL", $file);
			if (($file <> ".") && ($file <> "..") && ($subhandle = opendir("$CONTENT_DIR_REL/".$file))) {
				$pagecontent .= "<h3>".$specialchars->rebuildSpecialChars(substr($file, 3, strlen($file)-3), true)."</h3>";
				$hasdata = false;
				$pagecontent .= "<table class=\"data\">";
				
				$catcontent = array();
				while (($subfile = readdir($subhandle)))
					if (is_file("$CONTENT_DIR_REL/".$file."/".$subfile))
						array_push($catcontent, $subfile);
				sort($catcontent);
				foreach ($catcontent as $subfile) {
					$draft ="";
					$draftaction = "";
					if (substr($subfile, strlen($subfile)-4, strlen($subfile)) == ".tmp") {
						$draft = " (".getLanguageValue("draft").")";
						$draftaction = "&amp;action=draft";
					}
					$pagecontent .= "<tr><td class=\"config_row1\">".$specialchars->rebuildSpecialChars(substr($subfile, 3, strlen($subfile)-7), true)."$draft</td><td class=\"config_row2\">";
					$pagecontent .= "<a href=\"../index.php?cat=".$file."&amp;page=".substr($subfile, 0, strlen($subfile)-4)."$draftaction\" target=\"_blank\">".getLanguageValue("button_preview")."</a>";
					$pagecontent .= " - <a href=\"index.php?action=deletesite&amp;cat=".$file."&amp;file=".$subfile."\">".getLanguageValue("button_delete")."</a>";
					$pagecontent .= "</td></tr>";
					$hasdata = true;
				}
				if (!$hasdata)
				$pagecontent .= "<tr><td class=\"config_row1\">".getLanguageValue("page_no_data")."</td><td class=\"config_row2\">&nbsp;</td></tr>";
				$pagecontent .= "</table>";
			}
		}
		return array(getLanguageValue("button_site_delete"), $pagecontent);
	}

	function gallery() {
		$pagecontent = "<h2>".getLanguageValue("button_gallery")."</h2>";
		$pagecontent .= "<p>".getLanguageValue("gallery_text")."</p>";
		$pagecontent .= "<h3>".getLanguageValue("choice_text")."</h3>";
		$pagecontent .= "<ul>";
		$pagecontent .= "<li><a href=\"index.php?action=newgallery\">".getLanguageValue("button_gallery_new")."</a></li>";
		$pagecontent .= "<li><a href=\"index.php?action=editgallery\">".getLanguageValue("button_gallery_edit")."</a></li>";
		$pagecontent .= "<li><a href=\"index.php?action=deletegallery\">".getLanguageValue("button_gallery_delete")."</a></li>";
		$pagecontent .= "</ul>";
		return array(getLanguageValue("button_gallery"), $pagecontent);
	}

	function newGallery() {
		global $specialchars;
		global $GALLERIES_DIR_REL;
		global $PREVIEW_DIR_NAME;
		
		$pagecontent .= "<h2>".getLanguageValue("button_gallery_new")."</h2>";

		$specialcharregex = "/^[a-zA-Z0-9_\-�������\s\?\!\&\�\<\>\@\.]+$/";
		
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			if (isset($_POST['galleryname']) && preg_match($specialcharregex, $_POST['galleryname'])) {
				$dirname = $specialchars->deleteSpecialChars($_POST['galleryname']);
				// Galerieverzeichnis schon vorhanden? Wenn nicht: anlegen!
				if (!file_exists("$GALLERIES_DIR_REL/".$dirname)) {
					if (@mkdir($GALLERIES_DIR_REL."/".$dirname, 0777) && @mkdir($GALLERIES_DIR_REL."/".$dirname."/".$PREVIEW_DIR_NAME, 0777)) {
						$filename = "$GALLERIES_DIR_REL/".$dirname."/texte.conf";
						$fp = fopen ($filename, "w");
						chmod ($filename, 0777);
						fclose($fp);
						$pagecontent .= returnMessage(true, htmlentities($_POST['galleryname']).": ".getLanguageValue("gallery_create_success"));
					}
					else
						$pagecontent .= returnMessage(false, htmlentities($_POST['galleryname']).": ".getLanguageValue("gallery_create_error"));
				}
				else {
					$pagecontent .= returnMessage(false, htmlentities($_POST['galleryname']).": ".getLanguageValue("gallery_exists_error"));
				}
			}
			else
				$pagecontent .= returnMessage(false, htmlentities($_POST['galleryname']).": ".getLanguageValue("invalid_values"));
		}
		$pagecontent .= "<form method=\"post\" action=\"index.php\" enctype=\"multipart/form-data\"><input type=\"hidden\" name=\"action\" value=\"newgallery\" />";
		$pagecontent .= "<table class=\"data\">";
		// Zeile "NAME DER GALERIE"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("gallery_choose_name_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"galleryname\" /></td>";
		$pagecontent .= "</tr>";
		// Zeile "GALERIE ANLEGEN"
		$pagecontent .= "<tr><td class=\"config_row1\">&nbsp;</td><td class=\"config_row2\"><input type=\"submit\" class=\"submit\" value=\"".getLanguageValue("button_gallery_new")."\" /></td></tr>";
		$pagecontent .= "</table></form>";

		return array(getLanguageValue("button_gallery_new"), $pagecontent);
	}

	function editGallery() {
		global $specialchars;
		global $GALLERIES_DIR_REL;
		global $PREVIEW_DIR_NAME;

		if (isset($_GET['gal']) && file_exists("$GALLERIES_DIR_REL/".$_GET['gal']))
			$mygallery = $_GET['gal'];
		else
			$mygallery = $_POST['gal'];

		$pagecontent = "<h2>".getLanguageValue("button_gallery_edit")."</h2>";
		// Zuerst: Galerie w�hlen
		$pagecontent .= "<form action=\"index.php\" method=\"GET\"><input type=\"hidden\" name=\"action\" value=\"editgallery\">";
		$pagecontent .= "<table class=\"data\">";
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("choose_gal_for_editgallery")."</td>";
		$pagecontent .= "<td class=\"config_row2\">".getGalleriesAsSelect("")."</td></tr>";
		$pagecontent .= "<tr><td class=\"config_row1\">&nbsp;</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"submit\" name=\"chosen\" class=\"submit\" value=\"".getLanguageValue("choose_gallery_button")."\" /></td></tr>";
		$pagecontent .= "</table>";
		$pagecontent .= "</form>";

		// Wenn die Galerie schon gew�hlt wurde
		if (isset($mygallery) && file_exists("$GALLERIES_DIR_REL/".$mygallery)) {
			$galleryconf = new Properties("$GALLERIES_DIR_REL/".$mygallery."/texte.conf");
			$msg = "";
			$pagecontent = "<h2>".getLanguageValue("button_gallery_edit")."</h2>";
			// Galeriebild hochladen
		  if (isset($_FILES['uploadfile']) and !$_FILES['uploadfile']['error']) {
		  	$gallerydir = "$GALLERIES_DIR_REL/".$mygallery;
		    if (!fileHasExtension($_FILES['uploadfile']['name'], array("jpg", "jpeg", "jpe", "gif", "png")))
		    	$pagecontent .= returnMessage(false, htmlentities($_FILES['uploadfile']['name']).": ".getLanguageValue("gallery_uploadfile_wrongtype"));
		    elseif (file_exists($gallerydir."/".$_FILES['uploadfile']['name']))
		    	$pagecontent .= returnMessage(false, htmlentities($_FILES['uploadfile']['name']).": ".getLanguageValue("gallery_uploadfile_exists"));
		    elseif (!preg_match("/^[a-zA-Z0-9_\-�������.]+$/", $_FILES['uploadfile']['name'])) {
		    	$pagecontent .= returnMessage(false, htmlentities($_FILES['uploadfile']['name']).": ".getLanguageValue("invalid_values"));
		  	}
		  	else {
		  		// Bild und Kommentar speichern
		    	move_uploaded_file($_FILES['uploadfile']['tmp_name'], $gallerydir."/".$_FILES['uploadfile']['name']);
					$galleryconf = new Properties($gallerydir."/texte.conf");
					$galleryconf->set($_FILES['uploadfile']['name'], stripslashes($_POST['comment']));
		  		// Vorschaubild erstellen (nur, wenn GDlib installiert ist)
					if (extension_loaded("gd")) {
						require_once("../Thumbnail.php");
						$tn = new Thumbnail();
						$tn->createThumb($_FILES['uploadfile']['name'], $gallerydir."/", $gallerydir."/$PREVIEW_DIR_NAME/");
					}
		    	$pagecontent .= returnMessage(true, htmlentities($_FILES['uploadfile']['name']).": ".getLanguageValue("gallery_upload_success"));
			  }
			}
			// Wenn "Speichern" gedr�ckt wurde
			if (isset($_GET['save'])) {
				$galleryconf->set($_GET['image'], stripslashes($_GET['comment']));
				$pagecontent .= returnMessage(true, htmlentities($_GET['image']).": ".getLanguageValue("changes_applied"));
			} 
			// Wenn "L�schen" gedr�ckt wurde
			elseif (isset($_GET['delete'])) {
				// nach Best�tigung: l�schen
				if (isset($_GET['confirm'])) {
					$galleryconf->delete($_GET['image']);
					if (
						@unlink("$GALLERIES_DIR_REL/".$mygallery."/".$_GET['image']) 
						&& (!file_exists("$GALLERIES_DIR_REL/".$mygallery."/$PREVIEW_DIR_NAME/".$_GET['image']) || @unlink("$GALLERIES_DIR_REL/".$mygallery."/$PREVIEW_DIR_NAME/".$_GET['image']))
					) 
						$pagecontent .= returnMessage(true, htmlentities($_GET['image']).": ".getLanguageValue("gallery_image_deleted"));
					else
						$pagecontent .= returnMessage(false, htmlentities($_GET['image']).": ".getLanguageValue("data_file_delete_error"));
				}
				else
					$pagecontent .= returnMessage(false, htmlentities($_GET['image']).": ".getLanguageValue("gallery_confirm_delete")." <a href=\"index.php?action=editgallery&amp;delete=true&amp;gal=".$mygallery."&amp;image=".$_GET['image']."&amp;confirm=true\">".getLanguageValue("yes")."</a> - <a href=\"index.php?action=editgallery&amp;gal=".$mygallery."\">".getLanguageValue("no")."</a>");
			} 
			$pagecontent .= "<h3>".getLanguageValue("chosen_gallery")." ".$specialchars->rebuildSpecialChars($mygallery, true)."</h3>";
			$pagecontent .= "<p>".getLanguageValue("gallery_edit_text")."</p>";
			$pagecontent .= "<h3>".getLanguageValue("gallery_upload")."</h3>";
			$pagecontent .= "<form method=\"post\" action=\"index.php\" enctype=\"multipart/form-data\"><input type=\"hidden\" name=\"action\" value=\"editgallery\" /><input type=\"hidden\" name=\"gal\" value=\"".$mygallery."\" />";
			$pagecontent .= "<table>";
			// Zeile "BILDDATEI W�HLEN"
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("gallery_choose_file_text")."</td>";
			$pagecontent .= "<td class=\"config_row2\"><input type=\"file\" class=\"text1\" name=\"uploadfile\" /></td>";
			$pagecontent .= "</tr>";
			// Zeile "KOMMENTAR"
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("gallery_add_comment_text")."</td>";
			$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"comment\" /></td>";
			$pagecontent .= "</tr>";
			// Zeile "UPLOADEN"
			$pagecontent .= "<tr><td class=\"config_row1\">&nbsp;</td><td class=\"config_row2\"><input type=\"submit\" class=\"submit\" value=\"".getLanguageValue("button_gallery_upload")."\" /></td></tr>";
			$pagecontent .= "</table></form>";
			
			$pagecontent .= "<h3>".getLanguageValue("gallery_overview")."</h3>";
			// alle Bilder der Galerie auflisten
			$handle = opendir("$GALLERIES_DIR_REL/".$mygallery);
			$counter = 0;
			$gallerypics = array();
			while (($file = readdir($handle))) {
				if (is_file("$GALLERIES_DIR_REL/".$mygallery."/".$file) && ($file <> "texte.conf")) 
					array_push($gallerypics, $file);
			}
			sort($gallerypics);
			foreach ($gallerypics as $file) {
				$counter++;
				$pagecontent .= "<form action=\"index.php\" method=\"GET\"><input type=\"hidden\" name=\"action\" value=\"editgallery\"><input type=\"hidden\" name=\"gal\" value=\"".$mygallery."\"><input type=\"hidden\" name=\"image\" value=\"".$file."\">";
				$pagecontent .= "<table class=\"data\">";
				$pagecontent .= "<tr>";
				if (file_exists("$GALLERIES_DIR_REL/".$mygallery."/$PREVIEW_DIR_NAME/".$file))
					$pagecontent .= "<td class=\"config_row1\"><img src=\"$GALLERIES_DIR_REL/".$mygallery."/$PREVIEW_DIR_NAME/".$file."\" alt=\"$file\" style=\"width:100px;\" /><br />".$file."</td>";
				else			
					$pagecontent .= "<td class=\"config_row1\"><img src=\"$GALLERIES_DIR_REL/".$mygallery."/".$file."\" alt=\"$file\" style=\"width:100px;\" /><br />".$file."</td>";
				$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"comment\" value=\"".htmlentities($galleryconf->get($file))."\" /><input type=\"submit\" name=\"save\" value=\"".getLanguageValue("button_save")."\" class=\"submit\" /> <input type=\"submit\" name=\"delete\" value=\"".getLanguageValue("button_delete")."\" class=\"submit\" /></td>";
				$pagecontent .= "</tr>";
				$pagecontent .= "</table>";
				$pagecontent .= "</form>";
			}
			if ($counter == 0)
				{
					$pagecontent .= "<table class=\"data\">";
					$pagecontent .= "<tr>";
					$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("gallery_no_data")."</td>";
					$pagecontent .= "<td class=\"config_row2\"></td>";
					$pagecontent .= "</tr>";
					$pagecontent .= "</table>";
				}
		}
		return array(getLanguageValue("button_gallery_edit"), $pagecontent);
	}

	function deleteGallery() {
		global $specialchars;
		global $GALLERIES_DIR_REL;
		global $PREVIEW_DIR_NAME;
		
		// Zuerst: Kategorie w�hlen
		$pagecontent = "<h2>".getLanguageValue("button_gallery_delete")."</h2>";
		// Wenn die Kategorie schon gew�hlt wurde
		if (isset($_GET['gal']) && file_exists("$GALLERIES_DIR_REL/".$_GET['gal'])) {
			$mygallery = "$GALLERIES_DIR_REL/".$_GET['gal'];
			if (isset($_GET['confirm']) && ($_GET['confirm'] == "true")) {
				$success = true;
				$couldnotrmdir = false;
				// Vorschauverzeichnis leeren
				$handle = opendir($mygallery."/$PREVIEW_DIR_NAME");
				while ($file = readdir($handle)) {
					if (is_file($mygallery."/$PREVIEW_DIR_NAME/".$file))
						if (!@unlink($mygallery."/$PREVIEW_DIR_NAME/".$file))
							$success = false;
				}
				if (!@rmdir($mygallery."/$PREVIEW_DIR_NAME"))
					$couldnotrmdir = true;
				// Galerieverzeichnis leeren
				$handle = opendir($mygallery);
				while ($file = readdir($handle)) {
					if (is_file($mygallery."/".$file))
						if (!@unlink($mygallery."/".$file))
							$success = false;
				}
				if (!@rmdir($mygallery))
					$couldnotrmdir = true;
				if ($success && !$couldnotrmdir)
					$pagecontent .= returnMessage(true, $specialchars->rebuildSpecialChars($_GET['gal'], true).": ".getLanguageValue("gallery_delete_success"));
				elseif ($success && $couldnotrmdir)
					$pagecontent .= returnMessage(false, $specialchars->rebuildSpecialChars($_GET['gal'], true).": ".getLanguageValue("gallery_delete_success")." ".getLanguageValue("gallery_delete_no_rmdir"));
				else
					$pagecontent .= returnMessage(false, $specialchars->rebuildSpecialChars($_GET['gal'], true).": ".getLanguageValue("gallery_delete_error"));	
			}
			else {
				$pagecontent .= returnMessage(false, $specialchars->rebuildSpecialChars($_GET['gal'], true).": ".getLanguageValue("gallery_confirm_deleteall")." <a href=\"index.php?action=deletegallery&amp;delete=true&amp;gal=".$_GET['gal']."&amp;confirm=true\">".getLanguageValue("yes")."</a> - <a href=\"index.php?action=deletegallery\">".getLanguageValue("no")."</a>");
			}
		}
		$pagecontent .= "<p>".getLanguageValue("gallery_delete_text")."</p>";
		$pagecontent .= "<form action=\"index.php\" method=\"GET\"><input type=\"hidden\" name=\"action\" value=\"deletegallery\">";
		$pagecontent .= "<table class=\"data\">";
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("choose_gal_for_delete")."</td>";
		$pagecontent .= "<td class=\"config_row2\">".getGalleriesAsSelect($_GET['gal'])."</td></tr>";
		$pagecontent .= "<tr><td class=\"config_row1\">&nbsp;</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"submit\" name=\"chosen\" class=\"submit\" value=\"".getLanguageValue("choose_gallery_button")."\" /></td></tr>";
		$pagecontent .= "</table>";
		$pagecontent .= "</form>";
		return array(getLanguageValue("button_gallery_delete"), $pagecontent);
	}

	function files() {
		$pagecontent = "<h2>".getLanguageValue("button_data")."</h2>";
		$pagecontent .= "<p>".getLanguageValue("data_text")."</p>";
		$pagecontent .= "<h3>".getLanguageValue("choice_text")."</h3>";
		$pagecontent .= "<ul>";
		$pagecontent .= "<li><a href=\"index.php?action=newfile\">".getLanguageValue("button_data_new")."</a></li>";
		$pagecontent .= "<li><a href=\"index.php?action=deletefile\">".getLanguageValue("button_data_delete")."</a></li>";
		$pagecontent .= "</ul>";
		return array(getLanguageValue("button_data"), $pagecontent);
	}

	function newFile() {
		global $ADMIN_CONF;
		global $specialchars;
		global $CONTENT_DIR_REL;
		$pagecontent = "<h2>".getLanguageValue("button_data_new")."</h2>";
		// Datei hochladen
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
		  if (isset($_FILES['uploadfile']) and !$_FILES['uploadfile']['error']) {
		    if (file_exists("$CONTENT_DIR_REL/".$specialchars->deleteSpecialChars($_POST['cat'])."/dateien/".$_FILES['uploadfile']['name'])) {
		    	$pagecontent .= returnMessage(false, htmlentities($_FILES['uploadfile']['name']).": ".getLanguageValue("data_uploadfile_exists"));
		    }
		    elseif (fileHasExtension($_FILES['uploadfile']['name'], explode(",", $ADMIN_CONF->get("noupload")))) {
		    	$pagecontent .= returnMessage(false, htmlentities($_FILES['uploadfile']['name']).": ".getLanguageValue("data_uploadfile_wrongext"));
		    }
		    else {
		    	if(preg_match("/^[a-zA-Z0-9_\-�������.]+$/", $_FILES['uploadfile']['name'])) {
			    	move_uploaded_file($_FILES['uploadfile']['tmp_name'], "$CONTENT_DIR_REL/".$_POST['cat']."/dateien/".$_FILES['uploadfile']['name']);
			    	$pagecontent .= returnMessage(true, htmlentities($_FILES['uploadfile']['name']).": ".getLanguageValue("data_upload_success"));
		    	}
			    else {
			    	$pagecontent .= returnMessage(false, htmlentities($_FILES['uploadfile']['name']).": ".getLanguageValue("invalid_values"));
			    }
			  }
			}
		}
		$pagecontent .= "<p>".getLanguageValue("data_new_text")."</p>";
		$pagecontent .= "<form method=\"post\" action=\"index.php\" enctype=\"multipart/form-data\"><input type=\"hidden\" name=\"action\" value=\"newfile\" />";
		$pagecontent .= "<table><tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("data_choose_category_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\">".getCatsAsSelect($specialchars->deleteSpecialChars($_POST['cat']))."</td></tr>";
		// Datei ausw�hlen
		$pagecontent .= "<tr><td class=\"config_row1\">".getLanguageValue("data_choose_file_text")."</td><td class=\"config_row2\"><input type=\"file\" class=\"text1\" name=\"uploadfile\" /></td></tr>";
		// Button
		$pagecontent .= "<tr><td class=\"config_row1\">&nbsp;</td><td class=\"config_row2\"><input type=\"submit\" class=\"submit\" value=\"".getLanguageValue("button_data_new")."\" /></td></tr>";
		$pagecontent .= "</table></form>";
		return array(getLanguageValue("button_data_new"), $pagecontent);
	}

	function deleteFile() {
		global $specialchars;
		global $CONTENT_DIR_REL;
		$pagecontent = "<h2>".getLanguageValue("button_data_delete")."</h2>";
		// L�schen der Dateien nach Auswertung der �bergebenen Parameter
		if (isset($_GET['cat']) && isset($_GET['file']) && file_exists("$CONTENT_DIR_REL/".$_GET['cat']) && file_exists("$CONTENT_DIR_REL/".$_GET['cat']."/dateien/".$_GET['file'])) {
			if (isset($_GET['confirm']) && ($_GET['confirm'] == "true")) {
				if (@unlink("$CONTENT_DIR_REL/".$_GET['cat']."/dateien/".$_GET['file']))
					$pagecontent .= returnMessage(true, htmlentities($_GET['file']).": ".getLanguageValue("data_file_deleted"));
				else
					$pagecontent .= returnMessage(false, htmlentities($_GET['file']).": ".getLanguageValue("data_file_delete_error"));
			}
			else
				$pagecontent .= returnMessage(false, htmlentities($_GET['file']).": ".getLanguageValue("data_file_delete_confirm")." <a href=\"index.php?action=deletefile&amp;cat=".$_GET['cat']."&amp;file=".$_GET['file']."&amp;confirm=true\">".getLanguageValue("yes")."</a> - <a href=\"index.php?action=deletefile\">".getLanguageValue("no")."</a>");
		}
		$pagecontent .= "<p>".getLanguageValue("data_delete_text")."</p>";
		$dirs = getDirs("$CONTENT_DIR_REL");
		foreach ($dirs as $file) {
			$file = $file."_".specialNrDir("$CONTENT_DIR_REL", $file);
			if (($file <> ".") && ($file <> "..") && ($subhandle = opendir("$CONTENT_DIR_REL/".$file."/dateien"))) {
				$pagecontent .= "<h3>".$specialchars->rebuildSpecialChars(substr($file, 3, strlen($file)-3), true)."</h3>";
				$hasdata = false;
				$pagecontent .= "<table class=\"data\">";
				$mysubfiles = array();
				while (($subfile = readdir($subhandle))) {
					array_push($mysubfiles, $subfile);
				}
				sort($mysubfiles);
				foreach ($mysubfiles as $subfile) {
					if (($subfile <> ".") && ($subfile <> "..")) {
						$pagecontent .= "<tr><td class=\"config_row1\">$subfile</td><td class=\"config_row2\"><a href=\"$CONTENT_DIR_REL/".$file."/dateien/".$subfile."\" target=\"_blank\">".getLanguageValue("data_download")."</a> - <a href=\"index.php?action=deletefile&amp;cat=".$file."&amp;file=".$subfile."\">".getLanguageValue("button_delete")."</a></td></tr>";
						$hasdata = true;
					}
				}
				if (!$hasdata)
				$pagecontent .= "<tr><td class=\"config_row1\">".getLanguageValue("data_no_data")."</td><td class=\"config_row2\">&nbsp;</td></tr>";
				$pagecontent .= "</table>";
			}
		}
		return array(getLanguageValue("button_data_delete"), $pagecontent);
	}

	function config() {
		$pagecontent = "<h2>".getLanguageValue("button_config")."</h2>";
		$pagecontent .= "<p>".getLanguageValue("config_text")."</p>";
		$pagecontent .= "<h3>".getLanguageValue("choice_text")."</h3>";
		$pagecontent .= "<ul>";
		$pagecontent .= "<li><a href=\"index.php?action=displaycmsconfig\">".getLanguageValue("button_config_displaycms")."</a></li>";
		$pagecontent .= "<li><a href=\"index.php?action=displayadminconfig\">".getLanguageValue("button_config_displayadmin")."</a></li>";
		$pagecontent .= "<li><a href=\"index.php?action=loginadminconfig\">".getLanguageValue("button_config_loginadmin")."</a></li>";
		$pagecontent .= "</ul>";
		return array(getLanguageValue("button_config"), $pagecontent);
	}

	function configCMSDisplay() {
		global $CMS_CONF;
		global $specialchars;
		global $CONTENT_DIR_REL;
		$pagecontent .= "<h2>".getLanguageValue("button_config_displaycms")."</h2>";
		// �nderungen speichern
		$changesmade = false;
		if (isset($_GET['apply']) && ($_GET['apply'] == "true")) {
			$changesapplied = false;
			if (
				isset($_GET['gmw']) && preg_match("/^[0-9]+$/", $_GET['gmw'])
				&& isset($_GET['gmh']) && preg_match("/^[0-9]+$/", $_GET['gmh']) 
				&& isset($_GET['title']) && ($_GET['title'] <> "") 
				&& isset($_GET['template']) && ($_GET['template'] <> "") 
				&& isset($_GET['gallerytemplate']) && ($_GET['gallerytemplate'] <> "") 
				&& isset($_GET['gthumbs']) && ($_GET['gthumbs'] <> "")
				&& isset($_GET['gppr']) && (($_GET['gppr'] <> "") && preg_match("/^[0-9]+$/", $_GET['gppr']))
				&& isset($_GET['css']) && ($_GET['css'] <> "") 
				&& isset($_GET['favicon']) && ($_GET['favicon'] <> "") 
				&& isset($_GET['dcat']) && ($_GET['dcat'] <> "") 
				&& isset($_GET['syntaxslinks']) && ($_GET['syntaxslinks'] <> "")
				&& isset($_GET['lang']) && ($_GET['lang'] <> "")
				) {
				$CMS_CONF->set("websitetitle", htmlentities($_GET['title']));
				$CMS_CONF->set("templatefile", $_GET['template']);
				$CMS_CONF->set("gallerytemplatefile", $_GET['gallerytemplate']);
				$CMS_CONF->set("galleryusethumbs", $_GET['gthumbs']);
				$CMS_CONF->set("gallerypicsperrow", $_GET['gppr']);
				$CMS_CONF->set("gallerymaxwidth", $_GET['gmw']);
				$CMS_CONF->set("gallerymaxheight", $_GET['gmh']);
				$CMS_CONF->set("cssfile", $_GET['css']);
				$CMS_CONF->set("faviconfile", $_GET['favicon']);
				$CMS_CONF->set("defaultcat", $specialchars->deleteSpecialChars($_GET['dcat']));
				$CMS_CONF->set("shortenlinks", $_GET['syntaxslinks']);
				$CMS_CONF->set("cmslanguage", $_GET['lang']);
				if ($_GET['usesyntax'] == "on")
					$CMS_CONF->set("usecmssyntax", "true");
				else
					$CMS_CONF->set("usecmssyntax", "false");
				$pagecontent .= returnMessage(true, getLanguageValue("changes_applied"));
			}
			else
				$pagecontent .= returnMessage(false, getLanguageValue("invalid_values"));
		}
		$pagecontent .= "<p>".getLanguageValue("config_cmsdisplay_text")."</p>";
		$pagecontent .= "<form action=\"index.php\" method=\"get\"><input type=\"hidden\" name=\"action\" value=\"displaycmsconfig\"><input type=\"hidden\" name=\"apply\" value=\"true\">";

// ALLGEMEINE EINSTELLUNGEN
		$pagecontent .= "<h3>".getLanguageValue("config_cmsglobal_headline")."</h3>";
		$pagecontent .= "<table class=\"data\">";
		// Zeile "CMS-Version"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("cmsversion_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\">".$CMS_CONF->get("cmsversion")."</td>";
		$pagecontent .= "</tr>";
		// Zeile "SPRACHAUSWAHL"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("cmslanguage_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><select name=\"lang\">";
		if ($handle = opendir('../sprachen')){
			while ($file = readdir($handle)) {
				$selected = "";
				if (($file != ".") && ($file != "..")) {
					if (substr($file,0,strlen($file)-strlen(".conf")) == $CMS_CONF->get("cmslanguage"))
						$selected = " selected";
					$pagecontent .= "<option".$selected.">";
					$pagecontent .= substr($file,0,strlen($file)-strlen(".conf"));
					$pagecontent .= "</option>";
				}
			}
			closedir($handle);
		}
		$pagecontent .= "</select></td></tr>";
		// Zeile "WEBSITE-TITEL"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("websitetitle_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"title\" value=\"".$CMS_CONF->get("websitetitle")."\" /></td>";
		$pagecontent .= "</tr>";
		// Zeile "STANDARD-KATEGORIE"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("defaultcat_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\">";
		$dirs = array();
		$dirs = getDirs("$CONTENT_DIR_REL");
		$pagecontent .= "<select name=\"dcat\">";
		foreach ($dirs as $element) {
			$myfiles = getFiles("$CONTENT_DIR_REL/".$element."_".specialNrDir("$CONTENT_DIR_REL", $element));
			if (count($myfiles) == 0)
				continue;
			$selected = "";
			if ($element."_".$specialchars->rebuildSpecialChars(specialNrDir("$CONTENT_DIR_REL", $element), true) == $CMS_CONF->get("defaultcat"))
				$selected = "selected ";
			$pagecontent .= "<option ".$selected."value=\"".$element."_".$specialchars->rebuildSpecialChars(specialNrDir("$CONTENT_DIR_REL", $element), true)."\">".$specialchars->rebuildSpecialChars(specialNrDir("$CONTENT_DIR_REL", $element), true)."</option>";
		}
		$pagecontent .= "</select></td>";
		$pagecontent .= "</tr>";
		// Zeile "NUTZE CMS-SYNTAX"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("usesyntax_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"checkbox\" ";
		if ($CMS_CONF->get("usecmssyntax") == "true")
			$pagecontent .= "checked=checked";
		$pagecontent .= " name=\"usesyntax\">".getLanguageValue("usesyntax_text2")."</td>";
		$pagecontent .= "</tr>";
		$pagecontent .= "</table>";

// SYNTAX-EINSTELLUNGEN
		$pagecontent .= "<h3>".getLanguageValue("config_cmssyntax_headline")."</h3>";
		$pagecontent .= "<table class=\"data\">";
		// Zeile "LINKS K�RZEN"
		$checked0 = "";
		$checked1 = "";
		$checked2 = "";
		if ($CMS_CONF->get("shortenlinks") == "2")
			$checked2 = "checked=\"checked\" ";
		elseif ($CMS_CONF->get("shortenlinks") == "1")
			$checked1 = "checked=\"checked\" ";
		else
			$checked0 = "checked=\"checked\" ";
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("syntaxshortenlinks_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\">";
		$pagecontent .= "<input type=\"radio\" name=\"syntaxslinks\" value=\"0\"$checked0 />http://www.domain.com<br />";
		$pagecontent .= "<input type=\"radio\" name=\"syntaxslinks\" value=\"1\"$checked1 />www.domain.com<br />";
		$pagecontent .= "<input type=\"radio\" name=\"syntaxslinks\" value=\"2\"$checked2 />domain.com<br />";
		$pagecontent .= "</td>";
		$pagecontent .= "</tr>";
		$pagecontent .= "</table>";

// GALERIE-EINSTELLUNGEN
		$pagecontent .= "<h3>".getLanguageValue("config_cmsgallery_headline")."</h3>";
		$pagecontent .= "<table class=\"data\">";
		// Zeile "GALERIE IM EINZEL- ODER �BERSICHT-MODUS" (nur, wenn GDlib installiert ist)
		if (extension_loaded("gd")) {
			if ($CMS_CONF->get("galleryusethumbs") == "true")
				$checked1 = "checked=\"checked\" ";
			else
				$checked2 = "checked=\"checked\" ";
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("galleryusethumbs_text")."</td>";
			$pagecontent .= "<td class=\"config_row2\"><input type=\"radio\" name=\"gthumbs\" value=\"true\"$checked1 />".getLanguageValue("galleryusethumbs_yes")."<br /><input type=\"radio\" name=\"gthumbs\" value=\"false\"$checked2 />".getLanguageValue("galleryusethumbs_no")."</td>";
			$pagecontent .= "</tr>";
		}
			
		if (extension_loaded("gd") && ($CMS_CONF->get("galleryusethumbs") == "true")) {
			// "ANZAHL VORSCHAUBILDER IN EINER ZEILE" 
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("gallerypicsperrow_text");
			$pagecontent .= "<input type=\"hidden\" name=\"gmw\" value=\"".$CMS_CONF->get("gallerymaxwidth")."\">";
			$pagecontent .= "<input type=\"hidden\" name=\"gmh\" value=\"".$CMS_CONF->get("gallerymaxheight")."\">";
			$pagecontent .= "</td>";
			$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"gppr\" value=\"".$CMS_CONF->get("gallerypicsperrow")."\" /></td>";
			$pagecontent .= "</tr>";
		}
		
		// wenn GDlib nicht installiert ist oder Benutzer Einzelmodus gew�hlt hat
		if (!extension_loaded("gd") || ($CMS_CONF->get("galleryusethumbs") != "true")) {
			// Zeile "MAXIMALE BILDBREIE GALERIE"
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("gallerymaxwidth_text");
			$pagecontent .= "<input type=\"hidden\" name=\"gppr\" value=\"".$CMS_CONF->get("gallerypicsperrow")."\">";
			if (!extension_loaded("gd"))
				$pagecontent .= "<input type=\"hidden\" name=\"gthumbs\" value=\"false\">";
			$pagecontent .= "</td>";
			$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"gmw\" value=\"".$CMS_CONF->get("gallerymaxwidth")."\" /></td>";
			$pagecontent .= "</tr>";
			// Zeile "MAXIMALE BILDH�HE GALERIE"
			$pagecontent .= "<tr>";
			$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("gallerymaxheight_text")."</td>";
			$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"gmh\" value=\"".$CMS_CONF->get("gallerymaxheight")."\" /></td>";
			$pagecontent .= "</tr>";
		}
		$pagecontent .= "</table>";

// DETAILLIERTE EINSTELLUNGEN
		$pagecontent .= "<h3>".getLanguageValue("config_cmsdetail_headline")."</h3>";
		$pagecontent .= "<table class=\"data\">";
		// Zeile "HTML-TEMPLATE"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("template_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"template\" value=\"".$CMS_CONF->get("templatefile")."\" /></td>";
		$pagecontent .= "</tr>";
		// Zeile "GALERIE-TEMPLATE"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("gallerytemplate_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"gallerytemplate\" value=\"".$CMS_CONF->get("gallerytemplatefile")."\" /></td>";
		$pagecontent .= "</tr>";
		// Zeile "CSS-DATEI"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("css_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"css\" value=\"".$CMS_CONF->get("cssfile")."\" /></td>";
		$pagecontent .= "</tr>";
		// Zeile "FAVICON"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("favicon_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"favicon\" value=\"".$CMS_CONF->get("faviconfile")."\" /></td>";
		$pagecontent .= "</tr>";
		// Zeile "�BERNEHMEN"
		$pagecontent .= "<tr><td class=\"config_row1\">&nbsp;</td><td class=\"config_row2\"><input type=\"submit\" class=\"submit\" value=\"".getLanguageValue("config_submit")."\"/></td></tr>";
		$pagecontent .= "</table>";
		$pagecontent .= "</form>";
		return array(getLanguageValue("button_config_displaycms"), $pagecontent);
	}

	function configAdminDisplay() {
		global $ADMIN_CONF;
		// �nderungen speichern
		$changesmade = false;
		if (isset($_GET['apply']) && ($_GET['apply'] == "true")) {
			if (isset($_GET['tooltip']) && ($_GET['tooltip'] == "on")) {
				$ADMIN_CONF->set("showTooltips", "true");
				$changesmade = true;
			}
			else
				$ADMIN_CONF->set("showTooltips", "false");
			if (isset($_GET['lang'])) {
				$ADMIN_CONF->set("language", $_GET['lang']);
				$changesmade = true;
			}
			if (isset($_GET['noupload'])) {
				$ADMIN_CONF->set("noupload", $_GET['noupload']);
				$changesmade = true;
			}
			if (isset($_GET['textareaheight']) && preg_match("/^[0-9]+$/", $_GET['textareaheight'])) {
				$height = $_GET['textareaheight'];
				if ($height < 50)
					$height = 50;
				elseif ($height > 1000)
					$height = 1000;
				$ADMIN_CONF->set("textareaheight", $height);
				$changesmade = true;
			}
			if (isset($_GET['backupmsgintervall']) && preg_match("/^[0-9]+$/", $_GET['backupmsgintervall'])) {
				$ADMIN_CONF->set("backupmsgintervall", $_GET['backupmsgintervall']);
				$changesmade = true;
			}
		}
		$pagecontent .= "<h2>".getLanguageValue("button_config_displayadmin")."</h2>";
		if ($changesmade)
			$pagecontent .= returnMessage(true, getLanguageValue("changes_applied"));
		$pagecontent .= "<p>".getLanguageValue("config_admindisplay_text")."</p>";
		$pagecontent .= "<form action=\"index.php\" method=\"get\"><input type=\"hidden\" name=\"action\" value=\"displayadminconfig\"><input type=\"hidden\" name=\"apply\" value=\"true\">";
		$pagecontent .= "<table class=\"data\">";
		// Zeile "ZEIGE TOOLTIPS"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("showTooltips_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"checkbox\" ";
		if (showTooltips()=="true")
			$pagecontent .= "checked=checked";
		$pagecontent .= " name=\"tooltip\">".getLanguageValue("showTooltips_text2")."</td>";
		$pagecontent .= "</tr>";
		// Zeile "SPRACHAUSWAHL"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\"><a accesskey=\"".createNormalTooltip("languagechoose", "language_help", 150)."\"><img class=\"right\" src=\"gfx/information.gif\" alt=\"info\"></a>".getLanguageValue("selectLanguage_text")."</td><td class=\"config_row2\"><select name=\"lang\">";
		if ($handle = opendir('conf')){
			while ($file = readdir($handle)) {
				$selected = "";
				if ($file != "." && $file != "..") {
					if(substr($file,0,9) == "language_") {
						if (substr($file,9,4) == $ADMIN_CONF->get("language"))
							$selected = " selected";
						$pagecontent .= "<option".$selected.">";
						$pagecontent .= substr($file,9,4);
						$pagecontent .= "</option>";
					}
				}
			}
			closedir($handle);
		}
		$pagecontent .= "</select></td></tr>";
		// Zeile "H�HE DES TEXTFELDES"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("textareaheight_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"textareaheight\" value=\"".$ADMIN_CONF->get("textareaheight")."\" /></td>";
		$pagecontent .= "</tr>";
		// Zeile "BACKUP-ERINNERUNG"
		$backupmsgintervall = $ADMIN_CONF->get("backupmsgintervall");
		if ($backupmsgintervall == "")
			$backupmsgintervall = 0;
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("reminder_backup_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"backupmsgintervall\" value=\"".$backupmsgintervall."\" /></td>";
		$pagecontent .= "</tr>";
		// Zeile "UPLOAD-FILTER"
		$pagecontent .= "<tr>";
		$pagecontent .= "<td class=\"config_row1\">".getLanguageValue("uploadfilter_text")."</td>";
		$pagecontent .= "<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"noupload\" value=\"".$ADMIN_CONF->get("noupload")."\" /></td>";
		$pagecontent .= "</tr>";
		// Zeile "�BERNEHMEN"
		$pagecontent .= "<tr><td class=\"config_row1\">&nbsp;</td><td class=\"config_row2\"><input type=\"submit\" class=\"submit\" value=\"".getLanguageValue("config_submit")."\"/></td></tr>";
		$pagecontent .= "</table>";
		$pagecontent .= "</form>";
		return array(getLanguageValue("button_config"), $pagecontent);
	}

	function configAdminLogin() {
		$pagecontent .= "<h2>".getLanguageValue("button_config_loginadmin")."</h2>";
		$adminconf = new Properties("conf/logindata.conf");
		$erroroccured = false;
		require_once("Crypt.php");
		$pwcrypt = new Crypt();
		// �bergebene Werte pr�fen
		if ($_POST['apply'] == "true") {
			// Alle Felder �bergeben...
			if(!$erroroccured)
				if (isset($_POST['oldname']) && isset($_POST['oldpw']) && isset($_POST['newname']) && isset($_POST['newpw']) && isset($_POST['newpwrepeat']))
					$erroroccured = false;
				else {
					$erroroccured = true;
					$pagecontent .= returnMessage(false, getLanguageValue("config_admin_missingvalues"));
				}
				
			// ...und keines leer?
			if(!$erroroccured)
				if (($_POST['oldname'] <> "" ) && ($_POST['oldpw'] <> "" ) && ($_POST['newname'] <> "" ) && ($_POST['newpw'] <> "" ) && ($_POST['newpwrepeat'] <> "" ))
					$erroroccured = false;
				else {
					$erroroccured = true;
					$pagecontent .= returnMessage(false, getLanguageValue("config_admin_missingvalues"));
				}
			
			// Alte Zugangsdaten korrekt? 
			if(!$erroroccured)
				if (($_POST['oldname'] == $adminconf->get("name")) && ($pwcrypt->encrypt($_POST['oldpw']) == $adminconf->get("pw")))
					$erroroccured = false;
				else {
					$erroroccured = true;
					$pagecontent .= returnMessage(false, getLanguageValue("config_admin_wronglogindata"));
				}

			// Neuer Name wenigstens 5 Zeichen lang?
			if(!$erroroccured)
				if (strlen($_POST['newname']) >= 5)
					$erroroccured = false;
				else {
					$erroroccured = true;
					$pagecontent .= returnMessage(false, getLanguageValue("config_admin_tooshortname"));
				}

			// Neues Pa�wort zweimal exakt gleich eingegeben?
			if(!$erroroccured)
				if ($_POST['newpw'] == $_POST['newpwrepeat'])
					$erroroccured = false;
				else {
					$erroroccured = true;
					$pagecontent .= returnMessage(false, getLanguageValue("config_admin_newpwmismatch"));
				}

			// Neues Pa�wort wenigstens sechs Zeichen lang und mindestens aus kleinen und gro�en Buchstaben sowie Zahlen bestehend?
			if(!$erroroccured)
				if ((strlen($_POST['newpw']) >= 6) && preg_match("/[0-9]/", $_POST['newpw']) && preg_match("/[a-z]/", $_POST['newpw']) && preg_match("/[A-Z]/", $_POST['newpw']))
					$erroroccured = false;
				else {
					$erroroccured = true;
					$pagecontent .= returnMessage(false, getLanguageValue("config_admin_newpwerror"));
				}

			if (!$erroroccured){
				$adminconf->set("name", $_POST['newname']);
				$adminconf->set("pw", $pwcrypt->encrypt($_POST['newpw']));
				$adminconf->set("initialpw", "false");
				$pagecontent .= returnMessage(true, getLanguageValue("config_userdata_changed"));
			}
		}
		$pagecontent .= "<p>"
		.getLanguageValue("config_adminlogin_text")
		."<br />"
		."<br />"
		.getLanguageValue("config_adminlogin_rules_text")
		."</p>"
		."<form action=\"index.php\" method=\"post\"><input type=\"hidden\" name=\"apply\" value=\"true\">"
		."<table class=\"data\">"
		// Zeile "ALTER NAME"
		."<tr>"
		."<td class=\"config_row1\">".getLanguageValue("config_oldname_text")."</td>"
		."<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"oldname\" value=\"".$_POST['oldname']."\" /></td>"
		."</tr>"
		// Zeile "ALTES PASSWORT"
		."<tr>"
		."<td class=\"config_row1\">".getLanguageValue("config_oldpw_text")."</td>"
		."<td class=\"config_row2\"><input type=\"password\" class=\"text1\" name=\"oldpw\" /></td>"
		."</tr>"
		// Zeile "NEUER NAME"
		."<tr>"
		."<td class=\"config_row1\">".getLanguageValue("config_newname_text")."</td>"
		."<td class=\"config_row2\"><input type=\"text\" class=\"text1\" name=\"newname\" value=\"".$_POST['newname']."\" /></td>"
		."</tr>"
		// Zeile "NEUES PASSWORT"
		."<tr>"
		."<td class=\"config_row1\">".getLanguageValue("config_newpw_text")."</td>"
		."<td class=\"config_row2\"><input type=\"password\" class=\"text1\" name=\"newpw\" /></td>"
		."</tr>"
		// Zeile "NEUES PASSWORT - WIEDERHOLUNG"
		."<tr>"
		."<td class=\"config_row1\">".getLanguageValue("config_newpwrepeat_text")."</td>"
		."<td class=\"config_row2\"><input type=\"password\" class=\"text1\" name=\"newpwrepeat\" /></td>"
		."</tr>"
		// Zeile "�BERNEHMEN"
		."<tr><td class=\"config_row1\">&nbsp;</td><td class=\"config_row2\"><input type=\"hidden\" name=\"action\" value=\"loginadminconfig\" /><input type=\"submit\" class=\"submit\" value=\"".getLanguageValue("config_submit")."\"/></td></tr>"

		."</table>"
		."</form>";
		return array(getLanguageValue("button_config_loginadmin"), $pagecontent);
	}
	
	// Anzeige der Editieransicht
	function showEditPageForm($cat, $page, $action)	{
		global $ADMIN_CONF;
		global $CMS_CONF;
		global $specialchars;
		global $CONTENT_DIR_REL;
		$file = "$CONTENT_DIR_REL/".$cat."/".$page;
		if (file_exists($file)) {
			$handle=fopen($file, "r");
			$pagecontent = htmlentities(fread($handle, filesize($file)));
			fclose($handle);
		}
		else
			$pagecontent = "[ueber1|".substr($page, 3,strlen($page)-7)."]";
		
		if ($CMS_CONF->get("usecmssyntax") == "true") {
			$content = returnFormatToolbar($cat);
	  }

		// Seiteninhalt
		$height = $ADMIN_CONF->get("textareaheight");
		if ($height == "") {
			$height = 350;
			$ADMIN_CONF->set("textareaheight", $height);
		}
		$content .= "<textarea style=\"height:$height;\" name=\"pagecontent\">".$pagecontent."</textarea>";
		$content .= "<input type=\"hidden\" name=\"page\" value=\"$page\" />";
		$content .= "<input type=\"hidden\" name=\"action\" value=\"$action\" />";
		$content .= "<input type=\"hidden\" name=\"cat\" value=\"$cat\" />";
		$content .= "<input type=\"submit\" name=\"cancel\" value=\"".getLanguageValue("button_cancel")."\" accesskey=\"a\" /> ";
		$content .= "<input type=\"submit\" name=\"save\" value=\"".getLanguageValue("button_save")."\" accesskey=\"s\" /> ";
		$checked = "";
		if (substr($page, strlen($page)-4, 4) == ".tmp")
			$checked = " checked";
		$content .= "<input type=\"checkbox\"$checked name=\"draft\" accesskey=\"e\" /> ".getLanguageValue("draft_checkbox");
		return $content;
	}
	
	function saveContentToPage($content, $page) {
		global $specialchars;
		$handle=fopen($specialchars->deleteSpecialChars($page), "w");
		if (get_magic_quotes_gpc())
			fputs($handle, trim(stripslashes($content)));
		else
			fputs($handle, trim($content));
		fclose($handle);
	}
	
		// L�sche ein Verzeichnis rekursiv
	function deleteDir($path) {
		$success = true;
		// Existenz pr�fen
		if (!file_exists($path))
			return false;
		$handle = opendir($path);
		while ($currentelement = readdir($handle)) {
			if (($currentelement == ".") || ($currentelement == ".."))
				continue;
			// Verzeichnis: Rekursiver Funktionsaufruf
			if (is_dir($path."/".$currentelement))
				$success = deleteDir($path."/".$currentelement);
			// Datei: l�schen
			else
				$success = @unlink($path."/".$currentelement);				
		}
		closedir($handle);
		// Verzeichnis l�schen
		$success = @rmdir($path);
		return $success;
	}
	
	// �berpr�fe, ob die gegebene Datei eine der �bergebenen Endungen hat
	function fileHasExtension($filename, $extensions) {
		foreach ($extensions as $ext) {
			if (strtolower(substr($filename, strlen($filename)-(strlen($ext)+1), strlen($ext)+1)) == ".".strtolower($ext))
				return true;
		}
		return false;
	}
	
	// Gib Erfolgs- oder Fehlermeldung zur�ck
	function returnMessage($success, $message) {
		if ($success == true)
			return "<span class=\"erfolg\">".$message."</span>";
		else 
			return "<span class=\"fehler\">".$message."</span>";
	}
	
	function returnFormatToolbar($currentcat) {
			$content = "<div style=\"padding:10px;\">"//"<p class=\"toolbar\">"
			// show user information if javascript inactive
			."<noscript><span class=\"fehler\">".getLanguageValue("toolbar_nojs_text")."</span></noscript>"
			// syntax elements, colors
			."<table>"
			."<tr>"
			."<td style=\"padding-right:10px;\">"
			.getLanguageValue("toolbar_syntaxelements")
			."</td>"
			."<td>"
			.getLanguageValue("toolbar_textcoloring")
			."</td>"
			."</tr>"
			."<tr>"
			."<td style=\"padding-right:10px;\">"
			."<img class=\"js\" title=\"[link| ... ]\" alt=\"Link\" src=\"gfx/jsToolbar/link.png\" onClick=\"insert('[link|', ']')\">"
	    ."<img class=\"js\" alt=\"eMail\" title=\"[mail| ... ]\" src=\"gfx/jsToolbar/mail.png\" onClick=\"insert('[mail|', ']')\">"
	    ."<img class=\"js\" alt=\"Seite\"	title=\"[seite| ... ]\" src=\"gfx/jsToolbar/seite.png\" onClick=\"insert('[seite|', ']')\">"
	  	."<img class=\"js\" alt=\"Kategorie\"	title=\"[kategorie| ... ]\" src=\"gfx/jsToolbar/kategorie.png\" onClick=\"insert('[kategorie|', ']')\">"
	  	."<img class=\"js\" alt=\"Datei\" title=\"[datei| ... ]\" src=\"gfx/jsToolbar/datei.png\" onClick=\"insert('[datei|', ']')\">"
	  	."<img class=\"js\" alt=\"Galerie\"	title=\"[galerie| ... ]\" src=\"gfx/jsToolbar/galerie.png\" onClick=\"insert('[galerie|', ']')\">"
	  	."<img class=\"js\" alt=\"Bild\" title=\"[bild| ... ]\" src=\"gfx/jsToolbar/bild.png\" onClick=\"insert('[bild|', ']')\">"
	  	."<img class=\"js\" alt=\"Bildlinks\"	title=\"[bildlinks| ... ]\" src=\"gfx/jsToolbar/bildlinks.png\" onClick=\"insert('[bildlinks|', ']')\">"
	  	."<img class=\"js\" alt=\"Bildrechts\" title=\"[bildrechts| ... ]\" src=\"gfx/jsToolbar/bildrechts.png\" onClick=\"insert('[bildrechts|', ']')\">"
	  	."<img class=\"js\" alt=\"�berschrift1\" title=\"[ueber1| ... ]\" src=\"gfx/jsToolbar/ueber1.png\" onClick=\"insert('[ueber1|', ']')\">"
	  	."<img class=\"js\" alt=\"�berschrift2\" title=\"[ueber2| ... ]\" src=\"gfx/jsToolbar/ueber2.png\" onClick=\"insert('[ueber2|', ']')\">"
	  	."<img class=\"js\" alt=\"�berschrift3\" title=\"[ueber3| ... ]\" src=\"gfx/jsToolbar/ueber3.png\" onClick=\"insert('[ueber3|', ']')\">"
	  	."<img class=\"js\" alt=\"Liste1\" title=\"[liste1| ... ]\" src=\"gfx/jsToolbar/liste1.png\" onClick=\"insert('[liste1|', ']')\">"
	  	."<img class=\"js\" alt=\"Liste2\" title=\"[liste2| ... ]\" src=\"gfx/jsToolbar/liste2.png\" onClick=\"insert('[liste2|', ']')\">"
	  	."<img class=\"js\" alt=\"Liste3\" title=\"[liste3| ... ]\" src=\"gfx/jsToolbar/liste3.png\" onClick=\"insert('[liste3|', ']')\">"
	  	."<img class=\"js\" alt=\"Horizontale Linie\" title=\"[----]\" src=\"gfx/jsToolbar/linie.png\" onClick=\"insert('[----]', '')\">"
	  	."<img class=\"js\" alt=\"HTML\" title=\"[html| ... ]\" src=\"gfx/jsToolbar/html.png\" onClick=\"insert('[html|', ']')\">"
	  	."</td>"
	  	."<td>"
	  	."<table><tr><td>"
	  	."<img class=\"js\" style=\"background-color:#AA0000\" alt=\"Farbe\" id=\"farbicon\" title=\"[farbe=RRGGBB| ... ]\" src=\"gfx/jsToolbar/farbe.png\" onClick=\"insert('[farbe=' + document.getElementById('farbcode').value + '|', ']')\">"
	  	."</td><td>"
	  	."<div class=\"colordiv\">"
	  	."<input type=\"text\" readonly=\"readonly\" maxlength=\"6\" value=\"AA0000\" class=\"colorinput\" id=\"farbcode\" size=\"0\">"
			."<img class=\"colorimage\" src=\"js_color_picker_v2/images/select_arrow.gif\" onmouseover=\"this.src='js_color_picker_v2/images/select_arrow_over.gif'\" onmouseout=\"this.src='js_color_picker_v2/images/select_arrow.gif'\" onclick=\"showColorPicker(this,document.getElementById('farbcode'))\" alt=\"...\" title=\"Farbauswahl\" />"
			."</div>"
			."</td></tr></table>"
	  	."</td>"
	  	."</tr>"
			."</table>"
			."<table>"
	  	."<tr>"
			// text formatting
			."<td>"
			.getLanguageValue("toolbar_textformatting")
			."</td>"
			// contents
			."<td>"
			.getLanguageValue("toolbar_contents")
			."</td>"
			."</tr>"
			."<tr>"
			."<td style=\"padding-right:10px;\">"
	  	."<img class=\"js\" alt=\"Linksb�ndig\" title=\"[links| ... ]\" src=\"gfx/jsToolbar/links.png\" onClick=\"insert('[links|', ']')\">"
	  	."<img class=\"js\" alt=\"Zentriert\" title=\"[zentriert| ... ]\" src=\"gfx/jsToolbar/zentriert.png\" onClick=\"insert('[zentriert|', ']')\">"
	  	."<img class=\"js\" alt=\"Blocksatz\" title=\"[block| ... ]\" src=\"gfx/jsToolbar/block.png\" onClick=\"insert('[block|', ']')\">"
	  	."<img class=\"js\" alt=\"Rechtsb�ndig\" title=\"[rechts| ... ]\" src=\"gfx/jsToolbar/rechts.png\" onClick=\"insert('[rechts|', ']')\">"
	  	."<img class=\"js\" alt=\"Fett\" title=\"[fett| ... ]\" src=\"gfx/jsToolbar/fett.png\" onClick=\"insert('[fett|', ']')\">"
	  	."<img class=\"js\" alt=\"Kursiv\" title=\"[kursiv| ... ]\" src=\"gfx/jsToolbar/kursiv.png\" onClick=\"insert('[kursiv|', ']')\">"
	  	."<img class=\"js\" alt=\"Unterstrichen\" title=\"[unter| ... ]\" src=\"gfx/jsToolbar/unter.png\" onClick=\"insert('[unter|', ']')\">"
	  	."<img class=\"js\" alt=\"Durchgetrichen\" title=\"[durch| ... ]\" src=\"gfx/jsToolbar/durch.png\" onClick=\"insert('[durch|', ']')\">"
	  	."</td>"
			."<td>"
	  	.returnOverviewSelectbox(1, $currentcat)
	  	."&nbsp;"
	  	.returnOverviewSelectbox(2, $currentcat)
	  	."&nbsp;"
	  	.returnOverviewSelectbox(3, $currentcat)
	  	."&nbsp;"
	  	.returnOverviewSelectbox(4, $currentcat)
	  	."</td>"
	  	."</tr></table>"
	  	
	  	."</div>";
	  	
	  return $content;
	}
	
	
	// R�ckgabe einer Selectbox mit Elementen, die per Klick in die Inhaltsseite �bernommen werden k�nnen
	// $type: 1=Kategorien 2=Inhaltsseiten 3=Dateien
	function returnOverviewSelectbox($type, $currentcat) {
		global $specialchars;
		global $CONTENT_DIR_REL;
		global $GALLERIES_DIR_REL;

		$elements = array();
		$selectname = "";

		switch ($type) {
			// Kategorien
			case 1:
				$handle = opendir("$CONTENT_DIR_REL");
				while (($file = readdir($handle))) {
					if (($file <> ".") && ($file <> ".."))
						array_push($elements, $file);
				}
				closedir($handle);
				$selectname = "cats";
				break;
			// Inhaltsseiten
			case 2:
				$handle = opendir("$CONTENT_DIR_REL/$currentcat");
				while (($file = readdir($handle))) {
					if (($file <> ".") && ($file <> "..") && is_file("$CONTENT_DIR_REL/$currentcat/$file") && (substr($file, strlen($file)-4, 4) == ".txt"))
						array_push($elements, $file);
				}
				closedir($handle);
				$selectname = "pages";
				break;
			// Dateien
			case 3:
				$handle = opendir("$CONTENT_DIR_REL/$currentcat/dateien");
				while (($file = readdir($handle))) {
					if (($file <> ".") && ($file <> "..") && is_file("$CONTENT_DIR_REL/$currentcat/dateien/$file"))
						array_push($elements, $file);
				}
				closedir($handle);
				$selectname = "files";
				break;
			// Galerien
			case 4:
				$handle = opendir($GALLERIES_DIR_REL);
				while (($file = readdir($handle))) {
					if (($file <> ".") && ($file <> ".."))
						array_push($elements, $file);
				}
				closedir($handle);
				$selectname = "gals";
				break;
			default:
				return "WRONG PARAMETER!";
		}
		sort($elements);
		$select = "<select name=\"cat\" class=\"overviewselect\" onchange=\"insertAndResetSelectbox(this);\">";
		switch ($type) {
			// Kategorien
			case 1:
				$select .="<option value=\"\">".getLanguageValue("button_category").":</option>";
				foreach ($elements as $element) {
					$element = $specialchars->rebuildSpecialChars(substr($element, 3, strlen($element)), $true);
					$select .= "<option value=\"".$element."\">".$element."</option>";
				}
				break;
			// Inhaltsseiten
			case 2:
				$select .="<option value=\"\">".getLanguageValue("button_site").":</option>";
				foreach ($elements as $element) {
					$element = $specialchars->rebuildSpecialChars(substr($element, 3, strlen($element) - 3 - strlen(".txt")), $true);
					$select .= "<option value=\"".$element."\">".$element."</option>";
				}
				break;
			// Dateien
			case 3:
				$select .="<option value=\"\">".getLanguageValue("button_data").":</option>";
				foreach ($elements as $element) {
					$select .= "<option value=\"".$element."\">".$element."</option>";
				}
				break;
			// Galerien
			case 4:
				$select .="<option value=\"\">".getLanguageValue("button_gallery").":</option>";
				foreach ($elements as $element) {
					$select .= "<option value=\"".$specialchars->rebuildSpecialChars($element, true)."\">".$specialchars->rebuildSpecialChars($element, true)."</option>";
				}
				break;
		}
		$select .= "</select>";
		return $select;
	}