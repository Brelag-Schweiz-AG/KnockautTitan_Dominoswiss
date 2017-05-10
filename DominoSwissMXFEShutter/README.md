# DominoSwiss MXFE PRO/UP3
Das Modul ist ein DominoSwiss Jalousie- oder Markisenaktor.

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

- Unter "Instanz hinzufügen" ist das 'DominoSwiss MXFE PRO/UP3'-Modul unter dem Hersteller 'BRELAG' aufgeführt.  

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ---------------------------------
ID       | Auswahl der eingerichteten ID (Speicherpunkt im eGate).
Jalousie | Stellt ein ob der Aktor eine Jalousie- oder Markisensteuerung ist.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden automatisch folgende Statusvariablen angelegt.

Bezeichnung | Typ     | Beschreibung
----------- | ------- | -----------
Movement    | Integer | Schaltbare Variable, welche den Aktor Hoch/Runter bewegt oder stoppt.
Status      | Boolean | Zeigt an ob sich der Aktor in Stillstand und Bewegung befindet.

##### Profile:

Bezeichnung        | Beschreibung
------------------ | -----------------
BRELAG.ShutterMove | Profil für Movement
BRELAG.Shutter     | Profil für Status

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Via "Movement" kann der Aktor gesteuert werden.

### 7. PHP-Befehlsreferenz

`boolean BRELAG_MoveDown(integer $InstanzID);`  
Schaltet einen Aktor herunterzufahren.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_MoveDown(12345);`  

`boolean BRELAG_MoveUp(integer $InstanzID);`  
Schaltet einen Aktor hochzufahren.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_MoveUp(12345);`  

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

`boolean BRELAG_Stop(integer $InstanzID);`  
Stoppt einen Aktor.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_Stop(12345);`  