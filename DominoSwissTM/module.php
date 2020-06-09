<?
include_once __DIR__ . '/../libs/DominoSwissBase.php';

class DominoSwissTM extends IPSModule {
	
	public function Create() {
		parent::Create();

        if (!IPS_VariableProfileExists("BRELAG.ThermostatMiniTargetTemperature")) {
            IPS_CreateVariableProfile("BRELAG.ThermostatMiniTargetTemperature", 2);
            IPS_SetVariableProfileValues("BRELAG.ThermostatMiniTargetTemperature", 0, 7, 0);
            IPS_SetVariableProfileIcon("BRELAG.ThermostatMiniTargetTemperature", "Temperature");
            IPS_SetVariableProfileAssociation("BRELAG.ThermostatMiniTargetTemperature", 0, "25 °C", "", -1);
            IPS_SetVariableProfileAssociation("BRELAG.ThermostatMiniTargetTemperature", 1, "24 °C", "", -1);
            IPS_SetVariableProfileAssociation("BRELAG.ThermostatMiniTargetTemperature", 2, "23 °C", "", -1);
            IPS_SetVariableProfileAssociation("BRELAG.ThermostatMiniTargetTemperature", 3, "22 °C", "", -1);
            IPS_SetVariableProfileAssociation("BRELAG.ThermostatMiniTargetTemperature", 4, "21 °C", "", -1);
            IPS_SetVariableProfileAssociation("BRELAG.ThermostatMiniTargetTemperature", 5, "20 °C", "", -1);
            IPS_SetVariableProfileAssociation("BRELAG.ThermostatMiniTargetTemperature", 6, "19 °C", "", -1);
            IPS_SetVariableProfileAssociation("BRELAG.ThermostatMiniTargetTemperature", 7, "18 °C", "", -1);
        }

        if (!IPS_VariableProfileExists("BRELAG.ThermostatMiniCorrectionTemperature")) {
            IPS_CreateVariableProfile("BRELAG.ThermostatMiniCorrectionTemperature", 2);
            IPS_SetVariableProfileText("BRELAG.ThermostatMiniCorrectionTemperature", "", " °C");
            IPS_SetVariableProfileValues("BRELAG.ThermostatMiniCorrectionTemperature", -5, 5, 0.5);
            IPS_SetVariableProfileDigits("BRELAG.ThermostatMiniCorrectionTemperature", 1);
            IPS_SetVariableProfileIcon("BRELAG.ThermostatMiniCorrectionTemperature", "Temperature");
        }

		$this->RegisterVariableFloat("Temperature", $this->Translate("Temperature"), "~Temperature", 1);
        $this->RegisterVariableFloat("ActualTemperature", $this->Translate("Actual Temperature"), "~Temperature", 2);
        $this->RegisterVariableFloat("TargetTemperature", $this->Translate("Target Temperature"), "BRELAG.ThermostatMiniTargetTemperature", 3);
        $this->RegisterVariableFloat("CorrectionTemperature", $this->Translate("Temperature (Correction)"), "BRELAG.ThermostatMiniCorrectionTemperature", 4);
        $this->EnableAction("CorrectionTemperature");

		$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate

	}



	private function UpdateTemperature() {
        SetValue($this->GetIDForIdent("Temperature"), GetValue($this->GetIDForIdent("ActualTemperature")) + GetValue($this->GetIDForIdent("CorrectionTemperature")));
    }

	
	
	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);
		if($data->Values->ID == $this->ReadPropertyInteger("ID"))
		{
			switch($data->Values->Command) {
				case 35:
					SetValue($this->GetIDForIdent("ActualTemperature"), $data->Values->Value/2-20);
                    $this->UpdateTemperature();
					break;
					
				case 42:
					SetValue($this->GetIDForIdent("TargetTemperature"), $data->Values->Value);
					break;
			}
		}
		
	}



    public function RequestAction($Ident, $Value) {

        switch($Ident) {
            case "CorrectionTemperature":
                SetValue($this->GetIDForIdent($Ident), $Value);
                $this->UpdateTemperature();
                break;
            default:
                throw new Exception("Invalid Ident");
        }

    }

}