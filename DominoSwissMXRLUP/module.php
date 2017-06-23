<?
include_once __DIR__ . '/../libs/DominoSwissBase.php';

class DominoSwissMXRLUP extends DominoSwissBase {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.

		$this->MaintainVariable("SavedValue", $this->Translate("SavedValue"), 0, "~Switch", 0, true);
		$this->RegisterVariableBoolean("Status", "Status", "~Switch", 0);
		$this->RegisterVariableBoolean("Switch",  $this->Translate("Switch"), "~Switch", 0);
		$this->EnableAction("Switch");

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

		if(($data->Values->ID == $this->ReadPropertyInteger("ID")) && ($data->Values->Priority >= $this->GetHighestLockLevel())) {
			switch($data->Values->Command) {
				case 1:
				case 3:
					SetValue($this->GetIDForIdent("Status"), true);
					break;

				case 2:
				case 4:
					SetValue($this->GetIDForIdent("Status"), false);
					break;
				
				case 6:
					$invertedStatus = !(GetValue($this->GetIDForIdent("Status")));
					SetValue($this->GetIDForIdent("Status"), $invertedStatus);
					break;

				case 15:
					SetValue($this->GetIDForIdent("SavedValue"), GetValue($this->GetIDForIdent("Status")));
					break;

				case 16:
				case 23:
					SetValue($this->GetIDForIdent("Status"), GetValue($this->GetIDForIdent("SavedValue")));
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
					if(!GetValue($this->GetIDForIdent("Status"))) {
						$this->PulseUp(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
					}
				} else {
					if(GetValue($this->GetIDForIdent("Status"))) {
						$this->ContinuousDown(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
					}
				}
				break;

			default:
				parent::RequestAction($Ident, $Value);
		}
	}

}
?>