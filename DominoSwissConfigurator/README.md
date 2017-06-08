# DominoSwiss Konfigurator
Das Modul dient zum Einlesen von Konfigurationsdaten und automatischen Einrichten der Geräteinstanzen.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Liest eine BRELAG DominoSwisskonfigurationsdatei ein und stellt diese in einer Liste dar
* Erstellt via Knopfdruck alle vorhandenen Geräte und verknüft automatisch Gruppenadressen

### 2. Voraussetzungen

- IP-Symcon ab Version 4.3

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/Symcon/SymconBRELAG.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'DominoSwiss Konfigurator'-Modul unter dem Hersteller 'BRELAG' aufgeführt.  

__Konfigurationsseite__:

Name                  | Beschreibung
--------------------- | ---------------------------------
Text Datei            | Ausgewählte Konfigurationsdatei
Alle Geräte erstellen | Erstellt alle eingelesenen Geräte und Gruppen

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es gibt keine Statusvariablen.

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt.

### 6. WebFront

Es wird nichts im WebFront angezeigt.

### 7. PHP-Befehlsreferenz

`boolean BRELAG_CreateDevices(integer $InstanzID);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_CreateDevices(12345);`  