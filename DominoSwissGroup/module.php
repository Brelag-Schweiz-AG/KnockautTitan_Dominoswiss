<?
include_once __DIR__ . '/../libs/DominoSwissBase.php';

class DominoSwissGroup extends DominoSwissBase {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyString("Devices", "[]");

		if(!IPS_VariableProfileExists("BRELAG.GroupOrder")) {
			IPS_CreateVariableProfile("BRELAG.GroupOrder", 1);
			IPS_SetVariableProfileValues("BRELAG.GroupOrder", 1, 8, 0);
			IPS_SetVariableProfileIcon("BRELAG.GroupOrder", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.GroupOrder", 1, $this->Translate("UP"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.GroupOrder", 2, "<<", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.GroupOrder", 3, "STOP", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.GroupOrder", 4, ">>", "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.GroupOrder", 5, $this->Translate("DOWN"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.GroupOrder", 6, $this->Translate("Toggle"), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.GroupOrder", 7, $this->Translate("Save"), "",-1);
			IPS_SetVariableProfileAssociation("BRELAG.GroupOrder", 8, $this->Translate("Restore"), "", -1);
		}

		$this->RegisterVariableInteger("GroupOrder", $this->Translate("GroupOrder"), "BRELAG.GroupOrder", 0);
		$this->EnableAction("GroupOrder");

		$this->MaintainVariable("Saving", "Saving", 1, "", 0, false);
		
		$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate
	}

	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ReceiveData($JSONString) {

		$data = json_decode($JSONString);

		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);

		if(($data->Values->ID == $this->ReadPropertyInteger("ID")) && ($data->Values->Priority >= $this->GetHighestLockLevel())) {
			switch($data->Values->Command) {
				case 1:
					SetValue($this->GetIDForIdent("GroupOrder"), 2);
					break;

				case 2:
					SetValue($this->GetIDForIdent("GroupOrder"), 4);
					break;

				case 3:
					SetValue($this->GetIDForIdent("GroupOrder"), 1);
					break;

				case 4:
					SetValue($this->GetIDForIdent("GroupOrder"), 5);
					break;

				case 5:
					SetValue($this->GetIDForIdent("GroupOrder"), 3);
					break;

				case 6:
					SetValue($this->GetIDForIdent("GroupOrder"), 6);
					break;

				case 15:
					SetValue($this->GetIDForIdent("GroupOrder"), 7);
					break;

				case 16:
					SetValue($this->GetIDForIdent("GroupOrder"), 8);
					break;

				case 20:
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), true);
					break;

				case 21:
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), false);
					break;

				case 23:
					SetValue($this->GetIDForIdent("GroupOrder"), 8);
					break;
			}
		}

	}

	public function RequestAction($Ident, $Value) {

		switch($Ident) {

			case "GroupOrder":
				$this->SendCommand($this->GetCommandNumberforValue($Value), 0, GetValue($this->GetIDForIdent("SendingOnLockLevel")));
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
					$formdata->elements[4]->values[] = Array(
						"Name" => IPS_GetName($device->InstanceID),
						"ID" => IPS_GetProperty($device->InstanceID, "ID")
					);
				} else {
					$formdata->elements[4]->values[] = Array(
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
			case 1:
				return 3;

			case 2:
				return 1;

			case 3:
				return 5;

			case 4:
				return 2;

			case 5:
				return 4;

			case 6:
				return 6;

			case 7:
				return 15;

			case 8:
				return 16;

		}
	}

	public function SendCommand(int $Command, int $Value, int $Priority) {

		$groupIDs = Array();

		if($this->ReadPropertyString("Devices") != "") {
			$devices = json_decode($this->ReadPropertyString("Devices"));

			foreach ($devices as $device) {
				if (IPS_ObjectExists($device->InstanceID) && $device->InstanceID !== 0) {
					$groupIDs[] = IPS_GetProperty($device->InstanceID, "ID");
				}
			}
		}
		$id = $this->ReadPropertyInteger("ID");
		return $this->SendDataToParent(json_encode(Array("DataID" => "{C24CDA30-82EE-46E2-BAA0-13A088ACB5DB}", "ID" => $id, "Command" => $Command, "Value" => $Value, "Priority" => $Priority, "GroupIDs" => $groupIDs)));

	}

}
?>