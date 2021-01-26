<?PHP

declare(strict_types=1);

trait HttpRequest {
    const PING_TIMEOUT = 1000;

    public function HttpGetJson(string $DeltaUrl) {
		if(Sys_Ping($this->ipAddress, PING_TIMEOUT)) {
			$completeUrl = 'http://'.$this->ipAddress.$DeltaUrl;
			
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
			throw new Exception(sprintf('Host %s is not responding', $this->ipAddress));
    }

    public function HttpGetXML(string $DeltaUrl) {
		if(Sys_Ping($this->ipAddress, PING_TIMEOUT)) {
			$completeUrl = 'http://'.$this->ipAddress.$DeltaUrl;
			
			$result = self::request ('get', $completeUrl);

            $originalResult = $result;
			$result = simplexml_load_string($result);
            
            if($result!==false) {
                return $result;
            } else
                throw new Exception(sprintf("%s returned invalid XML. The returned value was %s", $completeUrl, $originalResult));
		} else
			throw new Exception(sprintf('Host %s is not responding', $this->ipAddress));
    }


    private function request($Type, $Url, $Data=NULL) {
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


trait ProfileHelper
{
    protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
    {
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 1);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 1) {
                throw new Exception('Variable profile type does not match for profile ' . $Name);
            }
        }

        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }

    protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        if (count($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[count($Associations) - 1][0];
        }

        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

        foreach ($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
    }
}
