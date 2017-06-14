<?
include_once __DIR__ . '/../libs/DominoSwissBase.php';

class DominoSwissGroup extends DominoSwissBase {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyString("Devices", "[]");
		$this->RegisterPropertyBoolean("ShowAwning", false);
		$this->RegisterPropertyBoolean("ShowToggle", true);
		$this->RegisterPropertyBoolean("ShowIntensity", true);


		if(!IPS_VariableProfileExists("BRELAG.ShutterMoveJalousie")) {
			IPS_CreateVariableProfile("BRELAG.ShutterMoveJalousie", 1);
			IPS_SetVariableProfileValues("BRELAG.ShutterMoveJalousie", 0, 4, 0);
			IPS_SetVariableProfileIcon("BRELAG.ShutterMoveJalousie", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveJalousie", 0, $this->Translate("UP"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveJalousie", 1, "<<", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveJalousie", 2, "STOP", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveJalousie", 3, ">>", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveJalousie", 4, $this->Translate("DOWN"), "", -1);
		}

		if(!IPS_VariableProfileExists("BRELAG.ShutterMoveAwning")) {
			IPS_CreateVariableProfile("BRELAG.ShutterMoveAwning", 1);
			IPS_SetVariableProfileValues("BRELAG.ShutterMoveAwning", 0, 4, 0);
			IPS_SetVariableProfileIcon("BRELAG.ShutterMoveAwning", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveAwning", 0, $this->Translate("UP"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveAwning", 2, "STOP", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.ShutterMoveAwning", 4, $this->Translate("DOWN"), "", -1);
		}

		if(!IPS_VariableProfileExists("BRELAG.SaveToggle")) {
			IPS_CreateVariableProfile("BRELAG.SaveToggle", 1);
			IPS_SetVariableProfileIcon("BRELAG.SaveToggle", "Lock");
			IPS_SetVariableProfileAssociation("BRELAG.SaveToggle", 0, $this->Translate("Save"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.SaveToggle", 1, $this->Translate("Restore"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.SaveToggle", 2, $this->Translate("Toggle"), "", -1);
		}

		$this->RegisterVariableInteger("Intensity", $this->Translate("Intensity"), "~Intensity.100", 0);
		$this->EnableAction("Intensity");
		
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
			$this->MaintainVariable("GroupOrder", $this->Translate("GroupOrder"), 1, "BRELAG.ShutterMoveAwning", 0, true);
			$this->EnableAction("GroupOrder");
		} else {
			$this->MaintainVariable("GroupOrder", $this->Translate("GroupOrder"), 1,  "BRELAG.ShutterMoveJalousie", 0, true);
			$this->EnableAction("GroupOrder");
		}

		if ($this->ReadPropertyBoolean("ShowToggle")) {
			$this->MaintainVariable("Saving", $this->Translate("Saving"), 1, "BRELAG.SaveToggle", 0, true);
		} else {
			$this->MaintainVariable("Saving", $this->Translate("Saving"), 1,  "BRELAG.Save", 0, true);
		}

		if ($this->ReadPropertyBoolean("ShowIntensity")) {
			IPS_SetHidden($this->GetIDForIdent("Intensity"), false);
		} else {
			IPS_SetHidden($this->GetIDForIdent("Intensity"), true);
		}

	}

	public function ReceiveData($JSONString) {

		$data = json_decode($JSONString);

		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);

		if ($data->Values->Instruction == 2) {
			$this->SendCommand2($data->Values->Command, $data->Values->Value, $data->Values->Priority);
			return;
		}

		if(($data->Values->ID == $this->ReadPropertyInteger("ID")) && ($data->Values->Priority >= $this->GetHighestLockLevel())) {
			switch($data->Values->Command) {
				case 1:
					SetValue($this->GetIDForIdent("GroupOrder"), 1);
					break;

				case 2:
					SetValue($this->GetIDForIdent("GroupOrder"), 3);
					break;

				case 3:
					SetValue($this->GetIDForIdent("GroupOrder"), 0);
					break;

				case 4:
					SetValue($this->GetIDForIdent("GroupOrder"), 4);
					break;

				case 5:
					SetValue($this->GetIDForIdent("GroupOrder"), 2);
					break;

				case 6:
					SetValue($this->GetIDForIdent("Saving"), 2);
					break;

				case 15:
					SetValue($this->GetIDForIdent("Saving"), 0);
					break;

				case 17:
					$intensityValue =($data->Values->Value * 100) /63;
					SetValue($this->GetIDForIdent("Intensity"), $intensityValue);
					break;

				case 20:
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), true);
					break;

				case 21:
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), false);
					break;

				case 23:
					SetValue($this->GetIDForIdent("Saving"), 1);
					break;
			}
		}

	}

	public function RequestAction($Ident, $Value) {

		switch($Ident) {

			case "GroupOrder":
				$this->SendCommand($this->GetCommandNumberforValue($Value), 0, GetValue($this->GetIDForIdent("SendingOnLockLevel")));
				break;

			case "Saving":
				switch ($Value){
					case 0:
						$this->Save(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
						break;

					case 1:
						$this->RestorePosition(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
						break;

					case 2:
						$this->Toggle(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
						break;
				}
				break;


			case "SendingOnLockLevel":
				SetValue($this->GetIDForIdent("SendingOnLockLevel"), $Value);
				break;

			case "LockLevel0":
			case "LockLevel1":
			case "LockLevel2":
			case "LockLevel3":
				if($Value) {
					$this->LockLevelSet(substr($Ident, -1, 1));
				} else {
					$this->LockLevelClear(substr($Ident, -1, 1));
				}
				break;

			case "Intensity":
				$this->Move(GetValue($this->GetIDForIdent("SendingOnLockLevel")), $Value);
				break;

			default:
				throw new Exception("Invalid ident");
		}
	}

	public function GetConfigurationForm() {

		$formdata = json_decode(file_get_contents(__DIR__ . "/form.json"));

		if($this->ReadPropertyString("Devices") != "") {
			$devices = json_decode($this->ReadPropertyString("Devices"));

			foreach($devices as $device) {
				if (IPS_ObjectExists($device->InstanceID) && $device->InstanceID !== 0) {
					$formdata->elements[7]->values[] = Array(
						"Name" => IPS_GetName($device->InstanceID),
						"ID" => IPS_GetProperty($device->InstanceID, "ID")
					);
				} else {
					$formdata->elements[7]->values[] = Array(
						"Name" => "Unknown Device",
						"ID" => 0
					);
				}
			}
		}

		return json_encode($formdata);

	}

	public function Move(int $Priority, int $Value){

		if ($Value < 0) {
			$Value = 0;
		} else if ($Value > 100) {
			$Value = 100;
		}

		$Value = round(($Value * 63) / 100, 0);
		$this->SendCommand(17, $Value, $Priority);

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

	public function SendCommand(int $Command, int $Value, int $Priority) {

		$id = $this->ReadPropertyInteger("ID");
		return $this->SendDataToParent(json_encode(Array("DataID" => "{C24CDA30-82EE-46E2-BAA0-13A088ACB5DB}", "ID" => $id, "Command" => $Command, "Value" => $Value, "Priority" => $Priority, "GroupIDs" => $this->GetGroupIDs())));

	}

	private function SendCommand2(int $Command, int $Value, int $Priority) {

		$id = $this->ReadPropertyInteger("ID");
		return $this->SendDataToParent(json_encode(Array("DataID" => "{C24CDA30-82EE-46E2-BAA0-13A088ACB5DB}", "ID" => $id, "Command" => $Command, "Value" => $Value, "Priority" => $Priority, "GroupIDs" => $this->GetGroupIDs(), "OnlyGroups" => true)));

	}

	private function GetGroupIDs(){

		$groupIDs = Array();

		if($this->ReadPropertyString("Devices") != "") {
			$devices = json_decode($this->ReadPropertyString("Devices"));

			foreach ($devices as $device) {
				if (IPS_ObjectExists($device->InstanceID) && $device->InstanceID !== 0) {
					$groupIDs[] = IPS_GetProperty($device->InstanceID, "ID");
				}
			}
		}
		return $groupIDs;
	}

}
?>