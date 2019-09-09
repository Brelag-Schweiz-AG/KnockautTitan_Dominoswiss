<?

	class BrelagConfigurator extends IPSModule {
		
		public function Create() {
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyString("FileData", "");

			$this->ConnectParent("{1252F612-CF3F-4995-A152-DA7BE31D4154}"); //DominoSwiss eGate

		}	

		
		
		public function GetConfigurationForm() {
			
			$data = json_decode(file_get_contents(__DIR__ ."/form.json"));
			$data->actions[0]->values = $this->PrepareConfigData();
			return json_encode($data);
		
		}

		
		
		public function CreateDevices() {

			$devices = $this->PrepareConfigData();
			
			foreach ($devices as $device) {
				$this->CreateSingleDevice($device);
			}

		}
		
		
		
		public function CheckSingleDevice($tableRow) {

			$tableRowInstanceID = substr($tableRow['InstanceID'], 1);
			if ($tableRow['InstanceID'] == "-") {
				$this->CreateSingleDevice($tableRow);
				return;
			}
			
			$deviceSupplement = json_decode(IPS_GetProperty($tableRowInstanceID, "Supplement"), true);
			$simpleSupplementArray = Array();
			foreach ($deviceSupplement as $singleSupplement) {
				$simpleSupplementArray[] = $singleSupplement["ID"];
			}
			
			$correctSupplementArray = explode(",", $tableRow['Supplement']);
			if ($tableRow['Supplement'] != $simpleSupplementArray) {
				$propertySupplement = Array();
				foreach($correctSupplementArray as $id) {
					$propertySupplement[] = array("ID" => $id);
				}
				
				IPS_SetProperty($tableRowInstanceID, "Supplement", json_encode($propertySupplement));
				IPS_ApplyChanges($tableRowInstanceID);
			}
				
		}



		private function CreateSingleDevice($device) {

			if ($device['ID'] == 0) {
				return;
			}

			$ModuleID = $this->GetGUIDforModuleType($device['Name']);
			if($ModuleID == "") {
				return;
			}

			$InsID = IPS_CreateInstance($ModuleID);

			IPS_SetName($InsID, $device['Name'] . " (ID: " . $device['ID'] . ")");
			IPS_SetPosition($InsID, $device['ID']);
			IPS_SetProperty($InsID, "ID", $device['ID']);

			$supplement = explode(",", $device['Supplement']);
			$propertySupplement = Array();
			foreach($supplement as $id) {
				$propertySupplement[] = array("ID" => $id);
			}

			//Konfiguration je nach Typ
			if ($device['Name'] != "Group") {
				if ($device['Awning'] == "yes") {
					IPS_SetProperty($InsID, "Awning", true);
				}
			}

			IPS_SetProperty($InsID, "Supplement", json_encode($propertySupplement));
			IPS_ApplyChanges($InsID);

		}
		
		
		
		private function CheckForInstancesIDs($result) {

			$instanceIDs = IPS_GetInstanceList();
			$childrenIDs = Array();
			$eGateID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
			foreach ($instanceIDs as $instanceID) {
				if ((IPS_GetInstance($instanceID)['ConnectionID'] == $eGateID) && ($instanceID != $this->InstanceID)) {
					$childrenIDs[] = $instanceID;
				}
			}

			foreach ($result as $pos => $partResult) {
				$result[$pos]['rowColor'] = "#C0FFC0";
				$result[$pos]['InstanceID'] = "-";
				foreach ($childrenIDs as $childrenID) {
					if (IPS_GetProperty($childrenID, "ID") == $partResult['ID']) {
						$result[$pos]['InstanceID'] = "#" . $childrenID;
						$result[$pos]['rowColor'] = "";
						break;
					}
				}
			}

			return $result;
			
		}



		private function CheckForHearingIDs($result) {

			foreach ($result as $pos => $partResult) {
				if ($partResult['InstanceID'] != "-") {
					$instanceIDOfPartResult = substr($partResult['InstanceID'], 1);
					if (IPS_InstanceExists($instanceIDOfPartResult)) {
						$deviceSupplement = json_decode(IPS_GetProperty($instanceIDOfPartResult, "Supplement"), true);
						$simpleSupplementArray = Array();
						foreach ($deviceSupplement as $singleSupplement) {
							$simpleSupplementArray[] = $singleSupplement["ID"];
						}
						if ($result[$pos]['Supplement'] != implode(",",$simpleSupplementArray)) {
							$result[$pos]['rowColor'] = "#FFC0C0";
						}
					}
				}
			}

			return $result;

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
					foreach ($linkArray as $key2 => $valueArray) {
						if (($explodedValue[1] === $valueArray[0]) && ($explodedValue[2] === $valueArray[1])) {
							$explodedValueArray = explode(",", $valueArray[3]);
							if ($explodedValueArray[0] === "RepeaterOnly=0") {
								$eGate1Array[$key]["Receiver"][] = $valueArray[2];
							}
						}
					}
					if (isset($eGate1Array[$key]["Receiver"])) {
						if (sizeof($eGate1Array[$key]["Receiver"]) > 1) {

							$onlyReceiver = $eGate1Array[$key]["Receiver"];
							foreach($onlyReceiver as $RecIDkey => $RecID) {
								$explodedValue2 = explode("~", $receiverArray[$RecID]);
								$onlyReceiver[$RecIDkey] = $explodedValue2[1];
							}
							$onlyReceiver = array_unique($onlyReceiver);

							if (sizeof($onlyReceiver) > 1) {
									$eGate1Array[$key]["Grouptype"] = "Group";
							}
							else {
								$eGate1Array[$key]["Grouptype"] = $onlyReceiver[0] ." Group";
							}
						}
						else {
							$eGate1Array[$key]["Grouptype"] = false;
						}
					}
					else {
						unset($eGate1Array[$key]);
					}
				}

				$eGate1Array = array_values($eGate1Array);

				if (sizeof($eGate1Array) > 0) {
					$hearingArray = Array();
					foreach ($eGate1Array as $value) {
						$groupValue = Array();
						if ($value["Grouptype"] == "Group") {
							$Awning = "---";
							foreach ($value['Receiver'] as $ID) {
								$groupValue[] = $ID;
							}
						}
						else {
							if ($value["Grouptype"] == false) {
								$explodedValue = explode("~", $receiverArray[$value['Receiver'][0]]);
								$value["Grouptype"] = $explodedValue[1];
								if (strpos($explodedValue[4], "NoSlatAdjustment=1") != FALSE) {
									$Awning = "yes";
								}
								else {
									$Awning = "no";
								}
								$groupValue[] = $value['Receiver'][0];
							}
							else {
								$Awning = "no";
								foreach ($value['Receiver'] as $ID) {
									$groupValue[] = $ID;
								}
							}

						}
						$row = array("ID" => $value['ID'], "Name" => $value["Grouptype"], "Group" => implode(",", $groupValue), "Awning" => $Awning);
						$hearingArray[$value['ID']] = $groupValue;
						$result[] = $row;
					}

					//Building the supplement
					foreach ($hearingArray as $key => $hearerID) {
						$supplement = Array();
						foreach ($result as $partResult) {
								$groupArray = explode(",", $partResult['Group']);
								if (sizeof(array_intersect($hearerID, $groupArray)) == sizeof($hearerID)) {
									if (($key != $partResult['ID'])) {
										$supplement[] = $partResult['ID'];
									}
								}
						}
						foreach ($result as $pos => $partResult) {
							if ($key == $partResult['ID']) {
								$result[$pos]['Supplement'] = implode(",", $supplement);
							}
						}						
					}
					
					$result = $this->CheckForInstancesIDs($result);
					$result = $this->CheckForHearingIDs($result);
				}
			}

			return $result;
		}

		
		
		private function GetGUIDforModuleType($Modultype) {

			switch ($Modultype) {
				case "MX FESLIM":
				case "MX FE SLIM":
				case "MX FESLIM Group":
					return "{0A5C3DFA-CD52-4529-82F1-99DCFCF8A7A2}";

				case "MX FEPRO":
				case "MX FEPRO Group":
				case "MX FEUP3":
				case "MX FEUP3 Group":
				case "MX FE SLIM 24V":
					return "{3AA1A627-78B0-4E17-9206-0BB012094D1C}";

				case "LX RLUP10A":
				case "LX RLUP10A Group":
				case "LX RLUP1A":
				case "LX RLUP1A Group":
				case "LX Plugin SWITCH":
					return "{E498DF29-57B1-48F5-8C13-A4673EE0EF9E}";

				case "LX DIMM NO LIMIT":
				case "LX DIMM NO LIMIT Group":
				case "LX DIMM RETROFIT":
				case "LX DIMM RETROFIT Group":
				case "LX Plugin DIMMER":
				case "LX DALA":
					return "{5ED1AA15-6D8B-4DA8-B1C8-781D24442288}";

				case "Group":
					return "{7F5C8432-CEAC-45A7-BF96-4BBC3CF04B57}";

				case "SWW SOL":
				case "SWRW":
					return "{B3F0007D-44EE-460B-81D1-5A74C85EE29C}";
			}
			
			return "";
			
		}
		
	}

?>
