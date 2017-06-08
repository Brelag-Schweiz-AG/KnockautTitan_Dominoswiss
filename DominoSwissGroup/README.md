# DominoSwiss Gruppe
Das Modul ist eine Instanz zum schalten von mehreren DominoSwiss Empfängern.

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
* Hinzufügen/Löschen von verknüpften Geräten
* Automatische Verwaltung von Datenpaketen
* Weiterleitung an verknüpfte DominoSwiss Geräte
* Visualisierung und Schaltbarkeit via WebFront

### 2. Voraussetzungen

- IP-Symcon ab Version 4.3

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/Symcon/SymconBRELAG.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'DominoSwiss Gruppe'-Modul unter dem Hersteller 'BRELAG' aufgeführt.  

__Konfigurationsseite__:

Name                                 | Beschreibung
------------------------------------ | ---------------------------------
ID                                   | Auswahl der eingerichteten ID (Speicherpunkt im eGate)
Zeige SperrLevel im WebFront an      | Ob die Sperrlevel im WebFront angezeigt werden sollen
Aktiviere SperrLevel Schaltbarkeit   | Aktiviert die Schaltbarkeit des jeweiligen Sperrlevels im WebFront
Zeige SendeSperrLevel im WebFront an | Ob das SendeSperrLevel im Webfront angezeigt werden soll
Geräteliste                          | Welche Empfängerinstanzen über die Gruppe geschaltet werden sollen 

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden automatisch folgende Statusvariablen angelegt.

Bezeichnung     | Typ     | Beschreibung
--------------- | ------- | -----------
Gruppenbefehl   | Integer | Gruppenbefehle, welche an die eingetragenen
SperrLevel 1-4  | Boolean | Das jeweilige SperrLevel und der Status ob dieses aktiv ist
SendeSperrLevel | Integer | Auf welcher Priority standardmäßig Befehle versendet werden

##### Profile:

Bezeichnung       | Beschreibung
----------------- | -----------------
BRELAG.GroupOrder | Profil für Gruppenbefehl


### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt.
Ebenso sind Gruppenbefehl, SendeSperrLevel und die einzelnen Sperrlevel schaltbar.

### 7. PHP-Befehlsreferenz

`boolean BRELAG_ContinuousDown(integer $InstanzID, integer $Priorität);`  
Schaltet einen Aktor herunterzufahren.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_ContinuousDown(12345);`  

`boolean BRELAG_ContinuousUp(integer $InstanzID, integer $Priorität);`  
Schaltet einen Aktor heraufzufahren.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_ContinuousUp(12345);`  

`boolean BRELAG_LockLevelClear(integer $InstanzID, integer $Wert);`  
Entsperrt das Sperrlevel mit dem Wert $Wert der Gruppe  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_LockLevelClear(12345);`  

`boolean BRELAG_LockLevelSet(integer $InstanzID, integer $Wert);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_LockLevelSet(12345);`  

`boolean BRELAG_Move(integer $InstanzID, integer $Priorität, integer $Wert);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_Move(12345);`  

`boolean BRELAG_PulseDown(integer $InstanzID, integer $Priorität);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_PulseDown(12345);`  

`boolean BRELAG_PulseUp(integer $InstanzID, integer $Priorität);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_PulseUp(12345);`  

`boolean BRELAG_RestorePosition(integer $InstanzID, integer $Priorität);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_RestorePosition(12345);`  

`boolean BRELAG_RestorePositionBoth(integer $InstanzID, integer $Priorität);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_RestorePositionBoth(12345);`  

`boolean BRELAG_Save(integer $InstanzID, integer $Priorität);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_Save(12345);`  

`boolean BRELAG_SendCommand(integer $InstanzID, integer $Kommando, integer $Priorität, integer $Wert);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_SendCommand(12345);`  

`boolean BRELAG_Stop(integer $InstanzID, integer $Priorität);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_Stop(12345);`  

`boolean BRELAG_Toogle(integer $InstanzID, integer $Priorität);`  
Erstellt alle eingelesenen Geräte.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_Toogle(12345);`  