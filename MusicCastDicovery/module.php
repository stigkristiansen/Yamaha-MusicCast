<?php

declare(strict_types=1);


	class MusicCastDiscovery extends IPSModule {
		public function Create() {
			//Never delete this line!
			parent::Create();
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
			$devices = $this->DiscoverMusicCastDevices();
			$instances = $this->GetMusicCastInstances();
	
			$values = [];

			$this->SendDebug(IPS_GetName($this->InstanceID), 'Building Discovery form...', 0);
	
			// Add devices that are discovered
			if(count($devices)>0)
				$this->SendDebug(IPS_GetName($this->InstanceID), 'Adding discovered products...', 0);
			else
				$this->SendDebug(IPS_GetName($this->InstanceID), 'No products discovered!', 0);

			foreach ($devices as $serialNumber => $device) {
				$value = [
					'SerialNumber'	=> $serialNumber,
					'Name' => $device['Name'],
					'Model' => $device['Model'],
					'IPAddress' => $device['IPAddress'],
					'instanceID' => 0
				];

				$this->SendDebug(IPS_GetName($this->InstanceID), sprintf('Added product with id "%s"', $serialNumber), 0);
				
				// Check if discovered device has an instance that is created earlier. If found, set InstanceID
				$instanceId = array_search($serialNumber, $instances);
				if ($instanceId !== false) {
					$this->SendDebug(IPS_GetName($this->InstanceID), sprintf('The product (%s) already has an instance (%s). Adding InstanceId...', $serialNumber, $instanceId), 0);
					unset($instances[$instanceId]); // Remove from list to avoid duplicates
					$value['instanceID'] = $instanceId;
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
				$this->SendDebug(IPS_GetName($this->InstanceID), 'Adding instances that are not discovered...', 0);
			}
			foreach ($instances as $instanceId => $serialNumber) {
				$values[] = [
					'SerialNumber'  => $serialNumber, 
					'Name' 		 => json_decode(IPS_GetConfiguration($instanceId),true)['Name'],
					'Model'		 => json_decode(IPS_GetConfiguration($instanceId),true)['Model'],
					'IPAddress'	 => json_decode(IPS_GetConfiguration($instanceId),true)['IPAddress'],
					'instanceID' => $instanceId
				];

				$this->SendDebug(IPS_GetName($this->InstanceID), sprintf('Adding instance "%s" with InstanceID "%s"', IPS_GetName($instanceId), $instanceId), 0);
			}

			$form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
			$form['actions'][0]['values'] = $values;

			$this->SendDebug(IPS_GetName($this->InstanceID), 'Building form completed', 0);

			return json_encode($form);
		}

		
		private function DiscoverMusicCastDevices() : array {
			$this->LogMessage('Discovering MusicCast devices...', KL_NOTIFY);

			$this->SendDebug(IPS_GetName($this->InstanceID), 'Discovering MusicCast devices...', 0);

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
								$this->SendDebug(IPS_GetName($this->InstanceID), $msg, 0);
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
			
							$devices[$serialNumber] = ['Model' => $model, 'Name' => $name, 'IPAddress' => $ipAddress];
						}
					}
				}
			}

			$this->SendDebug(IPS_GetName($this->InstanceID), sprintf('Found %d MusicCast devices...', count($devices)), 0);

			return $devices;
		}

		private function GetMusicCastInstances () : array {
			$instances = [];

			$this->SendDebug(IPS_GetName($this->InstanceID), 'Searching for existing instances of MusicCast devices...', 0);

			$instanceIds = IPS_GetInstanceListByModuleID('{5B66102A-96ED-DF96-0B89-54E37501F997}');
        	
        	foreach ($instanceIds as $instanceId) {
				$instances[$instanceId] = IPS_GetProperty($instanceId, 'SerialNumber');
			}

			$this->SendDebug(IPS_GetName($this->InstanceID), sprintf('Found %d instances of MusicCast devices', count($instances)), 0);
			$this->SendDebug(IPS_GetName($this->InstanceID), 'Finished searching for MusicCast devices', 0);	

			return $instances;
		}

		private function HttpGet($Url) {
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
