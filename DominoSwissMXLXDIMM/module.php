<?
include_once __DIR__ . '/../DominoSwissMXRLUP/module.php';

class DominoSwissMXDIMM extends DominoSwissMXRLUP {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.

		$this->MaintainVariable("SavedValue", $this->Translate("SavedValue"), 1, "~Intensity.100", 10, true);
		IPS_SetHidden($this->GetIDForIdent("SavedValue"), true);
		
		$this->RegisterVariableInteger("LastValue", $this->Translate("LastValue"), "~Intensity.100", 8);
		IPS_SetHidden($this->GetIDForIdent("LastValue"), true);
		
		$this->RegisterVariableInteger("Intensity", $this->Translate("Intensity"), "~Intensity.100", 5);
		$this->EnableAction("Intensity");
	}
	
	
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);

		//No ID check necessary, check is done by receiveFilter "DominoSwissBase.php->ApplyChanges()"
		if ($data->Values->Priority >= $this->GetHighestLockLevel()) {
			switch ($data->Values->Command) {
				case 1:
					$LastValue = GetValue($this->GetIDForIdent("LastValue"));
					if (!GetValue($this->GetIDForIdent("Status"))) {
							SetValue($this->GetIDForIdent("Status"), true);
							SetValue($this->GetIDForIdent("Intensity"), $LastValue);
					}
					SetValue($this->GetIDForIdent("Switch"), true);
					break;

				case 3:
					SetValue($this->GetIDForIdent("LastValue"), GetValue($this->GetIDForIdent("Intensity")));
					SetValue($this->GetIDForIdent("Status"), true);
					SetValue($this->GetIDForIdent("Intensity"), 100);
					SetValue($this->GetIDForIdent("Switch"), true);
					break;

				case 2:
				case 4:
					SetValue($this->GetIDForIdent("LastValue"), GetValue($this->GetIDForIdent("Intensity")));
					SetValue($this->GetIDForIdent("Status"), false);
					SetValue($this->GetIDForIdent("Intensity"), 0);
					SetValue($this->GetIDForIdent("Switch"), false);
					break;
				
				case 6:
					SetValue($this->GetIDForIdent("LastValue"), GetValue($this->GetIDForIdent("Intensity")));
					$invertedStatus = !(GetValue($this->GetIDForIdent("Status")));
					SetValue($this->GetIDForIdent("Status"), $invertedStatus);
					if ($invertedStatus) {
						SetValue($this->GetIDForIdent("Intensity"), 100);
					}
					else {
						SetValue($this->GetIDForIdent("Intensity"), 0);
					}
					break;

				case 15:
					//only save if its our ID
					if ($data->Values->ID == $this->ReadPropertyInteger("ID")) {
						SetValue($this->GetIDForIdent("SavedValue"), GetValue($this->GetIDForIdent("Intensity")));
						SetValue($this->GetIDForIdent("Saving"), 1);
					}
					break;

				case 16:
				case 23:
					if ($data->Values->Value != -1) {
						$savedValue = $data->Values->Value;
					}
					else {
						$savedValue = GetValue($this->GetIDForIdent("SavedValue"));
					}					
					SetValue($this->GetIDForIdent("Intensity"), $savedValue);
					
					if ($savedValue > 0) {
						SetValue($this->GetIDForIdent("Status"), true);
					}
					else {
						SetValue($this->GetIDForIdent("Status"), false);
					}
					SetValue($this->GetIDForIdent("Saving"), 0);
					break;

				case 17:
					SetValue($this->GetIDForIdent("LastValue"), GetValue($this->GetIDForIdent("Intensity")));
					$intensityValue = ($data->Values->Value * 100) / 63;
					SetValue($this->GetIDForIdent("Intensity"), $intensityValue);
					if ($intensityValue > 0) {
						SetValue($this->GetIDForIdent("Status"), true);
					}
					else {
						SetValue($this->GetIDForIdent("Status"), false);
					}
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
				if ($Value) {
					if (!GetValue($this->GetIDForIdent("Status"))) {
						$this->PulseUp(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
					}
				}
				else {
					if (GetValue($this->GetIDForIdent("Status"))) {
						$this->ContinuousDown(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
					}
				}
				break;
				
			case "Intensity":
				$this->Move(GetValue($this->GetIDForIdent("SendingOnLockLevel")), $Value);
				break;
			
			default:
				parent::RequestAction($Ident, $Value);
		}
	}
	
	
	
	public function Move(int $Priority, int $Value){
		
		if ($Value < 0) {
			$Value = 0;
		}
		else if ($Value > 100) {
			$Value = 100;
		}
		
		$Value = round(($Value * 63) / 100, 0);
		$this->SendCommand(17, $Value, $Priority);
		
	}
	
}
?>