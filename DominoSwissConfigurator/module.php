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
		
			$findInstanceID = function($id) {
				$eGateID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
				foreach (IPS_GetInstanceList() as $instanceID) {
					if($instanceID != $this->InstanceID) {
						if (IPS_GetInstance($instanceID)['ConnectionID'] == $eGateID) {
							if(IPS_GetProperty($instanceID, "ID") == $id) {
								return $instanceID;
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
			foreach($channels as $id => $channel) {

				//Use a special group if we have mixed types. Otherwise filter the " Group" keyword and use name to get ModuleID
				if($channel["Type"] == "Group") {
					$moduleID = "{7F5C8432-CEAC-45A7-BF96-4BBC3CF04B57}";
				} else {
					$moduleID = $this->GetModuleIDForType(str_replace(" Group", "", $channel["Type"]));
				}
				
				$value = [
					"ID" => $id,
					"Name" => $channel["Name"],
					"Type" => $channel["Type"],
					"Awning" => isset($channel["Awning"]) ? ($channel["Awning"] ? "yes" : "no") : "---",
					"Group" => implode(", ", $channel["Group"]),
					"Supplement" => implode(", ", $channel["Supplement"]),
					"instanceID" => $findInstanceID($id),
					"create" => [
						"moduleID" => $moduleID,
						"configuration" => [
							"ID" => $id
						]
					]
				];
				
				//Some properties are only available for receivers
				if($channel["IsReceiver"]) {
					$value["create"]["configuration"]["Supplement"] = json_encode($buildSupplementList($channel["Supplement"]));
				
					//Awning property is only available for non groups and only some devices
					if(isset($channel["Awning"])) {
						$value["create"]["configuration"]["Awning"] = $channel["Awning"];
					}
				}
				
				$data->actions[0]->values[] = $value;
			}
			
			return json_encode($data);
		
		}

		
		
		private function ParseFileData() {
			
			$data = base64_decode($this->ReadPropertyString("FileData"));
			
			if(!trim($data)) {
				return []; //we have nothing to do
			}
			
			//remove characters which the ini scanner does not like
			$data = str_replace(";", "~", $data);
			
			//parse ini compatible format
			$ini = parse_ini_string($data, true, INI_SCANNER_RAW);
			
			//array for our parsed representation
			$config = [];
			
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
			
			$getReceiverByIndex = function($index) use($config) {
				foreach($config["Receiver"] as $receiver) {
					if($receiver["Index"] == $index) {
						return $receiver;
					}
				}
				return null;
			};
			
			$getTransmitterByIndex = function($index) use($config) {
				foreach($config["Transmitter"] as $transmitter) {
					if($transmitter["Index"] == $index) {
						return $transmitter;
					}
				}
				return null;
			};
			
			$geteGate1ID = function($transmitterIndex, $channel) use($config) {
				foreach($config["eGate1"] as $eGate1) {
					if($eGate1["TransmitterIndex"] == $transmitterIndex && $eGate1["Channel"] == $channel) {
						return $eGate1["ID"];
					}
				}
				return null;
			};			
			
			$channels = [];
			
			//Go through all (non repeater) link channels for building the grouping (and associate with eGate IDs)
			foreach($config["link"] as $link) {
				if(isset($link["Options"]["RepeaterOnly"]) && ($link["Options"]["RepeaterOnly"] == 0)) {
					$id = $geteGate1ID($link["TransmitterIndex"], $link["Channel"]);
					if($id != null) {
						$channels[$id]["Group"][] = $link["ReceiverIndex"];
						$channels[$id]["Supplement"] = [];
						$channels[$id]["IsReceiver"] = true;
						$channels[$id]["IsTransmitter"] = false;
					}
				}
			}

			//Search a few special transmitter devices and also add them if they weren't assigned a group
			foreach($config["link"] as $link) {
				$transmitter = $getTransmitterByIndex($link["TransmitterIndex"]);
				if($this->IsSensorType($transmitter["Type"])) {
					$id = $geteGate1ID($link["TransmitterIndex"], $link["Channel"]);
					if($id != null) {
						if(!isset($channels[$id])) {
							$channels[$id]["Group"][] = $link["TransmitterIndex"];
							$channels[$id]["Supplement"] = [];
							$channels[$id]["IsReceiver"] = false;
							$channels[$id]["IsTransmitter"] = true;
						}
					}
				}
			}
			
			//Go through all channels and mark as Group or obtain the device type, name and awning
			foreach($channels as $id => $channel) {
				if(sizeof($channel["Group"]) > 1) {
					//Check if we have a homogeneous group of the same device
					$types = [];
					foreach($channel["Group"] as $group) {
						$receiver = $getReceiverByIndex($group);
						$types[] = $receiver["Type"];
					}
					$types = array_unique($types);
					
					if(sizeof($types) == 1) {
						$channels[$id]["Type"] = $types[0] . " Group";
					} else {
						$channels[$id]["Type"] = "Group";
					}
					
					$channels[$id]["Name"] = "";
					$channels[$id]["IsGroup"] = true;
				} else {
					//Group is the ReceiverIndex/TransmitterIndex which we can use to get the receiver/transmitter
					if($channel["IsReceiver"]) {
						$device = $getReceiverByIndex($channel["Group"][0]);
					}
					if($channel["IsTransmitter"]) {
						$device = $getTransmitterByIndex($channel["Group"][0]);
					}
					$channels[$id]["Type"] = $device["Type"];
					$channels[$id]["Name"] = $device["Name"];
					if(isset($device["Options"]["NoSlatAdjustment"])) {
						$channels[$id]["Awning"] = ($device["Options"]["NoSlatAdjustment"] == 1);
					}
					$channels[$id]["IsGroup"] = false;
				}
			}
			
			//Go through all channels and build supplement for group channels
			foreach($channels as $id => $channel) {
				if(!$channel["IsGroup"]) {
					//Go through each "group" channel und if and check if we are inside
					foreach($channels as $idx => $channelx) {
						if($channelx["IsGroup"]) {
							foreach($channelx["Group"] as $groupx) {
								if($groupx == $channel["Group"][0]) {
									$channels[$id]["Supplement"][] = $idx;
								}
							}
						}
					}
				}
			}
			
			return $channels;
			
		}

		
		
		private function IsSensorType($Type) {

			switch ($Type) {
				case "SWW SOL":
				case "SWRW":
				case "PIR DC":
				case "UTC":
					return true;
			}
			
			return false;
			
		}
		
		
		
		private function GetModuleIDForType($Type) {

			switch ($Type) {
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
					
				case "SWW SOL":
				case "SWRW":
					return "{B3F0007D-44EE-460B-81D1-5A74C85EE29C}";
					
				case "PIR DC":
					return "{CE892EF8-C01D-43D2-BBA7-D5B54484795E}";
					
				case "UTC":
					return "{4E1FBB10-9283-7779-6D79-7D190ECE33FF}";
			}
			
			return "";
			
		}
		
	}

?>
