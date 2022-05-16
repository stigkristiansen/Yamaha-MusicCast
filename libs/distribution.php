<?PHP

declare(strict_types=1);

class Distrbution {
    use HttpRequest;

    private System $master;
    private $clients;
    private $ipAddress;
    private $groupID;

    public function __construct(System $Master) {
        $this->master = $Master;
        $this->ipAddress = $this->master->IpAddress();
                
        $distributionInfo = self::GetDistributionInfo();
        if(isset($distributionInfo->status) && $distributionInfo->status=='working') {
            $this->groupID = $distributionInfo->group_id;
            foreach($distributionInfo->client_list as $client) {
                $this->clients[] = $client->ip_address;
            }
        } else {
            $this->groupID = self::GenerateGroupID();
            $this->clients = array();
        }        
    }

    public function AddClient(System $Client) {
        if(self::ValidateClient($Client)) {
            $this->clients[] = $Client->IPAddress();
            self::SetClientInfo($Client);
        } else
            throw new Exception(sprintf('Distribution: Uncompatible client %s'), $Client->IpAddress());
    }

    public function RemoveClient(System $Client) {
        if (($key = array_search($Client->IPAddress(), $this->clients)) !== false) {
           unset($this->clients[$key]);
           self::SetServerInfo();
           self::SetClientInfo($Client, 'remove');
        } else 
            throw new Exception(sprintf('The client %s is not a part of this distribution'), $Client->IpAddress());

        if(count($this->clients)==0) {
            self::StopDistribution();
            self::SetServerInfo(true);
        }
    }

    public function Start(int $Num=0) {
        self::SetServerInfo();
        self::StartDistribution($Num);
    }

    public function Stop() {
        foreach($this->clients as $client) {
            self::RemoveClient(new System($client));
        }    
    }

    public function IsActive(){
        $distributionInfo = self::GetDistributionInfo();
        if(isset($distributionInfo->status) && $distributionInfo->status=='working') 
            return true;
        else
            return false;
    }

    private function GetDistributionInfo() {
        return self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/dist/getDistributionInfo');    
    }

    private function SetClientInfo(System $Client, string $type='add') {
        if(strtolower($type)=='add') {
            $params = array('group_id' => $this->groupID);
            $zones[] = $Client->ZoneName();
            $params['zone'] = $zones;
        } else {
            $params = array('group_id' => '');
            $zones[] = $Client->ZoneName();
            $params['zone'] = $zones;
        }

        $jsonParams = json_encode($params);

        return self::HttpPostJson($Client->IpAddress(), '/YamahaExtendedControl/v1/dist/setClientInfo', $jsonParams);
    }

    private function SetServerInfo(bool $Reset=false) {
        if($Reset) {
            $params = array('group_id' => '');    
        } else {
            $params = array('group_id' => $this->groupID);
            $params['zone'] = $this->master->ZoneName();
            $params['type'] = 'add';
            
            $params['client_list'] = $this->clients;
        }

        $jsonParams = json_encode($params);

        //return self::HttpPostJson($this->master->IpAddress(), '/YamahaExtendedControl/v1/dist/setServerInfo', $jsonParams);
        return self::HttpPostJson($this->ipAddress, '/YamahaExtendedControl/v1/dist/setServerInfo', $jsonParams);
    }

    private function StopDistribution() {
        return self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/dist/stopDistribution');
    }

    private function StartDistribution($Num=0) {
        return self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/dist/startDistribution?num='.$Num);
    }

    private function ValidateClient(System $Client) {
        $compatibleClientVersions = $this->master->Features()->distribution->compatible_client;
        $clientVersion = intval($Client->Features()->distribution->version);
        foreach($compatibleClientVersions as $version) {
            if($version==$clientVersion)
                return true;
        }
        
        return false;
    }

    private function GenerateGroupID () {
        $result = '';
        $length = 32;
        $module_length = 40;   
        $steps = round(($length/$module_length) + 0.5);

        for($i=0;$i<$steps;$i++) {
            $result .= sha1(uniqid() . md5(rand() . uniqid()));
        }

        return strtoupper(substr($result, 0, $length));
    }

}