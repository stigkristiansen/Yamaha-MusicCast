<?php

declare(strict_types=1);

require_once(__DIR__ . "/../libs/autoload.php");

	class MusicCastDiscovery extends IPSModule {
		public function Create() {
			//Never delete this line!
			parent::Create();

			$this->SetBuffer('Devices', json_encode([]));
            $this->SetBuffer('SearchInProgress', json_encode(false));
		}

		public function Destroy() {
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges() {
			//Never delete this line!
			parent::ApplyChanges();
		}

		public function GetConfigurationForm() {
			$this->SendDebug(__FUNCTION__, 'Generating the form...', 0);
            $this->SendDebug(__FUNCTION__, sprintf('SearchInProgress is "%s"', json_decode($this->GetBuffer('SearchInProgress'))?'TRUE':'FALSE'), 0);
            			
			$devices = json_decode($this->GetBuffer('Devices'));
           
			if (!json_decode($this->GetBuffer('SearchInProgress'))) {
                $this->SendDebug(__FUNCTION__, 'Setting SearchInProgress to TRUE', 0);
				$this->SetBuffer('SearchInProgress', json_encode(true));
				
				$this->SendDebug(__FUNCTION__, 'Starting a timer to process the search in a new thread...', 0);
				$this->RegisterOnceTimer('LoadDevicesTimer', 'IPS_RequestAction(' . (string)$this->InstanceID . ', "Discover", 0);');
            }

			$form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
			$form['actions'][0]['visible'] = count($devices)==0;
			
			$this->SendDebug(__FUNCTION__, 'Adding cached devices to the form', 0);
			$form['actions'][1]['values'] = $devices;

			$this->SendDebug(__FUNCTION__, 'Finished generating the form', 0);

            return json_encode($form);
		}

		public function RequestAction($Ident, $Value) {
			$this->SendDebug( __FUNCTION__ , sprintf('ReqestAction called for Ident "%s" with Value %s', $Ident, (string)$Value), 0);

			switch (strtolower($Ident)) {
				case 'discover':
					$this->SendDebug(__FUNCTION__, 'Calling LoadDevices()...', 0);
					$this->LoadDevices();
					break;
			}
		}
		
		private function LoadDevices() {
			$this->SendDebug(__FUNCTION__, 'Updating Discovery form...', 0);

			$devices = $this->DiscoverMusicCastDevices();
			$instances = $this->GetMusicCastInstances();
			
			$this->SendDebug(__FUNCTION__, 'Setting SearchInProgress to FALSE', 0);
			$this->SetBuffer('SearchInProgress', json_encode(false));
            
			$values = [];
			
			// Add devices that are discovered
			if(count($devices)>0) {
				$this->SendDebug(__FUNCTION__, 'Adding discovered devices...', 0);
			} else {
				$this->SendDebug(__FUNCTION__, 'No devices discovered!', 0);
			}

			foreach ($devices as $serialNumber => $device) {
				$value = [
					'SerialNumber'	=> $serialNumber,
					'Name' => $device['Name'],
					'Model' => $device['Model'],
					'IPAddress' => $device['IPAddress'],
					'instanceID' => 0
				];

				$this->SendDebug(__FUNCTION__, sprintf('Added device with serialnumber "%s"', $serialNumber), 0);
				
				// Check if discovered device has an instance that is created earlier. If found, set InstanceID
				$instanceId = array_search($serialNumber, $instances);
				if ($instanceId !== false) {
					$this->SendDebug(__FUNCTION__, sprintf('The device (%s) already has an instance (%s). Setting InstanceId and changing the name to "%s"', $serialNumber, $instanceId, IPS_GetName($instanceId)), 0);
					unset($instances[$instanceId]); // Remove from list to avoid duplicates
					$value['instanceID'] = $instanceId;
					$value['Name'] = IPS_GetName($instanceId);
				} 
				
				$value['create'] = [
					'moduleID'       => '{5B66102A-96ED-DF96-0B89-54E37501F997}',  
					'Name'			 => $device['Name'],
					'configuration'	 => [
						'SerialNumber' 	=> $serialNumber,
						'Model' 		=> $device['Model'],
						'IPAddress'		=> $device['IPAddress'],
						'Name'			=> $device['Name']
					]
				];
			
				$values[] = $value;
			}

			// Add devices that are not discovered, but created earlier
			if(count($instances)>0) {
				$this->SendDebug(__FUNCTION__, 'Adding instances that are not discovered...', 0);
			}
			foreach ($instances as $instanceId => $serialNumber) {
				$values[] = [
					'SerialNumber'  => $serialNumber, 
					'Name' 		 	=> IPS_GetName($instanceId), //json_decode(IPS_GetConfiguration($instanceId),true)['Name'],
					'Model'		 	=> json_decode(IPS_GetConfiguration($instanceId),true)['Model'],
					'IPAddress'	 	=> json_decode(IPS_GetConfiguration($instanceId),true)['IPAddress'],
					'instanceID' 	=> $instanceId
				];

				$this->SendDebug(__FUNCTION__, sprintf('Added instance "%s" with InstanceID "%s"', IPS_GetName($instanceId), $instanceId), 0);
			}

			$newDevices = json_encode($values);
			$this->SetBuffer('Devices', $newDevices);
			            
			$this->UpdateFormField('Discovery', 'values', $newDevices);
            $this->UpdateFormField('SearchingInfo', 'visible', false);

			$this->SendDebug(__FUNCTION__, 'Updating Discovery form completed', 0);
		}
		
		private function DiscoverMusicCastDevices() : array {
			$this->LogMessage('Discovering MusicCast devices...', KL_NOTIFY);

			$this->SendDebug(__FUNCTION__, 'Discovering MusicCast devices...', 0);

			$SSDPInstance = IPS_GetInstanceListByModuleID('{FFFFA648-B296-E785-96ED-065F7CEE6F29}')[0];
        	$discoveredDevices = YC_SearchDevices($SSDPInstance, 'urn:schemas-upnp-org:device:MediaRenderer:1');

			$devices = [];
			foreach($discoveredDevices as $device) {
				if(isset($device['Fields'][0]) && isset($device['IPv4'])) {
					$field0 = $device['Fields'][0];
					$ipAddress = $device['IPv4'];
					if(stripos($field0, 'Location: ')===0) {
						$url = substr($field0, stripos($field0, 'http://'));
						if($url!=$field0) {
							$result = $this->HttpGet($url);

							if($result['error']) {
								$msg = sprintf('Retrieving %s failed with error "%s"', $url, $result['errortext']);
								$this->LogMessage($msg, KL_ERROR);
								$this->SendDebug(__FUNCTION__, $msg, 0);
								continue;
							}

							$xml = simplexml_load_string(str_replace(':X_', '_X_', $result['xml'])); // simplexml_load_string don't accept ":" in tags...

							if($xml===false) {
								continue;
							}
										
							if(!isset($xml->{"device"}->{"manufacturer"})) {
								continue;
							}
							$manufacturer = (string)$xml->{"device"}->{"manufacturer"};
						  
							if(strcasecmp($manufacturer, 'Yamaha Corporation')!=0) {
								continue;
							}
			
							if(!isset($xml->{"device"}->{"friendlyName"})) {
								continue;
							}
							$name = (string)$xml->{"device"}->{"friendlyName"};
			
							if(!isset($xml->{"device"}->{"modelName"})) {
								continue;
							}
							$model = (string)$xml->{"device"}->{"modelName"};
			
							if(!isset($xml->{"device"}->{"serialNumber"})) {
								continue;
							}
							$serialNumber = (string)$xml->{"device"}->{"serialNumber"};

							$system = New System($ipAddress);
			
							$devices[$serialNumber] = [
								'Model' => $model,
								'Name' => $name,
								'IPAddress' => $ipAddress,
								'Zones' => $system->ZoneNames()
							];
						}
					}
				}
			}

			$this->SendDebug(__FUNCTION__, sprintf('Found %d MusicCast device(s)', count($devices)), 0);
			$this->SendDebug(__FUNCTION__, 'Finished discovering MusicCast devices', 0);

			return $devices;
		}

		private function GetMusicCastInstances () : array {
			$instances = [];

			$this->SendDebug(__FUNCTION__, 'Searching for existing instances of MusicCast devices...', 0);

			$instanceIds = IPS_GetInstanceListByModuleID('{5B66102A-96ED-DF96-0B89-54E37501F997}');
        	
        	foreach ($instanceIds as $instanceId) {
				$instances[$instanceId] = IPS_GetProperty($instanceId, 'SerialNumber');
			}

			$this->SendDebug(__FUNCTION__, sprintf('Found %d instance(s) of MusicCast devices', count($instances)), 0);
			$this->SendDebug(__FUNCTION__, 'Finished searching for MusicCast devices', 0);	

			return $instances;
		}

		private function HttpGet($Url) : array {
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $Url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
						
			$result = curl_exec($ch);
		
			$response = array('httpcode' => curl_getinfo($ch, CURLINFO_RESPONSE_CODE));
		
			if($result===false) {
				$response['error'] = true;
				$response['errortext'] = curl_error($ch);
					
				return $response;
			} 
		
			$response['error'] = false;
			$response['xml'] =  $result;
		
			return  $response;
		}
		
		
	}
