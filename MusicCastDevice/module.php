<?php

	require_once(__DIR__ . "/../libs/autoload.php");
	
	class MusicCastDevice extends IPSModule {
		use ProfileHelper;

		public function Create() {
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyString("IPAddress", "");

			$this->RegisterProfileIntegerEx('YMC.Control', 'Speaker', '', '', [
				[0, 'Prev',  '', -1],
				[1, 'Play',  '', -1],
				[2, 'Pause', '', -1],
				[3, "Stop",  '', -1],
				[4, 'Next',  '', -1]
			]);
			
			$this->RegisterVariableBoolean('Power', 'Power', '~Switch', 1);
			$this->EnableAction('Power');

			$control = $this->RegisterVariableInteger('Control', 'Control', 'YMC.Control', 2);
        	$this->EnableAction('Control');
			
			$this->RegisterVariableInteger('Volume', 'Volume', 'Intensity.100', 3);
			$this->EnableAction('Volume');
	
			$this->RegisterVariableBoolean('Mute', 'Mute', '~Switch', 4);
			$this->EnableAction('Mute');

			$this->RegisterVariableString('Input', 'Input', '', 5);
			$this->RegisterVariableString('Artist', 'Artist', '', 6);
			$this->RegisterVariableString('Track', 'Track', '', 7);
			$this->RegisterVariableString('Album', 'Album', '', 8);
			$this->RegisterVariableString('Albumart', 'Album Art', '', 9);

			$this->RegisterPropertyBoolean('AutoUpdateLists', true);
			$this->RegisterPropertyInteger('UpdateListInterval', 30);

			$profileName = 'YMC.' . $this->InstanceID . ".Favorites";
			$this->RegisterProfileIntegerEx($profileName, 'Music', '', '', []);
			$this->RegisterVariableInteger('Favourite', 'Favourite', $profileName, 10);
			$this->EnableAction('Favourite');

			$profileName = 'YMC.' . $this->InstanceID . ".Playlists";
			$this->RegisterProfileIntegerEx($profileName, 'Music', '', '', []);
			$this->RegisterVariableInteger('MCPLaylist', 'Playlist', $profileName, 11);
			$this->EnableAction('MCPLaylist');
			
			
			$this->RegisterTimer('Update'.$this->InstanceID, 5000, 'RequestAction('.$control.', 255);');
			$this->RegisterTimer('UpdateLists'.$this->InstanceID, 30000, 'RequestAction('.$control.', 254);');
		}

		public function Destroy() {
			//$this->SetTimerInterval('UpdateLists'.$this->InstanceID, 0);
			//$this->SetTimerInterval('Update'.$this->InstanceID, 0);
			
			$profileName = 'YMC.' . $this->InstanceID . ".Favorites";
			$this->DeleteProfile($profileName);

			$profileName = 'YMC.' . $this->InstanceID . ".Playlists";
			$this->DeleteProfile($profileName);

			$module = json_decode(file_get_contents(__DIR__ . '/module.json'));
			if(count(IPS_GetInstanceListByModuleID($module->id))==0)
				$this->DeleteProfile('YMC.Control');
			
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges() {
			//Never delete this line!
			parent::ApplyChanges();
			
			if($this->ReadPropertyBoolean('AutoUpdateLists')) 
				$this->SetTimerInterval('UpdateLists'.$this->InstanceID, $this->ReadPropertyInteger('UpdateListInterval')*1000);
			else
				$this->SetTimerInterval('UpdateLists'.$this->InstanceID, 0);
		}

		public function RequestAction($Ident, $Value) {
			IPS_LogMessage('RequestAction', 'Ident: '.$Ident.' Value: '.$Value.' InstanceId: '.$this->InstanceID);
			try {
				switch ($Ident) {
					case 'Control':
						if($this->GetValue('Power')) {
							switch ($Value) {
								case 0:
									$this->SetValueEx('Control', 1);
									self::Playback(PlaybackState::PREVIOUS);
									break;
								case 1:
									$this->SetValueEx('Control', 1);
									self::Playback(PlaybackState::PLAY);
									break;
								case 2:
									$this->SetValueEx('Control', 2);
									self::Playback(PlaybackState::STOP);
									break;
								case 3:
									$this->SetValueEx('Control', 3);
									self::Playback(PlaybackState::STOP);
									break;
								case 4:
									$this->SetValueEx('Control', 1);
									self::Playback(PlaybackState::NEXT);
									break;
								case 255: // Call Update();
									IPS_LogMessage('RequestAction','Calling update for instance '.$this->InstanceID);
									$this->Update();
									break;
								case 254: // Call UpdateLists
									IPS_LogMessage('RequestAction','Calling UpdateLists for instance '.$this->InstanceID);
									$this->UpdateLists();
									break;
							}
						}
						break;
					case 'Volume':
						if($this->GetValue('Power')) {
							$this->SetValueEx('Volume', $Value);
							self::Volume($Value);
						}
						break;
					case 'Mute':
						if($this->GetValue('Power')) {
							$this->SetValueEx('Mute', $Vaue);
							self::Mute($Value);
						}
						break;
					case 'Power':
						$this->SetValueEx('Power', $Value);
						self::Power($Value);
						if($Value)
							self::Update();
						break;
					case 'Favourite':
						if($this->GetValue('Power')) {
							$this->SetValueEx('Favourite', $Value);
							self::SelectFavourite($Value);
							$favourite = IPS_GetObjectIDByIdent($Ident, $this->InstanceID);
							$this->RegisterOnceTimer("ResetFavourite".$this->InstanceID, "IPS_Sleep(10000);RequestAction(".$favourite.", 0);");
						}
						break;
					case 'MCPLaylist':
						if($this->GetValue('Power')) {
							$this->SetValueEx('MCPLaylist',$Value);
							self::SelectMCPlaylist($Value);
							$mcPlaylist = IPS_GetObjectIDByIdent($Ident, $this->InstanceID);
							$this->RegisterOnceTimer("ResetMCPLaylist".$this->InstanceID, "IPS_Sleep(10000);RequestAction(".$mcPlaylist.", 0);");
						}
						break;
				}
			} catch(Exception $e) {
				$this-LogMessage(sprintf('An unexpected error occured. The error was : %s',  $e->getMessage()));
			}
		}

		public function GetConfigurationForm () {
			$control = $this->GetIDForIdent('Control');
			
			$form =	["elements"=>	[
										["type"=>"Image","image"=>"data:image/png;base64,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"],
										["type"=>"ValidationTextBox","name"=>"IPAddress","caption"=>"Device IP","validate"=>"^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"],
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
		}

		public function StartLink(string $RoomName) {
			try {
				$ipAddress = $this->ReadPropertyString('IPAddress');
				if(self::VerifyDeviceIp($ipAddress)) {	
					$system = new System($ipAddress);
					$clientIpAddress = $system->FindRoom($RoomName);
					if($clientIpAddress!==false) {
						$distribution = new Distrbution($system);
						$distribution->AddClient(new System($clientIpAddress));
						$distribution->Start();
					} else
						$this->LogMessage(sprintf('Did not find the room specified: %s', $RoomName), KL_ERROR);
				}
			} catch(Exception $e) {
				$this-LogMessage(sprintf('An unexpected error occured. The error was : %s',  $e->getMessage()));
			}
		}

		public function StopLink() {
			try {
				$ipAddress = $this->ReadPropertyString('IPAddress');	
				if(self::VerifyDeviceIp($ipAddress)) {	
					$system = new System($ipAddress);
					$distribution = new Distrbution($system);
					$distribution->Stop();
				}
			} catch(Exception $e) {
				$this-LogMessage(sprintf('An unexpected error occured. The error was : %s',  $e->getMessage()));
			}
		}

		private function Update(){
			$ipAddress = $this->ReadPropertyString('IPAddress');
			IPS_LogMessage('Update','The IP used for instance '.$this->InstanceID.' is '.$ipAddress);
			if(self::VerifyDeviceIp($ipAddress)) {
				$system = new System($ipAddress);
				$zone = new Zone($system);
				$netUSB = new NetUSB($system);
				
				$status = $zone->Status();
				
				if($status->power=='on') {
					$netUSB = new NetUSB($system);
					$playInfo = $netUSB->PlayInfo();

					$this->SetValueEx('Power', true);
					$this->SetValueEx('Volume', $status->volume);
					$this->SetValueEx('Mute', $status->mute);
	
					$control = $playInfo->Playback();
					$this->SetValueEx('Control', $control);

					if($control==3) { // Stop
						$this->SetValueEx('Input', '');
						$this->SetValueEx('Artist', '');
						$this->SetValueEx('Track', '');
						$this->SetValueEx('Album', '');
						$this->SetValueEx('Albumart', '');
					} else {
						$this->SetValueEx('Input', $playInfo->Input());
						$this->SetValueEx('Artist', $playInfo->Artist());
						$this->SetValueEx('Track', $playInfo->Track());
						$this->SetValueEx('Album', $playInfo->Album());
						$this->SetValueEx('Albumart', $playInfo->AlbumartURL());
					}
				} else {
					$this->SetValueEx('Power', false);
					$this->SetValueEx('Volume', 0);
					$this->SetValueEx('Mute', false);
	
					$this->SetValueEx('Control', 3); // Stop

					$this->SetValueEx('Input', '');
					$this->SetValueEx('Artist', '');
					$this->SetValueEx('Track', '');
					$this->SetValueEx('Album', '');
					$this->SetValueEx('Albumart', '');
				}
			}
		}

		private function UpdateLists() {
			$this->SetTimerInterval('UpdateLists'.$this->InstanceID, 0);
			
			$this->UpdateFavourites();
			$this->UpdatePlaylists();

			$this->SetTimerInterval('UpdateLists'.$this->InstanceID, $this->ReadPropertyInteger('UpdateListInterval')*1000);
		}
		
		private function SelectFavourite(int $Value) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(self::VerifyDeviceIp($ipAddress) && $Value!=0) { 
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				$netUSB->SelectFavouriteById($Value);
			}
		}

		private function SelectMCPlaylist(int $Value) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(self::VerifyDeviceIp($ipAddress) && $Value!=0) { 
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				$netUSB->SelectMCPlaylistById($Value);
			}
		}

		private function Volume(int $Level) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(self::VerifyDeviceIp($ipAddress)){
				$system = new System($ipAddress);
				$zone = new Zone($system);
				$zone->Volume($Level);
			}
		}

		private function Mute(bool $State) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(self::VerifyDeviceIp($ipAddress)){
				$system = new System($ipAddress);
				$zone = new Zone($system);
				$zone->Mute($State);
			}
		}

		private function Playback(string $State) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(self::VerifyDeviceIp($ipAddress)){
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				$netUSB->Playback($State);
			}
		}

		private function Power(bool $State) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(self::VerifyDeviceIp($ipAddress)){
				$system = new System($ipAddress);
				$zone = new Zone($system);
				$zone->Power($State);
			}
		}

		private function SetValueEx(string $Ident, $Value) {
			$oldValue = $this->GetValue($Ident);
			if($oldValue!=$Value)
				$this->SetValue($Ident, $Value);
		}

		private function VerifyDeviceIp($IpAddress) {
			if(strlen($IpAddress)>0)
				return true;
			else  {
				$this->LogMessage("The device is missing information about it's ip address", KL_ERROR);
				return false;
			}
		}

		private function UpdateFavourites() {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(self::VerifyDeviceIp($ipAddress)){
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				
				$favourites = $netUSB->Favourites();
				if(count($favourites)>0) {
					$assosiations = $this->CreateProfileAssosiationList($favourites);
					$profileName = 'YMC.' . $this->InstanceID . ".Favorites";
					$this->RegisterProfileIntegerEx($profileName, 'Music', '', '', $assosiations);
				}
			}
		}

		private function UpdatePlaylists() {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(self::VerifyDeviceIp($ipAddress)){
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				
				$playlists = $netUSB->MCPlaylists();
				if(count($playlists)>0) {
					$assosiations = $this->CreateProfileAssosiationList($playlists);
					$profileName = 'YMC.' . $this->InstanceID . ".Playlists";
					$this->RegisterProfileIntegerEx($profileName, 'Music', '', '', $assosiations);
				}
			}
		}
	}

