<?
include_once __DIR__ . '/../DominoSwissMXRLUP/module.php';

class DominoSwissMXDIMM extends DominoSwissMXRLUP {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.

		$this->MaintainVariable("SavedValue", $this->Translate("SavedValue"), 1, "~Intensity.100", 0, true);
		$this->RegisterVariableInteger("Intensity", $this->Translate("Intensity"), "~Intensity.100", 0);
		$this->EnableAction("Intensity");
	}
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);

		if(($data->Values->ID == $this->ReadPropertyInteger("ID")) && ($data->Values->Priority >= $this->GetHighestLockLevel())) {
			switch($data->Values->Command) {
				case 1:
				case 3:
					SetValue($this->GetIDForIdent("Status"), true);
					SetValue($this->GetIDForIdent("Intensity"), 100);
					break;

				case 2:
				case 4:
					SetValue($this->GetIDForIdent("Status"), false);
					SetValue($this->GetIDForIdent("Intensity"), 0);
					break;
				
				case 6:
					$invertedStatus = !(GetValue($this->GetIDForIdent("Status")));
					SetValue($this->GetIDForIdent("Status"), $invertedStatus);
					break;

				case 15:
					SetValue($this->GetIDForIdent("SavedValue"), GetValue($this->GetIDForIdent("Intensity")));
					break;

				case 16:
				case 23:
					SetValue($this->GetIDForIdent("Intensity"), GetValue($this->GetIDForIdent("SavedValue")));
					break;

				case 17:
					$intensityValue =($data->Values->Value * 100) /63;
					SetValue($this->GetIDForIdent("Intensity"), $intensityValue);
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
			case "Status":
				if($Value) {
					$this->ContinuousUp(0);
				} else {
					$this->ContinuousDown(0);
				}
				break;

			case "Saving":
				switch ($Value){
					case 0:
						$this->Save(0);
						break;

					case 1:
						$this->RestorePosition(0);
						break;

					case 2:
						$this->RestorePositionBoth(0);
						break;
				}
				break;

			case "SendingOnLockLevel":
				SetValue($this->GetIDForIdent("SendingOnLockLevel"), $Value);
				break;

			case "LockLevel0":
			case "LockLevel1":
			case "LockLevel2":
			case "LockLevel3":
				if($Value) {
					$this->LockLevelSet(substr($Ident, -1, 1));
				} else {
					$this->LockLevelClear(substr($Ident, -1, 1));
				}
				break;
				
			case "Intensity":
				$this->Move(0, $Value);
				break;
			
			default:
				throw new Exception("Invalid ident");
		}
	}
	
	public function Move(int $Priority, int $Value){
		
		if ($Value < 0) {
			$Value = 0;
		} else if ($Value > 100) {
			$Value = 100;
		}
		
		$Value = round(($Value * 63) / 100, 0);
		$this->SendCommand(17, $Value, $Priority);
		
	}
	
}
?>