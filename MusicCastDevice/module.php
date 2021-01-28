<?php

	require_once(__DIR__ . "/../libs/autoload.php");
	
	class MusicCastDevice extends IPSModule {
		use ProfileHelper;

		public function Create() {
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyString ("IPAddress", "");

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

			//AutomaticallyUpdateLists UpdateListInterval
			$this->RegisterPropertyBoolean('AutomaticallyUpdateLists', true);
			$this->RegisterPropertyInteger('UpdateListInterval', 30);

			//$this->RegisterAttributeString('Favourites', '');
			$profileName = 'YMC.' . $this->InstanceID . ".Favorites";
			$this->RegisterProfileIntegerEx($profileName, 'Music', '', '', []);
			$this->RegisterVariableInteger('Favourite', 'Favourite', $profileName, 10);
			$this->EnableAction('Favourite');

			//$this->RegisterAttributeString('MCPlaylists', '');
			$profileName = 'YMC.' . $this->InstanceID . ".Playlists";
			$this->RegisterProfileIntegerEx($profileName, 'Music', '', '', []);
			$this->RegisterVariableInteger('MCPLaylist', 'Playlist', $profileName, 11);
			$this->EnableAction('MCPLaylist');
			
			$this->RegisterTimer('Update', 5000, 'YMC_Update('.$this->InstanceID.');');
			$this->RegisterTimer('UpdateLists', 30000, 'YMC_UpdateLists('.$this->InstanceID.');');
			
		}

		public function Destroy() {
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges() {
			$system;
			//Never delete this line!
			parent::ApplyChanges();

		}

		public function RequestAction($Ident, $Value) {
			//IPS_LogMessage('MusicCast', 'RequestAction '.$Ident.':'.$Value);
			switch ($Ident) {
				case 'Control':
					switch ($Value) {
						case 0:
							$this->SetValue('Control', 1);
							self::Playback(PlaybackState::PREVIOUS);
							break;
						case 1:
							$this->SetValue('Control', 1);
							self::Playback(PlaybackState::PLAY);
							break;
						case 2:
							$this->SetValue('Control', 2);
							self::Playback(PlaybackState::STOP);
							break;
						case 3:
							$this->SetValue('Control', 3);
							self::Playback(PlaybackState::STOP);
						    break;
						case 4:
							$this->SetValue('Control', 1);
							self::Playback(PlaybackState::NEXT);
							break;
					}
					break;
				case 'Volume':
					$this->SetValue('Volume', $Value);
					self::Volume($Value);
					break;
				case 'Mute':
					$this->SetValue('Mute', $Value);
					self::Mute($Value);
					break;
				case 'Power':
					$this->SetValue('Power', $Value);
					self::Power($Value);
					if($Value)
						self::Update();
					break;
				case 'Favourite':
					$this->SetValue('Favourite', $Value);
					self::SelectFavourite($Value);
					break;
				case 'MCPLaylist':
					$this->SetValue('MCPLaylist',$Value);
					self::SelectMCPlaylist($Value);
					break;
			}
		}

		public function GetConfigurationForm () {
			$form = json_decode(file_get_contents(__DIR__ . '/form.json'));

			return json_encode($form);
		}

		public function Update() {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0){
				$system = new System($ipAddress);
				$zone = new Zone($system);
				
				$status = $zone->Status();

				$this->SetValue('MCPLaylist', 0);
				$this->SetValue('Favourite', 0);
			
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
			$this->SetTimerInterval('UpdateLists', 0);
			
			$this->UpdateFavourites();
			$this->UpdatePlaylists();

			$this->SetTimerInterval('UpdateLists', 30000);
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
					//$this->SetBuffer('Favourites', json_encode($favourites));
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
					//$this->SetBuffer('MCPlaylists', json_encode($playlists));
				}
			}
		}

		private function CreateProfileAssosiationList($List) {
			$count = 0;
			foreach($List as $value) {
				$assosiations[] = [$count, $value,  '', -1];
				$count++;
			}

			return $assosiations;
		}
		
		public function SelectFavourite(int $Value) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0 && $Value!=0) { 
				//$favourites = json_decode($this->GetBuffer('Favourites'), true);
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				$netUSB->SelectFavouriteById($Value);
				//$netUSB->SelectFavouriteByName($favourites[$Value]);			
			}
		}

		public function SelectMCPlaylist(int $Value) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0 && $Value!=0) { 
				//$playlists = json_decode($this->GetBuffer('MCPlaylists'), true);
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				$netUSB->SelectMCPlaylistById($Value);
				//$netUSB->SelectMCPlaylistByName($playlists[$Value]);
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


	}

