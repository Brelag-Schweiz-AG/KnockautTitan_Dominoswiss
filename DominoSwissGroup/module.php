<?
include_once __DIR__ . '/../libs/DominoSwissBase.php';

class DominoSwissGroup extends DominoSwissBase {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		$this->RegisterPropertyBoolean("ShowAwning", false);
		$this->RegisterPropertyBoolean("ShowToggle", true);
		$this->RegisterPropertyBoolean("ShowIntensity", true);


		if (!IPS_VariableProfileExists("BRELAG.ShutterMoveJalousie")) {
			IPS_CreateVariableProfile("BRELAG.ShutterMoveJalousie", 1);
			IPS_SetVariableProfileValues("BRELAG.ShutterMoveJalousie", 0, 4, 0);
			IPS_SetVariableProfileIcon("BRELAG.ShutterMoveJalousie", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveJalousie", 0, $this->Translate("UP"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveJalousie", 1, "<<", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveJalousie", 2, "STOP", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveJalousie", 3, ">>", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveJalousie", 4, $this->Translate("DOWN"), "", -1);
		}

		if (!IPS_VariableProfileExists("BRELAG.ShutterMoveAwning")) {
			IPS_CreateVariableProfile("BRELAG.ShutterMoveAwning", 1);
			IPS_SetVariableProfileValues("BRELAG.ShutterMoveAwning", 0, 4, 0);
			IPS_SetVariableProfileIcon("BRELAG.ShutterMoveAwning", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveAwning", 0, $this->Translate("UP"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveAwning", 2, "STOP", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveAwning", 4, $this->Translate("DOWN"), "", -1);
		}

		if (!IPS_VariableProfileExists("BRELAG.SaveToggle")) {
			IPS_CreateVariableProfile("BRELAG.SaveToggle", 1);
			IPS_SetVariableProfileIcon("BRELAG.SaveToggle", "Lock");
			IPS_SetVariableProfileAssociation("BRELAG.SaveToggle", 0, $this->Translate("Restore"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.SaveToggle", 1, $this->Translate("Save"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.SaveToggle", 2, $this->Translate("Toggle"), "", -1);
		}

		if(!IPS_VariableProfileExists("BRELAG.Switch")) {
			IPS_CreateVariableProfile("BRELAG.Switch", 0);
			IPS_SetVariableProfileIcon("BRELAG.Switch", "Power");
			IPS_SetVariableProfileAssociation("BRELAG.Switch", 0, $this->Translate("Off"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Switch", 1, $this->Translate("On"), "", -1);
		}

		$this->RegisterVariableInteger("Intensity", $this->Translate("Intensity"), "~Intensity.100", 6);
		$this->EnableAction("Intensity");

		$this->RegisterVariableBoolean("Switch",  $this->Translate("Switch"), "BRELAG.Switch", 5);
		$this->EnableAction("Switch");

		$this->MaintainVariable("SavedValue", $this->Translate("SavedValue"), 1, "~Intensity.100", 10, true);
		IPS_SetHidden($this->GetIDForIdent("SavedValue"), true);
		
		$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate
	}

	
	
	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	
	
	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();

		if ($this->ReadPropertyBoolean("ShowAwning")) {
			$this->MaintainVariable("GroupOrder", $this->Translate("GroupOrder"), 1, "BRELAG.ShutterMoveAwning", 3, true);
			$this->EnableAction("GroupOrder");
		}
		else {
			$this->MaintainVariable("GroupOrder", $this->Translate("GroupOrder"), 1,  "BRELAG.ShutterMoveJalousie", 3, true);
			$this->EnableAction("GroupOrder");
		}

		if ($this->ReadPropertyBoolean("ShowToggle")) {
			$this->MaintainVariable("Saving", $this->Translate("Saving"), 1, "BRELAG.SaveToggle", 7, true);
		}
		else {
			$this->MaintainVariable("Saving", $this->Translate("Saving"), 1,  "BRELAG.Save", 7, true);
		}

		if ($this->ReadPropertyBoolean("ShowIntensity")) {
			IPS_SetHidden($this->GetIDForIdent("Intensity"), false);
		}
		else {
			IPS_SetHidden($this->GetIDForIdent("Intensity"), true);
		}
	}

	
	
	public function ReceiveData($JSONString) {

		$data = json_decode($JSONString);

		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);

		//No ID check necessary, check is done by receiveFilter "DominoSwissBase.php->ApplyChanges()"
		if ($data->Values->Priority >= $this->GetHighestLockLevel()) {
			switch($data->Values->Command) {
				case 1: //PulseUp
					SetValue($this->GetIDForIdent("GroupOrder"), 1);
                    SetValue($this->GetIDForIdent("Intensity"), 0);
                    SetValue($this->GetIDForIdent("Switch"), true);
					break;

				case 2: //PulseDown
					SetValue($this->GetIDForIdent("GroupOrder"), 3);
					SetValue($this->GetIDForIdent("Intensity"), 0);
					SetValue($this->GetIDForIdent("Switch"), false);
					break;

				case 3: //ContinuousUp
					SetValue($this->GetIDForIdent("GroupOrder"), 0);
					SetValue($this->GetIDForIdent("Intensity"), 100);
					SetValue($this->GetIDForIdent("Switch"), true);
					break;

				case 4: //ContinuousDown
					SetValue($this->GetIDForIdent("GroupOrder"), 4);
					SetValue($this->GetIDForIdent("Intensity"), 0);
					SetValue($this->GetIDForIdent("Switch"), false);
					break;

				case 5: //Stop
					SetValue($this->GetIDForIdent("GroupOrder"), 2);
					break;

				case 6: //Toggle
					SetValue($this->GetIDForIdent("Saving"), 2);
                    SetValue($this->GetIDForIdent("Switch"), !GetValue($this->GetIDForIdent("Switch")));
					break;

				case 15: //PosSaveBoth
					if ($data->Values->ID == $this->ReadPropertyInteger("ID")) {
						SetValue($this->GetIDForIdent("SavedValue"), GetValue($this->GetIDForIdent("Intensity")));
						SetValue($this->GetIDForIdent("Saving"), 1);
					}
					$this->SaveIntoArray($data->Values->ID);
					break;

				case 16: //PosRestoreBoth
				case 23: //PosRestore
					$savedValue = $this->LoadOutOfArray($data->Values->ID);

					SetValue($this->GetIDForIdent("Intensity"), $savedValue);
					SetValue($this->GetIDForIdent("Saving"), 0);
					break;

				case 17: //PosByVal
					$intensityValue =($data->Values->Value * 100) / 63;
					SetValue($this->GetIDForIdent("Intensity"), $intensityValue);
					break;

				case 20: //LockLevelSet
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), true);
					break;

				case 21: //LockLevelClear
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), false);
					break;
			}
		}
	}

	
	
	public function RequestAction($Ident, $Value) {

		switch($Ident) {
			case "Switch":
				if($Value) {
					$this->ContinuousUp(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
				}
				else {
					$this->ContinuousDown(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
				}
				break;

			case "GroupOrder":
				$this->SendCommand(1, $this->GetCommandNumberforValue($Value), 0, GetValue($this->GetIDForIdent("SendingOnLockLevel")), null);
				break;

			case "Intensity":
				$this->Move(GetValue($this->GetIDForIdent("SendingOnLockLevel")), $Value);
				break;

			default:
				parent::RequestAction($Ident, $Value);
		}
	}

	
	
	public function RestorePosition(int $Priority){

		$this->SendCommand( 1, 23, GetValue($this->GetIDForIdent("SavedValue"))  , $Priority, null);

	}

	
	
	public function Move(int $Priority, int $Value){

		if ($Value < 0) {
			$Value = 0;
		}
		else if ($Value > 100) {
			$Value = 100;
		}

		$Value = round(($Value * 63) / 100, 0);
		$this->SendCommand( 1, 17, $Value, $Priority, null);

	}

	
	
	private function GetCommandNumberforValue($Value) {

		switch ($Value) {
			case 0:
				return 3;

			case 1:
				return 1;

			case 2:
				return 5;

			case 3:
				return 2;

			case 4:
				return 4;

		}
	}


	
	private function LoadOutOfArray($ID) {

		$savedValuesIDs = json_decode(GetValue($this->GetIDForIdent("SavedValuesArray")), true);
		return $savedValuesIDs[$ID];

	}
	
	
	
	private function SaveIntoArray($ID) {

		$savedValuesIDs = json_decode(GetValue($this->GetIDForIdent("SavedValuesArray")), true);
		$savedValuesIDs[$ID] = GetValue($this->GetIDForIdent("Intensity"));

		SetValue($this->GetIDForIdent("SavedValuesArray"), json_encode($savedValuesIDs));
	}
	
	
	
	public function SendCommand(int $Instruction, int $Command, int $Value, int $Priority, int $CheckNr) {

		$id = $this->ReadPropertyInteger("ID");
		return $this->SendDataToParent(json_encode(Array("DataID" => "{C24CDA30-82EE-46E2-BAA0-13A088ACB5DB}", "Instruction" => $Instruction, "ID" => $id, "Command" => $Command, "Value" => $Value, "Priority" => $Priority, "CheckNr" => $CheckNr)));
	}

}
?>