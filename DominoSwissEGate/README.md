# DominoSwiss eGate
Das Modul ist die Splitter Instanz für die Verbindung zwischen Client Socket und Modul.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Stellt via Client Socket eine Verbindung zum eGate her
* Wird automatisch erstellt, wenn das erste Modul hinzugefügt wird
* Automatische Verwaltung von Datenpaketen
* Weiterleiten von aufgearbeiteten Werten an die Module

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/Symcon/SymconBRELAG.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'DominoSwiss eGate'-Modul unter dem Hersteller 'BRELAG' aufgeführt.  

__Konfigurationsseite__:

Es gibt keine weitere Konfiguration.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es gibt keine Statusvariablen.

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt.

### 6. WebFront

Es wird nichts im WebFront angezeigt.

### 7. PHP-Befehlsreferenz

Es gibt keine Befehle.