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

# Links
<b>ACP</b><br>
index.php?module=tools-uploadsystem<br>
<br>
<b>User-CP</b><br>
usercp.php?action=uploadsystem

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

