<?
class DominoSwissEGate extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		
		$this->RequireParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}"); //ClientSocket
		
	}

	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();
		
	}

	public function ForwardData($JSONString){
		
		$fssTransmitParameter = json_decode($JSONString);
		
		$checkNr = $this->GetCheckNRForCommand($fssTransmitParameter->Command);
		$data = "Instruction=1;ID=". $fssTransmitParameter->ID .";Command=". $fssTransmitParameter->Command .";Priority=0;CheckNr=". $checkNr .";";
		
		$this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => $data . chr(13))));
		
		$emulateData = "Instruction=1;ID=". $fssTransmitParameter->ID .";Command=". $fssTransmitParameter->Command .";Value=0;Priority=0;". chr(13) ."";
		$this->ReceiveData(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Buffer" => $emulateData)));
	}
	

	public function ReceiveData($JSONString) {
		
		$data = json_decode($JSONString);
		$this->SendDebug("BufferIn", $data->Buffer, 0);
		
		//FIXME: Buffer sammeln!
		
		$argumentsArray = explode(";", $data->Buffer);
		array_pop($argumentsArray);
		
		$valueArray = Array();
		foreach($argumentsArray as $argument) {
			$value = explode("=", $argument);
			$valueArray[$value[0]] = $value[1];
		}
		
		$this->SendDataToChildren(json_encode(Array("DataID" => "{BA70E3E8-68D2-4E3B-8C64-BBB86F188473}", "Values" => $valueArray)));
	}

	private function GetCheckNRForCommand(int $Command) {
		
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
					
		}
	}

}
?>