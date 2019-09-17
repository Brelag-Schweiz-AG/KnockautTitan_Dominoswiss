<?
class DominoSwissPIR extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("ID", 1);
		$this->RegisterPropertyInteger("Motiontimer", 300);

		$this->RegisterTimer("PIRTimer", 0, "BRELAG_StopPIRTimer(\$_IPS['TARGET']);");
		
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



	public function StopPIRTimer(){
		$this->SetTimerInterval("PIRTimer", 0);
		SetValue($this->GetIDForIdent("MotionValue"), false);
	}
	
	
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);
		if ($data->Values->ID == $this->ReadPropertyInteger("ID")) {
			switch ($data->Values->Command) {
				case 28:
					$motionTimer = $this->ReadPropertyInteger("Motiontimer");
					$this->SetTimerInterval("OffTimer", $motionTimer * 1000);
					SetValue($this->GetIDForIdent("MotionValue"), true);
					break;
			}
		}
	}

}
?>