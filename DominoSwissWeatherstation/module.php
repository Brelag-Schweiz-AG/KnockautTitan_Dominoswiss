<?
class DominoSwissWeatherstation extends IPSModule {
	const VALID_WIND_VALUES = [0, 10, 15, 20, 25, 30, 35, 40, 50, 60, 70, 80, 90, 100, 110, 120];
	const VALID_LIGHT_VALUES = [0, 5, 8, 10, 30, 100, 5000, 10000, 12000, 15000, 20000, 25000, 30000, 40000, 60000, 80000];
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		$this->RegisterPropertyInteger("ID", 1);
		
		$this->RegisterVariableInteger("LightValue", $this->Translate("Light"), "~Illumination", 0);
		$this->RegisterVariableFloat("WindValue", "Wind", "~WindSpeed.kmh", 0);
		$this->RegisterVariableBoolean("Raining", $this->Translate("Raining"), "~Raining", 0);
		$this->RegisterVariableFloat("GoldCap", "GoldCap", "", 0);

		$this->RegisterPropertyInteger("MaxWindValueDelta", 40);
		$this->RegisterPropertyInteger("MaxLightStepDelta", 2);

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
					$newStep = intval($data->Values->Value / 8);
					$newValue = $this->GetSimpleLightValue($newStep);

					$lightId = $this->GetIDForIdent("LightValue");
					$oldValue = GetValue($lightId);
					$oldStep = $this->GetStepFromLightValue($oldValue);
					
					$delta = $this->ReadPropertyInteger("MaxLightStepDelta");
					// We check that not more than $delta steps are changed at once. This is a simple way to avoid wrong values.
					if (abs($newStep - $oldStep) > $delta) {
						IPS_LogMessage("Unreasonable light step change", "Old: " . $oldValue . " New: " . $newValue . " Delta: " . $delta);
					} else {
						SetValue($lightId, $newValue);
					}
					break;
					
				case 33:
					$newValue = $this->GetSimpleWindValue(intval($data->Values->Value / 8));
					$windId = $this->GetIDForIdent("WindValue");
					$oldValue = GetValue($windId);
					$delta = $this->ReadPropertyInteger("MaxWindValueDelta");
					// We check that not more than $delta value is changed at once. This is a simple way to avoid wrong values.
					if (abs($newValue - $oldValue) > $delta) {
						IPS_LogMessage("Unreasonable wind value change", "Old: " . $oldValue . " New: " . $newValue . " Delta: " . $delta);
					} else {
						SetValue($windId, $newValue);
					}
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

	function GetStepFromWindValue($value) {
		$index = -1;
		foreach (self::VALID_WIND_VALUES as $key => $val) {
			if ($val == $value) {
				$index = $key;
				break;
			}
		}
		return $index;
	}

	function GetStepFromLightValue($value) {
		$index = -1;
		foreach (self::VALID_LIGHT_VALUES as $key => $val) {
			if ($val == $value) {
				$index = $key;
				break;
			}
		}
		return $index;
	}
	
	function GetSimpleWindValue($step) {
		return $this->validWindValues[$step];	
	}

	function GetSimpleLightValue($step) {
		return $this->validLightValues[$step];
	}
	
	// The following two function can calculate substeps for the light and wind values. But our weatherstation does not support this.
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
		$validValues = [0, 10, 15, 20, 25, 30, 35, 40, 50, 60, 70, 80, 90, 100, 110, 120];
		
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