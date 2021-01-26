<?php

	require_once(__DIR__ . "/../libs/autoload.php");
	
	class MusicCastDevice extends IPSModule {
		use ProfileHelper;

		public function Create() {
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyString ("IPAddress", "");

			$this->RegisterProfileIntegerEx('Control.MusicCast', 'Information', '', '', [
				[0, 'Prev',  '', -1],
				[1, 'Play',  '', -1],
				[2, 'Pause', '', -1],
				[3, "Stop",  '', -1],
				[4, 'Next',  '', -1]
			]);

			$this->RegisterVariableBoolean('Power', 'Power', '~Switch', 1);
			$this->EnableAction('Power');

			$this->RegisterVariableInteger('Control', 'Control', 'Control.MusicCast', 1);
        	$this->EnableAction('Control');
			
			$this->RegisterVariableInteger('Volume', 'Volume', 'Intensity.100', 3);
			$this->EnableAction('Volume');
	
			$this->RegisterVariableBoolean('Mute', 'Mute', '~Switch', 4);
			$this->EnableAction('Mute');

			$this->RegisterVariableString('Input', $this->Translate('Service'), '', 5);
			$this->RegisterVariableString('Artist', $this->Translate('Artist'), '', 6);
			$this->RegisterVariableString('Track', $this->Translate('Track'), '', 7);
			$this->RegisterVariableString('Album', $this->Translate('Album'), '', 8);
			
			$this->RegisterTimer('Update', 5000, 'YMC_Update('.$this->InstanceID.');');
			
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
			IPS_LogMessage("MusicCast", "RequestAction");
			switch ($Ident) {
				case 'Control':
					switch ($Value) {
						case 0:
							self::Playback(PlaybackState::PREVIOUS);
							$this->SetValue('Control', 0);
							break;
						case 1:
							self::Playback(PlaybackState::PLAY);
							$this->SetValue('Control', 1);
							break;
						case 2:
							self::Playback(PlaybackState::STOP);
							$this->SetValue('Control', 2);
							break;
						case 3:
							self::Playback(PlaybackState::STOP);
							$this->SetValue('Control', 3);
						    break;
						case 4:
							self::Playback(PlaybackState::NEXT);
							$this->SetValue('Control', 4);
							break;
					}
					break;
				case 'Volume':
					self::Volume($Value);
					$this->SetValue('Volume', $Value);
					break;
				case 'Mute':
					self::Mute($Value);
					$this->SetValue('Mute', $Value);
					break;
				case 'Power':
					self::Power($Value);
					$this->SetValue('Power', $Value);
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
				$netUSB = new NetUSB($system);
				
				$status = $zone->Status();
				$playInfo = $netUSB->PlayInfo();

				$this->SetValue('Volume', $status->volume);
				$this->SetValue('Mute', $status->mute);
				
				if($status->power=='on') {
					$this->SetValue('Power', true);
					$this->SetValue('Input', $playInfo->Input());
					$this->SetValue('Artist', $playInfo->Artist());
					$this->SetValue('Track', $playInfo->Track());
					$this->SetValue('Album', $playInfo->Album());
				} else {
					$this->SetValue('Power', false);
					$this->SetValue('Input', '');
					$this->SetValue('Artist', '');
					$this->SetValue('Track', '');
					$this->SetValue('Album', '');
				}
			}
		}

		private function Volume(int $Level) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0){
				$system = new System($ipAddress);
				$zone = new Zone($system);
				$zone->Volume($Level);
			}
		}

		private function Mute(bool $State) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0){
				$system = new System($ipAddress);
				$zone = new Zone($system);
				$zone->Mute($State);
			}
		}

		private function Playback(string $State) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0){
				$system = new System($ipAddress);
				$netUSB = new NetUSB($system);
				$netUSB->Playback($State);
			}
		}

		private function Power(bool $State) {
			$ipAddress = $this->ReadPropertyString('IPAddress');
			if(strlen($ipAddress)>0){
				$system = new System($ipAddress);
				$zone = new Zone($system);
				$zone->Power($State);
			}
		}


	}

