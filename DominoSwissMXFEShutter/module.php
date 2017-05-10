<?
class DominoSwissMXFEShutter extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("ID", 1);
		$this->RegisterPropertyBoolean("Jalousie", false);
		
		if(!IPS_VariableProfileExists("BRELAG.Shutter")){
			IPS_CreateVariableProfile("BRELAG.Shutter", 0);
			IPS_SetVariableProfileIcon("BRELAG.Shutter", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Shutter", False, "Stopped", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Shutter", True, "Moving", "", -1);
		}
		
		if(!IPS_VariableProfileExists("BRELAG.ShutterMove")){
			IPS_CreateVariableProfile("BRELAG.ShutterMove", 1);
			IPS_SetVariableProfileValues("BRELAG.ShutterMove", 0, 2, 0);
			IPS_SetVariableProfileIcon("BRELAG.ShutterMove", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMove", 0, "Down", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMove", 1, "Stop", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMove", 2, "Up", "", -1);
		}
		
		$this->RegisterVariableBoolean("Status", "Status", "BRELAG.Shutter");
		$this->RegisterVariableInteger("Movement", "Movement", "BRELAG.ShutterMove");
		$this->EnableAction("Movement");
		
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
				case 2:
					if (GetValue($this->GetIDForIdent("Status"))) {
						SetValue($this->GetIDForIdent("Status"), false);
					} else {
						if ($this->ReadPropertyBoolean("Jalousie")) {
							SetValue($this->GetIDForIdent("Status"), true);
						} else {
							SetValue($this->GetIDForIdent("Status"), false);
						}
					}
					break;
				
				case 3:
				case 4:
					SetValue($this->GetIDForIdent("Status"), true);
					break;
				
				case 5:
					SetValue($this->GetIDForIdent("Status"), false);
					break;
				
				case 16:
					if (GetValue($this->GetIDForIdent("Status"))) {
						SetValue($this->GetIDForIdent("Status"), false);
					} else {
						SetValue($this->GetIDForIdent("Status"), true);
					}
					break;
				
				case 23:
						SetValue($this->GetIDForIdent("Status"), true);
					break;
			}
		}
	
	}

	public function RequestAction($Ident, $Value) {
		
		switch($Ident) {
			case 'Movement':
				switch ($Value) {
					case 0:
						$this->MoveDown();
						SetValue($this->GetIDForIdent("Movement"), 0);
						SetValue($this->GetIDForIdent("Status"), 1);
						break;
					
					case 1:
						$this->Stop();
						SetValue($this->GetIDForIdent("Movement"), 1);
						SetValue($this->GetIDForIdent("Status"), 0);
						break;
					
					case 2:
						$this->MoveUp();
						SetValue($this->GetIDForIdent("Movement"), 2);
						SetValue($this->GetIDForIdent("Status"), 1);
						break;
				}
				
				break;
			default:
				throw new Exception("Invalid ident");
		}
	}

	public function MoveUp() {
		
		$this->SendCommand(3);
		
	}

	public function MoveDown() {
		
		$this->SendCommand(4);
		
	}

	public function Stop() {
		
		$this->SendCommand(5);
		
	}

	public function RestorePosition() {
		
		$this->SendCommand(23);
		
	}

	public function RestorePositionBoth() {
		
		$this->SendCommand(16);
		
	}

	
	protected function SendCommand(int $Command, $Value = 0) {
		
		//Zur 1Wire Coontroller Instanz senden
		$id = $this->ReadPropertyInteger("ID");
		return $this->SendDataToParent(json_encode(Array("DataID" => "{C24CDA30-82EE-46E2-BAA0-13A088ACB5DB}", "ID" => $id, "Command" => $Command, "Value" => $Value)));
		
	}
}
?>