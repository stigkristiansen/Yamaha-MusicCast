<?PHP 

declare(strict_types=1);

class System {
    use HttpRequest;

    private $ipAddress;
    private $features;
    private $deviceInfo;

    private $initialized = false;

    public function __construct(string $ipAddress) {
        $this->ipAddress = $ipAddress;

        $featuresResult = self::httpGet('YamahaExtendedControl/v1/system/getFeatures');
        if($featuresResult!==false)
            $this->features = $featuresResult;

        $devicdeInfoResult = self::httpGet('YamahaExtendedControl/v1/system/getDeviceInfo');
        if($devicdeInfoResult!==false)
            $this->deviceInfo = $devicdeInfoResult;

        if($devicdeInfoResult!==false && $featuresResult!==false)
            $this->initialized = true;

    }

    public function IpAddress() {
        return $this->ipAddress;
    }

    public function ModelName() {
        
        if($this->initialized)
            return $this->deviceInfo->model_name;
        
        return false;
    }

    public function ZoneList () {
        if($this->initialized)
            return $this->features->distribution->server_zone_list;
              
        return false;
    }

    public function Zones() {
        if($this->initialized)
            return $this->features->zone;
              
        return false;

    }

    public function ApiVersion(){
        if($this->initialized)
            return $this->deviceInfo->api_version;
        
        return false;
    }

    public function LocationInfo() {
        return self::httpGet('YamahaExtendedControl/v1/system/getLocationInfo');
    }
}
