# Upload-System
Dieses Plugin ermöglicht den Usern verschiedene Grafiken per internem Upload System hochzuladen. Auch kann eine Signatur-Datei hochgeladen werden.

# Datenbank-Änderungen
hinzugefügte Tabelle:
- PRÄFIX_uploadsystem (die angelegten Upload-Elementen)
- PRÄFIX_uploadfiles (die Dateinnamen von den hochgeladenen Daten der Usern)

# Einstellungen - Uploadsystem
- Erlaubte Dateitypen
- Signaturen hochladen
- maximale Signatur Größe
- Maximale Datei-Größe
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
Es wird automatisch in jedes bestehende und neue Design hinzugefügt. Man sollte es einfach einmal abspeichern, bevor man dies im Board mit der Untersuchungsfunktion bearbeiten will, da es dann passieren kann, dass das CSS für dieses Plugin in einen anderen Stylesheet gerutscht ist, obwohl es im ACP richtig ist.

# Neuer Ordner - uploadsystem
Es wurde ein neuer Ordner mit dem Namen uploadsystem im Ordner uploads erstellt. In diesem Ordner liegt schon der Ordner 'signatur' und bei jedem Upload-Element wird ein Ordner mit dem Identifikator erstelle. Die hochgeladenen Datein der User werden auch in den entsprechenden Ordner gespeichert.

# Benutzergruppen-Berechtigungen setzen
Damit alle Admin-Accounts Zugriff auf die Verwaltung vom Uploadsystem haben im ACP, müssen unter dem Reiter Benutzer & Gruppen » Administrator-Berechtigungen » Benutzergruppen-Berechtigungen die Berechtigungen einmal angepasst werden. Die Berechtigungen für das Uploadsystem befinden sich im Tab 'Tools & Verwaltung'.

# Datenbank aktualsieren
Bevor das Plugin in Betrieb genommen werden kann muss einmal die Datenbank-Tabelle PRÄFIX_uploadfiles aktualsierst werden. Damit alle bisherigen Accounts auch in der Tabelle stehen und diese Accounts Datein hochladen und speichern können. Dafür muss im ACP der Reiter Datenbank aktualisieren (index.php?module=tools-uploadsystem&action=usercheck) im Uploadsystem aufgerufen werden. Dort muss man nur auf den Button drücken und das einfügen der Accounts erfolgt automatisch. Wenn ein neuer Account registiertet wird werden die entsprechenden Felder direkt für diesen neuen Account angelegt. So muss man diese Aktualisierung nur einmal am Anfang ausführen!

# Account löschen
Damit die Löschung der Daten in der Datenbank und von den Datein vom Webspace richtig funktioniert müssen Accounts über das Popup "Optionen" im ACP gelöscht werden. Im ACP werden alle Accounts unter dem Reiter Benutzer & Gruppen > Benutzer aufgelistet. Und bei jedem Account befindet sich rechts ein Optionen Button. Wenn man diesen druckt erscheint eine Auswahl von verschiedenen Möglichkeiten. Über diese Variante müssen Accounts gelöscht werden, damit das automatische Löschen der Upload Informationen funktioniert.

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
{$post['identification']}<br>
<br>
<b>Mitgliederliste</b><br>
{$user['identification']}<br>
<br>
<b>Profil</b><br>
{$memprofile['identification']}<br>
<br>
<b>Global</b><br>
{$upload_data['identification']}

# Demo
# ACP
<img src="https://stormborn.at/plugins/uploadsystem_acp_overview.png">
<img src="https://stormborn.at/plugins/uploadsystem_acp_add.png">
<img src="https://stormborn.at/plugins/uploadsystem_acp_userEdit.png">
<img src="https://stormborn.at/plugins/uploadsystem_acp_usercheck.png">

# User-CP
<img src="https://stormborn.at/plugins/uploadsystem_ucp.png">
<img src="https://stormborn.at/plugins/uploadsystem_ucp_signatur.png">
