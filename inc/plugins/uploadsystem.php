<?php
/**
 * Upload-System - by little.evil.genius
 * https://github.com/little-evil-genius/Upload-System
 * https://storming-gates.de/member.php?action=profile&uid=1712
*/

// Direktzugriff auf die Datei aus Sicherheitsgründen sperren
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if(!defined("PLUGINLIBRARY")) {
    define("PLUGINLIBRARY", MYBB_ROOT."inc/plugins/pluginlibrary.php");
}

// HOOKS
$plugins->add_hook('admin_config_settings_change', 'uploadsystem_settings_change');
$plugins->add_hook('admin_settings_print_peekers', 'uploadsystem_settings_peek');
$plugins->add_hook("admin_rpgstuff_action_handler", "uploadsystem_admin_rpgstuff_action_handler");
$plugins->add_hook("admin_rpgstuff_permissions", "uploadsystem_admin_rpgstuff_permissions");
$plugins->add_hook("admin_rpgstuff_menu", "uploadsystem_admin_rpgstuff_menu");
$plugins->add_hook("admin_load", "uploadsystem_admin_manage");
$plugins->add_hook("admin_user_users_edit_graph_tabs", "uploadsystem_admin_users_edit_tabs");
$plugins->add_hook("admin_user_users_edit_graph", "uploadsystem_admin_users_edit_graph");
$plugins->add_hook("admin_user_users_edit_signatur", "uploadsystem_admin_users_edit_signatur");
$plugins->add_hook("admin_user_users_edit_validate", "uploadsystem_admin_users_edit_validate"); // Error
$plugins->add_hook("admin_user_users_edit_commit", "uploadsystem_admin_users_edit_save"); // Save
$plugins->add_hook("datahandler_user_insert_end", "uploadsystem_admin_user_insert");
$plugins->add_hook("datahandler_user_delete_end", "uploadsystem_admin_user_deleted");
$plugins->add_hook("admin_style_themes_edit", "uploadsystem_admin_themes_menu"); // Stylesheets bearbeiten
$plugins->add_hook("admin_style_themes_add_stylesheet", "uploadsystem_admin_themes_menu"); // Stylesheets hinzfügen
$plugins->add_hook("admin_style_themes_export", "uploadsystem_admin_themes_menu"); // Theme exportieren
$plugins->add_hook("admin_style_themes_duplicate", "uploadsystem_admin_themes_menu"); // Theme duplizieren
$plugins->add_hook("admin_style_themes_begin", "uploadsystem_admin_themes_pages");
$plugins->add_hook("admin_rpgstuff_update_stylesheet", "uploadsystem_admin_update_stylesheet");
$plugins->add_hook("admin_rpgstuff_update_plugin", "uploadsystem_admin_update_plugin");
$plugins->add_hook("admin_rpgstuff_update_core", "uploadsystem_admin_update_core");
$plugins->add_hook('admin_load', 'uploadsystem_integrity_check'); 
$plugins->add_hook('usercp_menu', 'uploadsystem_usercp_menu', 40);
$plugins->add_hook('usercp_start', 'uploadsystem_usercp_page');
$plugins->add_hook("usercp_start", "uploadsystem_usercp_do_editsig");
$plugins->add_hook("usercp_editsig_start", "uploadsystem_usercp_editsig"); 
$plugins->add_hook("character_menu_plugins", "uploadsystem_charactercp_menu");
$plugins->add_hook('character_start', 'uploadsystem_charactercp_page');
$plugins->add_hook("postbit", "uploadsystem_postbit"); // normaler Postbit
$plugins->add_hook("postbit_prev", "uploadsystem_postbit"); // Vorschau
$plugins->add_hook("postbit_pm", "uploadsystem_postbit"); // Private Nachricht
$plugins->add_hook("postbit_announcement", "uploadsystem_postbit"); // Ankündigungen
$plugins->add_hook("memberlist_user", "uploadsystem_memberlist");
$plugins->add_hook("member_profile_start", "uploadsystem_memberprofile");
$plugins->add_hook("global_intermediate", "uploadsystem_global");
$plugins->add_hook("fetch_wol_activity_end", "uploadsystem_online_activity");
$plugins->add_hook("build_friendly_wol_location_end", "uploadsystem_online_location");
 
// Die Informationen, die im Pluginmanager angezeigt werden
function uploadsystem_info() {
	return array(
		"name"		=> "Upload-System",
		"description"	=> "Dieses Plugin ermöglicht den Usern verschiedene Grafiken per internem Upload-System hochzuladen. Auch kann eine Signatur-Datei hochgeladen werden.",
		"website"	=> "https://github.com/little-evil-genius/Upload-System",
		"author"	=> "little.evil.genius",
		"authorsite"	=> "https://storming-gates.de/member.php?action=profile&uid=1712",
		"version"	=> "1.2",
		"compatibility" => "18*"
	);
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin installiert wird (optional).
function uploadsystem_install() {
    
    global $db, $lang, $cache;

    // SPRACHDATEI
    $lang->load("uploadsystem");

    // RPG Stuff Modul muss vorhanden sein
    if (!file_exists(MYBB_ADMIN_DIR."/modules/rpgstuff/module_meta.php")) {
		flash_message($lang->uploadsystem_error_rpgstuff, 'error');
		admin_redirect('index.php?module=config-plugins');
	}

    // DATENBANKTABELL & FELDER
    uploadsystem_database();

	// EINSTELLUNGEN HINZUFÜGEN
    $maxdisporder = $db->fetch_field($db->query("SELECT MAX(disporder) FROM ".TABLE_PREFIX."settinggroups"), "MAX(disporder)");
	$setting_group = array(
		'name'          => 'uploadsystem',
		'title'         => 'Upload-System',
        'description'   => 'Einstellungen für das Upload-System',
		'disporder'     => $maxdisporder+1,
		'isdefault'     => 0
	);
	$db->insert_query("settinggroups", $setting_group);  
    uploadsystem_settings();
	rebuild_settings();

    // TEMPLATES ERSTELLEN
	// Template Gruppe für jedes Design erstellen
    $templategroup = array(
        "prefix" => "uploadsystem",
        "title" => $db->escape_string("Upload-System"),
    );
    $db->insert_query("templategroups", $templategroup);
    // Templates 
    uploadsystem_templates();
    
    // STYLESHEET HINZUFÜGEN
	require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
    $css = uploadsystem_stylesheet();
    $sid = $db->insert_query("themestylesheets", $css);
	$db->update_query("themestylesheets", array("cachefile" => "uploadsystem.css"), "sid = '".$sid."'", 1);

	$tids = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($tids)) {
		update_theme_stylesheet_list($theme['tid']);
	}

    // VERZEICHNISSE ERSTELLEN
    uploadsystem_directories();

    // UIDs IN DB EINFÜGEN
    uploadsystem_addUID();
}
 
// Funktion zur Überprüfung des Installationsstatus; liefert true zurürck, wenn Plugin installiert, sonst false (optional).
function uploadsystem_is_installed() {

    global $db;

    if ($db->table_exists("uploadfiles")) {
        return true;
    }
    return false;
} 
 
// Diese Funktion wird aufgerufen, wenn das Plugin deinstalliert wird (optional).
function uploadsystem_uninstall() {
    
	global $db;

    // DATENBANKEN LÖSCHEN
    if($db->table_exists("uploadsystem"))
    {
        $db->drop_table("uploadsystem");
    }
    if($db->table_exists("uploadfiles"))
    {
        $db->drop_table("uploadfiles");
    }
    
    // EINSTELLUNGEN LÖSCHEN
    $db->delete_query('settings', "name LIKE 'uploadsystem%'");
    $db->delete_query('settinggroups', "name = 'uploadsystem'");
    rebuild_settings();

    // TEMPLATGRUPPE LÖSCHEN
    $db->delete_query("templategroups", "prefix = 'uploadsystem'");

    // TEMPLATES LÖSCHEN
    $db->delete_query("templates", "title LIKE 'uploadsystem%'");

    // VERZEICHNIS LÖSCHEN
    uploadsystem_delete_directory(MYBB_ROOT.'uploads/uploadsystem');

    // STYLESHEET ENTFERNEN
	require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
	$db->delete_query("themestylesheets", "name = 'uploadsystem.css'");
	$query = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($query)) {
		update_theme_stylesheet_list($theme['tid']);
	}
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin aktiviert wird.
function uploadsystem_activate() {

    global $db, $lang, $cache;

    $lang->load("uploadsystem");

    if(!file_exists(PLUGINLIBRARY)) {
        flash_message($lang->uploadsystem_error_pluginlibrary, "error");
        admin_redirect("index.php?module=config-plugins");
    }

    // PLUGINLIBRARY
    uploadsystem_pluginlibrary();

    // VARIABLEN EINFÜGEN
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets('usercp_editsig', '#'.preg_quote('<form action="usercp.php" method="post">').'#', '<form action="usercp.php" method="post" enctype="multipart/form-data">');
    find_replace_templatesets('usercp_editsig', '#'.preg_quote('{$error}').'#', '{$error}{$uploadsystem_sig_errors}');
    find_replace_templatesets('usercp_editsig', '#'.preg_quote('{$codebuttons}').'#', '{$codebuttons}{$uploadsystem_signatur}');
    
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin deaktiviert wird.
function uploadsystem_deactivate() {

    global $db, $cache;

    require_once PLUGINLIBRARY;
    $PL or $PL = new PluginLibrary();
    $PL->edit_core('uploadsystem', 'admin/modules/user/users.php', [], true);

    // VARIABLEN ENTFERNEN
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("usercp_editsig", "#".preg_quote(' enctype="multipart/form-data"')."#i", '', 0);
    find_replace_templatesets("usercp_editsig", "#".preg_quote('{$uploadsystem_sig_errors}')."#i", '', 0);
    find_replace_templatesets("usercp_editsig", "#".preg_quote('{$uploadsystem_signatur}')."#i", '', 0);
}

######################
### HOOK FUNCTIONS ###
######################

// ADMIN-CP PEEKER //

function uploadsystem_settings_change(){
    
    global $db, $mybb, $uploadsystem_settings_peeker;

    $result = $db->simple_select('settinggroups', 'gid', "name='uploadsystem'", array("limit" => 1));
    $group = $db->fetch_array($result);
    $uploadsystem_settings_peeker = ($mybb->get_input('gid') == $group['gid']) && ($mybb->request_method != 'post');
}

function uploadsystem_settings_peek(&$peekers){

    global $uploadsystem_settings_peeker;

	if ($uploadsystem_settings_peeker) {
        $peekers[] = 'new Peeker($(".setting_uploadsystem_signatur"), $("#row_setting_uploadsystem_signatur_max, #row_setting_uploadsystem_signatur_size, #row_setting_uploadsystem_signatur_extensions"),/1/,true)';
    }
}

// ADMIN BEREICH - KONFIGURATION //

// action handler fürs acp konfigurieren
function uploadsystem_admin_rpgstuff_action_handler(&$actions) {
	$actions['uploadsystem'] = array('active' => 'uploadsystem', 'file' => 'uploadsystem');
}

// Benutzergruppen-Berechtigungen im ACP
function uploadsystem_admin_rpgstuff_permissions(&$admin_permissions) {

	global $lang;
	
    $lang->load('uploadsystem');

	$admin_permissions['uploadsystem'] = $lang->uploadsystem_permission;

	return $admin_permissions;
}

// im Menü einfügen
function uploadsystem_admin_rpgstuff_menu(&$sub_menu) {

    global $lang;

    $lang->load('uploadsystem');

    $sub_menu[] = [
        'id'    => 'uploadsystem',
        'title' => $lang->uploadsystem_nav,
        'link'  => 'index.php?module=rpgstuff-uploadsystem'
    ];
}

// die Seiten Verwaltung
function uploadsystem_admin_manage() {

    global $mybb, $db, $lang, $page, $run_module, $action_file;

    if ($page->active_action != 'uploadsystem') {
		return false;
	}

	$lang->load('uploadsystem');

    // EINSTELLUNGEN
    $allowed_extensions = $mybb->settings['uploadsystem_allowed_extensions'];
    $extensions_values = array_map('trim', explode(',', strtolower($allowed_extensions)));

    if ($page->active_action != 'uploadsystem') {
		return false;
	}

	if ($run_module == 'rpgstuff' && $action_file == 'uploadsystem') {

		// Add to page navigation
		$page->add_breadcrumb_item($lang->uploadsystem_breadcrumb_main, "index.php?module=rpgstuff-uploadsystem");

        // ÜBERSICHT
        if ($mybb->get_input('action') == "" || !$mybb->get_input('action')) {

            $page->output_header($lang->uploadsystem_overview_header);

			// Menü
			$sub_tabs['overview'] = [
				"title" => $lang->uploadsystem_tabs_overview,
				"link" => "index.php?module=rpgstuff-uploadsystem",
				"description" => $lang->uploadsystem_tabs_overview_desc
			];
            $sub_tabs['add'] = [
				"title" => $lang->uploadsystem_tabs_add,
				"link" => "index.php?module=rpgstuff-uploadsystem&amp;action=add"
			];
            $page->output_nav_tabs($sub_tabs, 'overview');

			// Show errors
			if (isset($errors)) {
				$page->output_inline_error($errors);
			}

            $form_container = new FormContainer($lang->uploadsystem_overview_container);
			$form_container->output_row_header($lang->uploadsystem_overview_container_name, array('style' => 'text-align: left; width: 25%;'));
			$form_container->output_row_header($lang->uploadsystem_overview_container_path, array('style' => 'text-align: left; width: 20%;'));
			$form_container->output_row_header($lang->uploadsystem_overview_container_extensions, array('style' => 'text-align: left; width: 15%;'));
			$form_container->output_row_header($lang->uploadsystem_overview_container_dims, array('style' => 'text-align: left; width: 15%;'));
			$form_container->output_row_header($lang->uploadsystem_overview_container_square, array('style' => 'text-align: center; width: 5%;'));
			$form_container->output_row_header($lang->uploadsystem_overview_container_size, array('style' => 'text-align: center; width: 10%;'));
			$form_container->output_row_header($lang->uploadsystem_options_container, array('style' => 'text-align: center; width: 10%;'));

            $query_elements = $db->query("SELECT * FROM ".TABLE_PREFIX."uploadsystem
            ORDER BY disporder ASC, name ASC
            ");

            while ($element = $db->fetch_array($query_elements)) {

                // Leer laufen lassen
                $usid = "";
                $identification = "";
                $name = "";
                $description = "";
                $path = "";
                $allowextensions = "";
                $mindims = "";
                $maxdims = "";
                $square = "";
                $bytesize = "";

                // Mit Infos füllen
                $usid = $element['usid'];
                $identification = $element['identification'];
                $name = $element['name'];
                $description = $element['description'];
                $path = $element['path'];
                $allowextensions = $element['allowextensions'];
                $mindims = $element['mindims'];
                $maxdims = $element['maxdims'];
                $square = $element['square'];
                $bytesize = $element['bytesize'];

                $form_container->output_cell('<strong><a href="index.php?module=rpgstuff-uploadsystem&amp;action=edit&amp;usid='.$usid.'">'.$name.'</a></strong> ('.$identification.')<br><small>'.$description.'</small>');
                $form_container->output_cell($path);
                $form_container->output_cell($allowextensions);

                if (empty($maxdims)) {
                    $form_container->output_cell('<justify>'.$lang->sprintf($lang->uploadsystem_overview_container_dims_min, $mindims).'</justify>');
                } else {
                    if ($maxdims != $mindims) {
                        $form_container->output_cell('<justify>'.$lang->sprintf($lang->uploadsystem_overview_container_dims_minmax, $mindims, $maxdims).'</justify>');
                    } else {
                        $form_container->output_cell('<justify>'.$lang->sprintf($lang->uploadsystem_overview_container_dims_fix, $mindims).'</justify>');
                    }
                }

                if ($square == 1) {
                    $form_container->output_cell('<center>'.$lang->uploadsystem_overview_container_square_yes.'</center>');
                } else {
                    $form_container->output_cell('<center>'.$lang->uploadsystem_overview_container_square_no.'</center>');
                }

                if ($bytesize != 0) {
                    $form_container->output_cell('<center>'.get_friendly_size($bytesize*1024).'</center>');
                } else {
                    $form_container->output_cell('<center>'.$lang->uploadsystem_overview_container_noLimit.'</center>');
                }

                // OPTIONEN
				$popup = new PopupMenu("uploadsystem_".$usid, $lang->uploadsystem_options_popup);	
                $popup->add_item(
                    $lang->uploadsystem_options_popup_edit,
                    "index.php?module=rpgstuff-uploadsystem&amp;action=edit&amp;usid=".$usid
                );
                $popup->add_item(
                    $lang->uploadsystem_options_popup_delete,
                    "index.php?module=rpgstuff-uploadsystem&amp;action=delete&amp;usid=".$usid."&amp;my_post_key={$mybb->post_code}", 
					"return AdminCP.deleteConfirmation(this, '".$lang->uploadsystem_delete_notice."')"
                );
                $form_container->output_cell($popup->fetch(), array("class" => "align_center"));
                $form_container->construct_row();
            }

            if($db->num_rows($query_elements) == 0){
                $form_container->output_cell($lang->uploadsystem_overview_noElements, array("colspan" => 7, 'style' => 'text-align: center;'));
                $form_container->construct_row();
			}

            $form_container->end();
            $page->output_footer();
			exit;
        }

        // HINZUFÜGEN
        if ($mybb->get_input('action') == "add") {
            
            if ($mybb->request_method == "post") {

                $errors = uploadsystem_validate_element();

                // No errors - insert
                if (empty($errors)) {

                    $identification = $db->escape_string($mybb->get_input('identification'));
                    $extensionsInput = $mybb->get_input('allowedExtensions', MyBB::INPUT_ARRAY);
                    $inputsExtensions = implode(',', $extensionsInput);

                    // Ordner Pfad
                    $folder_path =  "uploads/uploadsystem/".$identification."/"; 
    
                    // Daten speichern
                    $new_uploadelement = array(
                        "disporder" => $mybb->get_input('disporder', MyBB::INPUT_INT),
                        "identification" => $identification,
                        "name" => $db->escape_string($mybb->get_input('name')),
                        "description" => $db->escape_string($mybb->get_input('description')),
                        "path" => $db->escape_string($folder_path),
                        "allowextensions" => $db->escape_string($inputsExtensions),
                        "mindims" => $db->escape_string($mybb->get_input('mindims')),
                        "maxdims" => $db->escape_string($mybb->get_input('maxdims')),
                        "square" => $mybb->get_input('square', MyBB::INPUT_INT),
                        "bytesize" => $db->escape_string($mybb->get_input('bytesize')),
                    );                    
                    
                    $db->insert_query("uploadsystem", $new_uploadelement);
            
                    // VERZEICHNIS ERSTELLEN
                    if (!is_dir(MYBB_ROOT."uploads/uploadsystem/".$identification)) {
                        mkdir(MYBB_ROOT."uploads/uploadsystem/".$identification, 0777, true);
                    }

                    // NEUE DB SPALTE
                    $db->write_query("ALTER TABLE ".TABLE_PREFIX."uploadfiles ADD ".$identification." TEXT NOT NULL");
    
                    flash_message($lang->uploadsystem_add_flash, 'success');
                    admin_redirect("index.php?module=rpgstuff-uploadsystem");
                }
            }

            $page->add_breadcrumb_item($lang->uploadsystem_breadcrumb_add);
			$page->output_header($lang->uploadsystem_breadcrumb_main." - ".$lang->uploadsystem_add_header);

			// Menü
			$sub_tabs['overview'] = [
				"title" => $lang->uploadsystem_tabs_overview,
				"link" => "index.php?module=rpgstuff-uploadsystem"
			];
            $sub_tabs['add'] = [
				"title" => $lang->uploadsystem_tabs_add,
				"link" => "index.php?module=rpgstuff-uploadsystem&amp;action=add",
				"description" => $lang->uploadsystem_tabs_add_desc
			];
            $page->output_nav_tabs($sub_tabs, 'add');

			// Show errors
			if (isset($errors)) {
				$page->output_inline_error($errors);
                $square = $mybb->get_input('square');
                $bytesize = $mybb->get_input('bytesize');
                $InputExtensions = $mybb->get_input('allowedExtensions', MyBB::INPUT_ARRAY);
			} else {
                $square = 0;
                $bytesize = 5120;
                $inputsExtensions = "";
                $InputExtensions = array_filter(explode(",", $inputsExtensions),static fn($v) => $v !== '');
            }

            // Build the form
            $form = new Form("index.php?module=rpgstuff-uploadsystem&amp;action=add", "post", "", 1);
            $form_container = new FormContainer($lang->uploadsystem_add_container);
            echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
    
            $form_container->output_row(
                $lang->uploadsystem_form_identification,
                $lang->uploadsystem_form_identification_desc,
                $form->generate_text_box('identification', $mybb->get_input('identification'))
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_name,
                $lang->uploadsystem_form_name_desc,
                $form->generate_text_box('name', $mybb->get_input('name'))
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_description,
                $lang->uploadsystem_form_description_desc,
                $form->generate_text_box('description', $mybb->get_input('description'))
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_disporder,
                $lang->uploadsystem_form_disporder_desc,
                $form->generate_numeric_field('disporder', $mybb->get_input('description'), array('id' => 'disporder', 'min' => 0))
            );
    
            $extensions_options = [];
            foreach ($extensions_values as $extension) {
                $checked = in_array($extension, $InputExtensions);
                $extensions_options[] = $form->generate_check_box("allowedExtensions[".$extension."]", $extension, strtoupper($extension), ['checked' => $checked, 'id' => $extension]);
            }
            $form_container->output_row(
                $lang->uploadsystem_form_extensions, 
                $lang->uploadsystem_form_extensions_desc,
                implode('<br />', $extensions_options), '', [], ['id' => 'row_extensions_options']
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_mindims,
                $lang->uploadsystem_form_mindims_desc,
                $form->generate_text_box('mindims', $mybb->get_input('mindims'))
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_maxdims,
                $lang->uploadsystem_form_maxdims_desc,
                $form->generate_text_box('maxdims', $mybb->get_input('maxdims'))
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_square,
                $lang->uploadsystem_form_square_desc,
                $form->generate_yes_no_radio('square', $square)
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_bytesize,
                $lang->uploadsystem_form_bytesize_desc,
                $form->generate_numeric_field('bytesize', $bytesize, array('id' => 'bytesize', 'min' => 0))
            );

            $form_container->end();
            $buttons[] = $form->generate_submit_button($lang->uploadsystem_add_button);
            $form->output_submit_wrapper($buttons);
            $form->end();
            $page->output_footer();
            exit;
        }

        // BEARBEITEN
        if ($mybb->get_input('action') == "edit") {

            // Get the data
            $usid = $mybb->get_input('usid', MyBB::INPUT_INT);
            $element_query = $db->simple_select("uploadsystem", "*", "usid = ".$usid);
            $element = $db->fetch_array($element_query);
            
            if ($mybb->request_method == "post") {
                    
                $usid = $mybb->get_input('usid', MyBB::INPUT_INT);

                $errors = uploadsystem_validate_element($usid);

                // No errors - insert
                if (empty($errors)) {

                    $extensionsInput = $mybb->get_input('allowedExtensions', MyBB::INPUT_ARRAY);
                    $inputsExtensions = implode(',', $extensionsInput);
    
                    // Daten speichern
                    $update_uploadelement = array(
                        "disporder" => $mybb->get_input('disporder', MyBB::INPUT_INT),
                        "name" => $db->escape_string($mybb->get_input('name')),
                        "description" => $db->escape_string($mybb->get_input('description')),
                        "allowextensions" => $db->escape_string($inputsExtensions),
                        "mindims" => $db->escape_string($mybb->get_input('mindims')),
                        "maxdims" => $db->escape_string($mybb->get_input('maxdims')),
                        "square" => $mybb->get_input('square', MyBB::INPUT_INT),
                        "bytesize" => $db->escape_string($mybb->get_input('bytesize')),
                    );

                    $identificationOld = $db->escape_string($mybb->get_input('identificationOld'));
                    $identificationNew = $db->escape_string($mybb->get_input('identification'));
                    
                    if ($identificationOld !== $identificationNew) {
                        uploadsystem_identification_update($identificationOld, $identificationNew);
                        $update_uploadelement['identification'] = $identificationNew;
                        $update_uploadelement['path'] = $db->escape_string('uploads/uploadsystem/'.$identificationNew.'/');
                    }

                    $db->update_query("uploadsystem", $update_uploadelement, "usid = ".$usid);
    
                    flash_message($lang->uploadsystem_edit_flash, 'success');
                    admin_redirect("index.php?module=rpgstuff-uploadsystem");
                }
            }

            $page->add_breadcrumb_item($lang->uploadsystem_breadcrumb_edit);
			$page->output_header($lang->uploadsystem_breadcrumb_main." - ".$lang->uploadsystem_edit_header);

			// Menü
			$sub_tabs['overview'] = [
				"title" => $lang->uploadsystem_tabs_overview,
				"link" => "index.php?module=rpgstuff-uploadsystem"
			];
            $sub_tabs['edit'] = [
				"title" => $lang->uploadsystem_tabs_edit,
				"link" => "index.php?module=rpgstuff-uploadsystem&amp;action=edit",
				"description" => $lang->sprintf($lang->uploadsystem_tabs_edit_desc, $element['name'])
			];
            $page->output_nav_tabs($sub_tabs, 'edit');

			// Show errors
			if (isset($errors)) {
				$page->output_inline_error($errors);
                $identification = $mybb->get_input('identification');
                $name = $mybb->get_input('name');
                $description = $mybb->get_input('description');
                $disporder = $mybb->get_input('disporder');
                $mindims = $mybb->get_input('mindims');
                $maxdims = $mybb->get_input('maxdims');
                $square = $mybb->get_input('square');
                $bytesize = $mybb->get_input('bytesize');
                $InputExtensions = $mybb->get_input('allowedExtensions', MyBB::INPUT_ARRAY);
			} else {
                $identification = $element['identification'];
                $name = $element['name'];
                $description = $element['description'];
                $disporder = $element['disporder'];
                $mindims = $element['mindims'];
                $maxdims = $element['maxdims'];
                $square = $element['square'];
                $bytesize = $element['bytesize'];
                $inputsExtensions = $element['allowextensions'];
                $InputExtensions = array_filter(explode(",", $inputsExtensions),static fn($v) => $v !== '');
            }

            // Build the form
            $form = new Form("index.php?module=rpgstuff-uploadsystem&amp;action=edit", "post", "", 1);
            $form_container = new FormContainer($lang->sprintf($lang->uploadsystem_edit_container, $element['name']));
            echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
            echo $form->generate_hidden_field("usid", $usid);
            echo $form->generate_hidden_field("identificationOld", $element['identification']);
    
            $form_container->output_row(
                $lang->uploadsystem_form_identification,
                $lang->uploadsystem_form_identification_desc,
                $form->generate_text_box('identification', $identification)
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_name,
                $lang->uploadsystem_form_name_desc,
                $form->generate_text_box('name', $name)
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_description,
                $lang->uploadsystem_form_description_desc,
                $form->generate_text_box('description', $description)
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_disporder,
                $lang->uploadsystem_form_disporder_desc,
                $form->generate_numeric_field('disporder', $disporder, array('id' => 'disporder', 'min' => 0))
            );
    
            $extensions_options = [];
            foreach ($extensions_values as $extension) {
                $checked = in_array($extension, $InputExtensions);
                $extensions_options[] = $form->generate_check_box("allowedExtensions[".$extension."]", $extension, strtoupper($extension), ['checked' => $checked, 'id' => $extension]);
            }
            $form_container->output_row(
                $lang->uploadsystem_form_extensions, 
                $lang->uploadsystem_form_extensions_desc,
                implode('<br />', $extensions_options), '', [], ['id' => 'row_extensions_options']
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_mindims,
                $lang->uploadsystem_form_mindims_desc,
                $form->generate_text_box('mindims', $mindims)
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_maxdims,
                $lang->uploadsystem_form_maxdims_desc,
                $form->generate_text_box('maxdims', $maxdims)
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_square,
                $lang->uploadsystem_form_square_desc,
                $form->generate_yes_no_radio('square', $square)
            );
    
            $form_container->output_row(
                $lang->uploadsystem_form_bytesize,
                $lang->uploadsystem_form_bytesize_desc,
                $form->generate_numeric_field('bytesize', $bytesize, array('id' => 'bytesize', 'min' => 0))
            );

            $form_container->end();
            $buttons[] = $form->generate_submit_button($lang->uploadsystem_edit_button);
            $form->output_submit_wrapper($buttons);
            $form->end();
            $page->output_footer();
            exit;
        }

        // LÖSCHEN
        if ($mybb->get_input('action') == "delete") {
            
            // Get the data
            $usid = $mybb->get_input('usid', MyBB::INPUT_INT);

			// Error Handling
			if (empty($usid)) {
				flash_message($lang->uploadsystem_error_invalid, 'error');
				admin_redirect("index.php?module=rpgstuff-uploadsystem");
			}

			// Cancel button pressed?
			if (isset($mybb->input['no']) && $mybb->input['no']) {
				admin_redirect("index.php?module=rpgstuff-uploadsystem");
			}

			if ($mybb->request_method == "post") {

                $identification = $db->fetch_field($db->simple_select("uploadsystem", "identification", "usid = ".$usid), "identification");

                // Spalte aus der DB löschen
                if ($db->field_exists($identification, "uploadfiles")) {
                    $db->drop_column("uploadfiles", $identification);
                }

                // Ordner löschen
                uploadsystem_delete_directory(MYBB_ROOT.'uploads/uploadsystem/'.$identification);
                
                $db->delete_query('uploadsystem', "usid = ".$usid);

				flash_message($lang->uploadsystem_delete_flash, 'success');
				admin_redirect("index.php?module=rpgstuff-uploadsystem");
			} else {
				$page->output_confirm_action(
					"index.php?module=rpgstuff-uploadsystem&amp;action=delete&amp;usid=".$usid,
					$lang->uploadsystem_delete_notice
				);
			}
			exit;
        }
    }
}

// USER ANGABEN
// Tabs erweitern
function uploadsystem_admin_users_edit_tabs(&$tabs) {

    global $lang;

	$lang->load('uploadsystem');

	$tabs['uploadsystem'] = $lang->uploadsystem_user;
}

// Tabinhalt
function uploadsystem_admin_users_edit_graph() {

    global $user, $lang, $form, $db, $mybb;

	$lang->load('uploadsystem');

    echo "<div id=\"tab_uploadsystem\">\n";
    
    $allElements = $db->query("SELECT * FROM ".TABLE_PREFIX."uploadsystem
    ORDER BY disporder ASC, name ASC
    ");
    
    while($element = $db->fetch_array($allElements)) {

        // Leer laufen lassen
        $identification = "";
        $name = "";
        $description = "";
        $path = "";
        $allowextensions = "";
        $mindims = "";
        $maxdims = "";
        $square = "";        
        $bytesize = "";

        // Mit Infos füllen
        $identification = $element['identification'];
        $name = $element['name'];
        $description = $element['description'];
        $path = $element['path'];
        $allowextensions = $element['allowextensions'];
        $mindims = $element['mindims'];
        $maxdims = $element['maxdims'];
        $square = $element['square'];
        $bytesize = $element['bytesize'];

        // Dateiname laden
        $file_name = $db->fetch_field($db->simple_select("uploadfiles", $identification, "ufid = ".$user['uid']), $identification);

        if(!empty($file_name)) {
            $preview = $mybb->settings['bburl']."/".$path.$file_name;
            $notice = $lang->uploadsystem_user_notice;
        } else {
            $preview = $mybb->settings['bburl']."/images/uploadsystem_default.png";  
            $notice = "";  
        }
    
        // Größe
        list($minwidth, $minheight) = preg_split('/[|x]/', my_strtolower($mindims));
        if(!empty($maxdims)) {
            list($maxwidth, $maxheight) = preg_split('/[|x]/', strtolower($maxdims));

            $maxwidth = (int)$maxwidth;
            $maxheight = (int)$maxheight;

            // exakte Größe
            if ($minwidth == $maxwidth && $minheight == $maxheight) {
                if ($mybb->settings['uploadsystem_retina'] == 1) {
                    $retina = $lang->sprintf($lang->uploadsystem_retina_dims, $maxwidth*2, $maxheight*2);
                    $dims = $lang->sprintf($lang->uploadsystem_user_dims_fixed, $maxwidth, $maxheight, $retina);
                } else {
                    $dims = $lang->sprintf($lang->uploadsystem_user_dims_fixed, $maxwidth, $maxheight, '');
                }
            } 
            // von bis
            else {
                if ($mybb->settings['uploadsystem_retina'] == 1) {
                    $retina = $lang->sprintf($lang->uploadsystem_retina_dims, $maxwidth*2, $maxheight*2);
                    $dims = $lang->sprintf($lang->uploadsystem_user_dims_maxmin, $mindims, $maxdims, $retina);
                } else {
                    $dims = $lang->sprintf($lang->uploadsystem_user_dims_maxmin, $mindims, $maxdims, '');
                }
            }
        }
        // nur minimal
        else {
            $dims = $lang->sprintf($lang->uploadsystem_user_dims_min, $mindims);
        }

        // Quadratisch
        if ($square == 1) {
            $square = $lang->uploadsystem_user_square;
        } else {
            $square = "";
        }
    
        // Dateigröße
        if ($bytesize != 0) {
            $size = $lang->sprintf($lang->uploadsystem_user_size, get_friendly_size($bytesize*1024));
        } else {
            $size = $lang->uploadsystem_user_noSize;
        }
    
        // Dateiformate
        $allowextensions = str_replace(",", ", ", $allowextensions);
        $extensions_array = array_map('trim', explode(',', $allowextensions));
        if (count($extensions_array) > 1) {
            $extensions = $lang->sprintf($lang->uploadsystem_user_extensions_plural, strtoupper($allowextensions));
        } else {
            $extensions = $lang->sprintf($lang->uploadsystem_user_extensions_singular, strtoupper($allowextensions));
        }
        
        $table = new Table;
        // linke Spalte = Vorschau
        $table->construct_cell("<div style=\"width:".$minwidth."px;height:".$minheight."px;\"><img src=\"".$preview."\" style=\"width:".$minwidth."px;height:".$minheight."px;\"></div>", array('width' => '150'));
    
        // rechte Spalte = Infos    
        if ($mybb->settings['uploadsystem_retina'] == 1 && !empty($maxdims)) {
            $retina_checkbox = "<br>".$form->generate_check_box("retinadisplay_".$identification, 1, $lang->uploadsystem_retina_checkbox);
        } else {
            $retina_checkbox = "";
        }

        $content = $description."<br><br>".$form->generate_check_box("remove_".$identification, 1, "<b>".$lang->sprintf($lang->uploadsystem_user_remove, $name)."</b>")."<br><br><small>".$dims." ".$square."<br>".$extensions."<br>".$size."</small><br><br><strong>".$lang->sprintf($lang->uploadsystem_user_upload, $name)."</strong>".$notice."<br>".$form->generate_file_upload_box("upload_".$identification).$retina_checkbox;

        $table->construct_cell($content);
        $table->construct_row();    
        $table->output($lang->sprintf($lang->uploadsystem_user_container, $name));
    }

    echo "</div>\n";
}

// Signatur
function uploadsystem_admin_users_edit_signatur() {

    global $user, $lang, $form, $db, $mybb, $form_container;

    $signatur_setting = $mybb->settings['uploadsystem_signatur'];

    if ($signatur_setting != 1) return;

	$lang->load('uploadsystem');

    // Dateiname laden
    $sigfile = $db->fetch_field($db->simple_select("uploadfiles", "signatur", "ufid = ".$mybb->user['uid']), "signatur");
    $filename = strtok($sigfile, '?');

    if(!empty($filename)) {
        $file_url = $mybb->settings['bburl']."/uploads/uploadsystem/signatur/".$filename;
        $remove = "";
    } else {
        $file_url = $lang->uploadsystem_user_signatur_nofile;
        $remove = "";
    }

    $content = $form->generate_check_box("remove_signatur", 1, "<b>".$lang->uploadsystem_user_signatur_button_remove."</b>")."<br><br>".$form->generate_file_upload_box("signaturlink");

    $form_container->output_row($lang->uploadsystem_user_signatur_headline, $file_url, $content);
}

// Error Anzeigen
function uploadsystem_admin_users_edit_validate() {

    global $db, $mybb, $lang, $errors;

    $errors = array();

    if(!empty($_FILES['signaturlink']['name'])) {
        $signatur_errors = uploadsystem_validate_upload_signatur('acp');
   
        if(!empty($signatur_errors)) {
            $errors = array_merge($errors, $signatur_errors);
        }
    }

    $allElements = $db->query("SELECT * FROM ".TABLE_PREFIX."uploadsystem
    ORDER BY disporder ASC, name ASC    
    ");
    
    while ($element = $db->fetch_array($allElements)){
        $input_name = "upload_".$element['identification'];
        
        if(!empty($_FILES[$input_name]['name'])) {
            $element_errors = uploadsystem_validate_upload($input_name, $element);
   
            if(!empty($element_errors)) {
                $errors = array_merge($errors, $element_errors);
            }
        }
    }
}

// Speichern
function uploadsystem_admin_users_edit_save() {

    global $db, $mybb;

    $ufid = (int)$mybb->input['uid'];

    // Signatur
    // hochladen
    if(!empty($_FILES['signaturlink']['name'])) {
        uploadsystem_element_upload($ufid, 'signatur', 'signaturlink');
    }
    // entfernen
    if($mybb->get_input('remove_signatur')) {
        uploadsystem_element_remove($ufid, 'signatur');
    }

    // Andere Elements
    $allElements = $db->query("SELECT * FROM ".TABLE_PREFIX."uploadsystem
    ORDER BY disporder ASC, name ASC
    ");

    while($element = $db->fetch_array($allElements)) {

        $identification = $element['identification'];
        $input_name = "upload_".$element['identification']; 

        // hochladen
        if(!empty($_FILES[$input_name]['name'])) {
            uploadsystem_element_upload($ufid, $identification, $input_name);
        }

        // entfernen
        if($mybb->get_input('remove_'.$identification)) {
            uploadsystem_element_remove($ufid, $identification);
        }
    }
}

// neuer Account
function uploadsystem_admin_user_insert(&$userhandler){
    
    global $db;

    $uid = (int)$userhandler->uid;
    if ($uid <= 0) {
        return;
    }

    $newuser = array(
        "ufid" => $uid,
        "signatur" => ''                
    );

    // Alle Felder    
    $allFields = $db->query("SELECT identification FROM ".TABLE_PREFIX."uploadsystem");

    while ($field = $db->fetch_array($allFields)) {
        $key = $field['identification'];
        if (!isset($newuser[$key])) {
            $newuser[$key] = '';
        }
    }

    $db->insert_query("uploadfiles", $newuser);
}

// Account wird gelöscht
function uploadsystem_admin_user_deleted($userhandler) {

    global $db;

    require_once MYBB_ROOT."inc/functions_upload.php";
    require_once MYBB_ROOT."inc/functions.php";

    $uids = $userhandler->delete_uids;
    $uid_list = explode(',', $uids);

    $allIdentifications = $db->query("SELECT identification FROM ".TABLE_PREFIX."uploadsystem");
    $allidentifications = [];
    while($id = $db->fetch_array($allIdentifications)) {
        $allidentifications[] = $id['identification'];
    }

    foreach ($uid_list as $uid) {

        $deleteChara = (int)$uid;

        foreach ($allidentifications as $identification) {
            uploadsystem_element_remove($deleteChara, $identification);
        }

        uploadsystem_element_remove($deleteChara, 'signatur');

        $db->delete_query("uploadfiles", "ufid = ".$deleteChara);
    }
}

// PLACEHOLDER //
// Tab Menu
function uploadsystem_admin_themes_menu() {

    global $mybb, $lang, $sub_tabs;

	$lang->load('uploadsystem');
    
    $sub_tabs['uploadsystem'] = array(
		'title' => $lang->uploadsystem_themes_tab,
		'link' => "index.php?module=style-themes&amp;action=uploadsystem&amp;tid=".$mybb->input['tid'],
		'description' => $lang->uploadsystem_themes_tab_desc
	);
}

// Seite
function uploadsystem_admin_themes_pages() {

    global $db, $mybb, $lang, $page, $form;
    
    // return if the action key isn't part of the input
    $uploadsystem_list  = ['uploadsystem', 'do_uploadsystem'];
    if (!in_array($mybb->get_input('action', MyBB::INPUT_STRING), $uploadsystem_list)) return;

	$lang->load('uploadsystem');

    $query = $db->simple_select("themes", "*", "tid = ".$mybb->get_input('tid', MyBB::INPUT_INT));
	$theme = $db->fetch_array($query);
    $properties = my_unserialize($theme['properties']);

	// Does the theme not exist?
	if(!$theme || $theme['tid'] == 1) {
		flash_message($lang->error_invalid_theme, 'error');
		admin_redirect("index.php?module=style-themes");
	}

	$page->add_breadcrumb_item(htmlspecialchars_uni($theme['name']), "index.php?module=style-themes&amp;action=edit&amp;tid={$mybb->input['tid']}");
	$page->add_breadcrumb_item($lang->uploadsystem_themes_header);
	$page->output_header($lang->themes." - ".$lang->uploadsystem_themes_header);
    
    $sub_tabs['uploadsystem'] = array(
		'title' => $lang->uploadsystem_themes_tab,
		'link' => "index.php?module=style-themes&amp;action=uploadsystem&amp;tid=".$mybb->input['tid'],
		'description' => $lang->sprintf($lang->uploadsystem_themes_tab_desc, $theme['name'])
	);

	$sub_tabs['edit_stylesheets'] = array(
		'title' => $lang->edit_stylesheets,
		'link' => "index.php?module=style-themes&amp;action=edit&amp;tid={$mybb->input['tid']}"
	);

	$sub_tabs['add_stylesheet'] = array(
		'title' => $lang->add_stylesheet,
		'link' => "index.php?module=style-themes&amp;action=add_stylesheet&amp;tid={$mybb->input['tid']}"
	);

	$sub_tabs['export_theme'] = array(
		'title' => $lang->export_theme,
		'link' => "index.php?module=style-themes&amp;action=export&amp;tid={$mybb->input['tid']}"
	);

	$sub_tabs['duplicate_theme'] = array(
		'title' => $lang->duplicate_theme,
		'link' => "index.php?module=style-themes&amp;action=duplicate&amp;tid={$mybb->input['tid']}"
	);

	$page->output_nav_tabs($sub_tabs, 'uploadsystem');

    if($mybb->get_input('action') == "uploadsystem") {

        if($mybb->request_method == "post" && $mybb->get_input('do') == "uploadsystem") {

            $path = $mybb->get_input('imgdir');

            $allElements = $db->query("SELECT * FROM ".TABLE_PREFIX."uploadsystem
            ORDER BY disporder ASC, name ASC
            ");
            
            $uploadsystem_errors = [];
            while($element = $db->fetch_array($allElements)) {

                $identification = $element['identification'];
                $input_name = "upload_".$identification;

                // hochladen
                if(!empty($_FILES[$input_name]['name'])) {
                    $element_errors = uploadsystem_validate_upload($input_name, $element);
                    
                    // No errors - insert
                    if (empty($element_errors)) {
                        uploadsystem_element_upload_placeholder($path, $identification, $input_name);
                    } else {
                        $uploadsystem_errors = array_merge($uploadsystem_errors, $element_errors);
                    }
                }
                
                // entfernen
                if($mybb->get_input('remove_'.$identification)) {
                    uploadsystem_element_remove_placeholder($path, $identification);
                }
            }

            if (empty($uploadsystem_errors)) {
                flash_message($lang->uploadsystem_themes_flash, 'success');
                admin_redirect("index.php?module=style-themes&action=uploadsystem&tid={$mybb->get_input('tid')}");
            }
        }

		// Show errors
		if (isset($uploadsystem_errors)) {
			$page->output_inline_error($uploadsystem_errors);
		}

        $form = new Form("index.php?module=style-themes&amp;action=uploadsystem", "post", "", 1);
        echo $form->generate_hidden_field("imgdir", $properties['imgdir']);
        echo $form->generate_hidden_field("do", 'uploadsystem');
        echo $form->generate_hidden_field("tid", $theme['tid']);
        
        $allElements = $db->query("SELECT * FROM ".TABLE_PREFIX."uploadsystem
        ORDER BY disporder ASC, name ASC
        ");
        
        while($element = $db->fetch_array($allElements)) {

            // Leer laufen lassen
            $identification = "";
            $name = "";
            $description = "";
            $allowextensions = "";
            $mindims = "";
            $maxdims = "";
            $square = "";        
            $bytesize = "";
        
            // Mit Infos füllen
            $identification = $element['identification'];
            $name = $element['name'];
            $description = $element['description'];
            $allowextensions = $element['allowextensions'];
            $mindims = $element['mindims'];
            $maxdims = $element['maxdims'];
            $square = $element['square'];
            $bytesize = $element['bytesize'];
        
            // Datei suchen 
            $extensions_array = array_map('trim', explode(',', strtolower($element['allowextensions'])));
            if(!in_array('webp', $extensions_array)) {
                $extensions_array[] = 'webp';
            }

            $filename = $properties['imgdir']."/default_".$identification.".";
        
            $found_format = '';
            foreach ($extensions_array as $format) {
                $file_path = MYBB_ROOT.$filename.$format;
                if (file_exists($file_path)) {
                    $found_format = $format;
                }
            }

            if (!empty($found_format)) {
                $preview = $mybb->settings['bburl']."/".$filename.$found_format."?v=".filemtime(MYBB_ROOT.$filename.$found_format);
                $notice = $lang->uploadsystem_user_notice;
            } else {  
                $preview = $mybb->settings['bburl']."/images/uploadsystem_default.png";  
                $notice = "";
            }

            // INFORMATIONEN //
            // Größe        
            list($minwidth, $minheight) = preg_split('/[|x]/', my_strtolower($mindims));
            if(!empty($maxdims)) {
                list($maxwidth, $maxheight) = preg_split('/[|x]/', strtolower($maxdims));

                $maxwidth = (int)$maxwidth;            
                $maxheight = (int)$maxheight;

                // exakte Größe
                if ($minwidth == $maxwidth && $minheight == $maxheight) {
                    if ($mybb->settings['uploadsystem_retina'] == 1) {
                        $retina = $lang->sprintf($lang->uploadsystem_retina_dims, $maxwidth*2, $maxheight*2);
                        $dims = $lang->sprintf($lang->uploadsystem_user_dims_fixed, $maxwidth, $maxheight, $retina);
                    } else {
                        $dims = $lang->sprintf($lang->uploadsystem_user_dims_fixed, $maxwidth, $maxheight, '');
                    }
                } 
                // von bis        
                else {
                    if ($mybb->settings['uploadsystem_retina'] == 1) {
                        $retina = $lang->sprintf($lang->uploadsystem_retina_dims, $maxwidth*2, $maxheight*2);
                        $dims = $lang->sprintf($lang->uploadsystem_user_dims_maxmin, $mindims, $maxdims, $retina);
                    } else {
                        $dims = $lang->sprintf($lang->uploadsystem_user_dims_maxmin, $mindims, $maxdims, '');
                    }
                }
            }
            // nur minimal
            else {
                $dims = $lang->sprintf($lang->uploadsystem_user_dims_min, $mindims);
            }
            
            // Quadratisch
            if ($square == 1) {
                $square = $lang->uploadsystem_user_square;
            } else {
                $square = "";
            }
    
            // Dateigröße    
            if ($bytesize != 0) {
                $size = $lang->sprintf($lang->uploadsystem_user_size, get_friendly_size($bytesize*1024));
            } else {
                $size = $lang->uploadsystem_user_noSize;    
            }
    
            // Dateiformate
            if (count($extensions_array) > 1) {
                $extensions = $lang->sprintf($lang->uploadsystem_user_extensions_plural, strtoupper($allowextensions));
            } else {
                $extensions = $lang->sprintf($lang->uploadsystem_user_extensions_singular, strtoupper($allowextensions));
            }
        
            $table = new Table;
        
            // linke Spalte = Vorschau
            $table->construct_cell("<div style=\"width:".$minwidth."px;height:".$minheight."px;\"><img src=\"".$preview."\" style=\"width:".$minwidth."px;height:".$minheight."px;\"></div>", array('width' => '150'));
    
            // rechte Spalte = Infos            
            if ($mybb->settings['uploadsystem_retina'] == 1 && !empty($maxdims)) {
                $retina_checkbox = "<br>".$form->generate_check_box("retinadisplay_".$identification, 1, $lang->uploadsystem_retina_checkbox);
            } else {
                $retina_checkbox = "";
            }    
            $content = $description."<br><br>".$form->generate_check_box("remove_".$identification, 1, "<b>".$lang->sprintf($lang->uploadsystem_user_remove, $name)."</b>")."<br><br><small>".$dims." ".$square."<br>".$extensions."<br>".$size."</small><br><br><strong>".$lang->sprintf($lang->uploadsystem_user_upload, $name)."</strong>".$notice."<br>".$form->generate_file_upload_box("upload_".$identification).$retina_checkbox;

            $table->construct_cell($content);
            $table->construct_row();    
            $table->output($lang->sprintf($lang->uploadsystem_user_container, $name));
        }

        $buttons = array($form->generate_submit_button($lang->uploadsystem_themes_button));
        $form->output_submit_wrapper($buttons);
        $form->end();
        $page->output_footer();
    }

}

// RPG STUFF //

// Stylesheet zum Master Style hinzufügen
function uploadsystem_admin_update_stylesheet(&$table) {

    global $db, $mybb, $lang;
	
    $lang->load('rpgstuff_stylesheet_updates');

    require_once MYBB_ADMIN_DIR."inc/functions_themes.php";

    // HINZUFÜGEN
    if ($mybb->input['action'] == 'add_master' AND $mybb->get_input('plugin') == "uploadsystem") {

        $css = uploadsystem_stylesheet();
        
        $sid = $db->insert_query("themestylesheets", $css);
        $db->update_query("themestylesheets", array("cachefile" => "uploadsystem.css"), "sid = '".$sid."'", 1);
    
        $tids = $db->simple_select("themes", "tid");
        while($theme = $db->fetch_array($tids)) {
            update_theme_stylesheet_list($theme['tid']);
        } 

        flash_message($lang->stylesheets_flash, "success");
        admin_redirect("index.php?module=rpgstuff-stylesheet_updates");
    }

    // Zelle mit dem Namen des Themes
    $table->construct_cell("<b>".htmlspecialchars_uni(uploadsystem_info()['name'])."</b>", array('width' => '70%'));

    // Ob im Master Style vorhanden
    $master_check = $db->fetch_field($db->query("SELECT tid FROM ".TABLE_PREFIX."themestylesheets 
    WHERE name = 'uploadsystem.css' 
    AND tid = 1
    "), "tid");
    
    if (!empty($master_check)) {
        $masterstyle = true;
    } else {
        $masterstyle = false;
    }

    if (!empty($masterstyle)) {
        $table->construct_cell($lang->stylesheets_masterstyle, array('class' => 'align_center'));
    } else {
        $table->construct_cell("<a href=\"index.php?module=rpgstuff-stylesheet_updates&action=add_master&plugin=uploadsystem\">".$lang->stylesheets_add."</a>", array('class' => 'align_center'));
    }
    
    $table->construct_row();
}

// Plugin Update
function uploadsystem_admin_update_plugin(&$table) {

    global $db, $mybb, $lang, $theme;
	
    $lang->load('rpgstuff_plugin_updates');

    // UPDATE
    if ($mybb->input['action'] == 'add_update' AND $mybb->get_input('plugin') == "uploadsystem") {

        // Templates
        // Templates umbenennen
        $alltpls_query = $db->query("SELECT title FROM ".TABLE_PREFIX."templates
        WHERE title LIKE 'uploadsystem_%'
        AND title NOT IN ('uploadsystem_usercp', 'uploadsystem_usercp_nav')
        ");
        while($tpl = $db->fetch_array($alltpls_query)) {
            $newtpl = str_replace('_usercp', '', $tpl['title']);
            if($newtpl != $tpl['title']) {
                $db->update_query("templates", ["title" => $db->escape_string($newtpl)], "title='".$db->escape_string($tpl['title'])."'");
            }
        }
        
        // generell Update
        uploadsystem_templates('update');

        // Stylesheet
        $update_data = uploadsystem_stylesheet_update();
        $update_stylesheet = $update_data['stylesheet'];
        $update_string = $update_data['update_string'];
        if (!empty($update_string)) {

            // Ob im Master Style die Überprüfung vorhanden ist
            $masterstylesheet = $db->fetch_field($db->query("SELECT stylesheet FROM ".TABLE_PREFIX."themestylesheets WHERE tid = 1 AND name = 'uploadsystem.css'"), "stylesheet");
            $masterstylesheet = (string)($masterstylesheet ?? '');
            $update_string = (string)($update_string ?? '');
            $pos = strpos($masterstylesheet, $update_string);
            if ($pos === false) { // nicht vorhanden 
            
                $theme_query = $db->simple_select('themes', 'tid, name');
                while ($theme = $db->fetch_array($theme_query)) {
        
                    $stylesheet_query = $db->simple_select("themestylesheets", "*", "name='".$db->escape_string('uploadsystem.css')."' AND tid = ".$theme['tid']);
                    $stylesheet = $db->fetch_array($stylesheet_query);
        
                    if ($stylesheet) {

                        require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
        
                        $sid = $stylesheet['sid'];
            
                        $updated_stylesheet = array(
                            "cachefile" => $db->escape_string($stylesheet['name']),
                            "stylesheet" => $db->escape_string($stylesheet['stylesheet']."\n\n".$update_stylesheet),
                            "lastmodified" => TIME_NOW
                        );
            
                        $db->update_query("themestylesheets", $updated_stylesheet, "sid='".$sid."'");
            
                        if(!cache_stylesheet($theme['tid'], $stylesheet['name'], $updated_stylesheet['stylesheet'])) {
                            $db->update_query("themestylesheets", array('cachefile' => "css.php?stylesheet=".$sid), "sid='".$sid."'", 1);
                        }
            
                        update_theme_stylesheet_list($theme['tid']);
                    }
                }
            } 
        }

        // Einstellungen
        uploadsystem_settings('update');
        rebuild_settings();

        // Datenbanktabellen & Felder
        uploadsystem_database();

        $pluginlibrary_check = uploadsystem_is_updated_core();
        if (empty($pluginlibrary_check)) {
            uploadsystem_pluginlibrary();
        }

        // Variabeln
        if (uploadsystem_info()['version'] <= '1.2') {
            require MYBB_ROOT."/inc/adminfunctions_templates.php";
            find_replace_templatesets("usercp_editsig", "#".preg_quote('{$uploadsystem_signatur}"')."#i", '', 0);
            find_replace_templatesets('usercp_editsig', '#'.preg_quote('{$error}').'#', '{$error}{$uploadsystem_sig_errors}');
            find_replace_templatesets('usercp_editsig', '#'.preg_quote('{$codebuttons}').'#', '{$codebuttons}{$uploadsystem_signatur}');
        }

        flash_message($lang->plugins_flash, "success");
        admin_redirect("index.php?module=rpgstuff-plugin_updates");
    }

    // Zelle mit dem Namen des Themes
    $table->construct_cell("<b>".htmlspecialchars_uni(uploadsystem_info()['name'])."</b>", array('width' => '70%'));

    // Überprüfen, ob Update erledigt
    $update_check = uploadsystem_is_updated();

    if (!empty($update_check)) {
        $table->construct_cell($lang->plugins_actual, array('class' => 'align_center'));
    } else {
        $table->construct_cell("<a href=\"index.php?module=rpgstuff-plugin_updates&action=add_update&plugin=uploadsystem\">".$lang->plugins_update."</a>", array('class' => 'align_center'));
    }
    
    $table->construct_row();
}

// Core Änderungen
function uploadsystem_admin_update_core(&$table) {

    global $db, $mybb, $lang, $theme;
	
    $lang->load('rpgstuff_core_updates');

    // UPDATE
    if ($mybb->input['action'] == 'add_core' AND $mybb->get_input('plugin') == "uploadsystem") {

        uploadsystem_pluginlibrary();

        flash_message($lang->core_flash, "success");
        admin_redirect("index.php?module=rpgstuff-core_updates");
    }

    // Zelle mit dem Namen des Themes
    $table->construct_cell("<b>".htmlspecialchars_uni(uploadsystem_info()['name'])."</b>", array('width' => '70%'));

    // Überprüfen, ob Update erledigt
    $update_check = uploadsystem_is_updated_core();

    if (!empty($update_check)) {
        $table->construct_cell($lang->core_actual, array('class' => 'align_center'));
    } else {
        $table->construct_cell("<a href=\"index.php?module=rpgstuff-core_updates&action=add_core&plugin=uploadsystem\">".$lang->core_update."</a>", array('class' => 'align_center'));
    }
    
    $table->construct_row();
}

// USERCP BEREICH //

// Menü
function uploadsystem_usercp_menu() {

	global $mybb, $templates, $lang, $usercpmenu;
    
    if ($mybb->settings['uploadsystem_charactercp'] != 0) return;

	$lang->load("uploadsystem");

	eval("\$usercpmenu .= \"".$templates->get("uploadsystem_usercp_nav")."\";");
}

// UCP-Seite
function uploadsystem_usercp_page() {

    global $db, $mybb, $lang, $templates, $theme, $header, $headerinclude, $footer, $page, $usercpnav, $uploadsystem_error;
    
    if ($mybb->settings['uploadsystem_charactercp'] != 0) return;
    
    // return if the action key isn't part of the input
    $usercp_list  = ['uploadsystem', 'do_uploadsystem'];
    if (!in_array($mybb->get_input('action', MyBB::INPUT_STRING), $usercp_list)) return;

    $lang->load("usercp");
    $lang->load("uploadsystem");

    add_breadcrumb($lang->nav_usercp, "usercp.php");

    uploadsystem_cp_page('usercp');
}

// CHARACTER CONTROL PANEL - Plugin sparksfly //

// Menü
function uploadsystem_charactercp_menu() {

    global $mybb, $templates, $lang, $characternavbit;

    if ($mybb->settings['uploadsystem_charactercp'] != 1) return;

    $lang->load("uploadsystem");

    $cpage['link'] = "character.php?action=uploadsystem";
    $linkname = $lang->uploadsystem_nav;

    eval('$characternavbit .= "'.$templates->get("character_nav_custom").'";');
}

// CharacterCP-Seite
function uploadsystem_charactercp_page() {

    global $db, $mybb, $lang, $templates, $theme, $header, $headerinclude, $footer, $page, $character_nav, $uploadsystem_error;

    if ($mybb->settings['uploadsystem_charactercp'] != 1) return;

    if ($mybb->user['uid'] == 0) {
        error_no_permission();
        return;
    }
    
    // return if the action key isn't part of the input
    $usercp_list  = ['uploadsystem', 'do_uploadsystem'];
    if (!in_array($mybb->get_input('action', MyBB::INPUT_STRING), $usercp_list)) return;

    $lang->load("character");
    $lang->load("uploadsystem");

    uploadsystem_cp_page('character');
}

// SIGNATUR - UserCP //

// Hochladen && Entfernen
function uploadsystem_usercp_do_editsig() {

    global $mybb, $lang, $uploadsystem_sig_errors;

    $signatur_setting = $mybb->settings['uploadsystem_signatur'];
    if ($signatur_setting != 1) return;

    $lang->load("uploadsystem");

    // Hochladen
    if(isset($mybb->input['new_signatur'])) {

        // Verify incoming POST request
        verify_post_check($mybb->get_input('my_post_key'));

        $uploadsystem_sig_errors = uploadsystem_validate_upload_signatur('cp');

        // No errors - insert
        if (empty($uploadsystem_sig_errors)) {
            uploadsystem_element_upload($mybb->user['uid'], 'signatur', 'signaturlink');
            redirect("usercp.php?action=editsig", $lang->uploadsystem_redirect);
        } else {
            $mybb->input['action'] = "editsig";
            $uploadsystem_sig_errors = inline_error($uploadsystem_sig_errors);
        }
    }

    // Entfernen
    if(isset($mybb->input['remove_signatur'])) {

        // Verify incoming POST request
        verify_post_check($mybb->get_input('my_post_key'));
                
        uploadsystem_element_remove($mybb->user['uid'], 'signatur');
        redirect("usercp.php?action=editsig", $lang->uploadsystem_redirect);
    }
}

// Anzeige
function uploadsystem_usercp_editsig() {

    global $mybb, $db, $templates, $lang, $uploadsystem_signatur, $uploadsystem_sig_errors;
   
    $lang->load("uploadsystem");

    $signatur_setting = $mybb->settings['uploadsystem_signatur'];

    $uploadsystem_signatur = "";
    if ($signatur_setting != 1) return;

    if(!isset($uploadsystem_sig_errors)) {
        $uploadsystem_sig_errors = '';
    }

    $sigfile = $db->fetch_field($db->simple_select("uploadfiles", "signatur", "ufid = ".$mybb->user['uid']), "signatur");
    $filename = strtok($sigfile, '?');

    if(!empty($filename)) {
        $file_url = $mybb->settings['bburl']."/uploads/uploadsystem/signatur/".$filename;
        eval("\$remove = \"".$templates->get("uploadsystem_signatur_remove")."\";");	
    } else {
        $file_url = $lang->uploadsystem_signatur_nofile;
        $remove = "";
    }

    if ($mybb->settings['uploadsystem_retina'] == 1 && !empty($mybb->settings['uploadsystem_signatur_max'])) {
        $identification = "signatur";
        eval("\$retina_checkbox = \"".$templates->get("uploadsystem_retina")."\";");
    } else {
        $retina_checkbox = "";
    }

    eval("\$uploadsystem_signatur = \"".$templates->get("uploadsystem_signatur")."\";");	
}

// ONLINE LOCATION //
function uploadsystem_online_activity($user_activity) {

	global $parameters, $user;

	$split_loc = explode(".php", $user_activity['location']);
	if(isset($user['location']) && $split_loc[0] == $user['location']) { 
		$filename = '';
	} else {
		$filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
	}

	switch ($filename) {
		case 'usercp':
            if($parameters['action'] == "uploadsystem") {
                $user_activity['activity'] = "uploadsystem_ucp";
            }
        break;
        case 'character':
            if($parameters['action'] == "uploadsystem") {
                $user_activity['activity'] = "uploadsystem_character";
            }
        break;
	}

	return $user_activity;
}
function uploadsystem_online_location($plugin_array) {

	global $lang;
    
    $lang->load("uploadsystem");

    if ($plugin_array['user_activity']['activity'] == 'uploadsystem_ucp') {
        $plugin_array['location_name'] = $lang->sprintf($lang->uploadsystem_online_location, 'usercp');
    }

    if ($plugin_array['user_activity']['activity'] == 'uploadsystem_character') {
        $plugin_array['location_name'] = $lang->sprintf($lang->uploadsystem_online_location, 'character');
    }

	return $plugin_array;
}

// VARIABELN //

// Profile $memprofile
function uploadsystem_memberprofile() {

    global $uploads, $memprofile;
    
    $uid = $memprofile['uid'];

    $uploads = uploadsystem_build_view($uid);    
    $memprofile = array_merge($memprofile, $uploads);
}

// Postbit
function uploadsystem_postbit(&$post) {

    global $uploads; 
    
    $uid = $post['uid']; 

    $uploads = uploadsystem_build_view($uid); 
    $post = array_merge($post, $uploads);
}

// Mitgliederliste
function uploadsystem_memberlist(&$user) {

    global $uploads;

    $uid = $user['uid'];

    $uploads = uploadsystem_build_view($uid);    
    $user = array_merge($user, $uploads);
}

// Global
function uploadsystem_global() {

    global $mybb;

    $uid = $mybb->user['uid'];

    $uploads = uploadsystem_build_view($uid);

    foreach($uploads as $key => $value) {
        $mybb->user[$key] = $value;
    }
}

#########################
### PRIVATE FUNCTIONS ###
#########################

// Validierung ACP Formular
function uploadsystem_validate_element($usid = ''){

    global $mybb, $lang, $db;

    $lang->load('uploadsystem');

    $errors = [];

    // Identifikation
    $identification = $mybb->get_input('identification');
    if (empty($identification)) {
        $errors[] = $lang->uploadsystem_form_error_identification;
    } else {
        if (!empty($usid)) {
            $identificationCheck = $db->fetch_field($db->simple_select("uploadsystem", "identification", "identification = '".$db->escape_string($identification)."' AND usid != ".$usid), "identification");
        } else {
            $identificationCheck = $db->fetch_field($db->simple_select("uploadsystem", "identification", "identification = '".$db->escape_string($identification)."'"), "identification");
        }

        if (!empty($identificationCheck)) {
            $errors[] = $lang->uploadsystem_form_error_identification_double;
        }
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $identification)) {
            $errors[] = $lang->uploadsystem_form_error_identification_machine;
        }
    }

    // Titel
    $name = $mybb->get_input('name');
    if (empty($name)) {
        $errors[] = $lang->uploadsystem_form_error_name;
    }

    // Erlaubte Dateitypen
    $extensions = $mybb->get_input('allowedExtensions', MyBB::INPUT_ARRAY);
    if (empty($extensions)) {
        $errors[] = $lang->uploadsystem_form_error_extensions;
    }

    // Minimale Grafikgröße
    $mindims = $mybb->get_input('mindims');
    if (empty($mindims)) {
        $errors[] = $lang->uploadsystem_form_error_mindims;
    } else {
        if(!preg_match("/\b\d+[|x]{1}\d+\b/i", $mindims)) {
            $errors[] = $lang->sprintf($lang->uploadsystem_form_error_dimension, $lang->uploadsystem_form_error_dimension_mindims);
        }
    }

    // Maximale Grafikgröße
    $maxdims = $mybb->get_input('maxdims');
    if (!empty($maxdims)) {
        if(!preg_match("/\b\d+[|x]{1}\d+\b/i", $maxdims)) {
            $errors[] = $lang->sprintf($lang->uploadsystem_form_error_dimension, $lang->uploadsystem_form_error_dimension_maxdims);
        }
    }

    // Quadratische Angaben
    $square = $mybb->get_input('square', MyBB::INPUT_INT);
    if ($square == 1) {
        if (!empty($mindims)) {
            list($minwidth, $minheight) = preg_split('/[|x]/', strtolower($mindims));
            if ($minwidth != $minheight) {
                $errors[] = $lang->uploadsystem_form_error_square_min;
            }
        }

        if (!empty($maxdims)) {
            list($maxwidth, $maxheight) = preg_split('/[|x]/', strtolower($maxdims));
            if ($maxwidth != $maxheight) {
                $errors[] = $lang->uploadsystem_form_error_square_max;
            }
        }
    }

    // Maximale Dateigröße
    $bytesize = $mybb->get_input('bytesize');
    if (!is_numeric($bytesize)) {
        $errors[] = $lang->uploadsystem_form_error_bytesize;
    }

    return $errors;
}

// Identification Update
function uploadsystem_identification_update($identificationOld = '', $identificationNew = '') {

    global $db;
                    
    // Alle Dateien ändern
    $allfiles_query = $db->query("SELECT ufid, ".$identificationOld." FROM ".TABLE_PREFIX."uploadfiles uf
    WHERE uf.".$identificationOld." != ''
    ");
    
    $allFiles = [];
    while($file = $db->fetch_array($allfiles_query)) {
        $ufid = $file['ufid'];
        $filename = strtok($file[$identificationOld], '?');
        $allFiles[$ufid] = $filename;
    }
               
    foreach ($allFiles as $ufid => $filename) {

        // Dateinamen anpassen
        $name = str_replace($identificationOld , $identificationNew, $filename);
        $newName = $name.'?dateline='.time(); // neuer Timestamp

        // Update DB
        $update_name = array(
            $identificationOld => $db->escape_string($newName)
        );
        $db->update_query("uploadfiles", $update_name, "ufid = ".$ufid);

        // Datei unbenennen
        rename(MYBB_ROOT."uploads/uploadsystem/".$identificationOld."/".$filename, MYBB_ROOT."uploads/uploadsystem/".$identificationOld."/".$name);
    }
    
    // Ordner Name ändern
    rename(MYBB_ROOT."uploads/uploadsystem/".$identificationOld, MYBB_ROOT."uploads/uploadsystem/".$identificationNew);
                    
    // Spalte in der DB ändern
    $db->write_query("ALTER TABLE ".TABLE_PREFIX."uploadfiles CHANGE `".$identificationOld."` `".$identificationNew."` TEXT NOT NULL");
}

// Validierung Upload
function uploadsystem_validate_upload(string $input_name, array $element){

    global $db, $mybb, $lang;

    $lang->load('uploadsystem');

    $errors = [];
    $langElement = $lang->sprintf($lang->uploadsystem_error_element, $element['name']);

    if(empty($_FILES[$input_name]['name'])) {
        $errors[] = $lang->uploadsystem_error_upload;
        return $errors;
    }
        
    // Endung
    $extension = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));
    $allowed_extensions = array_map('trim', explode(',', str_replace(",", ", ", $element['allowextensions'])));
    if(!in_array($extension, $allowed_extensions)) {
        $errors[] = $lang->sprintf($lang->uploadsystem_error_extensions, $langElement);
    }

    // Dateigröße
    if($element['bytesize'] > 0) {
        $max_size = (int)$element['bytesize'] * 1024;
        if($_FILES[$input_name]['size'] > $max_size) {
            $errors[] = $lang->sprintf($lang->uploadsystem_error_bytesize, $langElement, get_friendly_size($max_size));
        }
    }

    // Bilddaten
    $imgDimensions = @getimagesize($_FILES[$input_name]['tmp_name']);
    if(!is_array($imgDimensions)) {
        $errors[] = $lang->sprintf($lang->uploadsystem_error_imgDimensions, $langElement);
    } else {
        $width = (int)$imgDimensions[0];
        $height = (int)$imgDimensions[1];
        
        list($minwidth, $minheight) = preg_split('/[|x]/', strtolower($element['mindims']));
        
        $minwidth = (int)$minwidth;
        $minheight = (int)$minheight;
    
        if ($mybb->get_input('retinadisplay_'.$element['identification'], MyBB::INPUT_INT)) {
            $retina_factor = 2;
        } else {
            $retina_factor = 1;
        }
        
        // Muss quadratisch sein?
        if ((int)$element['square'] === 1 && $width != $height) {
            $errors[] = $lang->sprintf($lang->uploadsystem_error_square, $langElement);
        }

        // Maximale Größe vorhanden?
        if (!empty($element['maxdims'])) {
            list($maxwidth, $maxheight) = preg_split('/[|x]/', strtolower($element['maxdims']));
            
            $maxwidth = (int)$maxwidth;
            $maxheight = (int)$maxheight;
                    
            $graphic_size = "";
            if ($mybb->settings['uploadsystem_retina'] == 1) {
                $graphic_size = $lang->sprintf($lang->uploadsystem_retina_dims, $maxwidth*2, $maxheight*2);
            }

            // Feste Größe
            if ($minwidth == $maxwidth && $minheight == $maxheight) {
                if ($width != ($minwidth * $retina_factor) || $height != ($minheight * $retina_factor)) {
                    $errors[] = $lang->sprintf($lang->uploadsystem_error_dims_fixed, $langElement, $element['maxdims'], $graphic_size);
                }
            }
            // Größenbereich
            else {
                if ($width < ($minwidth * $retina_factor) || $height < ($minheight * $retina_factor) || $width > ($maxwidth * $retina_factor) || $height > ($maxheight * $retina_factor)) {
                    $errors[] = $lang->sprintf($lang->uploadsystem_error_dims_max, $langElement, $element['maxdims'], $graphic_size);
                }
            }
        }
        // Nur Mindestgröß
        else {
            if ($width < $minwidth || $height < $minheight) {
                $errors[] = $lang->sprintf($lang->uploadsystem_error_dims_min, $langElement);
                $errors[] = $lang->sprintf($lang->uploadsystem_error_dims_min, $langElement, $element['mindims']);
            }
        }
    }

    return $errors;
}

// Validierung Upload Signatur
function uploadsystem_validate_upload_signatur($mode = '') {

    global $mybb, $lang;

    $lang->load('uploadsystem');

    $allowextensions = $mybb->settings['uploadsystem_signatur_extensions'];
    $maxdims = $mybb->settings['uploadsystem_signatur_max'];
    $bytesize = $mybb->settings['uploadsystem_signatur_size'];

    if ($mode == 'acp') {
        $langElement = $lang->sprintf($lang->uploadsystem_error_extensions, $lang->uploadsystem_error_element_signatur);
    } else {
        $langElement = '';
    }

    $errors = [];

    if (empty($_FILES['signaturlink']['name'])) {
        $errors[] = $lang->uploadsystem_error_upload;
        return $errors;
    }

    // Endung
    $extension = strtolower(pathinfo($_FILES['signaturlink']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = array_map('trim', explode(',', str_replace(",", ", ", $allowextensions)));
    if(!in_array($extension, $allowed_extensions)) {
        $errors[] = $lang->sprintf($lang->uploadsystem_error_extensions, $langElement);
    }

    // Dateigröße
    if ($bytesize > 0) {
        $max_size = (int)$bytesize * 1024;
        if ($_FILES['signaturlink']['size'] > $max_size) {
            $errors[] = $lang->sprintf($lang->uploadsystem_error_bytesize, $langElement, get_friendly_size($max_size));
        }
    }

    // Bilddaten
    $imgDimensions = @getimagesize($_FILES['signaturlink']['tmp_name']);
    if (!is_array($imgDimensions)) {
        $errors[] = $lang->sprintf($lang->uploadsystem_error_bytesize, $langElement);
    } else {

        $width = (int)$imgDimensions[0];
        $height = (int)$imgDimensions[1];

        if(!empty($maxdims)) {
            list($maxwidth, $maxheight) = preg_split('/[|x]/', strtolower($maxdims));

            $maxwidth = (int)$maxwidth;
            $maxheight = (int)$maxheight;

            if ($mybb->get_input('retinadisplay_signatur')) {
                $retina_factor = 2;
            } else {
                $retina_factor = 1;
            }

            $allowed_width = $maxwidth * $retina_factor;
            $allowed_height = $maxheight * $retina_factor;

            if ($width > $allowed_width || $height > $allowed_height) {
                $graphic_size = "";

                if ($mybb->settings['uploadsystem_retina'] == 1) {
                    $graphic_size = $lang->sprintf($lang->uploadsystem_retina_dims, $allowed_width*2, $allowed_height*2);
                }

                $errors[] = $lang->sprintf($lang->uploadsystem_error_dims_max, $langElement, $maxdims, $graphic_size);
            }
        }
    }

    return $errors;
}

// Datei hochladen & Daten speichern
function uploadsystem_element_upload(int $ufid, string $identification, string $input_name) {

    global $db, $mybb;

    require_once MYBB_ROOT."inc/functions_upload.php";
    require_once MYBB_ROOT."inc/functions.php";

    $folder_path = MYBB_ROOT."uploads/uploadsystem/".$identification."/";

    // alte Datei(en) entfernen
    foreach(glob($folder_path.$identification.'_'.$ufid.'.*') as $file) {
        delete_uploaded_file($file);
    }

    $extension = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));

    $filename = $identification.'_'.$ufid.'.'.$extension;

    move_uploaded_file($_FILES[$input_name]['tmp_name'], $folder_path . $filename);

    if ($mybb->settings['uploadsystem_webp'] == 1) {
        $filename = uploadsystem_convert_to_webp($folder_path, $filename, $identification, $ufid);
    }

    $new_upload = array(
        $identification => $db->escape_string($filename).'?dateline='.time()
    );

    $db->update_query("uploadfiles", $new_upload, "ufid=".$ufid);
}

// Datei entfernen & Daten löschen
function uploadsystem_element_remove(int $ufid, string $identification){

    global $db;

    require_once MYBB_ROOT."inc/functions_upload.php";
    require_once MYBB_ROOT."inc/functions.php";
                
    $folder_path =  MYBB_ROOT."uploads/uploadsystem/".$identification."/"; 

    $filename = $db->fetch_field($db->simple_select("uploadfiles", $identification, "ufid = ".$ufid), $identification);
    $filename = strtok($filename, '?');
                    
    delete_uploaded_file($folder_path . $filename);

    $del_upload = array(
        $identification => ""
    );
    $db->update_query("uploadfiles", $del_upload, "ufid = ".$ufid);
}

// Placeholder hochladen
function uploadsystem_element_upload_placeholder(string $path, string $identification, string $input_name) {

    global $db, $mybb;

    require_once MYBB_ROOT."inc/functions_upload.php";
    require_once MYBB_ROOT."inc/functions.php";

    $folder_path = rtrim(MYBB_ROOT.$path, '/\\').DIRECTORY_SEPARATOR;
    $filename = 'default_'.$identification.'.*';

    // alte Datei(en) entfernen
    foreach(glob($folder_path.$filename) as $file) {
        delete_uploaded_file($file);
    }

    $extension = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));

    $filename = 'default_'.$identification.'.'.$extension;

    move_uploaded_file($_FILES[$input_name]['tmp_name'], $folder_path . $filename);

    if ($mybb->settings['uploadsystem_webp'] == 1) {
        $filename = uploadsystem_convert_to_webp($folder_path, $filename, $identification, 0);
    }
}

// Placeholder entfernen
function uploadsystem_element_remove_placeholder(string $path, string $identification){

    global $db;

    require_once MYBB_ROOT."inc/functions_upload.php";
    require_once MYBB_ROOT."inc/functions.php";
                
    $folder_path = rtrim(MYBB_ROOT.$path, '/\\').DIRECTORY_SEPARATOR;
    $filename = 'default_'.$identification.'.*';

    foreach(glob($folder_path . $filename) as $file) {
        delete_uploaded_file($file);
    }
}

// WebP Konverter
function uploadsystem_convert_to_webp(string $folder_path, string $filename, string $identification, int $ufid) {

    require_once MYBB_ROOT."inc/functions_upload.php";
    require_once MYBB_ROOT."inc/functions.php";

    if (!function_exists('imagewebp')) {
        return $filename;
    }

    $original_file = $folder_path.$filename;

    $info = @getimagesize($original_file);

    if (!$info) {
        return $filename;
    }

    switch($info['mime']) {
        case 'image/jpeg':
            $img = imagecreatefromjpeg($original_file);
            break;
        case 'image/png':
            $img = imagecreatefrompng($original_file);
            if (!imageistruecolor($img)) {
                imagepalettetotruecolor($img);
            }
            imagesavealpha($img, true);
            break;
        case 'image/gif':
            return $filename;
        default:
            return $filename;
    }

    // Placeholder
    if ($ufid == 0) {
        $webp_filename = 'default_'.$identification.'.webp';
    } 
    // User Datei
    else {
        $webp_filename = $identification.'_'.$ufid.'.webp';
    }
    $webp_file = $folder_path.$webp_filename;

    imagewebp($img, $webp_file, 85);

    if(PHP_VERSION_ID < 80000) {
        imagedestroy($img);
    }

    @unlink($original_file);

    return $webp_filename;
}

// Variable Inhalte
function uploadsystem_build_view(int $uid) {

    global $db, $mybb, $theme;

    // Dateiname + Pfad
    // {$xx['identification']}
    // Nur Dateiname (relevant für if Abfragen)
    // {$xx['files_identification']}

    $files = array();
    
    // alle Indientifikatoren
    $allidentification_query = $db->query("SELECT identification, allowextensions FROM ".TABLE_PREFIX."uploadsystem");
    
    $all_identification = array();
    while($allidentification = $db->fetch_array($allidentification_query)) {
        $all_identification[$allidentification['identification']] = $allidentification['allowextensions'];
    }
    
    foreach ($all_identification as $identification => $allowextensions) {

        // Userdatei Name
        $fieldvalue = $db->fetch_field($db->simple_select("uploadfiles", $identification, "ufid = ".$uid), $identification);

        // komplette Variable {$xxx['identification']}
        // uploads/uploadsystem/identification/identification_UID.format?dateline=xxxxx
        $arraylabel = $identification;

        // Gäste immer ausblenden && Default-Grafik
        if ($mybb->user['uid'] == 0 || $fieldvalue == '') {

            $extensions_array = array_map('trim', explode(',', strtolower($allowextensions)));
            if(!in_array('webp', $extensions_array)) {
                $extensions_array[] = 'webp';
            }

            $filename = $theme['imgdir']."/default_".$identification.".";
            $filename = str_replace($mybb->settings['bburl']."/", "", $filename);
        
            $found_format = '';
            foreach ($extensions_array as $format) {
                $file_path = MYBB_ROOT.$filename.$format;
                if (file_exists($file_path)) {
                    $found_format = $format;
                }
            }

            if (!empty($found_format)) {
                $files[$arraylabel] = $mybb->settings['bburl']."/".$filename.$found_format."?v=".filemtime(MYBB_ROOT.$filename.$found_format);
            } else {  
                $files[$arraylabel] = $mybb->settings['bburl']."/images/uploadsystem_default.png";;
            }

        } else {
            $path = $mybb->settings['bburl']."/uploads/uploadsystem/".$identification."/";
            $files[$arraylabel] = $path.$fieldvalue;
        }
        

        // nur Dateiname {$xx['files_identification']} 
        // identification_UID.format?dateline=xxxxx
        $arraylabel = "files_".$identification;

        $files[$arraylabel] = $fieldvalue;
    }

    return $files;
}

// CP Seiten
function uploadsystem_cp_page(string $mode) {

    global $mybb, $db, $lang, $templates, $theme, $header, $headerinclude, $footer, $page, $usercpnav, $character_nav, $uploadsystem_error;

    $lang->load('uploadsystem');

    // Speichern
    if($mybb->input['action'] == "do_uploadsystem" && $mybb->request_method == "post") {

        // Verify incoming POST request
        verify_post_check($mybb->get_input('my_post_key'));
        
        $allElements = $db->query("SELECT * FROM ".TABLE_PREFIX."uploadsystem
        ORDER BY disporder ASC, name ASC
        ");
        
        $uploadsystem_errors = [];
        while($element = $db->fetch_array($allElements)) {

            $identification = $element['identification'];
            $input_name = "pic_".$element['identification']; 
            
            // hochladen
            if(!empty($_FILES[$input_name]['name'])) {

                $element_errors = uploadsystem_validate_upload($input_name, $element);

                // No errors - insert
                if (empty($element_errors)) {
                    uploadsystem_element_upload($mybb->user['uid'], $identification, $input_name);
                } else {
                    $uploadsystem_errors = array_merge($uploadsystem_errors, $element_errors);
                }
            }
            
            // entfernen
            if($mybb->get_input('remove_'.$identification)) {
                uploadsystem_element_remove($mybb->user['uid'], $identification);
            }
        }

		if (empty($uploadsystem_errors)) {
            if ($mode == 'usercp') {
                redirect("usercp.php?action=uploadsystem", $lang->uploadsystem_redirect);
            } else {
                redirect("character.php?action=uploadsystem", $lang->uploadsystem_redirect);
            }
        } else {
            $mybb->input['action'] = "uploadsystem";
            $uploadsystem_error = inline_error($uploadsystem_errors);
        }
    }

    // Hauptseite
    if ($mybb->get_input('action') == 'uploadsystem') {
            
        add_breadcrumb($lang->uploadsystem_nav);

        if(!isset($uploadsystem_error)) {
            $uploadsystem_error = '';
        }

        $query_elements = $db->query("SELECT * FROM ".TABLE_PREFIX."uploadsystem
        ORDER BY disporder ASC, name ASC
        ");

        $upload_element = "";
        while ($element = $db->fetch_array($query_elements)) {

            // Leer laufen lassen
            $usid = "";
            $identification = "";
            $name = "";
            $description = "";
            $path = "";
            $allowextensions = "";
            $mindims = "";
            $maxdims = "";
            $square = "";            
            $bytesize = "";
            $headline = "";
            $file_url = "";
            $file_name = "";
            $minwidth = "";
            $minheight = "";
            $graphic_size = "";
            $upload = "";
            $remove_checkbox = "";
            $retina_checkbox = "";

            // Mit Infos füllen
            $usid = $element['usid'];
            $identification = $element['identification'];
            $name = $element['name'];
            $description = $element['description'];
            $path = $element['path'];
            $allowextensions = $element['allowextensions'];
            $mindims = $element['mindims'];
            $maxdims = $element['maxdims'];
            $square = $element['square'];    
            $bytesize = $element['bytesize'];

            $headline = $lang->sprintf($lang->uploadsystem_container, $name);
            list($minwidth, $minheight) = preg_split('/[|x]/', my_strtolower($mindims));

            $file_name = $db->fetch_field($db->simple_select("uploadfiles", $identification, "ufid = ".$mybb->user['uid']), $identification);
            
            if(!empty($file_name)) {
                $file_url = $mybb->settings['bburl']."/".$path.$file_name;
                $graphic_size = "";
                $element_notice = $lang->uploadsystem_notice;

                // Löschen
                $checkbox_remove = $lang->sprintf($lang->uploadsystem_remove_checkbox, $name);
                eval("\$remove_checkbox = \"".$templates->get("uploadsystem_element_remove")."\";");
            } else {
                $file_url = $mybb->settings['bburl']."/images/uploadsystem_default.png";  
                $graphic_size = $minwidth."x".$minheight;
                $element_notice = "";
            }

            // Größe        
            if(!empty($maxdims)) {
                list($maxwidth, $maxheight) = preg_split('/[|x]/', strtolower($maxdims));

                $maxwidth = (int)$maxwidth;            
                $maxheight = (int)$maxheight;

                // exakte Größe
                if ($minwidth == $maxwidth && $minheight == $maxheight) {
                    if ($mybb->settings['uploadsystem_retina'] == 1) {
                        $retina = $lang->sprintf($lang->uploadsystem_retina_dims, $maxwidth*2, $maxheight*2);
                        $dims = $lang->sprintf($lang->uploadsystem_dims_fixed, $maxwidth, $maxheight,$retina);
                    } else {
                        $dims = $lang->sprintf($lang->uploadsystem_dims_fixed, $maxwidth, $maxheight, '');
                    }
                } 
                // von bis
                else {
                    if ($mybb->settings['uploadsystem_retina'] == 1) {
                        $retina = $lang->sprintf($lang->uploadsystem_retina_dims, $maxwidth*2, $maxheight*2);
                        $dims = $lang->sprintf($lang->uploadsystem_dims_maxmin, $mindims, $maxdims, $retina);
                    } else {
                        $dims = $lang->sprintf($lang->uploadsystem_dims_maxmin, $mindims, $maxdims, '');
                    }
                }
            }
            // nur minimal
            else {
                $dims = $lang->sprintf($lang->uploadsystem_dims_min, $mindims);
            }

            // Quadratisch    
            if ($square == 1) {
                $square = $lang->uploadsystem_square;
            } else {
                $square = "";
            }
    
            // Dateigröße
            if ($bytesize != 0) {
                $size = $lang->sprintf($lang->uploadsystem_size, get_friendly_size($bytesize*1024));
            } else {
                $size = $lang->uploadsystem_noSize;
            }
            
            // Dateiformate
            $allowextensions = str_replace(",", ", ", $allowextensions);
            $extensions_array = array_map('trim', explode(',', str_replace(",", ", ", $allowextensions)));
            if (count($extensions_array) > 1) {
                $extensions = $lang->sprintf($lang->uploadsystem_extensions_plural, strtoupper($allowextensions));
            } else {
                $extensions = $lang->sprintf($lang->uploadsystem_extensions_singular, strtoupper($allowextensions));
            }

            // Upload
            $headline_upload = $lang->sprintf($lang->uploadsystem_upload_headline, $name);
            $subline_upload = $lang->uploadsystem_upload_subline;
            if ($mybb->settings['uploadsystem_retina'] == 1 && !empty($maxdims)) {
                eval("\$retina_checkbox = \"".$templates->get("uploadsystem_retina")."\";");
            } else {
                $retina_checkbox = "";
            }
            eval("\$upload = \"".$templates->get("uploadsystem_element_upload")."\";");

            eval("\$upload_element .= \"".$templates->get("uploadsystem_element")."\";");
        }

        if ($mode == 'usercp') {
            eval("\$page = \"".$templates->get("uploadsystem_usercp")."\";");
        } else {
            eval("\$page = \"".$templates->get("uploadsystem_charactercp")."\";");
        }
        output_page($page);
        die();
    }
}

// Verzeichnisse && Ordner löschen
function uploadsystem_delete_directory($path) {

    if (!is_dir($path)) {
        return;
    }

    $entries = scandir($path);

    foreach ($entries as $entry) {

        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $full_path = $path . DIRECTORY_SEPARATOR . $entry;

        if (is_dir($full_path)) {
            uploadsystem_delete_directory($full_path);
        } else {
            unlink($full_path);
        }
    }

    rmdir($path);
}

##################################################################################
### DATABASE | TEMPLATES | SETTINGS | DIRECTORIES | PLUGINLIBRARY | STYLESHEET ###
##################################################################################

// DATENBANKTABELLE
function uploadsystem_database() {

    global $db;

    // UCP - SEITEN
    if (!$db->table_exists("uploadsystem")) {
        $db->query("CREATE TABLE ".TABLE_PREFIX."uploadsystem(
            `usid` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `disporder` int(10) default '0',
            `identification` VARCHAR(500) NOT NULL,
            `name` VARCHAR(500) NOT NULL,
            `description` VARCHAR(500) NOT NULL,
            `path` text NOT NULL,
            `allowextensions` VARCHAR(500) NOT NULL,
            `mindims` VARCHAR(100) NOT NULL,
            `maxdims` VARCHAR(100) NOT NULL default '',
            `square` int(1) unsigned NOT NULL default '0',
            `bytesize` VARCHAR(100) NOT NULL default '5120',
            PRIMARY KEY(`usid`),
            KEY `usid` (`usid`)
            ) ENGINE=InnoDB ".$db->build_create_table_collation().";"
        );
    }

    // einzelne Datein
    if (!$db->table_exists("uploadfiles")) {
        $db->query("CREATE TABLE ".TABLE_PREFIX."uploadfiles(
            `ufid` int(10) unsigned NOT NULL default '0',
            `signatur` TEXT NOT NULL,
            PRIMARY KEY(`ufid`),
            KEY `ufid` (`ufid`)
            ) ENGINE=InnoDB ".$db->build_create_table_collation().";"
        );
    }
}

// TEMPLATES
function uploadsystem_templates($mode = '') {

    global $db, $mybb;

    $info = uploadsystem_info();
    $version = '';

    $templates[] = array(
        'title'		=> 'uploadsystem_charactercp',
        'template'	=> $db->escape_string('<html>
        <head>
		<title>{$lang->character} - {$lang->uploadsystem}</title>
		{$headerinclude}
        </head>
        <body>
		{$header}
		<form action="character.php" method="post" name="input" enctype="multipart/form-data">
			<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
			<table width="100%" border="0" align="center">
				<tr>
					{$character_nav}
					<td valign="top">
						{$uploadsystem_error}
						<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
							<tr>
								<td class="thead"><strong>{$lang->uploadsystem}</strong></td>
							</tr>
							<tr>
								<td class="trow1" valign="top">
									<div class="uploadsystem-desc">{$lang->uploadsystem_desc}</div>
									{$upload_element}
								</td>
							</tr>
						</table>				
						<br />
						<div align="center">
							<input type="hidden" name="action" value="do_uploadsystem" />
							<input type="submit" class="button" name="uploadsystem_do" value="{$lang->uploadsystem_button}" />
						</div>
					</td>
				</tr>
			</table>
		</form>
		{$footer}
        </body>
        </html>'),
        'sid'		=> '-2',
        'version'	=> $version,
        'dateline'	=> TIME_NOW
    );

    $templates[] = array(
        'title'		=> 'uploadsystem_element',
        'template'	=> $db->escape_string('<div class="uploadsystem_element">
        <div class="uploadsystem_element_headline"><strong>{$headline}</strong></div>
        <div class="uploadsystem_element_main">
		<div class="uploadsystem_element_info">{$description}</br></br>{$dims} {$square}<br>{$extensions}<br>{$size}{$element_notice}{$remove_checkbox}</div>
		<div>
			<div class="uploadsystem_element_preview" style="background:url(\'{$file_url}\');background-size: cover;width:{$minwidth}px;height:{$minheight}px;">{$graphic_size}</div>
			<center><div class="trow2" style="padding: 2px 4px; cursor: pointer;" data-copy="{$file_url}" onclick="navigator.clipboard.writeText(this.dataset.copy)">{$lang->uploadsystem_url}</div></center>
		</div>
        </div>
        {$upload}
        </div>'),
        'sid'		=> '-2',
        'version'	=> $version,
        'dateline'	=> TIME_NOW
    );

    $templates[] = array(
        'title'		=> 'uploadsystem_element_remove',
        'template'	=> $db->escape_string('<br><label><input type="checkbox" name="remove_{$identification}" value="1" class="checkbox_input"> {$checkbox_remove}</label>'),
        'sid'		=> '-2',
        'version'	=> $version,
        'dateline'	=> TIME_NOW
    );

    $templates[] = array(
        'title'		=> 'uploadsystem_element_upload',
        'template'	=> $db->escape_string('<div class="uploadsystem_upload">
        <div class="uploadsystem_upload_info">
		<b>{$headline_upload}</b><br>
		{$subline_upload}
        </div>
        <div class="uploadsystem_upload_input">
		<input type="file" name="pic_{$identification}">
		{$retina_checkbox}
        </div>
        </div>'),
        'sid'		=> '-2',
        'version'	=> $version,
        'dateline'	=> TIME_NOW
    );

    $templates[] = array(
        'title'		=> 'uploadsystem_retina',
        'template'	=> $db->escape_string('<br><label><input type="checkbox" name="retinadisplay_{$identification}" value="1" class="checkbox_input"> {$lang->uploadsystem_retina_checkbox}</label>'),
        'sid'		=> '-2',
        'version'	=> $version,
        'dateline'	=> TIME_NOW
    );

    $templates[] = array(
        'title'		=> 'uploadsystem_signatur',
        'template'	=> $db->escape_string('<div class="uploadsystem_signatur">
		<div class="uploadsystem_signatur_info">
			<b>{$lang->uploadsystem_signatur_headline}</b><br>
			<span class="smalltext">{$file_url}</span>
		</div>
		<div class="uploadsystem_signatur_input">
			<input type="file" name="signaturlink">
			{$retina_checkbox}
		</div>
		<div class="uploadsystem_signatur_button"> 
			<input type="hidden" name="action" value="do_uploadsig" />                    
			<input type="submit" class="button" name="new_signatur" value="{$lang->uploadsystem_signatur_button_upload}" />
			{$remove}
		</div>
        </div>'),
        'sid'		=> '-2',
        'version'	=> $version,
        'dateline'	=> TIME_NOW
    );

    $templates[] = array(
        'title'		=> 'uploadsystem_signatur_remove',
        'template'	=> $db->escape_string('<input type="submit" value="{$lang->uploadsystem_signatur_button_remove}" name="remove_signatur" class="button">'),
        'sid'		=> '-2',
        'version'	=> $version,
        'dateline'	=> TIME_NOW
    );

    $templates[] = array(
        'title'		=> 'uploadsystem_usercp',
        'template'	=> $db->escape_string('<html>
        <head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->uploadsystem}</title>
		{$headerinclude}
        </head>
        <body>
		{$header}
		<form action="usercp.php" method="post" name="input" enctype="multipart/form-data">
			<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
			<table width="100%" border="0" align="center">
				<tr>
					{$usercpnav}
					<td valign="top">
						{$uploadsystem_error}
						<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
							<tr>
								<td class="thead"><strong>{$lang->uploadsystem}</strong></td>
							</tr>
							<tr>
								<td width="100%" class="trow1" valign="top">
									<div class="uploadsystem-desc">{$lang->uploadsystem_desc}</div>
									{$upload_element}
								</td>
							</tr>
						</table>
						<br />
						<div align="center">
							<input type="hidden" name="action" value="do_uploadsystem" />
							<input type="submit" class="button" name="uploadsystem_do" value="{$lang->uploadsystem_button}" />
						</div>
					</td>
				</tr>
			</table>
		</form>
        {$footer}
        </body>
        </html>'),
        'sid'		=> '-2',
        'version'	=> $version,
        'dateline'	=> TIME_NOW
    );

    $templates[] = array(
        'title'		=> 'uploadsystem_usercp_nav',
        'template'	=> $db->escape_string('<tr>
        <td class="trow1 smalltext">
		<a href="usercp.php?action=uploadsystem" class="usercp_nav_item usercp_nav_subscriptions">{$lang->uploadsystem_nav}</a>
        </td>
        </tr>'),
        'sid'		=> '-2',
        'version'	=> $version,
        'dateline'	=> TIME_NOW
    );

    if ($mode == "update") {
        foreach ($templates as $template) {
            $query = $db->simple_select("templates", "tid, template", "title = '".$template['title']."' AND sid = '-2'");
            $existing_template = $db->fetch_array($query);

            if($existing_template) {
                if ($existing_template['template'] !== $template['template']) {
                    $db->update_query("templates", array(
                        'template' => $template['template'],
                        'dateline' => TIME_NOW,
                        'version'	=> $db->escape_string($mybb->version_code+1),
                    ), "tid = '".$existing_template['tid']."'");
                }
            }   
            else {
                $db->insert_query("templates", $template);
            }
        }
    } else {
        foreach ($templates as $template) {
            $check = $db->num_rows($db->simple_select("templates", "title", "title = '".$template['title']."' AND sid = '-2'"));
            if ($check == 0) {
                $db->insert_query("templates", $template);
            }
        }
    }
}

// EINSTELLUNGEN
function uploadsystem_settings($type = 'install') {

    global $db; 

    $setting_array = array(
        'uploadsystem_charactercp' => array(
            'title' => 'Character Control Panel',
            'description' => 'Ist das Plugin Character Control Panel von Julia Roloff (sparks fly) installiert und das Upload-System soll über dieses CP abrufbar sein?',
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 1
        ),
		'uploadsystem_allowed_extensions' => array(
			'title' => 'Erlaubte Dateitypen',
			'description' => 'Welche Dateitypen dürfen allgemein über das Upload-System hochgeladen werden?',
			'optionscode' => 'text',
			'value' => 'png, jpg, jpeg, gif, bmp', // Default
			'disporder' => 2
		),
		'uploadsystem_signatur' => array(
			'title' => 'Signaturen hochladen',
			'description' => 'Dürfen User auch ihre Signaturen über das Upload-System hochladen?',
			'optionscode' => 'yesno',
			'value' => '0', // Default
			'disporder' => 3
		),
        'uploadsystem_signatur_max' => array(
            'title' => 'maximale Signaturgröße',
            'description' => "Wie groß dürfen Signaturen maximal sein? Breite und Höhe getrennt durch x oder |. Wenn das Feld leer bleibt, wird die Größe nicht beschränkt.",
            'optionscode' => 'text',
            'value' => '500x250', // Default
            'disporder' => 4
        ),
        'uploadsystem_signatur_size' => array(
            'title' => 'Maximale Datei-Größe',
            'description' => 'Die maximale Dateigröße (in Kilobyte) für hochgeladene Signaturen beträgt (0 = Keine Beschränkung)? Der Defaultwert beträgt 5 MB.<br>Gewünschte MBx1024 = KB Wert. 5x1024 = 5120',
            'optionscode' => 'text',
            'value' => '5120', // Default
            'disporder' => 5
        ),
        'uploadsystem_signatur_extensions' => array(
            'title' => 'Erlaubte Dateitypen für Signaturen',
            'description' => 'Welche Dateitypen dürfen für die Signaturen hochgeladen werden?',
            'optionscode' => 'text',
            'value' => 'png, jpg, jpeg', // Default
            'disporder' => 6
        ),
        'uploadsystem_webp' => array(
            'title' => 'WebP-Konvertierung',
            'description' => 'Sollen die hochgeladenen Dateien in eine WebP-Datei konvertiert werden?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 7
        ),
        'uploadsystem_retina' => array(
            'title' => 'Retina-freundliche Dateien',
            'description' => 'Soll es möglich sein, Dateien in doppelter Auflösung der festgelegten Standardgröße hochzuladen?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 8
        )
    );

    $gid = $db->fetch_field($db->write_query("SELECT gid FROM ".TABLE_PREFIX."settinggroups WHERE name = 'uploadsystem' LIMIT 1;"), "gid");

    if ($type == 'install') {
        foreach ($setting_array as $name => $setting) {
          $setting['name'] = $name;
          $setting['gid'] = $gid;
          $db->insert_query('settings', $setting);
        }  
    }

    if ($type == 'update') {

        // Einzeln durchgehen 
        foreach ($setting_array as $name => $setting) {
            $setting['name'] = $name;
            $check = $db->write_query("SELECT name FROM ".TABLE_PREFIX."settings WHERE name = '".$name."'"); // Überprüfen, ob sie vorhanden ist
            $check = $db->num_rows($check);
            $setting['gid'] = $gid;
            if ($check == 0) { // nicht vorhanden, hinzufügen
              $db->insert_query('settings', $setting);
            } else { // vorhanden, auf Änderungen überprüfen
                
                $current_setting = $db->fetch_array($db->write_query("SELECT title, description, optionscode, disporder FROM ".TABLE_PREFIX."settings 
                WHERE name = '".$db->escape_string($name)."'
                "));
            
                $update_needed = false;
                $update_data = array();
            
                if ($current_setting['title'] != $setting['title']) {
                    $update_data['title'] = $setting['title'];
                    $update_needed = true;
                }
                if ($current_setting['description'] != $setting['description']) {
                    $update_data['description'] = $setting['description'];
                    $update_needed = true;
                }
                if ($current_setting['optionscode'] != $setting['optionscode']) {
                    $update_data['optionscode'] = $setting['optionscode'];
                    $update_needed = true;
                }
                if ($current_setting['disporder'] != $setting['disporder']) {
                    $update_data['disporder'] = $setting['disporder'];
                    $update_needed = true;
                }
            
                if ($update_needed) {
                    $db->update_query('settings', $update_data, "name = '".$db->escape_string($name)."'");
                }
            }
        }
    }

    rebuild_settings();
}

// VERZEICHNISSE
function uploadsystem_directories() {

    // HAUPTVERZEICHNIS ERSTELLEN
    if (!is_dir(MYBB_ROOT.'uploads/uploadsystem')) {
        mkdir(MYBB_ROOT.'uploads/uploadsystem', 0777, true);
    }

    // SIGNATUR VERZEICHNIS ERSTELLEN
    if (!is_dir(MYBB_ROOT.'uploads/uploadsystem/signatur') AND is_dir(MYBB_ROOT.'uploads/uploadsystem')) {
        mkdir(MYBB_ROOT.'uploads/uploadsystem/signatur', 0777, true);
    }
}

// UID EINFÜGEN
function uploadsystem_addUID() {

    global $db;

    $query_uids = $db->query("SELECT uid FROM ".TABLE_PREFIX."users
    WHERE uid NOT IN (SELECT ufid FROM ".TABLE_PREFIX."uploadfiles)
    ");

    while ($user = $db->fetch_array($query_uids)) {
        
        $adduser = array(
            "ufid" => $user['uid'],
            "signatur" => ''                
        );

        $db->insert_query("uploadfiles", $adduser);
    }
}

// PLUGINLIBRARY
function uploadsystem_pluginlibrary() {

    global $PL;

    require_once PLUGINLIBRARY;
    $PL or $PL = new PluginLibrary();
    $PL->edit_core('uploadsystem', 'admin/modules/user/users.php',
        array(
            array(
                'search' => 'if(isset($away_in_past))',
                'before' => '$plugins->run_hooks("admin_user_users_edit_validate");'
            ),
            array(
                'search' => '$form_container->output_row($lang->suspend_sig, $lang->suspend_sig_info, $actions);',
                'before' => '$plugins->run_hooks("admin_user_users_edit_signatur");'
            ),
        ),           
        true
    );

    // $PL->edit_core('uploadsystem', 'admin/modules/style/themes.php',
    //     array(
    //         'search' => 'if(isset($away_in_past))',
    //         'before' => '$plugins->run_hooks("admin_user_users_edit_validate");'
    //     ),           
    //     true
    // );
}

// STYLESHEET MASTER
function uploadsystem_stylesheet() {
    
    $css = array(
		'name' => 'uploadsystem.css',
		'tid' => 1,
		'attachedto' => '',
		'stylesheet' =>	'.uploadsystem-desc {
        text-align: justify;
        line-height: 180%;
        padding: 20px 40px;
        }

        .uploadsystem_element {
        margin-bottom: 10px;
        }

        .uploadsystem_element:last-child {
        margin-bottom: 0;
        }

        .uploadsystem_element_headline {
        background: #0f0f0f url(../../../images/tcat.png) repeat-x;
        color: #fff;
        border-top: 1px solid #444;
        border-bottom: 1px solid #000;
        padding: 6px;
        font-size: 12px;
        margin-bottom: 10px;
        }

        .uploadsystem_element_main {
        display: flex;
        gap: 10px;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: space-between;
        padding: 0 10px;
        }

        .uploadsystem_element_info {
        text-align: justify;
        }

        .uploadsystem_element_preview {
        background-size: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #6f6d6d;
        font-weight: bold;
        }

        .uploadsystem_upload {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 0 10px;
        align-items: center;
        }

        .uploadsystem_upload_info {
        width: 45%;
        border-right: 1px solid;
        border-color: #ddd;
        }

        .uploadsystem_upload_input {
        width: 53%;
        }

        .uploadsystem_upload_button {
        width: 100%;
        text-align: center;
        }

        .uploadsystem_signatur {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        padding: 0 10px;
        align-items: center;
        }

        .uploadsystem_signatur_info {
        width: 54%;
        border-right: 1px solid;
        border-color: #ddd;
        }
        
        .uploadsystem_signatur_input {
        width: 44%;
        padding-left: 10px;
        }

        .uploadsystem_signatur_button {
        width: 100%;
        text-align: center;
        margin-top: 10px;
        }',
		'cachefile' => 'uploadsystem.css',
		'lastmodified' => TIME_NOW
	);

    return $css;
}

// STYLESHEET UPDATE
function uploadsystem_stylesheet_update() {

    // Update-Stylesheet
    // wird an bestehende Stylesheets immer ganz am ende hinzugefügt
    $update = '';

    // Definiere den  Überprüfung-String (muss spezifisch für die Überprüfung sein)
    $update_string = '';

    return array(
        'stylesheet' => $update,
        'update_string' => $update_string
    );
}

// UPDATE CHECK
function uploadsystem_is_updated(){

    global $db, $mybb;

    if (isset($mybb->settings['uploadsystem_charactercp'])) {
		return true;
	}

    return false;
}

######################################
### CORE ÄNDERUNGEN - MyBB UPDATES ###
######################################
function uploadsystem_is_updated_core() {

    $file = MYBB_ROOT . 'admin/modules/user/users.php';
    if(!file_exists($file)) {
        return false;
    }

    $contents = file_get_contents($file);

    $missing = true;

    if(strpos($contents, 'admin_user_users_edit_validate') === false) {
        $missing = false;
    }

    return $missing;
}

function uploadsystem_integrity_check() {

    global $lang;

    $lang->load("uploadsystem");

    if(!defined('IN_ADMINCP')) {
        return;
    }

    if(!uploadsystem_is_updated_core()) {
        flash_message($lang->uploadsystem_error_integritycheck, 'error');
    }
}
