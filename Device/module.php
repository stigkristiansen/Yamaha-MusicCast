<?php

	require_once(__DIR__ . "/../libs/autoload.php");
	
	class Device extends IPSModule {
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

			$this->RegisterVariableString('Service', $this->Translate('Service'), '', 1);
			$this->RegisterVariableString('Artist', $this->Translate('Artist'), '', 2);
			$this->RegisterVariableString('Track', $this->Translate('Track'), '', 3);
			$this->RegisterVariableString('Album', $this->Translate('Album'), '', 4);
			
			$this->RegisterVariableInteger('Control', 'Control', 'Control.MusicCast', 5);
        	$this->EnableAction('Control');
			
			$this->RegisterVariableInteger('Volume', 'Volume', 'Intensity.100', 6);
			$this->EnableAction('Volume');
	
			$this->RegisterVariableBoolean('Mute', 'Mute', '~Switch', 7);
			$this->EnableAction('Mute');
			
		}

		public function Destroy() {
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges() {
			//Never delete this line!
			parent::ApplyChanges();
		}

		public function RequestAction($Ident, $Value) {
			switch ($Ident) {
				case 'Control':
					switch ($Value) {
						case 0:
							$this->LogMessage('Prev');
							$this->SetValue('Control', 0);
							break;
						case 1:
							$this->LogMessage('Play');
							$this->SetValue('Control', 1);
							break;
						case 2:
							$this->LogMessage('Pause');
							$this->SetValue('Control', 2);
							break;
						case 3:
							$this->LogMessage('Stop');
							$this->SetValue('Control', 3);
						    break;
						case 4:
							$this->LogMessage('Next');
							$this->SetValue('Control', 4);
							break;
					}
					break;
				case 'Volume':
					$this->LogMessage('Volume: '.$Value);
					break;
				case 'Mute':
					$this->LogMessage('Mute: '.$Value);
					break;
			}
		}
	}

