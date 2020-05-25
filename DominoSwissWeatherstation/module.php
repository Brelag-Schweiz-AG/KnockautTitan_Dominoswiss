<?
class DominoSwissWeatherstation extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		$this->RegisterPropertyInteger("ID", 1);
		
		$this->RegisterVariableInteger("LightValue", $this->Translate("Light"), "~Illumination", 0);
		$this->RegisterVariableFloat("WindValue", "Wind", "~WindSpeed.kmh", 0);
		$this->RegisterVariableBoolean("Raining", $this->Translate("Raining"), "~Raining", 0);
		$this->RegisterVariableFloat("GoldCap", "GoldCap", "", 0);
		
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
				case 32:
					SetValue($this->GetIDForIdent("LightValue"), $this->GetLightValue(intval($data->Values->Value / 8), ($data->Values->Value % 8)));
					break;
					
				case 33:
					SetValue($this->GetIDForIdent("WindValue"), $this->GetWindValue(intval($data->Values->Value / 8), ($data->Values->Value % 8)));
					break;
					
				case 34:
					if ($data->Values->Value >= 112) {
						SetValue($this->GetIDForIdent("Raining"), true);
					}
					else {
						SetValue($this->GetIDForIdent("Raining"), false);
					}
					break;
					
				case 39:
					SetValue($this->GetIDForIdent("GoldCap"), $data->Values->Value);
					break;
					
			}
		}
	
	}

	
	
	function GetLightValue($Category, $Modulo) {
		
		$base = 0;
		$step = 0;

		switch ($Category) {
			case 0:
				$base = 0;
				$step = 5;
				break;
				
			case 1:
				$base = 5;
				$step = 3;
				break;
			
			case 2:
				$base = 8;
				$step = 2;
				break;
			
			case 3:
				$base = 10;
				$step = 20;
				break;
			
			case 4:
				$base = 30;
				$step = 70;
				break;
			
			case 5:
				$base = 100;
				$step = 4900;
				break;
			
			case 6:
				$base = 5000;
				$step = 5000;
				break;
			
			case 7:
				$base = 10000;
				$step = 2000;
				break;
			
			case 8:
				$base = 12000;
				$step = 3000;
				break;
			
			case 9:
				$base = 15000;
				$step = 5000;
				break;
			
			case 10:
				$base = 20000;
				$step = 5000;
				break;
			
			case 11:
				$base = 25000;
				$step = 5000;
				break;
			
			case 12:
				$base = 30000;
				$step = 10000;
				break;
			
			case 13:
				$base = 40000;
				$step = 20000;
				break;
			
			case 14:
				$base = 60000;
				$step = 20000;
				break;
			
			case 15:
				$this->SendDebug("ValuesID", "häh", 0);
				return 80000;
		}
		
		return $base + $Modulo * ($step / 8);
	}

	
	
	function GetWindValue($Category, $Modulo) {
		
		$base = 0;
		$step = 0;
		
		switch ($Category) {
			case 0:
				$base = 0;
				$step = 10;
				break;
				
			case 1:
				$base = 10;
				$step = 5;
				break;
			
			case 2:
				$base = 15;
				$step = 5;
				break;
			
			case 3:
				$base = 20;
				$step = 5;
				break;
			
			case 4:
				$base = 25;
				$step = 5;
				break;
			
			case 5:
				$base = 30;
				$step = 5;
				break;
			
			case 6:
				$base = 35;
				$step = 5;
				break;
			
			case 7:
				$base = 40;
				$step = 10;
				break;
			
			case 8:
				$base = 50;
				$step = 10;
				break;
			
			case 9:
				$base = 60;
				$step = 10;
				break;
			
			case 10:
				$base = 70;
				$step = 10;
				break;
			
			case 11:
				$base = 80;
				$step = 10;
				break;
			
			case 12:
				$base = 90;
				$step = 10;
				break;
			
			case 13:
				$base = 100;
				$step = 10;
				break;
			
			case 14:
				$base = 110;
				$step = 10;
				break;
			
			case 15:
				return 120;
		}
		
		return $base + $Modulo * ($step / 8);
		
	}

}
?>