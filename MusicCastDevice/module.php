<?php

declare(strict_types=1);

require_once(__DIR__ . "/../libs/autoload.php");

class MusicCastDevice extends IPSModule {
	use ProfileHelper;
	use BufferHelper;

	public function Create() {
		//Never delete this line!
		parent::Create();

		$this->ConnectParent('{9FC1174B-C4C3-8798-0D55-C8FB70846CD1}');

		$this->RegisterPropertyString(Properties::IPADDRESS, '');
		$this->RegisterPropertyString(Properties::MODEL, '');
		$this->RegisterPropertyString(Properties::NAME, '');
		$this->RegisterPropertyString(Properties::SERIALNUMBER, '');
		
		$this->RegisterPropertyBoolean(Properties::AUTOUPDATELISTS, true);
		$this->RegisterPropertyInteger(Properties::AUTOUPDATELISTINTERVAL, 30);

		$this->RegisterProfileIntegerEx(Profiles::CONTROL, Profiles::CONTROL_ICON, '', '', [
			[PlaybackState::NOTHING_ID, PlaybackState::NOTHING_TEXT,  '', -1],
			[PlaybackState::PREVIOUS_ID, PlaybackState::PREVIOUS_TEXT,  '', -1],
			[PlaybackState::PLAY_ID, PlaybackState::PLAY_TEXT,  '', -1],
			[PlaybackState::PAUSE_ID, PlaybackState::PAUSE_TEXT, '', -1],
			[PlaybackState::STOP_ID, PlaybackState::STOP_TEXT,  '', -1],
			[PlaybackState::NEXT_ID, PlaybackState::NEXT_TEXT,  '', -1]
		]);

		$this->RegisterProfileIntegerEx(Profiles::INFORMATION, Profiles::INFORMATION_ICON, '', '', [
			[PlaybackState::NOTHING_ID, PlaybackState::NOTHING_TEXT,  '', -1],
			[PlaybackState::PREVIOUS_ID, PlaybackState::PREVIOUS_TEXT,  '', -1],
			[PlaybackState::PLAY_ID, PlaybackState::PLAY_TEXT,  '', -1],
			[PlaybackState::PAUSE_ID, PlaybackState::PAUSE_TEXT, '', -1],
			[PlaybackState::STOP_ID, PlaybackState::STOP_TEXT,  '', -1],
			[PlaybackState::NEXT_ID, PlaybackState::NEXT_TEXT,  '', -1]
		]);

		$this->RegisterProfileIntegerEx(Profiles::SLEEP, Profiles::SLEEP_ICON, '', '', [
			[Sleep::DISABLED, Sleep::DISABLED_TEXT, '', -1],
			[Sleep::STEP1, Sleep::STEP1_TEXT, '', -1],
			[Sleep::STEP2, Sleep::STEP2_TEXT, '', -1],
			[Sleep::STEP3, Sleep::STEP3_TEXT, '', -1],
			[Sleep::STEP4, Sleep::STEP4_TEXT, '', -1]
		]);

		$this->RegisterProfileBooleanEx(Profiles::MUTE, Profiles::MUTE_ICON, '', '', [
			[true, 'Muted', '', -1],
			[false, 'Unmuted', '', -1]
		]);

		$this->RegisterVariableBoolean(Variables::POWER_IDENT, Variables::POWER_TEXT, '~Switch', 1);
		$this->EnableAction(Variables::POWER_IDENT);

		$control = $this->RegisterVariableInteger(Variables::CONTROL_IDENT, Variables::CONTROL_TEXT, Profiles::CONTROL, 2);
		$this->EnableAction(Variables::CONTROL_IDENT);

		$this->RegisterVariableInteger(Variables::STATUS_IDENT, Variables::STATUS_TEXT, Profiles::INFORMATION, 3);

		// Using RequestAction on variable "Control" to excecute private functions inside scheduled scripts. 
		$this->RegisterTimer(Timers::UPDATE . (string) $this->InstanceID, 0, 'if(IPS_VariableExists(' . (string) $control . ')) RequestAction(' . (string) $control . ', 255);'); 
		$this->RegisterTimer(Timers::UPDATELISTS . (string) $this->InstanceID, 0, 'if(IPS_VariableExists(' . (string) $control . ')) RequestAction(' . (string) $control . ', 254);');
		$this->RegisterTimer(Timers::RESETCONTROL . (string) $this->InstanceID, 0, 'if(IPS_VariableExists(' . (string) $control . ')) RequestAction(' . (string) $control . ', 253);');
				
		$this->RegisterVariableInteger(Variables::VOLUME_IDENT, Variables::VOLUME_TEXT, 'Intensity.100', 4);
		$this->EnableAction(Variables::VOLUME_IDENT);

		$this->RegisterVariableBoolean(Variables::MUTE_IDENT, Variables::MUTE_TEXT, Profiles::MUTE, 5);
		$this->EnableAction(Variables::MUTE_IDENT);

		$this->RegisterVariableInteger(Variables::SLEEP_IDENT, Variables::SLEEP_TEXT, Profiles::SLEEP, 6);
		$this->EnableAction(Variables::SLEEP_IDENT);

		$this->RegisterVariableString(Variables::INPUT_IDENT, Variables::INPUT_TEXT, '', 7);

		$profileName = sprintf(Profiles::LINK, (string) $this->InstanceID);
		$this->RegisterProfileIntegerEx($profileName, Profiles::LINK_ICON, '', '', []);
		$this->RegisterVariableInteger(Variables::LINK_IDENT, Variables::LINK_TEXT, $profileName, 8);
		$this->EnableAction(Variables::LINK_IDENT);

		$this->RegisterVariableString(Variables::ARTIST_IDENT, Variables::ARTIST_TEXT, '', 9);
		$this->RegisterVariableString(Variables::TRACK_IDENT, Variables::TRACK_TEXT, '', 10);
		$this->RegisterVariableString(Variables::ALBUM_IDENT, Variables::ALBUM_TEXT, '', 11);
		$this->RegisterVariableString(Variables::ALBUMART_IDENT, Variables::ALBUMART_TEXT, '', 12);

		$profileName = sprintf(Profiles::FAVORITES, (string) $this->InstanceID);
		$this->RegisterProfileIntegerEx($profileName, Profiles::FAVORITES_ICON, '', '', []);
		$this->RegisterVariableInteger(Variables::FAVOURITE_IDENT, Variables::FAVOURITE_TEXT, $profileName, 13);
		$this->EnableAction(Variables::FAVOURITE_IDENT);

		$profileName = sprintf(Profiles::MCPLAYLISTS, (string) $this->InstanceID);
		$this->RegisterProfileIntegerEx($profileName, Profiles::MCPLAYLISTS_ICON, '', '', []);
		$this->RegisterVariableInteger(Variables::MCPLAYLIST_IDENT, Variables::MCPLAYLIST_TEXT, $profileName, 14);
		$this->EnableAction(Variables::MCPLAYLIST_IDENT);

		$this->RegisterMessage(0, IPS_KERNELMESSAGE);
	}

	public function Destroy() {
		$profileName = sprintf(Profiles::FAVORITES, (string) $this->InstanceID);
		$this->DeleteProfile($profileName);

		$profileName = sprintf(Profiles::MCPLAYLISTS, (string) $this->InstanceID);
		$this->DeleteProfile($profileName);

		$profileName = sprintf(Profiles::LINK, (string) $this->InstanceID);
		$this->DeleteProfile($profileName);

		$module = json_decode(file_get_contents(__DIR__ . '/module.json'));
		if(count(IPS_GetInstanceListByModuleID($module->id))==0) {
			$this->DeleteProfile(Profiles::CONTROL);
			$this->DeleteProfile(Profiles::MUTE);
			$this->DeleteProfile(Profiles::SLEEP);
			$this->DeleteProfile(Profiles::INFORMATION);
		}
		
		//Never delete this line!
		parent::Destroy();
	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();

		$this->SetReceiveDataFilter(sprintf('.*%s.*', $this->ReadPropertyString(Properties::IPADDRESS)));

		$report['IpAddressCheck'] = 0;
		if($this->Lock(Buffers::REPORT)) {
			$this->SetBuffer(Buffers::REPORT, serialize($report));
			$this->Unlock(Buffers::REPORT);
		}

		if (IPS_GetKernelRunlevel() == KR_READY) {
            $this->SetTimers();
			$this->SetValue(Variables::STATUS_IDENT, PlaybackState::NOTHING_ID);
			$this->SetValue(Variables::CONTROL_IDENT, PlaybackState::NOTHING_ID);
        }
	}

	public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        parent::MessageSink($TimeStamp, $SenderID, $Message, $Data);

        if ($Message == IPS_KERNELMESSAGE && $Data[0] == KR_READY) {
			$this->SetTimers();
			$this->SetValue(Variables::STATUS_IDENT, PlaybackState::NOTHING_ID);
			$this->SetValue(Variables::CONTROL_IDENT, PlaybackState::NOTHING_ID);
		}
            
    }

	public function RequestAction($Ident, $Value) {
		//$this->LogMessage("RequestAction: ".$Ident.":".$Value, KL_MESSAGE);

		try {
			switch ($Ident) {
				case 'HandleIncomingData':
					$this->HandleIncomingData(urldecode($Value));
					break;
				case Variables::CONTROL_IDENT:
					if($Value>200) { // Values above 200 is used inside scheduled scripts and Form Actions
						switch($Value) {
							case 255: // Call Update();
								$this->Update();
								break;
							case 254: // Call UpdateLists
								$this->UpdateLists();
								break;
							case 253: 
								$this->SetTimerInterval(Timers::RESETCONTROL . (string) $this->InstanceID, 0);
								$this->SetValue(Variables::CONTROL_IDENT, PlaybackState::NOTHING_ID);
						}
					} else if($this->GetValue(Variables::POWER_IDENT)) {   // Process only if device is powerd on
						//$this->LogMessage('Handeling Control: '.$Value, KL_MESSAGE);
						$this->SetTimerInterval(Timers::RESETCONTROL . (string) $this->InstanceID, 2000);
						switch ($Value) {
							case PlaybackState::PREVIOUS_ID:
								$this->SetValueEx($Ident, PlaybackState::PREVIOUS_ID);
								$this->Playback(PlaybackState::PREVIOUS);
								break;
							case PlaybackState::PLAY_ID:
								$this->SetValueEx($Ident, PlaybackState::PLAY_ID);
								$this->Playback(PlaybackState::PLAY);
								break;
							case PlaybackState::PAUSE_ID;
								$this->SetValueEx($Ident, PlaybackState::PAUSE_ID);
								$this->Playback(PlaybackState::STOP);
								break;
							case PlaybackState::STOP_ID:
								$this->SetValueEx($Ident, PlaybackState::STOP_ID);
								$this->Playback(PlaybackState::STOP);
								break;
							case PlaybackState::NEXT_ID:
								$this->SetValueEx($Ident, PlaybackState::NEXT_ID);
								$this->Playback(PlaybackState::NEXT);
								break;
						}
					}
					break;
				case Variables::SLEEP_IDENT:
					if($this->GetValue(Variables::POWER_IDENT)) {
						$this->SetValueEx($Ident, $Value);
						$this->Sleep($Value);
					}
					break;
				case Variables::VOLUME_IDENT:
					if($this->GetValue(Variables::POWER_IDENT)) {
						$this->SetValueEx($Ident, $Value);
						$this->Volume($Value);
					}
					break;
				case Variables::MUTE_IDENT:
					if($this->GetValue(Variables::POWER_IDENT)) {
						$this->SetValueEx($Ident, $Vaue);
						$this->Mute($Value);
					}
					break;
				case Variables::POWER_IDENT:
					$this->SetValueEx($Ident, $Value);
					$this->Power($Value);
					$this->Update();
					break;
				case Variables::FAVOURITE_IDENT:
					if($this->GetValue(Variables::POWER_IDENT)) {
						$this->SetValueEx($Ident, $Value);
						$this->SelectFavourite($Value);
						$favourite = IPS_GetObjectIDByIdent($Ident, $this->InstanceID);
						$this->RegisterOnceTimer(Timers::RESETFAVOURITE . (string) $this->InstanceID, 'IPS_Sleep(7000);if(IPS_VariableExists(' . (string) $favourite . ')) RequestAction(' . (string) $favourite . ', 0);');
					}
					break;
				case Variables::MCPLAYLIST_IDENT:
					if($this->GetValue(Variables::POWER_IDENT)) {
						$this->SetValueEx($Ident,$Value);
						$this->SelectMCPlaylist($Value);
						$mcPlaylist = IPS_GetObjectIDByIdent($Ident, $this->InstanceID);
						$this->RegisterOnceTimer(Timers::RESETMCPLAYLIST . (string) $this->InstanceID, 'IPS_Sleep(7000);if(IPS_VariableExists(' . (string) $mcPlaylist.')) RequestAction(' . (string) $mcPlaylist . ', 0);');
					}
					break;
				case Variables::LINK_IDENT:
					if($this->GetValue(Variables::POWER_IDENT)) {
						$this->SetValueEx($Ident,$Value);
						$this->StartLink($Value);
					}
			}
		} catch(Exception $e) {
			$this->LogMessage(sprintf(Errors::UNEXPECTED,  $e->getMessage()), KL_ERROR);
		}
	}

	/*public function GetConfigurationForm () {
		$control = $this->GetIDForIdent(Variables::CONTROL_IDENT);
		
		$form =	["elements"=>	[
									["type"=>"Image","image"=>"data:image/png;base64,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"],
									["type"=>"ValidationTextBox","name"=>"IPAddress","caption"=>"Device IP","validate"=>"^$|^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"],
									["type"=>"CheckBox","name"=>"AutoUpdateLists","caption"=>"Automatically update lists"],
									["type"=>"NumberSpinner","name"=>"UpdateListInterval","caption"=>"Interval","suffix"=>"seconds"]
								],
				"actions"=>		[
									["type"=>"Label","caption"=>"Playlists and Favourites"],
									["type"=>"Button","caption"=>"Update Lists","onClick"=>"RequestAction(".$control.", 254);","confirm"=>"Would you like to update the \"Favourites-\" and \"Playlists\"?"]
								],
				"status"=> 		[
								]
				];
		
		return json_encode($form);
	}*/

	public function ReceiveData($JSONString) {
		$data = json_decode($JSONString);
		//IPS_LogMessage('Device RECV', utf8_decode($data->Buffer));
		$this->SendDebug( __FUNCTION__ , 'Received data: '.$data->Buffer, 0);

		$script = 'IPS_RequestAction(' . (string)$this->InstanceID . ', "HandleIncomingData","'.urlencode($data->Buffer).'");';
		//$this->SendDebug( __FUNCTION__ , 'Executing script: '.$script, 0);
		$this->RegisterOnceTimer('HandleIncomingData', $script);
	}

	private function HandleIncomingData($Data) {
		$msg = 'Handling incoming data in a new thread: '.$Data;
		$data = json_decode($Data, true);

		if(is_array($data)) {
			foreach($data as $section) {
				if(is_array($section)) {
					foreach($section as $key => $value) {
						switch(strtolower($key)) {
							case 'power':
								$this->HandlePower($value);
								break;
							case 'play_time':
								$this->HandlePlayTime($value);
								break;
							case 'volume':
								$this->HandleVolume($value);
								break;
							case 'mute':
								$this->HandleMute($value);
								break;
							case 'play_info_updated':
								$this->HandlePlayInfoUpdated($value);
								break;
							case 'status_updated':
								$this->HandleStatusUpdated($value);
								break;
							case 'input'
								$this->HandleInput($value);
							default:
						}
					}
				} 
			}
		} else {
			// Invalid data!
		}

		$this->SendDebug(__FUNCTION__, $msg, 0);
	}

	private function HandlePower(string $State) {
		switch(strtolower($State)) {
			case 'on':
				$this->SetValueEx(Variables::POWER_IDENT, true);
				break;
			case 'standby':
				$this->SetValueEx(Variables::POWER_IDENT, false);
				break;
		}
	}

	private function HandleInput(string $Input) {
		$this->SetValueEx(Variables::INPUT_IDENT, $Input);
	}
	
	private function HandleMute(bool $State) {
		$this->SetValueEx(Variables::MUTE_IDENT, $State);
	}
	
	private function HandleVolume(int $Volume) {
		$this->SetValueEx(Variables::VOLUME_IDENT, $Volume);
	}
	
	private function HandlePlayTime(int $Seconds) {
		
	}

	private function HandleSleep(int $Minutes) {
		$this->SetValueEx(Variables::SLEEP_IDENT, $Minutes);
	}
	
	private function HandlePlayInfoUpdated(bool $State) {
		$playInfo = $this->GetPlayInfo();

		$this->SetValueEx(Variables::INPUT_IDENT, $playInfo->Input());
		$this->SetValueEx(Variables::ARTIST_IDENT, $playInfo->Artist());
		$this->SetValueEx(Variables::TRACK_IDENT, $playInfo->Track());
		$this->SetValueEx(Variables::ALBUM_IDENT, $playInfo->Album());
		$this->SetValueEx(Variables::ALBUMART_IDENT, $playInfo->AlbumartURL());

		$this->SetValueEx(Variables::STATUS_IDENT, $playInfo->Playback());
	}

	private function HandleStatusUpdated($State) {
		if($State) {
			$status = $this->GetStatus();
			$this->HandlePower($status->power);
			$this->HandleMute($status->mute);
			$this->handleSleep($status->sleep);
			$this->HandleVolume($status->volume);
			$this->HandleInput($status->input);
		}
	}

	private function GetStatus() {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$zone = new Zone($system);
			return $zone->Status();
		}
	}

	private function GetPlayInfo() {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$netUSB = new NetUSB($system);
			return $netUSB->PlayInfo();
		}
	}

	public function GetControlStatus() {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$netUSB = new NetUSB($system);
			$playInfo = $netUSB->PlayInfo();
			return $playInfo->Playback();
		}
	}

	private function SetTimers() {
		if($this->ReadPropertyBoolean(Properties::AUTOUPDATELISTS)) 
			$this->SetTimerInterval(Timers::UPDATELISTS . (string) $this->InstanceID, $this->ReadPropertyInteger(Properties::AUTOUPDATELISTINTERVAL)*1000);
		else
			$this->SetTimerInterval(Timers::UPDATELISTS . (string) $this->InstanceID, 0);
		
		$this->SetTimerInterval(Timers::UPDATE  . (string) $this->InstanceID, 5000);
	}

	private function StartLink(int $RoomIndex) {
		if($RoomIndex==0) {
			$this->StopLink();
		} else {
			$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
			if($this->VerifyDeviceIp($ipAddress)) {
				$profileName = sprintf(Profiles::LINK, (string) $this->InstanceID);
				$selectedRoom = $this->GetProfileAssosiationName($profileName, $RoomIndex);
				if($selectedRoom!==false) {
					$system = new System($ipAddress);
					$clientIpAddress = $system->FindRoom($selectedRoom);
					if($clientIpAddress!==false) {
						$distribution = new Distrbution($system);
						$distribution->AddClient(new System($clientIpAddress));
						$distribution->Start();
					} else
						$this->LogMessage(sprintf(Errors::UNKNOWNROOM, $selectedRoom), KL_ERROR);
				}  else
					$this->LogMessage(Errors::ROOMERROR, KL_ERROR);
			} 
		}
	}

	private function StopLink() {
			$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);	
			if($this->VerifyDeviceIp($ipAddress)) {	
				$system = new System($ipAddress);
				$distribution = new Distrbution($system);
				$distribution->Stop();
			}
	}

	private function Update(){
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)) {
			$system = new System($ipAddress);
			$zone = new Zone($system);
							
			$status = $zone->Status();
			if($status->power=='on') {
				$netUSB = new NetUSB($system);
				$playInfo = $netUSB->PlayInfo();
				$distribution = $distribution = new Distrbution($system);

				//$this->SetValueEx(Variables::POWER_IDENT, true);
				//$this->SetValueEx(Variables::VOLUME_IDENT, $status->volume);
				//$this->SetValueEx(Variables::MUTE_IDENT, $status->mute);
				$this->SetValueEx(Variables::SLEEP_IDENT, $status->sleep);

				if($distribution->IsActive()==false)
					$this->SetValueEx(Variables::LINK_IDENT, 0);

				//$control = $playInfo->Playback();
				//$this->SetValueEx(Variables::STATUS_IDENT, $control);

				/*if($control==3) { // Stop
					$this->SetValueEx(Variables::INPUT_IDENT, '');
					$this->SetValueEx(Variables::ARTIST_IDENT, '');
					$this->SetValueEx(Variables::TRACK_IDENT, '');
					$this->SetValueEx(Variables::ALBUM_IDENT, '');
					$this->SetValueEx(Variables::ALBUMART_IDENT, '');
				} else {
					$this->SetValueEx(Variables::INPUT_IDENT, $playInfo->Input());
					$this->SetValueEx(Variables::ARTIST_IDENT, $playInfo->Artist());
					$this->SetValueEx(Variables::TRACK_IDENT, $playInfo->Track());
					$this->SetValueEx(Variables::ALBUM_IDENT, $playInfo->Album());
					$this->SetValueEx(Variables::ALBUMART_IDENT, $playInfo->AlbumartURL());
				} */
			} else {
				//$this->SetValueEx(Variables::POWER_IDENT, false);
				//$this->SetValueEx(Variables::VOLUME_IDENT, 0);
				//$this->SetValueEx(Variables::MUTE_IDENT, false);

				$this->SetValueEx(Variables::CONTROL_IDENT, PlaybackState::NOTHING_ID);
				$this->SetValueEx(Variables::STATUS_IDENT, PlaybackState::NOTHING_ID); 

				//$this->SetValueEx(Variables::INPUT_IDENT, '');
				//$this->SetValueEx(Variables::ARTIST_IDENT, '');
				//$this->SetValueEx(Variables::TRACK_IDENT, '');
				//$this->SetValueEx(Variables::ALBUM_IDENT, '');
				//$this->SetValueEx(Variables::ALBUMART_IDENT, '');
			}
		}
	}

	private function UpdateLists() {
		try {
			if($this->ReadPropertyBoolean(Properties::AUTOUPDATELISTS)) 
				$this->SetTimerInterval(Timers::UPDATELISTS . (string) $this->InstanceID, 0);

			$this->UpdateFavourites();
			$this->UpdatePlaylists();
			$this->UpdateLink();
		} finally {
			if($this->ReadPropertyBoolean(Properties::AUTOUPDATELISTS)) 
				$this->SetTimerInterval(Timers::UPDATELISTS . (string) $this->InstanceID, $this->ReadPropertyInteger(Properties::AUTOUPDATELISTINTERVAL)*1000);
		}
	}
	
	private function SelectFavourite(int $Value) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress) && $Value!=0) { 
			$system = new System($ipAddress);
			$netUSB = new NetUSB($system);
			$netUSB->SelectFavouriteById($Value);
		}
	}

	private function SelectMCPlaylist(int $Value) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress) && $Value!=0) { 
			$system = new System($ipAddress);
			$netUSB = new NetUSB($system);
			$netUSB->SelectMCPlaylistById($Value);
		}
	}

	private function Sleep(int $Minutes) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$zone = new Zone($system);
			$zone->Sleep($Minutes);
		}
	}	

	private function Volume(int $Level) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$zone = new Zone($system);
			$zone->Volume($Level);
		}
	}

	private function Mute(bool $State) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$zone = new Zone($system);
			$zone->Mute($State);
		}
	}

	private function Playback(string $State) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$netUSB = new NetUSB($system);
			$netUSB->Playback($State);
		}
	}

	private function Power(bool $State) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$zone = new Zone($system);
			$zone->Power($State);
		}
	}

	private function UpdateFavourites() {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$netUSB = new NetUSB($system);
			
			$favourites = $netUSB->Favourites();
			if(count($favourites)>0) {
				$assosiations = $this->CreateProfileAssosiationList($favourites);
				$profileName = sprintf(Profiles::FAVORITES, (string) $this->InstanceID);
				$this->RegisterProfileIntegerEx($profileName, Profiles::FAVORITES_ICON, '', '', $assosiations);
			}
		}
	}

	private function UpdatePlaylists() {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$netUSB = new NetUSB($system);
			
			$playlists = $netUSB->MCPlaylists();
			if(count($playlists)>0) {
				$assosiations = $this->CreateProfileAssosiationList($playlists);
				$profileName = sprintf(Profiles::MCPLAYLISTS, (string) $this->InstanceID);
				$this->RegisterProfileIntegerEx($profileName, Profiles::MCPLAYLISTS_ICON, '', '', $assosiations);
			}
		}
	}

	private function UpdateLink() {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$rooms = $system->Rooms();
			$num = count($rooms);
			$roomList[] = 'None';
			for($idx=1;$idx<$num;$idx++) { // $idx is initialized to 1 because index 0 is this instances room name
				$room = $rooms[$idx];
				$roomList[] = $room['name'];
			}
			
			$assosiations = $this->CreateProfileAssosiationList($roomList);
			$profileName = sprintf(Profiles::LINK, (string) $this->InstanceID);
			$this->RegisterProfileIntegerEx($profileName, Profiles::LINK_ICON, '', '', $assosiations);	
		}
	}
	
	private function SetValueEx(string $Ident, $Value) {
		$oldValue = $this->GetValue($Ident);
		if($oldValue!=$Value)
			$this->SetValue($Ident, $Value);
	}

	private function VerifyDeviceIp($IpAddress) {
		if(strlen($IpAddress)>0)
			if($this->PingTest($IpAddress)) {
				$report['IpAddressCheck'] = 0; // Reset count on success
			
				if($this->Lock(Buffers::REPORT)) {
					$this->SetBuffer(Buffers::REPORT, serialize($report));
					$this->Unlock(Buffers::REPORT);
				}
				
				$this->SetStatus(102);
				return true;
			} else
				$msg = sprintf(Errors::NOTRESPONDING, (string) $this->InstanceID, $IpAddress);
		else
			$msg = sprintf(Errors::MISSINGIP, (string) $this->InstanceID);	

		$this->SetStatus(104);
		
		if($this->Lock(Buffers::REPORT)) {
			$report = unserialize($this->GetBuffer(Buffers::REPORT));
			$this->Unlock(Buffers::REPORT);
		}
		
		$countReported = isset($report['IpAddressCheck'])?$report['IpAddressCheck']:0;
		if($countReported<10) {
			$countReported++;
			$report['IpAddressCheck'] = $countReported;
			
			if($this->Lock(Buffers::REPORT)) {
				$this->SetBuffer(Buffers::REPORT, serialize($report));
				$this->Unlock(Buffers::REPORT);
			}
			
			$this->LogMessage($msg, KL_ERROR);
		}
		
		return false;	
	}

	private function PingTest(string $IPAddress) {
		$wait = 500;
		for($count=0;$count<3;$count++) {
			if(Sys_Ping($IPAddress, $wait))
				return true;
			$wait*=2;
		}

		return false;
	}



}

