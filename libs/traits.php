<?PHP

declare(strict_types=1);

trait HttpRequest {
    protected function HttpGetJson(string $IpAddress, string $DeltaUrl) {
		if(self::Ping($IpAddress)) {
			$completeUrl = 'http://' . $IpAddress . $DeltaUrl;
			
			$result = self::request ('get', $completeUrl);

            $originalResult = $result;
			$result = json_decode($result);
            
            if($result!==null) {
                if(isset($result->response_code) && $result->response_code==0) {
                    return $result;
                } else if(isset($result->response_code) && $result->response_code!=0)
                    throw new Exception(sprintf("%s returned: error %d: %s", $completeUrl, $result->response_code, ResponseCodes::GetMessage($result->response_code)));
                else
                    throw new Exception(sprintf("Error: %s returned: %s", $completeUrl, $result));
            } else
                throw new Exception(sprintf("%s returned invalid JSON. The returned value was %s", $completeUrl, $originalResult));
		} else
			throw new Exception(sprintf('Host %s is not responding', $IpAddress));
    }

    protected function HttpGetXML(string $IpAddress, string $DeltaUrl) {
		if(self::Ping($IpAddress)) {
			$completeUrl = 'http://' . $IpAddress . $DeltaUrl;
			
			$result = self::request ('get', $completeUrl);

            $originalResult = $result;
			$result = simplexml_load_string($result);
            
            if($result!==false) {
                return $result;
            } else
                throw new Exception(sprintf("%s returned invalid XML. The returned value was %s", $completeUrl, $originalResult));
		} else
			throw new Exception(sprintf('Host %s is not responding', $IpAddress));
    }

    protected function HttpPostJson(string $IpAddress, string $DeltaUrl, string $JsonParams) {
	    if(self::Ping($IpAddress)) {
			$completeUrl = 'http://' . $IpAddress . $DeltaUrl;
			
			$result = self::request('post', $completeUrl, $JsonParams);

            $originalResult = $result;
			$result = json_decode($result);
            
            if($result!==null) {
                if(isset($result->response_code) && $result->response_code==0) {
                    return $result;
                } else if(isset($result->response_code) && $result->response_code!=0)
                    throw new Exception(sprintf("%s returned: error %d: %s", $completeUrl, $result->response_code, ResponseCodes::GetMessage($result->response_code)));
                else
                    throw new Exception(sprintf("Error: %s returned: %s", $completeUrl, $result));
            } else
                throw new Exception(sprintf("%s returned invalid JSON. The returned value was %s", $completeUrl, $originalResult));
		} else
			throw new Exception(sprintf('Host %s is not responding', $IpAddress));	
    }

    protected function Ping(string $IpAddress) {
        return true; // Checked in module.php

        $wait = 500;
        for($count=0;$count<3;$count++) {
            if(Sys_Ping($IpAddress, $wait))
                return true;
            $wait*=2;
        }

        return false;
    }


    protected function request($Type, $Url, $Data=NULL) {
		$ch = curl_init();
		
		switch(strtolower($Type)) {
			case "put":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				break;
			case "post":
				curl_setopt($ch, CURLOPT_POST, 1 );
				break;
			case "get":
				// Get is default for cURL
				break;
		}
		
		curl_setopt($ch, CURLOPT_URL, $Url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		
		if($Data!=NULL)
			curl_setopt($ch, CURLOPT_POSTFIELDS, $Data); 
		
		$result=curl_exec($ch);

		if($result!==false)
            return $result;
		else
		    throw new Exception(sprintf("%s failed.", $Url));
				
	}
}


trait ProfileHelper {
    protected function DeleteProfile($Name) {
        if(IPS_VariableProfileExists($Name))
            IPS_DeleteVariableProfile($Name);
    }

    protected function RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix) {

        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 0);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 0) {
                throw new Exception('Variable profile type (boolean) does not match for profile ' . $Name);
            }
        }

        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
    }

    protected function RegisterProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
        
        $this->RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix);

        foreach ($Associations as $association) {
            IPS_SetVariableProfileAssociation($Name, $association[0], $association[1], $association[2], $association[3]);
        }
        
        // Remove assiciations that is not specified in $Associations
        $profileAssociations = IPS_GetVariableProfile($Name)['Associations'];
        foreach($profileAssociations as $profileAssociation) {
            $found = false;
            foreach($Associations as $association) {
                if($profileAssociation['Value']==$association[0]) {
                    $found = true;
                    break;
                }
            }

            if(!$found)
                IPS_SetVariableProfileAssociation($Name, $profileAssociation['Value'], '', '', -1);    
        }
    }
    
    protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 1);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 1) {
                throw new Exception('Variable profile type (integer) does not match for profile ' . $Name);
            }
        }

        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }

    protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
        
        if (count($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[count($Associations) - 1][0];
        }

        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

        foreach ($Associations as $association) {
            IPS_SetVariableProfileAssociation($Name, $association[0], $association[1], $association[2], $association[3]);
        }
        
        // Remove assiciations that is not specified in $Associations
        $profileAssociations = IPS_GetVariableProfile($Name)['Associations'];
        foreach($profileAssociations as $profileAssociation) {
            $found = false;
            foreach($Associations as $association) {
                if($profileAssociation['Value']==$association[0]) {
                    $found = true;
                    break;
                }
            }

            if(!$found)
                IPS_SetVariableProfileAssociation($Name, $profileAssociation['Value'], '', '', -1);    
        }
    }

    protected function CreateProfileAssosiationList($List) {
        $count = 0;
        foreach($List as $value) {
            $assosiations[] = [$count, $value,  '', -1];
            $count++;
        }

        return $assosiations;
    }

    protected function GetProfileAssosiationName($ProfileName, $Index) {
        $profile = IPS_GetVariableProfile($ProfileName);
    
        if($profile!==false) {
            foreach($profile['Associations'] as $association) {
                if($association['Value']==$Index)
                    return $association['Name'];
            }
        } 
    
        return false;
    
    }
}

trait BufferHelper {
    protected function Lock(string $Id) {
		for ($count=0;$count<10;$count++) {
			if (IPS_SemaphoreEnter(get_class() . (string) $this->InstanceID . $Id, 1000)) {
				return true;
			} else {
				IPS_Sleep(mt_rand(1, 5));
			}
		}

		return false;
	}

	protected function Unlock(string $Id) {
		IPS_SemaphoreLeave(get_class() . (string) $this->InstanceID . $Id);
	}

}
