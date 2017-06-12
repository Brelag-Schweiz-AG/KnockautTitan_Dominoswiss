# Beispiele
Einfache Skriptbeispiele

### Ereignis via Variable De-/Aktivieren

* Ereignis erstellen
* Aktionsskript erstellen
* Schaltervariable erstellen, Profil "~Switch" auswählen und Aktionskript auswählen
 

Skriptinhalt:

    switch ($_IPS['VARIABLE']) {
    
        case 37036: //ID der Variable, welche das Ereignis schaltet
            IPS_SetEventActive(16105 , $_IPS['VALUE']); //Event welches geschaltet werden soll  
            break;
    
    }
    
    //Aktualisierung der Schaltvariable
    SetValue($_IPS['VARIABLE'], $_IPS['VALUE']);
