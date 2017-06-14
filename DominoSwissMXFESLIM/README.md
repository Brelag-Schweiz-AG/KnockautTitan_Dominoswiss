# DominoSwiss MXFE SLIM
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

- IP-Symcon ab Version 4.3

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/Symcon/SymconBRELAG.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'DominoSwiss MXFE SLIM'-Modul unter dem Hersteller 'BRELAG' aufgeführt.  

__Konfigurationsseite__:

Name                                 | Beschreibung
------------------------------------ | ---------------------------------
ID                                   | Auswahl der eingerichteten ID (Speicherpunkt im eGate)
Zeige SperrLevel im WebFront an      | Ob die Sperrlevel im WebFront angezeigt werden sollen
Aktiviere SperrLevel Schaltbarkeit   | Aktiviert die Schaltbarkeit des jeweiligen Sperrlevels im WebFront
Zeige SendeSperrLevel im WebFront an | Ob das SendeSperrLevel im Webfront angezeigt werden soll
Markise                              | Gibt an, ob es sich beim den Empfänger um eine Markise oder Jalousie handelt 

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden automatisch folgende Statusvariablen angelegt.

Bezeichnung          | Typ     | Beschreibung
-------------------- | ------- | -----------
Bewegung             | Integer | Schaltbare Variable, welche den Aktor Hoch/Runter bewegt oder stoppt.
Favoriteneinstellung | Integer | Speichert und ruft gespeicherte Werte via WebFront ab
Priorität            | Integer | Auf welcher Priority standardmäßig Befehle versendet werden
Sperrlevel 1-4       | Boolean | Das jeweilige SperrLevel und der Status ob dieses aktiv ist
Status               | Boolean | Zeigt an ob sich der Aktor in Stillstand und Bewegung befindet.

##### Profile:

Bezeichnung                | Beschreibung
-------------------------- | -----------------
BRELAG.Shutter             | Profil für Status
BRELAG.ShutterMoveAwning   | Markisenprofil für Movement
BRELAG.ShutterMoveJalousie | Jalousieprofil für Movement
BRELAG.Save                | Profil für Saving
BRELAG.SendingOnLockLevel  | Profil für Priorität

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt.
Die LockLevel 1-4 schalten die jeweiligen LockLevel An/Aus.
Priorität definiert die Priorität auf dem standardmäßig gesendet wird.
Via "Movement" kann der Aktor gesteuert werden.
Es kann ebenfalls ein Wert mit Gespeichert und abgerufen werden.

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
`BRELAG_ContinuousUp(12345, 0);`  

`boolean BRELAG_LockLevelClear(integer $InstanzID, integer $Wert);`  
Entsperrt das Sperrlevel mit dem Wert $Wert.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_LockLevelClear(12345, 3);`  

`boolean BRELAG_LockLevelSet(integer $InstanzID, integer $Wert);`  
Sperrt das Sperrlevel mit dem Wert $Wert.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_LockLevelSet(12345, 3);`  

`boolean BRELAG_PulseDown(integer $InstanzID, integer $Priorität);`  
Sendet einen Impuls runter auf den Aktor.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_PulseDown(12345, 0);`  

`boolean BRELAG_PulseUp(integer $InstanzID, integer $Priorität);`  
Sendet einen Impuls hoch auf den Aktor.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_PulseUp(12345, 0);`  

`boolean BRELAG_RestorePosition(integer $InstanzID, integer $Priorität);`  
Ruft den gespeicherten Wert ab.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_RestorePosition(12345, 0);`  

`boolean BRELAG_Save(integer $InstanzID, integer $Priorität);`  
Speichert den Momentanen Wert.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_Save(12345, 0);`  

`boolean BRELAG_SendCommand(integer $InstanzID, integer $Kommando, integer $Priorität, integer $Wert);`  
Sendet das Kommando $KOmmando mit der Priorität $Priorität und dem Wert $Wert.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_SendCommand(12345, 1, 0);`  

`boolean BRELAG_Stop(integer $InstanzID, integer $Priorität);`  
Stoppt den Aktor.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`BRELAG_Stop(12345), 0;`