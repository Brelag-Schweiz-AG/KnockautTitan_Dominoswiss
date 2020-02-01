<?
class DominoSwissUTC extends IPSModule {
	
	public function Create() {
		parent::Create();
		
		$this->RegisterPropertyInteger("ID", 0);
		
		//We have imported this module from: https://github.com/DonCri/DominoswissUTC
		//Do not change to upper case for legacy reasons
		$this->RegisterVariableInteger("Light", $this->Translate("Light"), "~Illumination", 0);
		$this->RegisterVariableFloat("Temperature", $this->Translate("Temperature"), "~Temperature", 1);
		 
		$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate

	}

	
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);
		if($data->Values->ID == $this->ReadPropertyInteger("ID"))
		{
			switch($data->Values->Command) {
				case 35:
					SetValue($this->GetIDForIdent("Temperature"), $data->Values->Value/2-20);
					break;
					
				case 36:
					SetValue($this->GetIDForIdent("Light"), 0.1*10**(0.05*$data->Values->Value));
					break;
			}
		}
		
	}

}