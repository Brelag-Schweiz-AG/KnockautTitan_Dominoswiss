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
		
			//Add category for actuators
			$data->actions[0]->values[] = [
					"id" => 1,
					"expanded" => true,
					"ID" => "",
					"Name" => "",
					"Type" => $this->Translate("Actuators"),
					"Awning" => "",
					"Group" => "",
					"Supplement" => ""
			];
			
			//Add category for sensors
			$data->actions[0]->values[] = [
					"id" => 2,
					"expanded" => true,
					"ID" => "",
					"Name" => "",
					"Type" => $this->Translate("Sensors"),
					"Awning" => "",
					"Group" => "",
					"Supplement" => ""
			];
			
			$findInstanceID = function($moduleID, $id) {
				$eGateID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
				foreach (IPS_GetInstanceList() as $instanceID) {
					if($instanceID != $this->InstanceID) {
						$instance = IPS_GetInstance($instanceID);
						if ($instance['ConnectionID'] == $eGateID) {
							//Also check ModuleID to distinguish actuators and sensors
							if($instance["ModuleInfo"]["ModuleID"] == $moduleID) {
								$configuration = json_decode(IPS_GetConfiguration($instanceID), true);
								if(isset($configuration["ID"]) && ($configuration["ID"] == $id)) {
									return $instanceID;
								}
							}
						}
					}
				}
				
				return 0;
			};
			
			$buildSupplementList = function($ids) {
				$result = [];
				foreach($ids as $id) {
					$result[] = ["ID" => $id];
				}
				return $result;
			};
			
			$channels = $this->BuildChannels();
			
			//Add all Actuators
			foreach($channels["receivers"] as $id => $channel) {

				//Use a special group if we have mixed types. This is defined as an empty moduleID
				if($channel["Type"] == "") {
					$moduleID = "{7F5C8432-CEAC-45A7-BF96-4BBC3CF04B57}";
				} else {
					$moduleID = $this->GetModuleIDForType($channel["Type"]);
				}
				
				$typeName = $channel["Type"];
				
				//append Group into name
				if($channel["IsGroup"]) {
					$typeName .= " " . $this->Translate("Group");
				}

				$displayName = !empty($channel["Name"]) ? $channel["Name"] : $typeName;
				
				$value = [
					"ID" => $id,
					"Name" => $channel["Name"],
					"Type" => $typeName,
					"Awning" => isset($channel["Awning"]) ? ($channel["Awning"] ? $this->Translate("yes") : $this->Translate("no")) : "---",
					"Group" => implode(", ", $channel["Group"]),
					"Supplement" => implode(", ", $channel["Supplement"]),
					"instanceID" => $findInstanceID($moduleID, $id),
					"name" => sprintf("%s (ID: %d)", $displayName, $id),
					"parent" => 1,
					"create" => [
						"moduleID" => $moduleID,
						"configuration" => [
							"ID" => $id
						],
						"position" => $id
					]
				];
				
				//Some properties are only available for receivers
				$value["create"]["configuration"]["Supplement"] = json_encode($buildSupplementList($channel["Supplement"]));
			
				//Awning property is only available for non groups and only some devices
				if(isset($channel["Awning"])) {
					$value["create"]["configuration"]["Awning"] = $channel["Awning"];
				}
				
				$data->actions[0]->values[] = $value;
			}
			
			//Add all Actuators
			foreach($channels["transmitters"] as $id => $channel) {
				
				$moduleID = $this->GetModuleIDForType($channel["Type"]);
				
				$value = [
					"ID" => $id,
					"Name" => $channel["Name"],
					"Type" => $channel["Type"],
					"Awning" => "---",
					"Group" => "",
					"Supplement" => "",
					"instanceID" => $findInstanceID($moduleID, $id),
					"name" => sprintf("%s (%s) (ID: %d)", $channel["Type"], $channel["Name"], $id),
					"parent" => 2,
					"create" => [
						"moduleID" => $moduleID,
						"configuration" => [
							"ID" => $id
						],
						"position" => $id
					]
				];
				
				$data->actions[0]->values[] = $value;
			}
			
			return json_encode($data);
		
		}

		
		
		private function ParseFileData() {

			//array for our parsed representation, with defaults
			$config = [
				"Transmitter" => [],
				"Receiver" => [],
				"ReceiverGroup" => [],
				"link" => [],
				"eGate1" => [],
				"eGateDeviceNr" => null
			];

			$data = base64_decode($this->ReadPropertyString("FileData"));
			
			if(!trim($data)) {
				return $config; //we have nothing to do. return defaults
			}
			
			//remove characters which the ini scanner does not like
			$data = str_replace(";", "~", $data);
			
			//parse ini compatible format
			$ini = parse_ini_string($data, true, INI_SCANNER_RAW);

			//parse Transmitter
			$transmitter = $ini['Transmitter'];
			$transmitterFields = explode("~", $transmitter['//Index']);
			$transmitterFields[4] = "Location"; //Rename this field
			unset($transmitter['//Index']);
			$index = 1;
			foreach($transmitter as $row) {
				$row = explode("~", $row);
				$configTransmitter= ["Index" => $index++];
				foreach($row as $key => $value) {
					if($transmitterFields[$key] != "") {
						$configTransmitter[$transmitterFields[$key]] = $value;
					}
				}
				$config["Transmitter"][] = $configTransmitter;
			}
			
			//parse receiver
			$receiver = $ini['Receiver'];
			$receiverFields = explode("~", $receiver['//Index']);
			$receiverFields[4] = "Options"; //Rename this field
			unset($receiver['//Index']);
			$index = 1;
			foreach($receiver as $row) {
				$row = explode("~", $row);
				$configReceiver= ["Index" => $index++];
				foreach($row as $key => $value) {
					if($receiverFields[$key] != "") {            
						$configReceiver[$receiverFields[$key]] = $value;
					}
				}
				$config["Receiver"][] = $configReceiver;
			}
			
			//parse receiver options
			foreach($config["Receiver"] as &$receiver) {
				$options = explode(",", $receiver["Options"]);
				$receiver["Options"] = [];
				foreach($options as $option) {
					$option = explode("=", $option);
					if($option[0] != "") {
						$receiver["Options"][$option[0]] = $option[1];
					}
				}
			}
			
			//parse ReceiverGroup if available
			if(isset($ini['ReceiverGroup'])) {
				$receiverGroup = $ini['ReceiverGroup'];
				unset($receiverGroup['//Index']);
				$index = 1;
				foreach($receiverGroup as $row) {
					$row = explode("~", $row);
					if(count($row) >= 3) {
						$receiverIndices = explode(",", $row[2]);
						$receiverIndices = array_map('trim', $receiverIndices);
						$config["ReceiverGroup"][] = [
							"Index" => $index++,
							"GroupID" => $row[0],
							"Name" => $row[1],
							"ReceiverIndices" => $receiverIndices
						];
					}
				}
			}
			
			//parse link
			$link = $ini['Link'];
			$linkFields = explode("~", $link['//Index']);
			$linkFields[3] = "Options"; //Rename this field
			unset($link['//Index']);
			$index = 1;
			foreach($link as $row) {
				$row = explode("~", $row);
				$configLink= ["Index" => $index++];
				foreach($row as $key => $value) {
					if($linkFields[$key] != "") {            
						$configLink[$linkFields[$key]] = $value;
					}
				}
				$config["link"][] = $configLink;
			}
			
			//parse link options
			foreach($config["link"] as &$link) {
				$options = explode(",", $link["Options"]);
				$link["Options"] = [];
				foreach($options as $option) {
					$option = explode("=", $option);
					if($option[0] != "") {
						$link["Options"][$option[0]] = $option[1];
					}
				}
			}
			
			//Extract eGate DeviceNr for consecutive group name logic
			if(isset($ini['eGate1']['DeviceNr'])) {
				$config["eGateDeviceNr"] = $ini['eGate1']['DeviceNr'];
			}
			
			//remove all egate1 options which we do not need
			foreach ($ini['eGate1'] as $key => $value) {
				if ($key == "//Index") {
					break;
				}
				unset($ini['eGate1'][$key]);
			}
			
			//parse egate1
			$egate1 = $ini['eGate1'];
			$egate1Fields = explode("~", $egate1['//Index']);
			$egate1Fields[4] = "Location"; //Rename this field

			unset($egate1['//Index']);
			$index = 1;
			foreach($egate1 as $row) {
				$row = explode("~", $row);
				$configeGate1= ["Index" => $index++];
				foreach($row as $key => $value) {
					if($egate1Fields[$key] != "") {
						$configeGate1[$egate1Fields[$key]] = $value;
					}
				}
				$config["eGate1"][] = $configeGate1;
			}
			
			return $config;
			
		}
		
		
		
		public function BuildChannels() {
			$config = $this->ParseFileData();
			
			$receiverChannels = $this->BuildReceiverChannels($config);
			$transmitterChannels = $this->BuildTransmitterChannels($config);
			
			$this->ResolveReceiverChannelDetails($receiverChannels, $config);
			$this->DeduplicateReceiverChannels($receiverChannels);
			$this->BuildReceiverSupplements($receiverChannels);
			$this->ResolveTransmitterChannelDetails($transmitterChannels, $config);
			
			return [
				"receivers" => $receiverChannels,
				"transmitters" => $transmitterChannels
			];
			
		}

		private function GetReceiverByIndex(array $config, $index) {
			foreach($config["Receiver"] as $receiver) {
				if($receiver["Index"] == $index) {
					return $receiver;
				}
			}
			return null;
		}

		private function GetTransmitterByIndex(array $config, $index) {
			foreach($config["Transmitter"] as $transmitter) {
				if($transmitter["Index"] == $index) {
					return $transmitter;
				}
			}
			return null;
		}

		private function GetEGate1ID(array $config, $transmitterIndex, $channel) {
			foreach($config["eGate1"] as $eGate1) {
				if($eGate1["TransmitterIndex"] == $transmitterIndex && $eGate1["Channel"] == $channel) {
					return $eGate1["ID"];
				}
			}
			return null;
		}

		private function BuildReceiverChannels(array $config) {
			//Go through all (non repeater) link channels for building the grouping (and associate with eGate IDs)
			$receiverChannels = [];
			foreach($config["link"] as $link) {
				if(isset($link["Options"]["RepeaterOnly"]) && ($link["Options"]["RepeaterOnly"] == 0)) {
					$id = $this->GetEGate1ID($config, $link["TransmitterIndex"], $link["Channel"]);
					if($id != null) {
						$receiverChannels[$id]["Group"][] = $link["ReceiverIndex"];
						if(!isset($receiverChannels[$id]["Supplement"])) {
							$receiverChannels[$id]["Supplement"] = [];
						}
					}
				}
			}
			return $receiverChannels;
		}

		private function BuildTransmitterChannels(array $config) {
			//Search a few special transmitter devices and also add them
			$transmitterChannels = [];
			foreach($config["link"] as $link) {
				$transmitter = $this->GetTransmitterByIndex($config, $link["TransmitterIndex"]);
				if($transmitter && $this->IsSensorType($transmitter["Type"], $link["Channel"])) {
					$id = $this->GetEGate1ID($config, $link["TransmitterIndex"], $link["Channel"]);
					if($id != null) {
						$transmitterChannels[$id]["Group"][] = $link["TransmitterIndex"];
						$transmitterChannels[$id]["Supplement"] = [];
					}
				}
			}
			return $transmitterChannels;
		}

		private function ResolveReceiverChannelDetails(array &$receiverChannels, array $config) {
			//Go through all receiver channels and mark as Group or obtain the device type, name and awning
			foreach($receiverChannels as $id => &$channel) {
				if(sizeof($channel["Group"]) > 1) {
					$types = [];
					foreach($channel["Group"] as $groupIndex) {
						$receiver = $this->GetReceiverByIndex($config, $groupIndex);
						$types[] = $receiver["Type"];
					}
					$types = array_unique($types);
					$channel["Type"] = (sizeof($types) == 1) ? $types[0] : "";
					$channel["Name"] = $this->FindGroupName($channel["Group"], $config);
					$channel["IsGroup"] = true;
				} else {
					$device = $this->GetReceiverByIndex($config, $channel["Group"][0]);
					$channel["Type"] = $device["Type"];
					$channel["Name"] = $device["Name"];
					if(isset($device["Options"]["NoSlatAdjustment"])) {
						$channel["Awning"] = ($device["Options"]["NoSlatAdjustment"] == 1);
					}
					$channel["IsGroup"] = false;
				}
			}
			unset($channel);
		}

		/**
		 * Compute a human-readable group name for a set of receiver indices.
		 *
		 * Rules:
		 * 1) Exact group match: If an entry in ReceiverGroup matches the exact set of
		 *    receiver indices (ignoring order), use that group's Name.
		 * 2) Consecutive numeric range: If all receiver names have numeric suffixes
		 *    (e.g., "G.EG.0003") and the numbers form a consecutive sequence, use
		 *    "firstName - lastName" (based on min/max numeric suffix).
		 * 3) Two receivers: If there are exactly two receivers, order them by numeric
		 *    suffix when available and use "name1 + name2".
		 * 4) Otherwise return an empty string to use a generic name elsewhere.
		 *
		 * Examples:
		 * - groupIndices [23, 24] with ReceiverGroup "Hello23" -> "Hello23".
		 * - groupIndices [3, 1, 2] -> "G.EG.0001 - G.EG.0003" (consecutive).
		 * - groupIndices [24, 23] -> "G.EG.0023 + G.EG.0024" (two items, ordered).
		 * - groupIndices [7, 10, 12] (not consecutive) -> "".
		 */
		private function FindGroupName(array $groupIndices, array $config) {
			// 1) Try exact match with ReceiverGroup definitions (order-insensitive)
			$groupName = "";
			$sortedGroupIndices = $groupIndices;
			sort($sortedGroupIndices, SORT_NUMERIC);
			foreach($config["ReceiverGroup"] as $receiverGroup) {
				$rgIndices = $receiverGroup["ReceiverIndices"];
				sort($rgIndices, SORT_NUMERIC);
				if($sortedGroupIndices === $rgIndices) {
					$groupName = $receiverGroup["Name"];
					break;
				}
			}
			if($groupName != "") {
				return $groupName;
			}
			// 2) Extract numeric suffixes from receiver names (e.g., "G.EG.0003" -> 3)
			$receiverNumbersByIndex = [];
			foreach($groupIndices as $idx) {
				$receiver = $this->GetReceiverByIndex($config, $idx);
				if(preg_match('/\.(\d+)$/', $receiver["Name"], $matches)) {
					$receiverNumbersByIndex[$idx] = intval($matches[1]);
				} else {
					$receiverNumbersByIndex[$idx] = null;
				}
			}
			
			// 2a) Include eGate DeviceNr for consecutive check (Titan Controller address)
			$eGateNumber = null;
			if(isset($config["eGateDeviceNr"]) && $config["eGateDeviceNr"]) {
				if(preg_match('/\.(\d+)$/', $config["eGateDeviceNr"], $matches)) {
					$eGateNumber = intval($matches[1]);
				}
			}
			
			// 2b) Check if numeric suffixes are present and form a consecutive range
			$isConsecutive = true;
			$sortedNumbers = array_values($receiverNumbersByIndex);
			// Add eGate number to the list for consecutive check
			if($eGateNumber !== null) {
				$sortedNumbers[] = $eGateNumber;
			}
			sort($sortedNumbers);
			foreach($sortedNumbers as $num) {
				if($num === null) {
					$isConsecutive = false;
					break;
				}
			}
			if($isConsecutive) {
				for($i = 1; $i < count($sortedNumbers); $i++) {
					if($sortedNumbers[$i] != $sortedNumbers[$i-1] + 1) {
						$isConsecutive = false;
						break;
					}
				}
			}
			// 2c) Consecutive: use min/max endpoints for range naming (only actual receivers, not eGate)
			if($isConsecutive && count($groupIndices) > 1) {
				// Find min/max from actual receivers only (not eGate)
				$minNum = PHP_INT_MAX; $maxNum = PHP_INT_MIN; $minIdx = null; $maxIdx = null;
				foreach($receiverNumbersByIndex as $idx => $num) {
					if($num !== null) {
						if($num < $minNum) { $minNum = $num; $minIdx = $idx; }
						if($num > $maxNum) { $maxNum = $num; $maxIdx = $idx; }
					}
				}
				
				$firstReceiver = $this->GetReceiverByIndex($config, $minIdx);
				$lastReceiver = $this->GetReceiverByIndex($config, $maxIdx);
				return $firstReceiver["Name"] . " - " . $lastReceiver["Name"];
			}
			// 3) Two receivers: order by numeric suffix (if present) and join with '+'
			if(count($groupIndices) == 2) {
				$idxA = $groupIndices[0];
				$idxB = $groupIndices[1];
				$numA = $receiverNumbersByIndex[$idxA];
				$numB = $receiverNumbersByIndex[$idxB];
				if($numA !== null && $numB !== null && $numA > $numB) { $tmp = $idxA; $idxA = $idxB; $idxB = $tmp; }
				$firstReceiver = $this->GetReceiverByIndex($config, $idxA);
				$secondReceiver = $this->GetReceiverByIndex($config, $idxB);
				return $firstReceiver["Name"] . " + " . $secondReceiver["Name"];
			}
			// 4) No specific naming rule applies
			return "";
		}

		private function BuildReceiverSupplements(array &$receiverChannels) {
			foreach($receiverChannels as $id => $channel) {
				//Go through each "group" channel und if and check if we are inside
				foreach($receiverChannels as $idx => $channelx) {
					if ($id != $idx) {
						if (array_intersect($channel["Group"], $channelx["Group"]) == $channel["Group"]) {
							$receiverChannels[$id]["Supplement"][] = $idx;
						}
					}
				}
				sort($receiverChannels[$id]["Supplement"]);
			}
		}

		private function DeduplicateReceiverChannels(array &$receiverChannels) {
			$seen = [];
			$duplicates = [];
			foreach($receiverChannels as $id => $channel) {
				$group = $channel["Group"];
				sort($group, SORT_NUMERIC);
				$signature = implode(",", $group);
				if(isset($seen[$signature])) {
					$duplicates[] = $id;
				} else {
					$seen[$signature] = $id;
				}
			}
			foreach($duplicates as $dupId) {
				unset($receiverChannels[$dupId]);
			}
		}

		private function ResolveTransmitterChannelDetails(array &$transmitterChannels, array $config) {
			foreach($transmitterChannels as $id => &$channel) {
				$device = $this->GetTransmitterByIndex($config, $channel["Group"][0]);
				$channel["Type"] = $device["Type"];
				$channel["Name"] = $device["Name"];
			}
			unset($channel);
		}

		
		
		private function IsSensorType($Type, $Channel) {

			switch ($Type) {
				case "MX FS1W TM":
				case "MX FS1B TM":
				case "MX FS1WF TM":
				case "MX FS1BF TM":
					return true;

				case "SWW SOL":
				case "SWRW":
					return ($Channel == 1); //This is an explicit exception for weather station devices
				
				case "PIR DC":
				case "MAG TFK":
				case "MAG TFK INV":
				case "UTC":
					return true;
			}
			
			return false;
			
		}
		
		
		
		private function GetModuleIDForType($Type) {

			switch ($Type) {
				case "MX FESLIM":
				case "MX FE SLIM":
					return "{0A5C3DFA-CD52-4529-82F1-99DCFCF8A7A2}";

				case "MX FE ULTRA":
					return "{FE8DB15B-6FD0-F9A7-D7FD-B5188D368528}";

				case "MX FEPRO":
				case "MX FE PRO":
				case "MX FEUP3":
				case "MX FE UP3":
				case "MX FE SLIM 24V":
					return "{3AA1A627-78B0-4E17-9206-0BB012094D1C}";

				case "MX FS1W TM":
				case "MX FS1B TM":
				case "MX FS1WF TM":
				case "MX FS1BF TM":
					return "{61CD5357-4D1E-E0CF-CBE0-08EAA8478A39}";

				case "LX RLUP10A":
				case "LX RLUP1A":
				case "LX Plugin SWITCH":
					return "{E498DF29-57B1-48F5-8C13-A4673EE0EF9E}";

				case "LX DIMM NO LIMIT":
				case "LX DIMM RETROFIT":
				case "LX Plugin DIMMER":
				case "LX DALA":
					return "{5ED1AA15-6D8B-4DA8-B1C8-781D24442288}";
					
				case "SWW SOL":
				case "SWRW":
					return "{B3F0007D-44EE-460B-81D1-5A74C85EE29C}";
					
				case "PIR DC":
					return "{CE892EF8-C01D-43D2-BBA7-D5B54484795E}";

				case "MAG TFK":
				case "MAG TFK INV":
					return "{26AE9337-13A8-4BF8-99D0-EE11D91FDEE2}";
					
				case "UTC":
					return "{4E1FBB10-9283-7779-6D79-7D190ECE33FF}";
			}
			
			return "";
			
		}
		
	}

?>
