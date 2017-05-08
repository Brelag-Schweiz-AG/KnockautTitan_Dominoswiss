<?
class DominoSwissMXFS extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("ID", 1);
		
		$this->RegisterVariableBoolean("Status", "Status", "~Switch");
		$this->EnableAction("Status");
		
		$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate
	}

	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();
		
		//Apply filter
		//$this->SetReceiveDataFilter(".*\"ID=\":". $this->ReadPropertyInteger("ID") .".*");
		
	}

	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
				
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);
		
		if($data->Values->ID == $this->ReadPropertyInteger("ID")) {
			switch($data->Values->Command) {
				case 1:
				case 3:
					SetValue($this->GetIDForIdent("Status"), true);
					break;
				case 2:
				case 4:
					SetValue($this->GetIDForIdent("Status"), false);
					break;
			}
		}

	}

	public function RequestAction($Ident, $Value) {
		
		switch($Ident) {
			case "Status":
				if($Value) {
					$this->SendUpImpulse();
				} else {
					$this->SendDownImpulse();
				}
				break;
			default:
				throw new Exception("Invalid ident");
		}
	}

	public function SendUpImpulse(){
		
		$this->SendCommand(1);
		
	}

	public function SendUpContinious(){
		
		$this->SendCommand(3);
		
	}

	public function SendDownImpulse(){
		
		$this->SendCommand(4);
		
	}

	public function SendDownContinious(){
		
		$this->SendCommand(4);
		
	}

	public function SendStop(){
		
		$this->SendCommand(5);
		
	}

	public function SendToggle(){
		
		$this->SendCommand(6);
		
	}

	private function SendCommand(int $Command) {
		
		//Zur 1Wire Coontroller Instanz senden
		$id = $this->ReadPropertyInteger("ID");
		return $this->SendDataToParent(json_encode(Array("DataID" => "{C24CDA30-82EE-46E2-BAA0-13A088ACB5DB}", "ID" => $id, "Command" => $Command)));
		
	}
}
?>