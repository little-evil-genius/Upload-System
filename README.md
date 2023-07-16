# Upload-System
Dieses Plugin fügt eurem Forum ein internes Uploadsystem hinzu. Das Hochladen von Dateien/Grafiken funktioniert sowie das Hochladen von Avataren im User-CP. Die hochgeladenen Dateien werden auf dem Webspace gespeichert und können per Variable/Link überall eingebunden werden.<br>
Administratoren können im ACP verschiedene Upload-Elemente erstellen. Wenn ein neues Element erstellt wird, können verschiedene Kriterien festgelegt werden. Wie die zulässige Minimal- und Maximalgröße, die Dateiformate, die Dateigröße in Kilobyte. Auch die Dateien von den Usern verwalten, bearbeiten und gegebenenfalls löschen. User haben die Möglichkeit die einzelnen Dateien im User-CP auf einer extra Seite zu verwalten.<br>
Außerdem kann das Team auch einstellen, ob User auch eine Signatur-Datei hochladen können und dann im bekannten Signatur-Feld einbinden können.

# Datenbank-Änderungen
hinzugefügte Tabelle:
hinzugefügte Tabelle:
- PRÄFIX_uploadsystem (die angelegten Upload-Elemente)
- PRÄFIX_uploadfiles (die Dateinamen von den hochgeladenen Daten der Usern)

# Einstellungen - Uploadsystem
- Erlaubte Dateitypen
- Signaturen hochladen
- maximale Signaturgröße
- Maximale Dateigröße
- Erlaubte Dateitypen für Signaturen

# Neue Template-Gruppe innerhalb der Design-Templates
- Uploadsystem

# Neue Templates (nicht global!)
- uploadsystem_usercp
- uploadsystem_usercp_element
- uploadsystem_usercp_element_remove
- uploadsystem_usercp_element_upload
- uploadsystem_usercp_nav
- uploadsystem_usercp_signatur
- uploadsystem_usercp_signatur_remove<br>
<br>
<b>HINWEIS:</b><br>
Alle Templates wurden ohne Tabellen-Struktur gecodet. Das Layout wurde auf ein MyBB Default Design angepasst.

# Template Änderungen
- usercp_editsig <br> 
```<form action="usercp.php" method="post">``` => ```<form action="usercp.php" method="post" enctype="multipart/form-data">```<br>
```<tr><td class="trow2"><span class="smalltext">{$lang->edit_sig_note2}</span></td>``` => ```{$upload_signatur} <tr><td class="trow2"><span class="smalltext">{$lang->edit_sig_note2}</span></td>```

# Neues CSS - uploadsystem.css
Es wird automatisch zu jedem bestehenden und neuem Design hinzugefügt. Man sollte es einfach einmal abspeichern, bevor man dies im Board mit der Untersuchungsfunktion bearbeiten will, da es dann passieren kann, dass das CSS für dieses Plugin in einen anderen Stylesheet gerutscht ist, obwohl es im ACP richtig ist.

# Neuer Ordner - uploadsystem
Es wurde ein neuer Ordner mit dem Namen uploadsystem im Ordner uploads erstellt. In diesem Ordner liegt schon der Ordner 'signatur' und bei jedem Upload-Element wird ein Ordner mit dem Identifikator erstellt. Die hochgeladenen Dateien der User werden auch in den entsprechenden Ordner gespeichert.

# Benutzergruppen-Berechtigungen setzen
Damit alle Admin-Accounts Zugriff auf die Verwaltung vom Uploadsystem haben im ACP, müssen unter dem Reiter Benutzer & Gruppen » Administrator-Berechtigungen » Benutzergruppen-Berechtigungen die Berechtigungen einmal angepasst werden. Die Berechtigungen für das Uploadsystem befinden sich im Tab 'Tools & Verwaltung'.

# Datenbank aktualsieren
Bevor das Plugin in Betrieb genommen werden kann muss einmal die Datenbank-Tabelle PRÄFIX_uploadfiles aktualisiert werden. Damit alle bisherigen Accounts auch in der Tabelle stehen und diese Accounts Dateien hochladen und speichern können. Dafür muss im ACP der Reiter Datenbank aktualisieren (index.php?module=tools-uploadsystem&action=usercheck) im Uploadsystem aufgerufen werden. Dort muss man nur auf den Button drücken und das einfügen der Accounts erfolgt automatisch. Wenn ein neuer Account registriert wird, werden die entsprechenden Felder direkt für diesen neuen Account angelegt. So muss man diese Aktualisierung nur einmal am Anfang ausführen!

# Account löschen
Damit die Löschung der Daten in der Datenbank und von den Dateien vom Webspace richtig funktioniert, müssen Accounts über das Popup "Optionen" im ACP gelöscht werden. Im ACP werden alle Accounts unter dem Reiter Benutzer & Gruppen > Benutzer aufgelistet. Und bei jedem Account befindet sich rechts ein Optionen-Button. Wenn man diesen drückt, erscheint eine Auswahl von verschiedenen Möglichkeiten. Über diese Variante müssen Accounts gelöscht werden, damit das automatische Löschen der Upload Informationen funktioniert.

# Links
<b>ACP</b><br>
index.php?module=tools-uploadsystem<br>
<br>
<b>User-CP</b><br>
usercp.php?action=uploadsystem<br>
<br>
<b>Datein</b><br>
Forenlink/upload/uploadsystem/identification/identification_uid.<i>dateiformat</i>

# Variabeln für Templates
<b>Postbit</b><br>
Dateiname + Pfad <br>
{$post['identification']}<br>
Nur Dateiname (relevant für if Abfragen) <br>
{$post['files_identification']}<br>
<br>
<b>Mitgliederliste</b><br>
Dateiname + Pfad <br>
{$user['identification']}<br>
Nur Dateiname (relevant für if Abfragen) <br>
{$user['files_identification']}<br>
<br>
<b>Profil</b><br>
Dateiname + Pfad <br>
{$memprofile['identification']}<br>
Nur Dateiname (relevant für if Abfragen) <br>
{$memprofile['files_identification']}<br>
<br>
<b>Global</b><br>
Dateiname + Pfad <br>
{$upload_data['identification']}<br>
Nur Dateiname (relevant für if Abfragen) <br>
{$upload_data['files_identification']}

# Beispiel If-Abfrage
Viele Boards verstecken ihre Grafiken vor Gästen oder möchten, dass eine Default Grafik angezeigt wird, wenn es keine entsprechende Grafik gibt. Genau wie bei den Profilfeldern kann man das auch für die hochgeladenen Dateien machen. Dies ist jetzt ein Beispiel für den Postbit. <br>
```
if ($post['files_identification'] != "") {
   $post['eigener_Name'] = $post['identification'];
} else {
   $post['eigener_Name'] = $theme['imgdir']."/eigenerName_default.png";
}
```
<br>
In das Template postbit und postbit_classic würde dann die Variable {$post['eigener_Name']} an die gewünschte Stelle kommen. Auf den Webspace würde dann in den oder die entsprechende/n Grafiken-Ordner für das Design eine Datei mit dem Namen eigenerName_default.png kommen. <br><br>
<b>HINWEIS</b>
Solche If-Abfragen, sollten sie nicht über das Plugin 'php in tpl' erfolgen, sollten <b>NACH</b> folgenden Stellen in der PHP eingefügt werden:
<b>Postbit (& PN & Postbit Vorschau):</b><br>
$post = $plugins->run_hooks("postbit", $post);<br><br>
<b>Mitgliederliste:</b><br>
$user = $plugins->run_hooks("memberlist_user", $user);<br><br>
<b>Profil:</b><br>
$plugins->run_hooks("member_profile_end");

# Demo
# ACP
<img src="https://stormborn.at/plugins/uploadsystem_acp_overview.png">
<img src="https://stormborn.at/plugins/uploadsystem_acp_add.png">
<img src="https://stormborn.at/plugins/uploadsystem_acp_userEdit.png">
<img src="https://stormborn.at/plugins/uploadsystem_acp_usercheck.png">

# User-CP
<img src="https://stormborn.at/plugins/uploadsystem_ucp.png">
<img src="https://stormborn.at/plugins/uploadsystem_ucp_signatur.png">
