# Beispiele
Einfache Skriptbeispiele

### Ereignis via Variable De-/Aktivieren

* Ausgelöstes Ereignis erstellen/erweitern (siehe unten)
* Aktionsskript erstellen
* Schaltervariable erstellen, Profil "~Switch" auswählen und Aktionskript auswählen

### Ereignis via Wochenplan De-/Aktivieren

* Aktionsskript erstellen/erweitern (siehe unten)
* Wochenplan Ereignis unterhalb des Skripts erstellen
* Im WebFront Wochenplan konfigurieren

Skriptinhalt:

    switch ($_IPS['SENDER']) {

        case "WebFront":
        
            switch ($_IPS['VARIABLE']) {
            
                case 36635 /*[DominoSwiss SWW SOL\Beschattungsautomatik]*/:
                    IPS_SetEventActive(58291 /*[DominoSwiss MX FESLIM\Bei Grenzüberschreitung der Variable "" mit Grenze 20000]*/ , $_IPS['VALUE']); 
                    IPS_SetEventActive(43914 /*[DominoSwiss MX FESLIM\Bei Grenzunterschreitung der Variable "" mit Grenze 5000]*/ , $_IPS['VALUE']);
                    break;
                    
                case 18656 /*[DominoSwiss SWW SOL\Windautomatik]*/:
                    IPS_SetEventActive(27324 /*[DominoSwiss SWW SOL\Windalarm\Bei Grenzüberschreitung der Variable "" mit Grenze 60]*/ , $_IPS['VALUE']); 
                    IPS_SetEventActive(28745 /*[DominoSwiss SWW SOL\Windalarm\Bei Grenzunterschreitung der Variable "" mit Grenze 40]*/ , $_IPS['VALUE']);
                    break;
            
            }
            
            //Aktualisierung der Schaltvariable
            SetValue($_IPS['VARIABLE'], $_IPS['VALUE']);
            break;
            
        case "TimerEvent"://Wochenplan
            
            switch ($_IPS['ACTION']) {
            
                case 1: //ID 1 = Ereignisse AN
                    IPS_SetEventActive(58291 /*[DominoSwiss MX FESLIM\Bei Grenzüberschreitung der Variable "" mit Grenze 20000]*/ , true); 
                    IPS_SetEventActive(43914 /*[DominoSwiss MX FESLIM\Bei Grenzunterschreitung der Variable "" mit Grenze 5000]*/ , true);
                    IPS_SetEventActive(27324 /*[DominoSwiss SWW SOL\Windalarm\Bei Grenzüberschreitung der Variable "" mit Grenze 60]*/ , true); 
                    IPS_SetEventActive(28745 /*[DominoSwiss SWW SOL\Windalarm\Bei Grenzunterschreitung der Variable "" mit Grenze 40]*/ , true);
                    break;
                    
                case 2: //ID 2 = Ereignisse AUS
                    IPS_SetEventActive(58291 /*[DominoSwiss MX FESLIM\Bei Grenzüberschreitung der Variable "" mit Grenze 20000]*/ , false); 
                    IPS_SetEventActive(43914 /*[DominoSwiss MX FESLIM\Bei Grenzunterschreitung der Variable "" mit Grenze 5000]*/ , false);
                    IPS_SetEventActive(27324 /*[DominoSwiss SWW SOL\Windalarm\Bei Grenzüberschreitung der Variable "" mit Grenze 60]*/ , false); 
                    IPS_SetEventActive(28745 /*[DominoSwiss SWW SOL\Windalarm\Bei Grenzunterschreitung der Variable "" mit Grenze 40]*/ , false);
                    break;
            }
	     break;
	
    }


###Wechselschalter für Ereignisse

Dieser Wechelschalter wechselt die Ereignisse auf De-/Aktiviert und führt einen Befehl aus.

* Skript erstellen
* Ereignisse erstellen
* Ereignisse konfigurieren
* Skript anpassen

Skriptinhalt:

    switch ($_IPS['SENDER']) {
    
        case "Variable":
            
            switch ($_IPS['EVENT']) {
            
                case 58291 /*[DominoSwiss MX FESLIM\Bei Grenzüberschreitung der Variable "" mit Grenze 20000]*/:
                    BRELAG_RestorePosition(56550 /*[DominoSwiss MX FESLIM]*/, 0);
                    IPS_SetEventActive($_IPS['EVENT'] , false); 
                    IPS_SetEventActive(43914, true);
                    break;
                    
                case 43914 /*[DominoSwiss MX FESLIM\Bei Grenzunterschreitung der Variable "" mit Grenze 5000]*/:
                    BRELAG_ContinuousUp(56550 /*[DominoSwiss MX FESLIM]*/, 0);
                    IPS_SetEventActive($_IPS['EVENT'] , false); 
                    IPS_SetEventActive(58291, true);
                    break;
            
            }
            
        case "TimerEvent":
                
            switch ($_IPS['EVENT']) {
                
                case 42564 /*[DominoSwiss MX FESLIM\WechselStatusBeiÜberUnterschreitung\1300 Check Helligkeit]*/:
                    if (GetValue(29807 /*[DominoSwiss SWW SOL\Light]*/) > 20000) {
                        BRELAG_RestorePosition(56550 /*[DominoSwiss MX FESLIM]*/, 0);
                        IPS_SetEventActive(58291, false); 
                        IPS_SetEventActive(43914, true);
                    }		
                    break;
            }
            
        break;
        
    }