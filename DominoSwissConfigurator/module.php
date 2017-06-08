<?

	class BrelagConfigurator extends IPSModule {
		
		public function Create() {
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyString("FileData", "");

			$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate

		}	

		public function GetConfigurationForm() {
			
			$data = json_decode(file_get_contents(__DIR__ . "/form.json"));
			$data->actions[0]->values = $this->PrepareConfigData();
			return json_encode($data);
		
		}

		public function CreateDevices() {

			$devices = $this->PrepareConfigData();

			//EinzelReceiver erstellen
			$ReceiverIDs = Array();
			foreach ($devices as $device) {
				if ($device['Name'] != "Group") {
					if ($device['ID'] != 0) {
						$InsID = IPS_CreateInstance($this->GetGUIDforModuleType($device['Name']));
						$ReceiverIDs[$device['ID']] = $InsID;

						IPS_SetName($InsID, $device['Name'] . " (ID: " . $device['ID'] . ")");
						IPS_SetPosition($InsID, $device['ID']);

						//Konfiguration
						IPS_SetProperty($InsID, "ID", $device['ID']);
						if ($device['Awning'] == "yes") {
							IPS_SetProperty($InsID, "Awning", true);
						}

						IPS_ApplyChanges($InsID);
					}
				}
			}

			//Gruppen erstellen
			foreach ($devices as $device) {
				if ($device['Name'] == "Group") {
					if ($device['ID'] != 0) {
						$InsID = IPS_CreateInstance($this->GetGUIDforModuleType($device['Name']));

						IPS_SetName($InsID, $device['Name'] . " (ID: " . $device['ID'] . ")");
						IPS_SetPosition($InsID, $device['ID']);

						//Konfiguration
						IPS_SetProperty($InsID, "ID", $device['ID']);

						$groupIDs = explode(",", $device['Group']);
						array_pop($groupIDs);
						$propertyString = Array();
						foreach($groupIDs as $ID) {
							$propertyString[] =	Array("InstanceID" => $ReceiverIDs[$ID]);
						}

						IPS_SetProperty($InsID, "Devices", json_encode($propertyString));

						IPS_ApplyChanges($InsID);
					}
				}
			}


		}

		private function PrepareConfigData() {

			$file = base64_decode($this->ReadPropertyString("FileData"));
			$result = Array();

			if ($file != "") {
				$file = str_replace(";", "~", $file);
				$fileArray = parse_ini_string($file, true, INI_SCANNER_RAW);

				$transmitterArray = $fileArray['Transmitter'];
				$receiverArray = $fileArray['Receiver'];
				$linkArray = $fileArray['Link'];
				$eGate1Array = $fileArray['eGate1'];

				unset($transmitterArray['//Index']);
				unset($receiverArray['//Index']);
				unset($linkArray['//Index']);
				foreach ($eGate1Array as $key => $value) {
					if ($key == 1) {
						break;
					}
					unset($eGate1Array[$key]);
				}

				foreach ($linkArray as $key => $value) {
					$explodedValue = explode("~", $value);
					$linkArray[$key] = $explodedValue;
				}

				foreach ($eGate1Array as $key => $value) {
					$explodedValue = explode("~", $value);
					$eGate1Array[$key] = array("ID" => $explodedValue[0]);
					foreach ($linkArray as $valueArray) {
						if (($explodedValue[1] === $valueArray[0]) && ($explodedValue[2] === $valueArray[1])) {
							$eGate1Array[$key]["Receiver"][] = $valueArray[2];
						}
					}
					if (sizeof($eGate1Array[$key]["Receiver"]) > 1) {
						$eGate1Array[$key]["Group"] = true;
					} else {
						$eGate1Array[$key]["Group"] = false;
					}
				}

				if (sizeof($eGate1Array) > 0) {
					foreach ($eGate1Array as $value) {
						$GroupValue = "";
						if ($value["Group"]) {
							$Name = "Group";
							$Awning = "---";
							foreach ($value['Receiver'] as $ID) {
								$GroupValue = $GroupValue . $ID . ",";
							}
						} else {
							$explodedValue = explode("~", $receiverArray[$value['Receiver'][0]]);
							$Name = $explodedValue[1];
							if (strpos($explodedValue[4], "NoSlatAdjustment=1") != FALSE) {
								$Awning = "yes";
							} else {
								$Awning = "no";
							}
							$GroupValue = $value['Receiver'][0];
						}
						$row = array("ID" => $value['ID'], "Name" => $Name, "Group" => $GroupValue, "Awning" => $Awning);
						$result[] = $row;
					}
				}

			}
			return $result;
		}

		private function GetGUIDforModuleType($Modultype) {

			switch ($Modultype) {
				case "MX FESLIM":
					return "{0A5C3DFA-CD52-4529-82F1-99DCFCF8A7A2}";

				case "MX FEPRO":
				case "MX FEUP3":
					return "{3AA1A627-78B0-4E17-9206-0BB012094D1C}";

				case "LX RLUP10A":
				case "LX RLUP1A":
					return "{E498DF29-57B1-48F5-8C13-A4673EE0EF9E}";

				case "LX DIMM NO LIMIT":
				case "LX DIMM RETROFIT":
					return "{5ED1AA15-6D8B-4DA8-B1C8-781D24442288}";

				case "Group":
					return "{7F5C8432-CEAC-45A7-BF96-4BBC3CF04B57}";

				case "SWW SOL":
				case "SWRW":
					return "{B3F0007D-44EE-460B-81D1-5A74C85EE29C}";
			}
		}
		
	}

?>
