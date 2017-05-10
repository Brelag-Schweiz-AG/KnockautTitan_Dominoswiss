# DominoSwiss MX RLUP 1A/10A
Das Modul ist ein DominoSwiss Schaltaktor.

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
* Automatische Verwaltung von Datenpaketen
* Kann durch Befehle angesteuert werden
* Steuerbarkeit via WebFront

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/Symcon/SymconBRELAG.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'DominoSwiss MX RLUP 1A/10A'-Modul unter dem Hersteller 'BRELAG' aufgeführt.  

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ---------------------------------
ID       | Auswahl der eingerichteten ID (Speicherpunkt im eGate).

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden automatisch folgende Statusvariablen angelegt.

Bezeichnung | Typ     | Beschreibung
----------- | ------- | -----------
Status      | Boolean | Zeigt an ob sich der Aktor in Stillstand und Bewegung befindet.

##### Profile:

Es werden keine neuen Profile angelegt.

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Via "Status" kann der Aktor gesteuert werden.

### 7. PHP-Befehlsreferenz  

`boolean BRELAG_RestorePosition(integer $InstanzID);`  
Ruft die gespeicherte Position auf und überschreibt diese.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_RestorePosition(12345);`  

`boolean BRELAG_RestorePositionBoth(integer $InstanzID);`  
Ruft die gespeicherte Position auf, ohne diese zu überschreiben.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_RestorePositionBoth(12345);`  

`boolean BRELAG_SwitchMode(integer $InstanzID, boolean $Status);`  
Setzt den Schalter auf AN (Status = true) oder AUS (Status = false).
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_SwitchMode(12345, true);`  

`boolean BRELAG_Toogle(integer $InstanzID);`  
Wechselt den Status basierend auf dem Bisherigen.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_Toogle(12345);`  