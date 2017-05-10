<?

include_once '/../DominoSwissMXRLUP/module.php';

class DominoSwissMXDIMM extends DominoSwissMXRLUP {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("ID", 1);
		
		$this->RegisterVariableBoolean("Status", "Status", "~Switch");
		$this->EnableAction("Status");
		
		$this->RegisterVariableInteger("Intensity", "Intensity", "~Intensity.100");
		$this->EnableAction("Intensity");
		
		$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate
	}
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);
		
		if($data->Values->ID == $this->ReadPropertyInteger("ID")) {
			switch($data->Values->Command) {
				case 1:
				case 3:
				case 16:
				case 23:
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
				
				case 17:
					$intensityValue =($data->Values->Value * 100) /63;
					SetValue($this->GetIDForIdent("Intensity"), $intensityValue);
					break;
			}
		}
	
	}
	
	public function RequestAction($Ident, $Value) {
		
		switch($Ident) {
			case "Status":
				if($Value) {
					$this->SwitchMode(true);
				} else {
					$this->SwitchMode(false);
				}
				break;
				
			case "Intensity":
				$this->Move($Value);
				break;
			
			default:
				throw new Exception("Invalid ident");
		}
	}
	
	public function Move(int $Value){
		
		if ($Value < 0) {
			$Value = 0;
		} else if ($Value > 100) {
			$Value = 100;
		}
		
		$Value = round(($Value * 63) / 100, 0);
		$this->SendCommand(17, $Value);
		
	}
	
}
?>