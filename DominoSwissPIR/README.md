# DominoSwiss PIR
Das Modul ist ein DominoSwiss PIR.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Stellt selbstständig eine Verbindung zum eGate her
* Einstellbarkeit der ID
* Einstellbarkeit der Bewegungsdauer in Sekunden
* Visualisierung via WebFront

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/Symcon/SymconBRELAG.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'DominoSwiss PIR'-Modul unter dem Hersteller 'BRELAG' aufgeführt.  

__Konfigurationsseite__:

Name               | Beschreibung
------------------ | ---------------------------------
ID                 | Auswahl der eingerichteten ID (Speicherpunkt im eGate).
Bewegungsintervall | Dauer der Bewegungsanzeige in Sekunden.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden automatisch folgende Statusvariablen angelegt.

Bezeichnung    | Typ     | Beschreibung
-------------- | ------- | -----------
MotionValue    | Boolean | Es liegt eine Bewegung vor oder nicht

##### Profile:

Es werden keine neuen Profile angelegt.

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt.

### 7. PHP-Befehlsreferenz

Es sind keine weiteren Befehle vorhanden.