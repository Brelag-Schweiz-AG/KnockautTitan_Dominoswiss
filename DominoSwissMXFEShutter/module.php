<?
include_once __DIR__ . '/../libs/DominoSwissBase.php';

class DominoSwissMXFEShutter extends DominoSwissBase {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		$this->RegisterPropertyBoolean("Awning", false);
		$this->RegisterPropertyInteger("CountRockerSteps", 8);
		$this->RegisterPropertyInteger("Runtime", 90);

		if (!IPS_VariableProfileExists("BRELAG.Shutter")) {
			IPS_CreateVariableProfile("BRELAG.Shutter", 0);
			IPS_SetVariableProfileIcon("BRELAG.Shutter", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Shutter", 0, $this->Translate("Stopped"), "", 0x00D500);
			IPS_SetVariableProfileAssociation("BRELAG.Shutter", 1, $this->Translate("Moving"), "", 0xFF0000);
		}
		
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

		if (!IPS_VariableProfileExists("BRELAG.Rocker8")) {
			IPS_CreateVariableProfile("BRELAG.Rocker8", 1);
			IPS_SetVariableProfileValues("BRELAG.Rocker8", 0, 8, 1);
			IPS_SetVariableProfileIcon("BRELAG.Rocker8", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Rocker8", 0, $this->Translate($this->Translate("Closed")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker8", 1, $this->Translate($this->Translate("1x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker8", 2, $this->Translate($this->Translate("2x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker8", 3, $this->Translate($this->Translate("3x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker8", 4, $this->Translate($this->Translate("4x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker8", 5, $this->Translate($this->Translate("5x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker8", 6, $this->Translate($this->Translate("6x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker8", 7, $this->Translate($this->Translate("7x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker8", 8, $this->Translate($this->Translate("8x Upper")), "", -1);
		}

		if (!IPS_VariableProfileExists("BRELAG.Rocker7")) {
			IPS_CreateVariableProfile("BRELAG.Rocker7", 1);
			IPS_SetVariableProfileValues("BRELAG.Rocker7", 0, 7, 1);
			IPS_SetVariableProfileIcon("BRELAG.Rocker7", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Rocker7", 0, $this->Translate($this->Translate("Closed")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker7", 1, $this->Translate($this->Translate("1x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker7", 2, $this->Translate($this->Translate("2x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker7", 3, $this->Translate($this->Translate("3x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker7", 4, $this->Translate($this->Translate("4x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker7", 5, $this->Translate($this->Translate("5x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker7", 6, $this->Translate($this->Translate("6x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker7", 7, $this->Translate($this->Translate("7x Upper")), "", -1);
		}

		if (!IPS_VariableProfileExists("BRELAG.Rocker6")) {
			IPS_CreateVariableProfile("BRELAG.Rocker6", 1);
			IPS_SetVariableProfileValues("BRELAG.Rocker6", 0, 6, 1);
			IPS_SetVariableProfileIcon("BRELAG.Rocker6", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Rocker6", 0, $this->Translate($this->Translate("Closed")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker6", 1, $this->Translate($this->Translate("1x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker6", 2, $this->Translate($this->Translate("2x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker6", 3, $this->Translate($this->Translate("3x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker6", 4, $this->Translate($this->Translate("4x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker6", 5, $this->Translate($this->Translate("5x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker6", 6, $this->Translate($this->Translate("6x Upper")), "", -1);
		}

		if (!IPS_VariableProfileExists("BRELAG.Rocker5")) {
			IPS_CreateVariableProfile("BRELAG.Rocker5", 1);
			IPS_SetVariableProfileValues("BRELAG.Rocker5", 0, 5, 1);
			IPS_SetVariableProfileIcon("BRELAG.Rocker5", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Rocker5", 0, $this->Translate($this->Translate("Closed")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker5", 1, $this->Translate($this->Translate("1x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker5", 2, $this->Translate($this->Translate("2x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker5", 3, $this->Translate($this->Translate("3x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker5", 4, $this->Translate($this->Translate("4x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker5", 5, $this->Translate($this->Translate("5x Upper")), "", -1);
		}

		if (!IPS_VariableProfileExists("BRELAG.Rocker4")) {
			IPS_CreateVariableProfile("BRELAG.Rocker4", 1);
			IPS_SetVariableProfileValues("BRELAG.Rocker4", 0, 4, 1);
			IPS_SetVariableProfileIcon("BRELAG.Rocker4", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Rocker4", 0, $this->Translate($this->Translate("Closed")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker4", 1, $this->Translate($this->Translate("1x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker4", 2, $this->Translate($this->Translate("2x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker4", 3, $this->Translate($this->Translate("3x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker4", 4, $this->Translate($this->Translate("4x Upper")), "", -1);
		}

		if (!IPS_VariableProfileExists("BRELAG.Rocker3")) {
			IPS_CreateVariableProfile("BRELAG.Rocker3", 1);
			IPS_SetVariableProfileValues("BRELAG.Rocker3", 0, 3, 1);
			IPS_SetVariableProfileIcon("BRELAG.Rocker3", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Rocker3", 0, $this->Translate($this->Translate("Closed")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker3", 1, $this->Translate($this->Translate("1x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker3", 2, $this->Translate($this->Translate("2x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker3", 3, $this->Translate($this->Translate("3x Upper")), "", -1);
		}

		if (!IPS_VariableProfileExists("BRELAG.Rocker2")) {
			IPS_CreateVariableProfile("BRELAG.Rocker2", 1);
			IPS_SetVariableProfileValues("BRELAG.Rocker2", 0, 2, 1);
			IPS_SetVariableProfileIcon("BRELAG.Rocker2", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Rocker2", 0, $this->Translate($this->Translate("Closed")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker2", 1, $this->Translate($this->Translate("1x Upper")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker2", 2, $this->Translate($this->Translate("2x Upper")), "", -1);
		}

		if (!IPS_VariableProfileExists("BRELAG.Rocker1")) {
			IPS_CreateVariableProfile("BRELAG.Rocker1", 1);
			IPS_SetVariableProfileValues("BRELAG.Rocker1", 0, 1, 1);
			IPS_SetVariableProfileIcon("BRELAG.Rocker1", "IPS");
			IPS_SetVariableProfileAssociation("BRELAG.Rocker1", 0, $this->Translate($this->Translate("Closed")), "", -1);
			IPS_SetVariableProfileAssociation("BRELAG.Rocker1", 1, $this->Translate($this->Translate("1x Upper")), "", -1);
		}

		$this->RegisterVariableBoolean("Status", "Status", "BRELAG.Shutter", 1);
		
		$this->MaintainVariable("SavedRocker", $this->Translate("SavedRocker"), 1, "", 10, true);
		IPS_SetHidden($this->GetIDForIdent("SavedRocker"), true);

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
			$this->MaintainVariable("Movement", $this->Translate("Movement"), 1, "BRELAG.ShutterMoveAwning", 3, true);
			$this->EnableAction("Movement");
			$this->MaintainVariable("RockerControl", $this->Translate("RockerControl"), 1, "BRELAG.Rocker".$this->ReadPropertyInteger("CountRockerSteps"), 4, false);
		}
		else {
			$this->MaintainVariable("Movement", $this->Translate("Movement"), 1,  "BRELAG.ShutterMoveJalousie", 3, true);
			$this->EnableAction("Movement");
			$this->MaintainVariable("RockerControl", $this->Translate("RockerControl"), 1, "BRELAG.Rocker".$this->ReadPropertyInteger("CountRockerSteps"), 4, true);
			$this->EnableAction("RockerControl");
		}
	}

	
	
	public function ReceiveData($JSONString) {

		$data = json_decode($JSONString);
		
		$this->SendDebug("BufferIn", print_r($data->Values, true), 0);

		//No ID check necessary, check is done by receiveFilter "DominoSwissBase.php->ApplyChanges()"
		if ($data->Values->Priority >= $this->GetHighestLockLevel()) {
			$command = $data->Values->Command;
			switch ($command) {
				case 1:
				case 2:
					if (GetValue($this->GetIDForIdent("Status"))){
						SetValue($this->GetIDForIdent("Status"), false);
						SetValue($this->GetIDForIdent("Movement"), 2);
					}
					else {
						
						if ($this->ReadPropertyBoolean("Awning")) {
							SetValue($this->GetIDForIdent("Status"), true);
							if ($command == 1) {
								SetValue($this->GetIDForIdent("Movement"), 0);
							}
							else {
								SetValue($this->GetIDForIdent("Movement"), 4);
							}
							$this->SetTimerInterval("SetMovementStopTimer", $this->ReadPropertyInteger("Runtime") * 1000);
						}
						else {
							SetValue($this->GetIDForIdent("Status"), false);
							if ($command == 1) {
								if ((GetValue($this->GetIDForIdent("RockerControl")) + 1) > $this->ReadPropertyInteger("CountRockerSteps")) {
									SetValue($this->GetIDForIdent("RockerControl"), $this->ReadPropertyInteger("CountRockerSteps"));
								}
								else {
									SetValue($this->GetIDForIdent("RockerControl"), GetValue($this->GetIDForIdent("RockerControl")) + 1);
								}
								SetValue($this->GetIDForIdent("Movement"), 1);
							}
							else {
								if ((GetValue($this->GetIDForIdent("RockerControl")) - 1) < 0 ) {
									SetValue($this->GetIDForIdent("RockerControl"), 0);
								}
								else {
									SetValue($this->GetIDForIdent("RockerControl"), GetValue($this->GetIDForIdent("RockerControl")) - 1);
								}
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
						SetValue($this->GetIDForIdent("RockerControl"), $this->ReadPropertyInteger("CountRockerSteps"));
						SetValue($this->GetIDForIdent("Movement"), 0);
					}
					else {
						SetValue($this->GetIDForIdent("RockerControl"), 0);
						SetValue($this->GetIDForIdent("Movement"), 4);
					}
					break;

				case 5:
					SetValue($this->GetIDForIdent("Status"), false);
					SetValue($this->GetIDForIdent("Movement"), 2);
					break;

				case 15:
					//only save if its our ID
					if ($data->Values->ID == $this->ReadPropertyInteger("ID")) {
						SetValue($this->GetIDForIdent("SavedRocker"), GetValue($this->GetIDForIdent("RockerControl")));
						SetValue($this->GetIDForIdent("Saving"), 1);
					}
					break;
					
				case 16:
					if (GetValue($this->GetIDForIdent("Status"))) {
						SetValue($this->GetIDForIdent("Status"), false);
						SetValue($this->GetIDForIdent("Movement"), 2);
					}
					else {
						SetValue($this->GetIDForIdent("Status"), true);
						SetValue($this->GetIDForIdent("Movement"), 2);
						$this->SetTimerInterval("SetMovementStopTimer", $this->ReadPropertyInteger("Runtime") * 1000);
					}
					$savedRocker = ($this->GetIDForIdent("SavedRocker"));
					SetValue($this->GetIDForIdent("RockerControl"), $savedRocker);
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
					SetValue($this->GetIDForIdent("Saving"), 0);
					$this->SetTimerInterval("SetMovementStopTimer", $this->ReadPropertyInteger("Runtime") * 1000);
					$savedRocker = GetValue($this->GetIDForIdent("SavedRocker"));
					SetValue($this->GetIDForIdent("RockerControl"), $savedRocker);
					break;
			}
		}
	}

	
	
	public function RequestAction($Ident, $Value) {

		switch ($Ident) {
			case 'Movement':
				switch ($Value) {
					case 0:
						$this->ContinuousUp(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
						break;

					case 1:
						$this->PulseUp(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
						break;

					case 2:
						$this->Stop(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
						break;

					case 3:
						$this->PulseDown(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
						break;

					case 4:
						$this->ContinuousDown(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
						break;

				}
			break;

			case 'RockerControl':
				$this->SetRocker($Value);
				break;


			default:
				parent::RequestAction($Ident, $Value);
		}
	}

	
	
	public function SetMovementStop() {
		SetValue($this->GetIDForIdent("Status"), false);
		$this->SetTimerInterval("SetMovementStopTimer", 0);
	}

	
	
	public function SetRocker($Value) {

		$oldValue = GetValue($this->GetIDForIdent("RockerControl"));

		if ($Value > $oldValue) {
			for($i = 0; $i < ($Value - $oldValue); $i++) {
				$this->PulseUp(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
			}
		}
		else {
			for($i = 0; $i < abs($oldValue - $Value); $i++) {
				$this->PulseDown(GetValue($this->GetIDForIdent("SendingOnLockLevel")));
			}
		}

		SetValue($this->GetIDForIdent("RockerControl"), $Value);
	}

}
?>