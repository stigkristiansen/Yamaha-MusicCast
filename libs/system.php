<?PHP 

declare(strict_types=1);

class System {
    use HttpRequest;

    private $ipAddress;
    private $features;
    private $deviceInfo;
    private $deviceDesc;
    private $locationInfo;

    private $initialized = false;

    private $zoneName;

    public function __construct(string $ipAddress) {
        $this->ipAddress = $ipAddress;

        $featuresResult = self::HttpGetJson('/YamahaExtendedControl/v1/system/getFeatures');
        if($featuresResult!==false)
            $this->features = $featuresResult;

        $devicdeInfoResult = self::HttpGetJson('/YamahaExtendedControl/v1/system/getDeviceInfo');
        if($devicdeInfoResult!==false)
            $this->deviceInfo = $devicdeInfoResult;

        $locationInfoResult = self::HttpGetJson('/YamahaExtendedControl/v1/system/getLocationInfo');
        if($locationInfoResult!==false)
            $this->locationInfo = $locationInfoResult;

        $deviceDescResult = self::HttpGetXML(':49154/MediaRenderer/desc.xml');
        if($deviceDescResult!==false)
            $this->deviceDesc = $deviceDescResult;

        if($devicdeInfoResult!==false && $featuresResult!==false && $deviceDescResult!==false && $locationInfoResult!==false) {
            if(isset($locationInfoResult->zone_list->main) && $locationInfoResult->zone_list->main == true)
                $this->zoneName = Zones::MAIN;
            else if(isset($locationInfoResult->zone_list->zone2) && $locationInfoResult->zone_list->zone2 == true)
                $this->zoneName = Zones::ZONE2;
            else if(isset($locationInfoResult->zone_list->zone3) && $locationInfoResult->zone_list->zone3 == true)
                $this->zoneName = Zones::ZONE3;
            else if(isset($locationInfoResult->zone_list->zone4) && $locationInfoResult->zone_list->zone4 == true)
                $this->zoneName = Zones::ZONE4;
            else 
                throw new Exception("Failed to initilize the System object. Ivalid zone");
        } else
            throw new Exception("Failed to initilize the System object");

        //var_dump($featuresResult);
    }

    public function Features() {
        return $this->features;
    }

    public function IpAddress() {
        return $this->ipAddress;
    }

    public function ModelName() {
        return $this->deviceInfo->model_name;
    }

    public function ZoneList () {
        return $this->features->distribution->server_zone_list;
    }

    public function ZoneName() {
        return $this->zoneName;
    }

    public function ApiVersion(){
        return $this->deviceInfo->api_version;
    }

    public function LocationName() {
        return (string)$this->locationInfo->name;
    }

    public function RoomName() {
        return (string)$this->deviceDesc->device->friendlyName;
    }

    public function InputList(){
        foreach($this->features->zone as $zone) {
            if(strtolower($zone->id)==$this->zoneName) {
                return $zone->input_list;
            }
        }

        return false;
    }

    public function Rooms(){
        $rooms[] = ['name'=>$this->deviceDesc->device->friendlyName, $this->ipAddress);
        
        $treeInfo = self::HttpGetJson('/YamahaExtendedControl/v1/system/getMusicCastTreeInfo');
        foreach($treeInfo->mac_address_list as $device) {
            if($this->ipAddress!=$device->ip_address) {
                $system = new System($device->ip_address);
                $rooms[] = ['name'=>$system->RoomName(), 'ip'=>$device->ip_address];
            }
        }

        return $rooms;
    }


    public function FindRoom(string $RoomName) {
        $treeInfo = self::HttpGetJson('/YamahaExtendedControl/v1/system/getMusicCastTreeInfo');
        foreach($treeInfo->mac_address_list as $device) {
            if($this->ipAddress!=$device->ip_address) {
                $system = new System($device->ip_address);
                if(strtolower($RoomName)==strtolower($system->RoomName()))
                    return $device->ip_address;
            }
        }

        return false;
    }

    public function ValidFeature(string $Feature) {
        $Feature = strtolower($Feature);
        foreach($this->features->zone as $zone) {
            if(strtolower($zone->id)==$this->zoneName) {
                foreach($zone->func_list as $func) {
                    if(strtolower($func)==$Feature)
                        return true;
                }
            }
        }

        return false;
    }

    public function ValidInput(string $Input) {
        $Input = strtolower($Input);
        foreach($this->features->zone as $zone) {
            if(strtolower($zone->id)==$this->zoneName) {
                foreach($zone->input_list as $input) {
                    if(strtolower($input)==$Input)
                        return true;
                }
            }
        }

        return false;
    }

    public function ValidateVolume(int $Level) {
        foreach($this->features->zone as $zone) {
            if(strtolower($zone->id)==$this->zoneName) {
                foreach($zone->range_step as $range) {
                    if($range->id=='volume') {
                    $min = $range->min;
                    $max = $range->max;        
                    if($Level >=$min && $Level <=$max)
                        return true;
                    }
                }
            }
        }

        return false;
    }

}