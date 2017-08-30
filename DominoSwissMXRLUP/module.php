<?
include_once __DIR__ . '/../libs/DominoSwissBase.php';

class DominoSwissMXRLUP extends DominoSwissBase {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.

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

		$this->RegisterVariableBoolean("Switch",  $this->Translate("Switch"), "BRELAG.Switch", 6);
		$this->EnableAction("Switch");

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

	}

	
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);

		//No ID check necessary, check is done by receiveFilter "DominoSwissBase.php->ApplyChanges()"
		if ($data->Values->Priority >= $this->GetHighestLockLevel()) {
			switch ($data->Values->Command) {
				case 1:
				case 3:
					SetValue($this->GetIDForIdent("Status"), true);
					SetValue($this->GetIDForIdent("Switch"), true);
					break;

				case 2:
				case 4:
					SetValue($this->GetIDForIdent("Status"), false);
					SetValue($this->GetIDForIdent("Switch"), false);
					break;
				
				case 6:
					$invertedStatus = !(GetValue($this->GetIDForIdent("Status")));
					SetValue($this->GetIDForIdent("Status"), $invertedStatus);
					break;
				
				case 15:
					//only save if its our ID
					if ($data->Values->ID == $this->ReadPropertyInteger("ID")) {
						SetValue($this->GetIDForIdent("SavedValue"), GetValue($this->GetIDForIdent("Status")));
						SetValue($this->GetIDForIdent("Saving"), 1);
					}
					break;

				case 16:
				case 23:
					SetValue($this->GetIDForIdent("Status"), GetValue($this->GetIDForIdent("SavedValue")));
					SetValue($this->GetIDForIdent("Saving"), 0);
					break;

				case 20:
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), true);
					break;

				case 21:
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), false);
					break;
			}
		}

	}

	public function RequestAction($Ident, $Value) {

		switch($Ident) {
			case "Switch":
				if($Value) {
					//if(!GetValue($this->GetIDForIdent("Status"))) {
						$this->PulseUp(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
					//}
				}
				else {
					//if(GetValue($this->GetIDForIdent("Status"))) {
						$this->ContinuousDown(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
					//}
				}
				break;

			default:
				parent::RequestAction($Ident, $Value);
		}
	}

}
?>