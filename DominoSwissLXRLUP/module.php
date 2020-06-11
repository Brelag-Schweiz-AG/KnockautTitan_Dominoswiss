<?
include_once __DIR__ . '/../libs/DominoSwissBase.php';

class DominoSwissLXRLUP extends DominoSwissBase {
	
	public function Create(){
		//Never delete this line!
		parent::Create();

        $this->RegisterPropertyBoolean("ShowStatus", false);

        if(!IPS_VariableProfileExists("BRELAG.Switch")) {
			IPS_CreateVariableProfile("BRELAG.Switch", 0);
			IPS_SetVariableProfileIcon("BRELAG.Switch", "Power");
			IPS_SetVariableProfileAssociation("BRELAG.Switch", 0, $this->Translate("Off"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Switch", 1, $this->Translate("On"), "", -1);
		}

		if(!IPS_VariableProfileExists("BRELAG.Status")) {
			IPS_CreateVariableProfile("BRELAG.Status", 0);
			IPS_SetVariableProfileIcon("BRELAG.Status", "Power");
			IPS_SetVariableProfileAssociation("BRELAG.Status", 0, $this->Translate("Off"), "", 0xFF0000);
			IPS_SetVariableProfileAssociation("BRELAG.Status", 1, $this->Translate("On"), "", 0x00D500);
		}

		$this->MaintainVariable("SavedValue", $this->Translate("SavedValue"), 0, "BRELAG.Status", 10, true);
		IPS_SetHidden($this->GetIDForIdent("SavedValue"), true);

		$this->RegisterVariableBoolean("Switch",  $this->Translate("Switch"), "BRELAG.Switch", 5);
		$this->EnableAction("Switch");

		//Keep this only for legacy reasons. We hide it from visualization by default
		$this->RegisterVariableBoolean("Status", "Status", "BRELAG.Status", 1);

		$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate
	}

	
	
	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	
	
	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();

        if ($this->ReadPropertyBoolean("ShowStatus")) {
            IPS_SetHidden($this->GetIDForIdent("Status"), false);
        }
        else {
            IPS_SetHidden($this->GetIDForIdent("Status"), true);
        }

	}

	
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);

		//No ID check necessary, check is done by receiveFilter "DominoSwissBase.php->ApplyChanges()"
		if ($data->Values->Priority >= $this->GetHighestLockLevel()) {
			switch ($data->Values->Command) {
				case 1: //PulseUp
				case 3: //ContinuousUp
					SetValue($this->GetIDForIdent("Status"), true);
					SetValue($this->GetIDForIdent("Switch"), true);
					break;

				case 2: //PulseDown
				case 4: //ContinuousDown
					SetValue($this->GetIDForIdent("Status"), false);
					SetValue($this->GetIDForIdent("Switch"), false);
					break;
				
				case 6: //Toggle
					SetValue($this->GetIDForIdent("Status"), !(GetValue($this->GetIDForIdent("Status"))));
                    SetValue($this->GetIDForIdent("Switch"), !(GetValue($this->GetIDForIdent("Switch"))));
					break;
				
				case 15: //PosSaveBoth
					if ($data->Values->ID == $this->ReadPropertyInteger("ID")) {
						SetValue($this->GetIDForIdent("SavedValue"), GetValue($this->GetIDForIdent("Status")));
						SetValue($this->GetIDForIdent("Saving"), 1);
					}
					$this->SaveIntoArray($data->Values->ID);
					break;

				case 16: //PosRestoreBoth
				case 23: //PosRestore
					$savedValue = $this->LoadOutOfArray($data->Values->ID);
	
					if ($savedValue > 0) {
						SetValue($this->GetIDForIdent("Status"), true);
                        SetValue($this->GetIDForIdent("Switch"), true);
					}
					else {
						SetValue($this->GetIDForIdent("Status"), false);
                        SetValue($this->GetIDForIdent("Switch"), false);
					}
					SetValue($this->GetIDForIdent("Saving"), 0);
					break;

				case 20: //LockLevelSet
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), true);
					break;

				case 21: //LockLevelClear
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), false);
					break;
			}
		}

	}

	
	
	public function RequestAction($Ident, $Value) {

		switch($Ident) {
			case "Switch":
				if($Value) {
                    $this->ContinuousUp(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
				}
				else {
                    $this->ContinuousDown(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
				}
				break;

			default:
				parent::RequestAction($Ident, $Value);
		}
	}

	
	
	private function SaveIntoArray($ID) {

		$savedValuesIDs = json_decode(GetValue($this->GetIDForIdent("SavedValuesArray")), true);
		$savedValuesIDs[$ID] = GetValue($this->GetIDForIdent("Status"));

		SetValue($this->GetIDForIdent("SavedValuesArray"), json_encode($savedValuesIDs));
	}

	
	
	private function LoadOutOfArray($ID) {

		$savedValuesIDs = json_decode(GetValue($this->GetIDForIdent("SavedValuesArray")), true);
		return $savedValuesIDs[$ID];
		
	}

}
?>