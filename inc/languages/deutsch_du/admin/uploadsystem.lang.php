<?php
$l['uploadsystem_error_rpgstuff'] = "Das ACP Modul <a href=\"https://github.com/little-evil-genius/rpgstuff_modul\" target=\"_blank\">\"RPG Stuff\"</a> muss vorhanden sein!";
$l['uploadsystem_error_pluginlibrary'] = "Die <a href=\"https://github.com/frostschutz/MyBB-PluginLibrary\" target=\"_blank\">\"PluginLibrary for MyBB\"</a> muss vorhanden sein!";
$l['uploadsystem_error_integritycheck'] = "Die Core-Änderungen für das Plugin 'Upload-System' fehlen - dies passiert häufig nach einem MyBB-Update. Die Core-Änderungen können erneut im Modul RPG Stuff hinzufügt werden.";
$l['uploadsystem_error_invalid'] = "Ungültige Option";

$l['uploadsystem_permission'] = "Kann die Upload-Elemente verwalten?";

$l['uploadsystem_nav'] = "Uploadsystem";

$l['uploadsystem_breadcrumb_main'] = "Upload-Elemente";
$l['uploadsystem_breadcrumb_add'] = "Upload-Element hinzufügen";
$l['uploadsystem_breadcrumb_edit'] = "Upload-Element bearbeiten";
$l['uploadsystem_breadcrumb_themes'] = "Uploadsystem - Default-Grafiken";

$l['uploadsystem_options_container'] = "Steuerung";
$l['uploadsystem_options_popup'] = "Optionen";
$l['uploadsystem_options_popup_edit'] = "Upload-Element bearbeiten";
$l['uploadsystem_options_popup_delete'] = "Upload-Element löschen";

$l['uploadsystem_tabs_overview'] = "Upload-Elemente";
$l['uploadsystem_tabs_overview_desc'] = "In diesem Bereich kannst du die Elemente für das Upload-System verwalten, erstellen und löschen.";
$l['uploadsystem_tabs_add'] = "Upload-Element hinzufügen";
$l['uploadsystem_tabs_add_desc'] = "Hier kannst du ein neues Element für das Upload-System erstellen.";
$l['uploadsystem_tabs_edit'] = "Upload-Element bearbeiten";
$l['uploadsystem_tabs_edit_desc'] = "Hier kannst du das Upload-Element '{1}' bearbeiten.";

$l['uploadsystem_overview_header'] = "Upload-Elemente";
$l['uploadsystem_overview_container'] = "Upload-Elemente";
$l['uploadsystem_overview_container_name'] = "Name";
$l['uploadsystem_overview_container_path'] = "Upload-Pfad";
$l['uploadsystem_overview_container_extensions'] = "erlaubte Dateitypen";
$l['uploadsystem_overview_container_dims'] = "Größe (Breite x Höhe)";
$l['uploadsystem_overview_container_dims_min'] = "<b>min.</b> {1} Pixel<br><b>max.</b> /";
$l['uploadsystem_overview_container_dims_minmax'] = "<b>min.</b> {1} Pixel<br><b>max.</b> {2} Pixel";
$l['uploadsystem_overview_container_dims_fix'] = "{1} Pixel";
$l['uploadsystem_overview_container_square'] = "Quadratisch";
$l['uploadsystem_overview_container_square_yes'] = "Ja";
$l['uploadsystem_overview_container_square_no'] = "Nein";
$l['uploadsystem_overview_container_size'] = "max. Dateigröße";
$l['uploadsystem_overview_container_noLimit'] = "kein Limit";
$l['uploadsystem_overview_noElements'] = "Bisher wurden keine Elemente für das Upload-System erstellt.";

$l['uploadsystem_add_header'] = "Neues Upload-Element hinzufügen";
$l['uploadsystem_add_container'] = "Neues Upload-Element hinzufügen";
$l['uploadsystem_add_button'] = "Upload-Element speichern";
$l['uploadsystem_add_flash'] = "Das neue Element für das Upload-System wurde erfolgreich erstellt.";

$l['uploadsystem_edit_header'] = "Upload-Element bearbeiten";
$l['uploadsystem_edit_container'] = "Upload-Element '{1}' bearbeiten";
$l['uploadsystem_edit_button'] = "Upload-Element speichern";
$l['uploadsystem_edit_flash'] = "Das Element für das Upload-System wurde erfolgreich gespeichert.";

$l['uploadsystem_delete_notice'] = "Soll das Element wirklich gelöscht werden? Alle hochgeladenen Dateien werden auch gelöscht.";
$l['uploadsystem_delete_flash'] = "Das Element für das Upload-System wurde erfolgreich gelöscht.";

$l['uploadsystem_form_identification'] = "Identifikator <em>*</em>";
$l['uploadsystem_form_identification_desc'] = "Dieser Titel dient als Name für den Speicher-Ordner und für die Variabel Bezeichnung im Template. Es sind keine Sonderzeichen oder Leerzeichen erlaubt (maschinenlesbar). Der Name muss für jedes Feld unterschiedlich sein.";
$l['uploadsystem_form_name'] = "Titel <em>*</em>";
$l['uploadsystem_form_name_desc'] = "Wie lautet die Bezeichnung für das Upload-Element? Dieser wird auch später angezeigt.";
$l['uploadsystem_form_description'] = "Kurzbeschreibung";
$l['uploadsystem_form_description_desc'] = "Beschreibe kurz dieses Element. Wo und wofür es beispielsweise verwendet wird.";
$l['uploadsystem_form_disporder'] = "Sortierung";
$l['uploadsystem_form_disporder_desc'] = "Dies ist die Anzeigereihenfolge, abhängig von anderen Upload-Elementen. Die Zahl sollte für jedes Element unterschiedlich sein.";
$l['uploadsystem_form_extensions'] = "Erlaubte Dateitypen <em>*</em>";
$l['uploadsystem_form_extensions_desc'] = "Welche Dateitypen sind für dieses Element erlaubt?";
$l['uploadsystem_form_extensions_value'] = "{1} erlauben";
$l['uploadsystem_form_mindims'] = "Minimale Grafikgröße <em>*</em>";
$l['uploadsystem_form_mindims_desc'] = "Die minimal zulässige Größe für dieses Element, Breite und Höhe getrennt durch x oder |.";
$l['uploadsystem_form_maxdims'] = "Maximale Grafikgröße";
$l['uploadsystem_form_maxdims_desc'] = "Die maximal zulässige Größe für dieses Element, Breite und Höhe getrennt durch x oder |. Wenn das Feld leer bleibt, wird die Größe nicht beschränkt.";
$l['uploadsystem_form_square'] = "Quadratisches Element <em>*</em>";
$l['uploadsystem_form_square_desc'] = "Muss das Element quadratisch sein?";
$l['uploadsystem_form_bytesize'] = "Maximale Dateigröße <em>*</em>";
$l['uploadsystem_form_bytesize_desc'] = "Die maximale Dateigröße (in Kilobyte) für hochgeladene Elemente (0 = Keine Beschränkung). Der Defaultwert beträgt 5 MB.";

$l['uploadsystem_form_error_identification'] = "Du hast kein Idenifikator angegeben.";
$l['uploadsystem_form_error_identification_double'] = "Dieser Idenifikator ist schon vergeben.";
$l['uploadsystem_form_error_identification_machine'] = "Dieser Idenifikator besitzt Sonderzeichen oder Leerzeichen.";
$l['uploadsystem_form_error_name'] = "Du hast keinen Titel angegeben.";
$l['uploadsystem_form_error_extensions'] = "Du musst mindestens ein Dateityp auswählen.";
$l['uploadsystem_form_error_mindims'] = "Du hast keine minimale Grafikgröße angegeben.";
$l['uploadsystem_form_error_dimension'] = "Das Format von der {1} Grafikgröße ist ungültig.";
$l['uploadsystem_form_error_dimension_mindims'] = "minimalen";
$l['uploadsystem_form_error_dimension_maxdims'] = "maximalen";
$l['uploadsystem_form_error_square_min'] = "Die Angabe für die minimale Grafikgröße ist nicht quadratisch.";
$l['uploadsystem_form_error_square_max'] = "Die Angabe für die maximale Grafikgröße ist nicht quadratisch.";
$l['uploadsystem_form_error_bytesize'] = "Du musst eine maximale Dateigröße angeben oder eine 0, für keine Begrenzung.";

$l['uploadsystem_user'] = "Upload-Elemente";
$l['uploadsystem_user_container'] = "Upload-Element: {1}";
$l['uploadsystem_user_upload'] = "{1} hochladen:";
$l['uploadsystem_user_remove'] = "{1} löschen";
$l['uploadsystem_user_dims_fixed'] = "Die exakten Abmessungen sind {1}x{2}{3}.";
$l['uploadsystem_user_dims_maxmin'] = "Die Abmessungen sind von min. {1} bis max. {2}{3}.";
$l['uploadsystem_user_dims_min'] = "Die <i>minimalen</i> Abmessungen sind {1}.";
$l['uploadsystem_user_square'] = " Die Datei muss <i>quadratisch</i> sein.";
$l['uploadsystem_user_extensions_plural'] = "Es sind {1} -Dateien zulässig.";
$l['uploadsystem_user_extensions_singular'] = "Es sind nur {1} Dateien sind zulässig.";
$l['uploadsystem_user_size'] = "Die maximal zulässige Dateigröße beträgt {1}.";
$l['uploadsystem_user_noSize'] = "Es gibt keine Begrenzung bei der Dateigröße.";
$l['uploadsystem_user_notice'] = "<br><small>Wenn du ein anderes Bild wählst, wird das ältere Bild vom Server gelöscht.</small>";
$l['uploadsystem_user_signatur_headline'] = "Signatur-Datei hochladen:";
$l['uploadsystem_user_signatur_nofile'] = "Aktuell wurde keine Signatur-Datei hochgeladen.";
$l['uploadsystem_user_signatur_button_remove'] = "aktuelle Signatur-Datei löschen";

$l['uploadsystem_themes_tab'] = "Uploadsystem - Default-Grafiken";
$l['uploadsystem_themes_tab'] = "Uploadsystem - Default-Grafiken";
$l['uploadsystem_themes_tab_desc'] = "Hier kannst du für das Desgin {1} die Default-Grafiken für die Elemente vom Upload-System hochladen und löschen.";
$l['uploadsystem_themes_header'] = "Uploadsystem - Default-Grafiken";
$l['uploadsystem_themes_button'] = "Default-Grafiken speichern";
$l['uploadsystem_themes_flash'] = "Die Default-Grafiken wurde erfolgreich aktualisiert.";

$l['uploadsystem_retina_dims'] = " (Retina-Display: {1}x{2})";
$l['uploadsystem_retina_checkbox'] = "Retina-freundliche Datei";

$l['uploadsystem_error_element'] = "<b>{1}</b>: ";
$l['uploadsystem_error_element_signatur'] = "Signatur-Datei";
$l['uploadsystem_error_upload'] = "Es wurde keine Datei zum hochladen ausgewählt.";
$l['uploadsystem_error_extensions'] = "{1}Die Datei besitzt ein ungültiges Dateiformat.";
$l['uploadsystem_error_bytesize'] = "{1}Die Datei ist größer als {2}.";
$l['uploadsystem_error_imgDimensions'] = "{1}Die Datei ist kein gültiges Bild.";
$l['uploadsystem_error_dims_fixed'] = "{1}Die Datei entspricht nicht den zugelassenen Abmessungen - {2}{3}.";
$l['uploadsystem_error_dims_max'] = "{1}Die Datei ist von den Abmessungen zu groß - {2}{3}.";
$l['uploadsystem_error_dims_min'] = "{1}Die Datei ist von den Abmessungen zu klein - {2}.";
$l['uploadsystem_error_square'] = "{1}Die Datei ist nicht quadratisch";
