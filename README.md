# Upload-System
Dieses Plugin erweitert das Forum um ein internes Uploadsystem für Grafiken. Hochgeladene Grafiken werden auf dem Webspace gespeichert und können anschließend über einen direkten Link oder eine Variable an verschiedenen Stellen im Forum eingebunden werden.<br>
Im ACP können beliebig viele Upload-Elemente erstellt werden. Für jedes Element lassen sich individuelle Vorgaben festlegen, beispielsweise die erlaubten Dateiformate, die maximale Dateigröße sowie die minimalen und maximalen Abmessungen einer Grafik.<br>
Für jedes Upload-Element wird automatisch ein eigener Ordner unterhalb von `uploads/uploadsystem` auf dem Webspace angelegt.<br>
User:innen können ihre hochgeladenen Grafiken über eine eigene Seite im User-CP (oder, wenn das [Plugin 'Character Control Panel'](https://github.com/its-sparks-fly/character-control-panel-mybb) von Julia Roloff (sparks fly) kann es direkt dort eingebunden werden) verwalten. Zusätzlich besteht für das Team die Möglichkeit, die Grafiken im ACP einzusehen, zu löschen oder selbst Grafiken für einen Account hochzuladen.

### Bildumwandlung in WebP
Hochgeladene Grafiken können automatisch in das **WebP-Format** umgewandelt werden. Dadurch lässt sich die Dateigröße reduzieren, ohne dass dabei auf eine gute Bildqualität verzichtet werden muss.

### Retina-Grafiken
Für Upload-Elemente können Retina-freundliche Grafiken zugelassen werden. Dabei darf eine Grafik bis zur doppelten Größe der festgelegten maximalen Abmessungen hochgeladen werden.<br>
Wird beispielsweise eine maximale Größe von 500 × 500 Pixeln festgelegt, kann eine Grafik mit bis zu 1000 × 1000 Pixeln hochgeladen werden. Die Grafik kann dadurch auf hochauflösenden Displays schärfer dargestellt werden.<br>
Damit eine solche Grafik im Forum anschließend in der vorgesehenen Größe angezeigt wird, muss die entsprechende Skalierung über das CSS des Forums umgesetzt werden.

### Default-Grafiken
Für jedes Theme bzw. Design können für die einzelnen Upload-Elemente eigene Default-Grafiken im ACP hinterlegt werden. Die Option befindet sich beim Bearbeiten des Themes als weiteren Reiter. Diese werden automatisch im für das jeweilige Theme hinterlegten Bilderpfad gespeichert. Dadurch können für unterschiedliche Themes und Designs passende Standardgrafiken hinterlegt werden, ohne dass diese manuell auf dem Webspace hochgeladen werden müssen.<br>
Eine Default-Grafik wird angezeigt, wenn für das jeweilige Upload-Element keine eigene Grafik für den entsprechenden Account vorhanden ist.<br>
Bei der Ansicht des Forums durch Gäste wird grundsätzlich die Default-Grafik verwendet.

### User-Dateien bearbeiten
Im ACP steht auf der Seite "Benutzer bearbeiten" ein zusätzlicher Tab für das Upload-System zur Verfügung. Dort können die Uploads des jeweiligen Accounts verwaltet, bearbeitet und gelöscht werden. Das Team kann außerdem selbst Grafiken für den Account hochladen.<br>
Die Verwaltung der Signatur-Grafik ist ebenfalls direkt im entsprechenden Tab Signatur möglich.

### Signatur-Upload
Das Uploadsystem bietet die Funktion, dass auch Signatur-Grafiken über das System hochgeladen werden können. Ist diese Funktion aktiviert, kann ein Account eine Grafik für die eigene Signatur hochladen und diese anschließend im bekannten Signaturfeld des User-CPs verwenden.<br>
Die Signatur-Grafik kann sowohl vom jeweiligen Account als auch vom Team verwaltet und gelöscht werden.

# Vorrausetzung
- Das ACP Modul <a href="https://github.com/little-evil-genius/rpgstuff_modul" target="_blank">RPG Stuff</a> <b>muss</b> vorhanden sein.
- Die <a href="https://github.com/frostschutz/MyBB-PluginLibrary" target="_blank">PluginLibrary for MyBB</a> von frostschutz <b>muss</b> installiert sein.

# Datenbank-Änderungen
hinzugefügte Tabelle:
- PRÄFIX_uploadsystem (die angelegten Upload-Elemente)
- PRÄFIX_uploadfiles (die Dateinamen von den hochgeladenen Daten der Usern)

# Einstellungen - Uploadsystem
- Character Control Panel
- Erlaubte Dateitypen
- Signaturen hochladen
- maximale Signaturgröße
- Maximale Datei-Größe
- Erlaubte Dateitypen für Signaturen
- WebP-Konvertierung
- Retina-freundliche Dateien

# Neue Template-Gruppe innerhalb der Design-Templates
- Uploadsystem

# Neue Templates (nicht global!)
- uploadsystem_charactercp
- uploadsystem_element
- uploadsystem_element_remove
- uploadsystem_element_upload
- uploadsystem_retina
- uploadsystem_signatur
- uploadsystem_signatur_remove
- uploadsystem_usercp
- uploadsystem_usercp_nav<br>
<br>
<b>HINWEIS:</b><br>
Alle Templates wurden ohne Tabellen-Struktur gecodet. Das Layout wurde auf ein MyBB Default Design angepasst.

# Template Änderungen
- usercp_editsig

# Neuer Ordner - uploadsystem
Es wurde ein neuer Ordner mit dem Namen uploadsystem im Ordner 'uploads' erstellt. In diesem Ordner liegt schon der Ordner 'signatur' und bei jedem Upload-Element wird ein Ordner mit dem Identifikator erstellt. Die hochgeladenen Dateien der User:innen werden auch in den entsprechenden Ordner gespeichert.

# Neues CSS - uploadsystem.css
Es wird automatisch in jedes bestehende und neue Design hinzugefügt. Man sollte es einfach einmal abspeichern - auch im Default. Nach einem MyBB Upgrade fehlt der Stylesheets im Masterstyle? Im ACP Modul "RPG Erweiterungen" befindet sich der Menüpunkt "Stylesheets überprüfen" und kann von hinterlegten Plugins den Stylesheet wieder hinzufügen.
```css
.uploadsystem-desc {
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
        }
```

# Benutzergruppen-Berechtigungen setzen
Damit alle Admin-Accounts Zugriff auf die Verwaltung vom Uploadsystem haben im ACP, müssen unter dem Reiter Benutzer & Gruppen » Administrator-Berechtigungen » Benutzergruppen-Berechtigungen die Berechtigungen einmal angepasst werden. Die Berechtigungen für das Uploadsystem befinden sich im Tab 'RPG Erweiterungen'.

# Links
<b>ACP</b><br>
index.php?module=rpgstuff-uploadsystem<br>
index.php?module=style-themes&action=uploadsystem&tid=X
<br>
<b>User-CP</b><br>
usercp.php?action=uploadsystem<br>
<br>
<b>Datein</b><br>
Forenlink/upload/uploadsystem/identification/identification_uid.<i>dateiformat</i>

# Variabeln für Templates
<b>Postbit</b><br>
Dateiname + Pfad - direkter Link<br>
{$post['identification']}<br>
Nur Dateiname (relevant für if Abfragen) <br>
{$post['files_identification']}<br>
<br>
<b>Mitgliederliste</b><br>
Dateiname + Pfad - direkter Link<br>
{$user['identification']}<br>
Nur Dateiname (relevant für if Abfragen) <br>
{$user['files_identification']}<br>
<br>
<b>Profil</b><br>
Dateiname + Pfad - direkter Link<br>
{$memprofile['identification']}<br>
Nur Dateiname (relevant für if Abfragen) <br>
{$memprofile['files_identification']}<br>
<br>
<b>Global</b><br>
Dateiname + Pfad - direkter Link<br>
{$mybb->user['identification']}<br>
Nur Dateiname (relevant für if Abfragen) <br>
{$mybb->user['files_identification']}

# Demo
# ACP
<img src="https://stormborn.at/plugins/uploadsystem_acp_overview.png">
<img src="https://stormborn.at/plugins/uploadsystem_acp_add.png">
<img src="https://stormborn.at/plugins/uploadsystem_acp_user.png">
<img src="https://stormborn.at/plugins/uploadsystem_acp_sig.png">
<img src="https://stormborn.at/plugins/uploadsystem_acp_default.png">

# User-CP
<img src="https://stormborn.at/plugins/uploadsystem_ucp2.png">
<img src="https://stormborn.at/plugins/uploadsystem_ucp_signatur.png">
