<?
include_once __DIR__ . '/../DominoSwissLXRLUP/module.php';

class DominoSwissLXDIMM extends DominoSwissLXRLUP {
	
	public function Create(){
		//Never delete this line!
		parent::Create();

		$this->RegisterVariableInteger("SavedValue", $this->Translate("SavedValue"), "~Intensity.100", 10);
		IPS_SetHidden($this->GetIDForIdent("SavedValue"), true);
		
		$this->RegisterVariableInteger("LastValue", $this->Translate("LastValue"), "~Intensity.100", 8);
		IPS_SetHidden($this->GetIDForIdent("LastValue"), true);
		
		$this->RegisterVariableInteger("Intensity", $this->Translate("Intensity"), "~Intensity.100", 6);
		$this->EnableAction("Intensity");
	}
	
	
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);

		//No ID check necessary, check is done by receiveFilter "DominoSwissBase.php->ApplyChanges()"
		if ($data->Values->Priority >= $this->GetHighestLockLevel()) {
			switch ($data->Values->Command) {
				case 1: //PulseUp
					SetValue($this->GetIDForIdent("Status"), true);
					SetValue($this->GetIDForIdent("Switch"), true);
					SetValue($this->GetIDForIdent("Intensity"), 0); //Letzter Wert (evtl. unbekannt da eine Dimmfahrt)
					break;

				case 2: //PulseDown
					SetValue($this->GetIDForIdent("Status"), false);
					SetValue($this->GetIDForIdent("Switch"), false);
					SetValue($this->GetIDForIdent("Intensity"), 0);
					break;

				case 3: //ContinuousUp
					SetValue($this->GetIDForIdent("LastValue"), GetValue($this->GetIDForIdent("Intensity")));
					SetValue($this->GetIDForIdent("Status"), true);
					SetValue($this->GetIDForIdent("Switch"), true);
					SetValue($this->GetIDForIdent("Intensity"), 100);
					break;

				case 4: //ContinuousDown
					//Only save intensity if we have a proper intensity (fix issues with double off)
					if(GetValue($this->GetIDForIdent("Intensity")) > 0) {
						SetValue($this->GetIDForIdent("LastValue"), GetValue($this->GetIDForIdent("Intensity")));
					}
					SetValue($this->GetIDForIdent("Status"), false);
					SetValue($this->GetIDForIdent("Switch"), false);
					SetValue($this->GetIDForIdent("Intensity"), 0);
					break;
				
				case 6: //Toggle
					//Fetch the last value and update the last value to the current one
					$lastValue = GetValue($this->GetIDForIdent("LastValue"));
					SetValue($this->GetIDForIdent("LastValue"), GetValue($this->GetIDForIdent("Intensity")));

					//Set the value to last value and toggle the status
					SetValue($this->GetIDForIdent("Status"), !(GetValue($this->GetIDForIdent("Status"))));
					SetValue($this->GetIDForIdent("Switch"), !(GetValue($this->GetIDForIdent("Switch"))));
					SetValue($this->GetIDForIdent("Intensity"), $lastValue);
					break;
					
				case 15: //PosSaveBoth
					if ($data->Values->ID == $this->ReadPropertyInteger("ID")) {
						SetValue($this->GetIDForIdent("SavedValue"), GetValue($this->GetIDForIdent("Intensity")));
						SetValue($this->GetIDForIdent("Saving"), 1);
					}
					$this->SaveIntoArray($data->Values->ID);
					break;

				case 16: //PosRestoreBoth
				case 23: //PosRestore
					$savedValue = $this->LoadOutOfArray($data->Values->ID);

					SetValue($this->GetIDForIdent("Intensity"), $savedValue);

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

				case 17: //PosByVal
					SetValue($this->GetIDForIdent("LastValue"), GetValue($this->GetIDForIdent("Intensity")));
					$intensityValue = ($data->Values->Value * 100) / 63;
					SetValue($this->GetIDForIdent("Intensity"), $intensityValue);
					if ($intensityValue > 0) {
						SetValue($this->GetIDForIdent("Status"), true);
						SetValue($this->GetIDForIdent("Switch"), true);
					}
					else {
						SetValue($this->GetIDForIdent("Status"), false);
						SetValue($this->GetIDForIdent("Switch"), false);
					}
					break;

				case 20: //LockLeveSet
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
				if ($Value) {
					if(GetValue($this->GetIDForIdent("Status"))) {
						//We want to use Move to switch to the same value. Just send the same value
						$this->Move(GetValue($this->GetIDForIdent("SendingOnLockLevel")), GetValue($this->GetIDForIdent("LastValue")));
					} else if(GetValue($this->GetIDForIdent("LastValue")) > 0) {
						//We want to use Move to switch on with last value. ContinuousUp would switch on with 100%
						$this->Move(GetValue($this->GetIDForIdent("SendingOnLockLevel")), GetValue($this->GetIDForIdent("LastValue")));
					} else {
						//If the LastValue was zero dimm to 100%. The users want to switch on and setting to lastValue = 0 would leave it off
						$this->ContinuousUp(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
					}
				}
				else {
					$this->ContinuousDown(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
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
		$this->SendCommand( 1, 17, $Value, $Priority);
		
	}

	
	
	private function SaveIntoArray($ID) {

		$savedValuesIDs = json_decode(GetValue($this->GetIDForIdent("SavedValuesArray")), true);
		$savedValuesIDs[$ID] = GetValue($this->GetIDForIdent("Intensity"));

		SetValue($this->GetIDForIdent("SavedValuesArray"), json_encode($savedValuesIDs));
	}



	private function LoadOutOfArray($ID) {

		$savedValuesIDs = json_decode(GetValue($this->GetIDForIdent("SavedValuesArray")), true);
		return $savedValuesIDs[$ID];

	}
	
}
?>
