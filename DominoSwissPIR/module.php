<?
class DominoSwissPIR extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("ID", 1);
		
		$this->RegisterVariableInteger("SensoractivityValue", $this->Translate("Sensoractivity"), "", 0);
		$this->RegisterVariableBoolean("MotionValue", $this->Translate("Motion"), "~Motion", 0);
		
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
				case 28:
					//Hier den Wert von Sensoracticity eintragen
					//eGate CheckNR muss noch eingefügt werden
					//SetValue($this->GetIDForIdent("LightValue"), $this->GetLightValue(intval($data->Values->Value / 8), ($data->Values->Value % 8)));
					break;
			}
		}
	}

}
?>