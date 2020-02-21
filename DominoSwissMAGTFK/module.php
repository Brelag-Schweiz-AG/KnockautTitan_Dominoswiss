<?
class DominoSwissMAGTFK extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("ID", 1);

		if (!IPS_VariableProfileExists("BRELAG.MAGContact")) {
			IPS_CreateVariableProfile("BRELAG.MAGContact", 0);
			IPS_SetVariableProfileIcon("BRELAG.MAGContact", "");
			IPS_SetVariableProfileAssociation("BRELAG.MAGContact", 0, $this->Translate("Closed"), "LockClosed", 0x00D500);
			IPS_SetVariableProfileAssociation("BRELAG.MAGContact", 1, $this->Translate("Open"), "LockOpen", 0xFF0000);
		}
		
		$this->RegisterVariableBoolean("StateValue", $this->Translate("State"), "BRELAG.MAGContact", 0);
		
		$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate
	}	
	
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);
		if ($data->Values->ID == $this->ReadPropertyInteger("ID")) {
			switch ($data->Values->Command) {
				//TODO Richtiger Command muss noch eingetragen werden -> Kontakt mit Brelag nötig
				case 30:
					SetValue($this->GetIDForIdent("StateValue"), true);
					break;
			}
		}
	}

}