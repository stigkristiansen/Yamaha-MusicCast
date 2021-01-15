<?PHP

declare(strict_types=1);

trait HttpRequest {
    public function HttpGet(string $url) {
        $completeUrl = 'http://'.$this->ipAddress.'/'.$url;
        
        return self::request ('get', $completeUrl);
        
    }

    public function HttpPost(string $url, array $params) {

    }

    private function request($Type, $Url, $Data=NULL, $ReturnData=True) {
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

		if($result!==false){
			$originalResult = $result;
			$result = json_decode($result);
            
            if($result!==null) {
                if(isset($result->response_code) && $result->response_code==0) {
                    if($ReturnData)
                        return $result;
                    return true;
                } else if(isset($result->response_code) && $result->response_code!=0)
                    throw new Exception(sprintf("%s returned: error %d: %s", $Url, $result->response_code, ResponseCodes::GetMessage($result->response_code)));
                else
                    throw new Exception(sprintf("%s returned: %s", $Url, $result));
            } else
                throw new Exception(sprintf("%s returned invalid JSON. The returned value was %s", $Url, $originalResult));
		} else
			throw new Exception(sprintf("%s failed.", $Url));
				
	}
