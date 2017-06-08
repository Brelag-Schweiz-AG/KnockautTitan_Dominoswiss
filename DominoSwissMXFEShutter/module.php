<?
include_once __DIR__ . '/../libs/DominoSwissBase.php';

class DominoSwissMXFEShutter extends DominoSwissBase {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyBoolean("Awning", false);
		$this->RegisterPropertyInteger("SavedPosition", 0);
		$this->RegisterPropertyInteger("Runtime", 90);

		if(!IPS_VariableProfileExists("BRELAG.Shutter")) {
			IPS_CreateVariableProfile("BRELAG.Shutter", 0);
			IPS_SetVariableProfileIcon("BRELAG.Shutter", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Shutter", 0, $this->Translate("Stopped"), "", 0x00FF00);
			IPS_SetVariableProfileAssociation("BRELAG.Shutter", 1, $this->Translate("Moving"), "", 0xFF0000);
		}
		
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
		
		$this->RegisterVariableBoolean("Status", "Status", "BRELAG.Shutter", 0);

		$this->RegisterTimer("SetMovementStopTimer", 0, 'BRELAG_SetMovementStop($_IPS[\'TARGET\']);');

		$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate
	}

	public function Destroy() {
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();

		if ($this->ReadPropertyBoolean("Awning")) {
			$this->MaintainVariable("Movement", $this->Translate("Movement"), 1, "BRELAG.ShutterMoveAwning", 0, true);
			$this->EnableAction("Movement");
		} else {
			$this->MaintainVariable("Movement", $this->Translate("Movement"), 1,  "BRELAG.ShutterMoveJalousie", 0, true);
			$this->EnableAction("Movement");
		}
		
	}

	public function ReceiveData($JSONString) {

		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);

		if(($data->Values->ID == $this->ReadPropertyInteger("ID")) && ($data->Values->Priority >= $this->GetHighestLockLevel())) {
			$command = $data->Values->Command;
			switch($command) {
				case 1:
				case 2:
					if (GetValue($this->GetIDForIdent("Status"))){
						SetValue($this->GetIDForIdent("Status"), false);
						SetValue($this->GetIDForIdent("Movement"), 2);
					} else {
						if ($this->ReadPropertyBoolean("Awning")) {
							SetValue($this->GetIDForIdent("Status"), true);
							if ($command == 1) {
								SetValue($this->GetIDForIdent("Movement"), 0);
							} else {
								SetValue($this->GetIDForIdent("Movement"), 4);
							}
							$this->SetTimerInterval("SetMovementStopTimer", $this->ReadPropertyInteger("Runtime") * 1000);
						} else {
							SetValue($this->GetIDForIdent("Status"), false);
							if ($command == 1) {
								SetValue($this->GetIDForIdent("Movement"), 1);
							} else {
								SetValue($this->GetIDForIdent("Movement"), 3);
							}
						}
					}
					break;

				case 3:
				case 4:
					SetValue($this->GetIDForIdent("Status"), true);
					$this->SetTimerInterval("SetMovementStopTimer", $this->ReadPropertyInteger("Runtime") * 1000);
					if ($command == 3) {
						SetValue($this->GetIDForIdent("Movement"), 0);
					} else {
						SetValue($this->GetIDForIdent("Movement"), 4);
					}
					break;

				case 5:
					SetValue($this->GetIDForIdent("Status"), false);
					SetValue($this->GetIDForIdent("Movement"), 2);
					break;
				
				case 16:
					if (GetValue($this->GetIDForIdent("Status"))) {
						SetValue($this->GetIDForIdent("Status"), false);
						SetValue($this->GetIDForIdent("Movement"), 2);
					} else {
						SetValue($this->GetIDForIdent("Status"), true);
						SetValue($this->GetIDForIdent("Movement"), 2);
						$this->SetTimerInterval("SetMovementStopTimer", $this->ReadPropertyInteger("Runtime") * 1000);
					}
					break;

				case 20:
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), true);
					break;

				case 21:
					SetValue($this->GetIDForIdent("LockLevel". $data->Values->Value .""), false);
					break;
				
				case 23:
					SetValue($this->GetIDForIdent("Status"), true);
					SetValue($this->GetIDForIdent("Movement"), 2);
					$this->SetTimerInterval("SetMovementStopTimer", $this->ReadPropertyInteger("Runtime") * 1000);
					break;
			}
		}
	
	}

	public function RequestAction($Ident, $Value) {

		switch($Ident) {
			case 'Movement':
				switch ($Value) {
					case 0:
						$this->ContinuousUp(0);
						break;

					case 1:
						$this->PulseUp(0);
						break;

					case 2:
						$this->Stop(0);
						break;

					case 3:
						$this->PulseDown(0);
						break;

					case 4:
						$this->ContinuousDown(0);
						break;

				}
			break;

			case "Saving":
				switch ($Value){
					case 0:
						$this->Save(0);
						break;

					case 1:
						$this->RestorePosition(0);
						break;

					case 2:
						$this->RestorePositionBoth(0);
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

			default:
				throw new Exception("Invalid ident");
		}
	}

	public function SetMovementStop() {
		SetValue($this->GetIDForIdent("Status"), false);
		$this->SetTimerInterval("SetMovementStopTimer", 0);
	}

}
?>