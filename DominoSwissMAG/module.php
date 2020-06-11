<?
class DominoSwissMAG extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		$this->RegisterPropertyInteger("ID", 1);

		if (!IPS_VariableProfileExists("BRELAG.MAGEvent")) {
			IPS_CreateVariableProfile("BRELAG.MAGEvent", 1);
			IPS_SetVariableProfileValues("BRELAG.MAGEvent", 0, 1, 0);
			IPS_SetVariableProfileIcon("BRELAG.MAGEvent", "");
			IPS_SetVariableProfileAssociation("BRELAG.MAGEvent", 0, $this->Translate("Nothing"), "Ok", 0x787875);
			IPS_SetVariableProfileAssociation("BRELAG.MAGEvent", 1, "Sabotage", "Alert", 0xFF0000);
		}

		if (!IPS_VariableProfileExists("BRELAG.MAGContact")) {
			IPS_CreateVariableProfile("BRELAG.MAGContact", 0);
			IPS_SetVariableProfileIcon("BRELAG.MAGContact", "");
			IPS_SetVariableProfileAssociation("BRELAG.MAGContact", 0, $this->Translate("Closed"), "LockClosed", 0x00D500);
			IPS_SetVariableProfileAssociation("BRELAG.MAGContact", 1, $this->Translate("Open"), "LockOpen", 0xFF0000);
		}

		if (!IPS_VariableProfileExists("BRELAG.MAGBattery")) {
			IPS_CreateVariableProfile("BRELAG.MAGBattery", 0);
			IPS_SetVariableProfileIcon("BRELAG.MAGBattery", "");
			IPS_SetVariableProfileAssociation("BRELAG.MAGBattery", 0, "Ok", "Battery", 0x00D500);
			IPS_SetVariableProfileAssociation("BRELAG.MAGBattery", 1, $this->Translate("Battery Low"), "Alert", 0xFF0000);
		}

		if (!IPS_VariableProfileExists("BRELAG.MAGLifesign")) {
			IPS_CreateVariableProfile("BRELAG.MAGLifesign", 0);
			IPS_SetVariableProfileIcon("BRELAG.MAGLifesign", "");
			IPS_SetVariableProfileAssociation("BRELAG.MAGLifesign", 0, "Ok", "Ok", 0x00D500);
			IPS_SetVariableProfileAssociation("BRELAG.MAGLifesign", 1, $this->Translate("No"), "Alert", 0xFF0000);
		}
		
		$this->RegisterVariableBoolean("Contact", "Status", "BRELAG.MAGContact", 1);
		$this->RegisterVariableBoolean("Battery", $this->Translate("Battery"), "BRELAG.MAGBattery", 3);
		$this->RegisterVariableInteger("Event", $this->Translate("Event"), "BRELAG.MAGEvent", 2);
		$this->RegisterVariableBoolean("Lifesign", $this->Translate("Lifesign"), "BRELAG.MAGLifesign", 4);

		$this->RegisterTimer("CheckLifesignTimer", 60 * 60 * 1000, 'BRELAG_CheckLifesign($_IPS[\'TARGET\']);');
		
		$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate
	}

	
	
	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	
	
	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();
		
	}

	
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);
		
		if ($data->Values->ID == $this->ReadPropertyInteger("ID")) {
			switch ($data->Values->Command) {
				case 46:
					SetValue($this->GetIDForIdent("Contact"), $data->Values->Value & 0x01);
					SetValue($this->GetIDForIdent("Battery"), ($data->Values->Value >> 1) & 0x01);
					$this->SetTimerInterval("CheckLifesignTimer", 60 * 60 * 1000);
					SetValue($this->GetIDForIdent("Lifesign"), false);
					break;
					
				case 47:
					SetValue($this->GetIDForIdent("Event"), $data->Values->Value);
					$this->SetTimerInterval("CheckLifesignTimer", 60 * 60 * 1000);
					SetValue($this->GetIDForIdent("Lifesign"), false);
					break;
				
			}
		}
	}
	
	
	
	public function CheckLifesign() {
		
		SetValue($this->GetIDForIdent("Lifesign"), true);
	}
	
}
?>