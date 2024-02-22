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
					if ($newStep < 0 || $newStep > 15) {
						IPS_LogMessage("Invalid new light step", "Step: " . $newStep);
						return;
					}
					$newValue = $this->GetLightValue($newStep);

					$lightId = $this->GetIDForIdent("LightValue");
					$oldValue = GetValue($lightId);
					$oldStep = $this->GetStepFromLightValue($oldValue);
					if ($oldStep == -1) {
						// Old value is not valid for some reason (manually overwritten?). Just overwrite it and return
						IPS_LogMessage("Invalid old light value", "Old: " . $oldValue);
						SetValue($lightId, $newValue);
						return;
					}
					
					$delta = $this->ReadPropertyInteger("MaxLightStepDelta");
					// We check that not more than $delta steps are changed at once. This is a simple way to avoid wrong values.
					if (abs($newStep - $oldStep) > $delta) {
						IPS_LogMessage("Unreasonable light step change", "Old: " . $oldValue . " New: " . $newValue . " Delta: " . $delta);
					} else {
						SetValue($lightId, $newValue);
					}
					break;
					
				case 33:
					$newStep = intval($data->Values->Value / 8);
					if ($newStep < 0 || $newStep > 15) {
						IPS_LogMessage("Invalid new wind step", "Step: " . $newStep);
						return;
					}
					$newValue = $this->GetWindValue($newStep);
					$windId = $this->GetIDForIdent("WindValue");
					$oldValue = GetValue($windId);
					if ($oldValue < 0) {
						// Old value is not valid for some reason (manually overwritten?). Just overwrite it and return
						SetValue($windId, $newValue);
						return;
					}
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

	private function GetStepFromLightValue($value) {
		$index = -1;
		foreach (self::VALID_LIGHT_VALUES as $key => $val) {
			if ($val == $value) {
				$index = $key;
				break;
			}
		}
		return $index;
	}
	
	private function GetWindValue($step) {
		return self::VALID_WIND_VALUES[$step];	
	}

	private function GetLightValue($step) {
		return self::VALID_LIGHT_VALUES[$step];
	}
}
?>