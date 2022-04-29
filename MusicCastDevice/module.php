<?php

declare(strict_types=1);

require_once(__DIR__ . "/../libs/autoload.php");

class MusicCastDevice extends IPSModule {
	use Profile;
	use Buffer;
	use Utils;
	
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

		$this->RegisterProfileInteger(Profiles::POSITION, Profiles::POSITION_ICON, '', ' %', 0, 100, 1);
		$this->RegisterProfileString(Profiles::TIME, Profiles::TIME_ICON, '', '');
		$this->RegisterProfileString(Profiles::MUSIC, Profiles::MUSIC_ICON, '', '');

				
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
			[true, Mute::MUTED, '', -1],
			[false, Mute::UNMUTED, '', -1]
		]);

		$this->RegisterVariableBoolean(Variables::POWER_IDENT, Variables::POWER_TEXT, '~Switch', 1);
		$this->EnableAction(Variables::POWER_IDENT);

		$control = $this->RegisterVariableInteger(Variables::CONTROL_IDENT, Variables::CONTROL_TEXT, Profiles::CONTROL, 2);
		$this->EnableAction(Variables::CONTROL_IDENT);

		$this->RegisterVariableInteger(Variables::STATUS_IDENT, Variables::STATUS_TEXT, Profiles::INFORMATION, 3);
		
		$this->RegisterTimer(Timers::UPDATELISTS . (string) $this->InstanceID, 0, 'IPS_RequestAction(' . (string)$this->InstanceID . ', "UpdateLists", false);');
		$this->RegisterTimer(Timers::UPDATE . (string) $this->InstanceID, 0, 'IPS_RequestAction(' . (string)$this->InstanceID . ', "Update", 0);');
		$this->RegisterTimer(Timers::RESETCONTROL . (string) $this->InstanceID, 0, 'IPS_RequestAction(' . (string)$this->InstanceID . ', "ResetControl", 0);');
				
		$this->RegisterVariableInteger(Variables::VOLUME_IDENT, Variables::VOLUME_TEXT, 'Intensity.100', 4);
		$this->EnableAction(Variables::VOLUME_IDENT);

		$this->RegisterVariableBoolean(Variables::MUTE_IDENT, Variables::MUTE_TEXT, Profiles::MUTE, 5);
		$this->EnableAction(Variables::MUTE_IDENT);

		$this->RegisterVariableInteger(Variables::SLEEP_IDENT, Variables::SLEEP_TEXT, Profiles::SLEEP, 6);
		$this->EnableAction(Variables::SLEEP_IDENT);

		$this->RegisterVariableString(Variables::INPUT_IDENT, Variables::INPUT_TEXT, Profiles::MUSIC, 7);

		$profileName = sprintf(Profiles::LINK, (string) $this->InstanceID);
		$this->RegisterProfileIntegerEx($profileName, Profiles::LINK_ICON, '', '', []);
		$this->RegisterVariableInteger(Variables::LINK_IDENT, Variables::LINK_TEXT, $profileName, 8);
		$this->EnableAction(Variables::LINK_IDENT);
		
		$this->RegisterVariableString(Variables::ARTIST_IDENT, Variables::ARTIST_TEXT, Profiles::MUSIC, 9);
		$this->RegisterVariableString(Variables::TRACK_IDENT, Variables::TRACK_TEXT, Profiles::MUSIC, 10);
		$this->RegisterVariableString(Variables::ALBUM_IDENT, Variables::ALBUM_TEXT, Profiles::MUSIC, 11);
		$this->RegisterVariableString(Variables::ALBUMART_IDENT, Variables::ALBUMART_TEXT, Profiles::MUSIC, 12);

		$this->RegisterVariableString(Variables::PLAYTIME_IDENT, Variables::PLAYTIME_TEXT, Profiles::TIME, 13);
		$this->RegisterVariableString(Variables::TOTALTIME_IDENT, Variables::TOTALTIME_TEXT, Profiles::TIME, 14);
		$this->RegisterVariableString(Variables::TIME_LEFT_IDENT, Variables::TIME_LEFT_TEXT, Profiles::TIME, 15);
		
		$this->RegisterVariableInteger(Variables::POSITION_IDENT, Variables::POSITION_TEXT, Profiles::POSITION, 16);
		$this->EnableAction(Variables::POSITION_IDENT);

		$profileName = sprintf(Profiles::FAVORITES, (string) $this->InstanceID);
		$this->RegisterProfileIntegerEx($profileName, Profiles::FAVORITES_ICON, '', '', []);
		$this->RegisterVariableInteger(Variables::FAVOURITE_IDENT, Variables::FAVOURITE_TEXT, $profileName, 17);
		$this->EnableAction(Variables::FAVOURITE_IDENT);

		$profileName = sprintf(Profiles::MCPLAYLISTS, (string) $this->InstanceID);
		$this->RegisterProfileIntegerEx($profileName, Profiles::MCPLAYLISTS_ICON, '', '', []);
		$this->RegisterVariableInteger(Variables::MCPLAYLIST_IDENT, Variables::MCPLAYLIST_TEXT, $profileName, 18);
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
			$this->DeleteProfile(Profiles::POSITION);
			$this->DeleteProfile(Profiles::TIME);
			$this->DeleteProfile(Profiles::MUSIC);
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

	private function SetTimers() {
		$this->SetTimerInterval(Timers::UPDATELISTS . (string) $this->InstanceID, $this->ReadPropertyInteger(Properties::AUTOUPDATELISTINTERVAL)*1000);
		$this->SetTimerInterval(Timers::UPDATE  . (string) $this->InstanceID, 10000);
	}

	public function RequestAction($Ident, $Value) {
		//$msg = sprintf('RequestAction was called: %s:%s', (string)$Ident, (string)$Value);
		//$this->SendDebug(__FUNCTION__, $msg, 0);
		
		try {
			switch ($Ident) {
				case Variables::POSITION_IDENT:
					return;
				case Variables::POWER_IDENT:
					$this->SetValueEx($Ident, $Value);
					$this->Power($Value);
					$this->Update();
					return;
				case 'PlayInfoUpdated':
					$this->HandlePlayInfoUpdated($Value);
					return;
				case 'StatusUpdated':
					$this->HandleStatusUpdated($Value);
					return;
				case 'UpdateLists':
					$this->UpdateLists($Value);
					return;
				case 'Update':
					$this->Update();
					return;
				case 'ResetControl':
					$this->SetTimerInterval(Timers::RESETCONTROL . (string) $this->InstanceID, 0);
					$this->SetValue(Variables::CONTROL_IDENT, PlaybackState::NOTHING_ID);
					return;
			}

			if($this->GetValue(Variables::POWER_IDENT)) {   // Process only if device is powered on
				$this->SetValueEx($Ident, $Value);
				
				switch ($Ident) {
					case Variables::CONTROL_IDENT:
						$this->Playback($Value);
						$this->SetTimerInterval(Timers::RESETCONTROL . (string) $this->InstanceID, 2000);
						break;
					case Variables::SLEEP_IDENT:
						$this->Sleep($Value);
						break;
					case Variables::VOLUME_IDENT:
						$this->Volume($Value);
						break;
					case Variables::MUTE_IDENT:
						$this->Mute($Value);
						break;
					case Variables::FAVOURITE_IDENT:
						$this->SelectFavourite($Value);
						$favourite = IPS_GetObjectIDByIdent($Ident, $this->InstanceID);
						$this->RegisterOnceTimer(Timers::RESETFAVOURITE . (string)$this->InstanceID, 'IPS_Sleep(5000);if(IPS_VariableExists(' . (string)$favourite . ')) RequestAction(' . (string)$favourite . ', 0);');
						break;
					case Variables::MCPLAYLIST_IDENT:
						$this->SelectMCPlaylist($Value);
						$mcPlaylist = IPS_GetObjectIDByIdent($Ident, $this->InstanceID);
						$this->RegisterOnceTimer(Timers::RESETMCPLAYLIST . (string)$this->InstanceID, 'IPS_Sleep(5000);if(IPS_VariableExists(' . (string)$mcPlaylist.')) RequestAction(' . (string)$mcPlaylist . ', 0);');
						break;
					case Variables::LINK_IDENT:
						$this->StartLink($Value);
				}
			}
		} catch(Exception $e) {
			$msg = sprintf(Errors::UNEXPECTED,  $e->getMessage());
			$this->LogMessage($msg, KL_ERROR);
			$this->SendDebug(__FUNCTION__, $msg, 0);
		}
	}

	public function ReceiveData($JSONString) {
		$data = json_decode($JSONString);
		$this->HandleIncomingData($data->Buffer);
	}

	private function HandleIncomingData(string $Data) {
		$msg = sprintf('Incoming data: %s', $Data);
		$this->SendDebug(__FUNCTION__, $msg, 0);

		$data = json_decode($Data, true);

		try {
			if(is_array($data)) {
				foreach($data as $sectionKey => $section) {
					if(is_array($section)) { // Only process if it is an array
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
									$this->SendDebug(__FUNCTION__, Debug::HANDLEPLAYINFO, 0);
									
									$identValue = json_encode([
										'status'=>$value?'true':'false',
										'type'=>$sectionKey
									]);
																											
									$script = 'IPS_RequestAction(' . (string)$this->InstanceID . ', "PlayInfoUpdated",\''.$identValue.'\');';
									$this->RegisterOnceTimer('PlayInfoUpdated', $script);
									break;
								case 'status_updated':
									$this->SendDebug(__FUNCTION__, Debug::HANDLESTATUSUPDATED, 0);

									$identValue = $value?'true':'false';
									$script = 'IPS_RequestAction(' . (string)$this->InstanceID . ', "StatusUpdated",'.$identValue.');';
									
									$this->RegisterOnceTimer('StatusUpdated', $script);
									break;
								case 'input':
									$this->HandleInput($value);
								default:
							}
						}
					} 
				}
			} else {
				throw new Exception(Errors::INVALIDATA);
			}
		} catch(Exception $e) {
			$msg = sprintf(Errors::UNEXPECTED,  $e->getMessage());
			$this->LogMessage($msg, KL_ERROR);
			$this->SendDebug(__FUNCTION__, $msg, 0);
		}
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
		$this->SetValueEx(Variables::INPUT_IDENT, PlayInfo::MapInput($Input));
	}
	
	private function HandleMute(bool $State) {
		$this->SetValueEx(Variables::MUTE_IDENT, $State);
	}
	
	private function HandleVolume(int $Volume) {
		$this->SetValueEx(Variables::VOLUME_IDENT, $Volume);
	}
	
	private function HandlePlayTime(int $Seconds) {
		$this->SetValueEx(Variables::PLAYTIME_IDENT, $this->SecondsToString($Seconds));

		if($this->Lock(Variables::TOTALTIME_IDENT)) {
			$totalTime = unserialize($this->GetBuffer(Variables::TOTALTIME_TEXT));
			$this->Unlock(Variables::TOTALTIME_IDENT);

			if($totalTime>0) {
				$position = (int)ceil((float)($Seconds/$totalTime*100));
				$timeLeft = $totalTime-$Seconds;
			} else {
				$position = 0;
				$timeLeft = 0;
			}

			$this->SetValueEx(Variables::POSITION_IDENT, $position);
			$this->SetValueEx(Variables::TIME_LEFT_IDENT, $this->SecondsToString($timeLeft));
		}
	}

	private function HandleSleep(int $Minutes) {
		$this->SetValueEx(Variables::SLEEP_IDENT, $Minutes);
	}
	
	private function HandlePlayInfoUpdated(String $JsonParameters) {
		$parameters = json_decode($JsonParameters);

		if($parameters->status) {
			$this->SendDebug(__FUNCTION__, Debug::STARTPLAYINFO, 0);

			$this->SendDebug(__FUNCTION__, sprintf(Debug::GETPLAYINFO, $parameters->type), 0);
			$playInfo = $this->GetMCPlayInfo($parameters->type);

			$this->SendDebug(__FUNCTION__, Debug::UPDATINGVARIABLES, 0);

			$this->SetValueEx(Variables::INPUT_IDENT, $playInfo->Input());
			$this->SetValueEx(Variables::ARTIST_IDENT, $playInfo->Artist());
			$this->SetValueEx(Variables::TRACK_IDENT, $playInfo->Track());
			$this->SetValueEx(Variables::ALBUM_IDENT, $playInfo->Album());
			$this->SetValueEx(Variables::ALBUMART_IDENT, $playInfo->AlbumartURL());

			if($this->Lock(Variables::TOTALTIME_IDENT)) {
				$this->SetBuffer(Variables::TOTALTIME_TEXT, serialize($playInfo->TotalTime()));
				$this->Unlock(Variables::TOTALTIME_IDENT);
			}
			
			$this->SetValueEx(Variables::TOTALTIME_IDENT, $this->SecondsToString($playInfo->TotalTime()));
			$this->SetValueEx(Variables::PLAYTIME_IDENT, $this->SecondsToString($playInfo->PlayTime()));
			
			if($playInfo->TotalTime()>0) {
				$position = (int)ceil((float)($playInfo->PlayTime()/$playInfo->TotalTime()*100));
			} else {
				$position=0;
			}

			$this->SetValueEx(Variables::POSITION_IDENT, $position);
			$this->SetValueEx(Variables::STATUS_IDENT, $playInfo->Playback());
		} else {
			$this->SendDebug(__FUNCTION__, Debug::STOPPLAYINFO, 0);
		}
	}

	private function HandleStatusUpdated(bool $State) {
		if($State) {
			$this->SendDebug(__FUNCTION__, Debug::STARTSTATUSUPDATED, 0);

			$this->SendDebug(__FUNCTION__, Debug::GETSTATUS, 0);

			$status = $this->GetMCStatus();

			$this->SendDebug(__FUNCTION__, Debug::UPDATINGVARIABLES, 0);
		
			$this->HandlePower($status->power);
			$this->HandleMute($status->mute);
			$this->handleSleep($status->sleep);
			$this->HandleVolume($status->volume);
			$this->HandleInput($status->input);
		} else {
			$this->SendDebug(__FUNCTION__, Debug::STOPSTATUSUPDATED, 0);
		}
	}

	private function GetMCStatus() {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			$zone = new Zone($system);
			return $zone->Status();
		}
	}

	private function GetMCPlayInfo(string $Type) {
		
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)){
			$system = new System($ipAddress);
			if(strtolower($Type)=='netusb') {
				$obj = new NetUSB($system);
			} else {
				$obj = new Tuner($system);
			}
			
			return $obj->PlayInfo();
		}
	}

	private function StartLink(int $RoomIndex) {
		if($RoomIndex==0) {
			$this->StopLink();
		} else {
			$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
			if($this->VerifyDeviceIp($ipAddress)) {
				$profileName = sprintf(Profiles::LINK, (string)$this->InstanceID);
				$selectedRoom = $this->GetProfileAssosiationName($profileName, $RoomIndex);
				if($selectedRoom!==false) {
					$msg = sprintf(Debug::ESTABLISHLINK, $selectedRoom);
					$this->SendDebug(__FUNCTION__, $msg, 0);

					$system = new System($ipAddress);
					$clientIpAddress = $system->FindRoom($selectedRoom);
					if($clientIpAddress!==false) {
						$distribution = new Distrbution($system);
						$distribution->AddClient(new System($clientIpAddress));
						$distribution->Start();
					} else {
						$msg = sprintf(Errors::UNKNOWNROOM, $selectedRoom);
						$this->LogMessage($msg, KL_ERROR);
						$this->SendDebug(__FUNCTION__, $msg, 0);
					}
				}  else {
					$this->LogMessage(Errors::ROOMERROR, KL_ERROR);
					$this->SendDebug(__FUNCTION__, Errors::ROOMERROR, 0);
				}
			} 
		}
	}

	private function StopLink() {
		$this->SendDebug(__FUNCTION__, Debug::STOPLINK, 0);

		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);	
		if($this->VerifyDeviceIp($ipAddress)) {	
			$system = new System($ipAddress);
			$distribution = new Distrbution($system);
			$distribution->Stop();
		}
	}

	private function Update(){
		$this->SendDebug(__FUNCTION__, Debug::GETINFORMATION, 0);
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)) {
			$system = new System($ipAddress);
			$zone = new Zone($system);
							
			$status = $zone->Status();

			$this->SendDebug(__FUNCTION__, Debug::UPDATINGVARIABLES, 0);

			if($status->power=='on') {
				$netUSB = new NetUSB($system);
				$playInfo = $netUSB->PlayInfo();
				$distribution = $distribution = new Distrbution($system);

				$this->SetValueEx(Variables::POWER_IDENT, true);
				$this->SetValueEx(Variables::VOLUME_IDENT, $status->volume);
				$this->SetValueEx(Variables::MUTE_IDENT, $status->mute);
				$this->SetValueEx(Variables::SLEEP_IDENT, $status->sleep);

				if($distribution->IsActive()==false)
					$this->SetValueEx(Variables::LINK_IDENT, 0);

				$control = $playInfo->Playback();
				$this->SetValueEx(Variables::STATUS_IDENT, $control);

				if($control==3) { // Stop
					$this->SetValueEx(Variables::INPUT_IDENT, '');
					$this->SetValueEx(Variables::ARTIST_IDENT, '');
					$this->SetValueEx(Variables::TRACK_IDENT, '');
					$this->SetValueEx(Variables::ALBUM_IDENT, '');
					$this->SetValueEx(Variables::ALBUMART_IDENT, '');
					$this->SetValueEx(Variables::TOTALTIME_IDENT, '');
					$this->SetValueEx(Variables::PLAYTIME_IDENT, '');
					$this->SetValueEx(Variables::POSITION_IDENT, 0);
				} else {
					$this->SetValueEx(Variables::INPUT_IDENT, $playInfo->Input());
					$this->SetValueEx(Variables::ARTIST_IDENT, $playInfo->Artist());
					$this->SetValueEx(Variables::TRACK_IDENT, $playInfo->Track());
					$this->SetValueEx(Variables::ALBUM_IDENT, $playInfo->Album());
					$this->SetValueEx(Variables::ALBUMART_IDENT, $playInfo->AlbumartURL());
								
					if($this->Lock(Variables::TOTALTIME_IDENT)) {
						$this->SetBuffer(Variables::TOTALTIME_TEXT, serialize($playInfo->TotalTime()));
						$this->Unlock(Variables::TOTALTIME_IDENT);
					}

					$this->SetValueEx(Variables::TOTALTIME_IDENT, $this->SecondsToString($playInfo->TotalTime()));
					$this->SetValueEx(Variables::PLAYTIME_IDENT, $this->SecondsToString($playInfo->PlayTime()));
					
					if($playInfo->TotalTime()>0) {
						$position = (int)ceil((float)($playInfo->PlayTime()/$playInfo->TotalTime()*100));
					} else {
						$position=0;
					}
		
					$this->SetValueEx(Variables::POSITION_IDENT, $position);
				} 
			} else {
				$this->SetValueEx(Variables::POWER_IDENT, false);
				$this->SetValueEx(Variables::VOLUME_IDENT, 0);
				$this->SetValueEx(Variables::MUTE_IDENT, false);

				$this->SetValueEx(Variables::CONTROL_IDENT, PlaybackState::NOTHING_ID);
				$this->SetValueEx(Variables::STATUS_IDENT, PlaybackState::NOTHING_ID); 

				$this->SetValueEx(Variables::INPUT_IDENT, '');
				$this->SetValueEx(Variables::ARTIST_IDENT, '');
				$this->SetValueEx(Variables::TRACK_IDENT, '');
				$this->SetValueEx(Variables::ALBUM_IDENT, '');
				$this->SetValueEx(Variables::ALBUMART_IDENT, '');
				$this->SetValueEx(Variables::TOTALTIME_IDENT, '');
				$this->SetValueEx(Variables::PLAYTIME_IDENT, '');
				$this->SetValueEx(Variables::POSITION_IDENT, 0);
			}
		}
	}

	private function UpdateLists(bool $Force=false) {
		try {
			$this->SetTimerInterval(Timers::UPDATELISTS . (string)$this->InstanceID, 0);

			$update = $this->ReadPropertyBoolean(Properties::AUTOUPDATELISTS); 
			
			$msg = $Force || $update?Debug::UPDATEALLLISTS:Debug::UPDATELINK;
			$this->SendDebug(__FUNCTION__, $msg, 0);

			$this->UpdateLink();

			if($Force || $update) {
				$this->UpdateFavourites();
				$this->UpdatePlaylists();
			}

			$msg = $Force || $update?Debug::ALLLISTS:Debug::LINKLIST;
			$this->SendDebug(__FUNCTION__, $msg, 0);

		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		} finally {
			
			$this->SetTimerInterval(Timers::UPDATELISTS . (string)$this->InstanceID, $this->ReadPropertyInteger(Properties::AUTOUPDATELISTINTERVAL)*1000);
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

	private function Playback(int $Value) {
		try {
			$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
			if($this->VerifyDeviceIp($ipAddress)) {
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				$state = $this->MapPlaybackState($Value); 
				$netUSB->Playback($state);
			}
		} catch (Exception $e) {
			$this->SendDebug(__FUNCTION__, 'Error:' . $e->getMessage(), 0); 
		}
	}

	private function Power(bool $State) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)) {
			$system = new System($ipAddress);
			$zone = new Zone($system);
			$zone->Power($State);
		}
	}

	private function UpdateFavourites() {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
		if($this->VerifyDeviceIp($ipAddress)) {
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
		if($this->VerifyDeviceIp($ipAddress)) {
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
		if($this->VerifyDeviceIp($ipAddress)) {
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

		$this->SendDebug(__FUNCTION__, $msg, 0);
		
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

	private function MapPlaybackState(int $Value) : string {
        switch($Value) {
            case PlaybackState::PLAY_ID:
                return PlaybackState::PLAY;
            case PlaybackState::STOP_ID:
                return PlaybackState::STOP;
            case PlaybackState::PAUSE_ID:
                return PlaybackState::PAUSE;
            case PlaybackState::PREVIOUS_ID:
                return PlaybackState::PREVIOUS;
            case PlaybackState::NEXT_ID:
                return PlaybackState::NEXT;
            default:
                return PlaybackState::NOTHING;

        }
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

