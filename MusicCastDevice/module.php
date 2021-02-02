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

			$this->RegisterVariableInteger('Control', 'Control', 'YMC.Control', 2);
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
			
			$this->RegisterTimer('Update'.$this->InstanceID, 5000, 'YMC_Update('.$this->InstanceID.');');
			$this->RegisterTimer('UpdateLists'.$this->InstanceID, 30000, 'YMC_UpdateLists('.$this->InstanceID.');');
		}

		public function Destroy() {
			$this->SetTimerInterval('UpdateLists'.$this->InstanceID, 0);
			$this->SetTimerInterval('Update'.$this->InstanceID, 0);
			$this->SetTimerInterval('ResetFavourite'.$this->InstanceID, 0);
			$this->SetTimerInterval('ResetMCPLaylist'.$this->InstanceID, 0);

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
				$this->SetTimerInterval('UpdateLists'.$this->InstanceID, $this->ReadPropertyInteger('UpdateListInterval'));
			else
				$this->SetTimerInterval('UpdateLists'.$this->InstanceID, 0);
		}

		public function RequestAction($Ident, $Value) {
			switch ($Ident) {
				case 'Control' :
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
		}

		public function GetConfigurationForm () {
			$form = json_decode(file_get_contents(__DIR__ . '/form.json'));

			return json_encode($form);
		}

		public function StartLink(string $RoomName) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0) {	
				$system = new System($ipAddress);
				$clientIpAddress = $system->FindRoom($RoomName);
				if($clientIpAddress!==false) {
					$distribution = new Distrbution($system);
					$distribution->AddClient(new System($clientIpAddress));
					$distribution->Start();
				}
			}
		}

		public function StopLink() {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0) {	
				$system = new System($ipAddress);
				$distribution = new Distrbution($system);
				$distribution->Stop();
			}
		}

		public function Update() {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0) {
				$system = new System($ipAddress);
				$zone = new Zone($system);
				
				$status = $zone->Status();

				if($status->power=='on') {
					$netUSB = new NetUSB($system);
					$playInfo = $netUSB->PlayInfo();

					$this->SetValueEx('Power', true);
					$this->SetValueEx('Volume', $status->volume);
					$this->SetValueEx('Mute', $status->mute);
	
					$this->SetValueEx('Control', $playInfo->Playback());

					$this->SetValueEx('Input', $playInfo->Input());
					$this->SetValueEx('Artist', $playInfo->Artist());
					$this->SetValueEx('Track', $playInfo->Track());
					$this->SetValueEx('Album', $playInfo->Album());
					$this->SetValueEx('Albumart', $playInfo->AlbumartURL());
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

		public function UpdateLists() {
			$this->SetTimerInterval('UpdateLists'.$this->InstanceID, 0);
			
			$this->UpdateFavourites();
			$this->UpdatePlaylists();

			$this->SetTimerInterval('UpdateLists'.$this->InstanceID, $this->ReadPropertyInteger('UpdateListInterval'));
		}
		
		public function SelectFavourite(int $Value) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0 && $Value!=0) { 
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				$netUSB->SelectFavouriteById($Value);
			}
		}

		public function SelectMCPlaylist(int $Value) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0 && $Value!=0) { 
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				$netUSB->SelectMCPlaylistById($Value);
			}
		}

		public function Volume(int $Level) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0){
				$system = new System($ipAddress);
				$zone = new Zone($system);
				$zone->Volume($Level);
			}
		}

		public function Mute(bool $State) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0){
				$system = new System($ipAddress);
				$zone = new Zone($system);
				$zone->Mute($State);
			}
		}

		public function Playback(string $State) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0){
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				$netUSB->Playback($State);
			}
		}

		public function Power(bool $State) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0){
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

		private function CreateProfileAssosiationList($List) {
			$count = 0;
			foreach($List as $value) {
				$assosiations[] = [$count, $value,  '', -1];
				$count++;
			}

			return $assosiations;
		}

		private function UpdateFavourites() {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0){
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
			if(strlen($ipAddress)>0){
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

