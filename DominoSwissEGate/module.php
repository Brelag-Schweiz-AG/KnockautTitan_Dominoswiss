<?
class DominoSwissEGate extends IPSModule {
	
	public function Create(){
		$isFusionONE = file_exists("/mnt/system/.skin");

		//Never delete this line!
		parent::Create();

		$this->RegisterPropertyInteger("MessageDelay", 250);
		$this->RegisterPropertyInteger("Mode", $isFusionONE ? 1 : 0);
		
		$this->RegisterVariableString("Name","Name");
		$this->RegisterVariableString("ID",$this->Translate("DeviceID"));
		$this->RegisterVariableString("Type",$this->Translate("Type"));
		$this->RegisterVariableString("Firmware","Firmware");
		$this->RegisterVariableInteger("Serial",$this->Translate("Serialnumber"));
		
		$this->RegisterTimer("DeviceInfoGetTimer", 60 * 1000, 'BRELAG_SendDeviceInfoGet($_IPS[\'TARGET\']);');
		
		if ($isFusionONE) {
			$this->RequireParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}"); //SerialPort
		}
		else {
			$this->RequireParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}"); //ClientSocket
		}
	}

	
	
	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	
	
	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();

		switch ($this->ReadPropertyInteger("Mode")) {
			case 0:
				$this->ForceParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}");
				break;
			case 1:
				$this->ForceParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}");
				break;
		}
	}
	
	
	
	public function GetConfigurationForParent() {

		switch ($this->ReadPropertyInteger("Mode")) {
			case 0: // eGate LAN
				return "";
				
			case 1: // eGate direct
				$isFusionONEMetal = file_exists("/mnt/system/.uart");
				if ($isFusionONEMetal) {
					// Since Kernel 6.1+ Serial interfaces are named after the real number. Prefer ttyAMA3 and fall back to old behaviour using ttyAMA1 for older Kernels.
					if (file_exists("/dev/ttyAMA3")) {
						return "{\"Port\":\"/dev/ttyAMA3\", \"BaudRate\": \"115200\", \"StopBits\": \"1\", \"DataBits\": \"8\", \"Parity\": \"None\"}";
					}
					else {
						return "{\"Port\":\"/dev/ttyAMA1\", \"BaudRate\": \"115200\", \"StopBits\": \"1\", \"DataBits\": \"8\", \"Parity\": \"None\"}";
					}
				} else {
					return "{\"Port\":\"/dev/ttyAMA0\", \"BaudRate\": \"115200\", \"StopBits\": \"1\", \"DataBits\": \"8\", \"Parity\": \"None\"}";
				}

			default:
				break;
		}
	}

	
	
	public function ForwardData($JSONString){

		$fssTransmitParameter = json_decode($JSONString);

		$CheckNr = (isset($fssTransmitParameter->CheckNr) ? $fssTransmitParameter->CheckNr : null);

		if (IPS_SemaphoreEnter($_IPS['SELF'], 20 * $this->ReadPropertyInteger("MessageDelay"))) {
			$data = $this->GetDataString(
				$fssTransmitParameter->Instruction,
				$fssTransmitParameter->ID,
				$fssTransmitParameter->Command,
				$fssTransmitParameter->Value,
				$fssTransmitParameter->Priority,
				$CheckNr,
				true
			);
			$this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => $data)));
			IPS_Sleep($this->ReadPropertyInteger("MessageDelay"));
			IPS_SemaphoreLeave($_IPS['SELF']);
		}
		
		if ($fssTransmitParameter->Instruction != 200) {
			$emulateData = $this->GetDataString(
				$fssTransmitParameter->Instruction,
				$fssTransmitParameter->ID,
				$fssTransmitParameter->Command,
				$fssTransmitParameter->Value,
				$fssTransmitParameter->Priority,
				$CheckNr,
				false
			);
			$this->ReceiveData(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Buffer" => $emulateData)));
		}
	}
	
	

	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);

		$bufferData = $this->GetBuffer("DataBuffer");
		$bufferData .= $data->Buffer;

		$this->SendDebug("BufferIn", $bufferData, 0);

		$bufferParts = explode("\r", $bufferData);

		//Letzten Eintrag nicht auswerten, da dieser nicht vollständig ist.
		if (sizeof($bufferParts) > 1) {
			for ($i = 0; $i < sizeof($bufferParts) - 1; $i++) {
				$this->SendDebug("Data", $bufferParts[$i], 0);
				$argumentsArray = explode(";", $bufferParts[$i]);
				array_pop($argumentsArray);

				$valueArray = Array();
				foreach ($argumentsArray as $argument) {
					$value = explode("=", $argument);
					$valueArray[$value[0]] = $value[1];
				}
				
				$this->SendDebug("ArrayDevice", print_r($valueArray, true), 0);
				
				if (array_key_exists("DeviceName", $valueArray)) {
					SetValue($this->GetIDForIdent("Name"), $valueArray["DeviceName"]);
					SetValue($this->GetIDForIdent("ID"), $valueArray["DeviceId"]);
					SetValue($this->GetIDForIdent("Type"), $valueArray["DeviceType"]);
					SetValue($this->GetIDForIdent("Firmware"), $valueArray["FwVersion"]);
					SetValue($this->GetIDForIdent("Serial"), $valueArray["SerialNr"]);
				}
				else {
					$this->SendDataToChildren(json_encode(Array("DataID" => "{BA70E3E8-68D2-4E3B-8C64-BBB86F188473}", "Values" => $valueArray)));
				}
			}
		}

		$bufferData = $bufferParts[sizeof($bufferParts) - 1];

		//Übriggebliebene Daten auf den Buffer schreiben
		$this->SetBuffer("DataBuffer", $bufferData);

	}
	
	

	public function SendDeviceInfoGet() {

		return $this->ForwardData(json_encode(Array("DataID" => "{C24CDA30-82EE-46E2-BAA0-13A088ACB5DB}", "Instruction" => 200, "ID" => 0, "Command" => 0, "Value" => 0, "Priority" => 0)));
	}

	/**
	 * Die eGate hat CheckNr zur überprüfung des Befehls und leitet den Befehl nur an
	 * das Funktnetz weiter wen die CheckNr übereinstimmt.
	 */
	private function GetCheckNRForCommand($Command) {
		
		switch ($Command) {
			case 1:
				return 3415347;
			
			case 2:
				return 2764516;
			
			case 3:
				return 2867016;
			
			case 4:
				return 973898;
			
			case 5:
				return 5408219;
			
			case 6:
				return 3111630;
			
			case 7:
				return 4000544;
			
			case 8:
				return 4675523;
			
			case 9:
				return 718953;
			
			case 11:
				return 392895;
			
			case 12:
				return 3510908;
			
			case 13:
				return 5304418;
			
			case 15:
				return 1779188;
			
			case 16:
				return 5810117;
			
			case 17:
				return 2196219;
			
			case 18:
				return 7171965;
			
			case 19:
				return 6643442;
			
			case 20:
				return 4917508;
			
			case 21:
				return 7669942;
			
			case 22:
				return 2108857;
			
			case 23:
				return 6886678;
			
			case 24:
				return 5711815;
			
			case 25:
				return 1875207;
			
			case 26:
				return 6123675;

			// MaxFlex
			case 43:
				return 2942145;		
		}
	}

	

	private function GetDataString($Instruction, $ID, $Command, $Value, $Priority, $CustomCheckNr, $SendCheckNr){

		switch ($Instruction) {
			case 200:
				$result = "DeviceInfoGet;";
				break;
				
			default:
				$result = "Instruction=" . $Instruction . ";ID=" . $ID . ";Command=" . $Command . ";Value=" . $Value . ";Priority=" . $Priority . ";";
				if ($SendCheckNr) {
					if ($CustomCheckNr == null || $CustomCheckNr == 0) {
						$checkNr = $this->GetCheckNRForCommand($Command);
					} else {
						$checkNr = $CustomCheckNr;
					}
					$result .= "CheckNr=" . $checkNr . ";";
				}
				break;
		}

		return ($result . chr(13));
	}
}
?>
