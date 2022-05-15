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

    private $zoneNames;

    public function __construct(string $ipAddress) {
        $this->ipAddress = $ipAddress;

        $featuresResult = self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/system/getFeatures');
        if($featuresResult!==false)
            $this->features = $featuresResult;

        $deviceInfoResult = self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/system/getDeviceInfo');
        if($deviceInfoResult!==false)
            $this->deviceInfo = $deviceInfoResult;

        $locationInfoResult = self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/system/getLocationInfo');
        if($locationInfoResult!==false)
            $this->locationInfo = $locationInfoResult;

        $deviceDescResult = self::HttpGetXML($this->ipAddress, ':49154/MediaRenderer/desc.xml');
        if($deviceDescResult!==false)
            $this->deviceDesc = $deviceDescResult;

        if($deviceInfoResult!==false && $featuresResult!==false && $deviceDescResult!==false && $locationInfoResult!==false) {
            if(isset($locationInfoResult->zone_list->main) && $locationInfoResult->zone_list->main == true)
                $this->zoneNames[] = Zones::MAIN;
            if(isset($locationInfoResult->zone_list->zone2) && $locationInfoResult->zone_list->zone2 == true)
                $this->zoneNames[] = Zones::ZONE2;
            if(isset($locationInfoResult->zone_list->zone3) && $locationInfoResult->zone_list->zone3 == true)
                $this->zoneNames[] = Zones::ZONE3;
            if(isset($locationInfoResult->zone_list->zone4) && $locationInfoResult->zone_list->zone4 == true)
                $this->zoneNames[] = Zones::ZONE4;
        } else
            throw new Exception("Failed to initilize the System object");
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

    public function ZoneNames() {
        return $this->zoneNames;
    }

    public function ZoneName() {
        return 'main';
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

    public function InputList(string $ZoneName = 'main'){
        foreach($this->features->zone as $zone) {
            if(strtolower($zone->id)==$ZoneName) {
                return $zone->input_list;
            }
        }

        return false;
    }

    public function Rooms(){
        $rooms[] = ['name'=>$this->RoomName(), 'ip'=>$this->ipAddress];

        $treeInfo = self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/system/getMusicCastTreeInfo');
        foreach($treeInfo->mac_address_list as $device) {
            if($this->ipAddress!=$device->ip_address) {
                $system = new System($device->ip_address);
                $rooms[] = ['name'=>$system->RoomName(), 'ip'=>$device->ip_address];
            }
        }

        return $rooms;
    }

    public function FindRoom(string $RoomName) {
        $rooms = $this->Rooms();
        foreach($rooms as $room) {
            if(strtolower($room['name'])==strtolower($RoomName))
                return $room['ip'];
        }

        return false;
    }
    
    public function ValidFeature(string $Feature, string $ZoneName = 'main') {
        $Feature = strtolower($Feature);
        foreach($this->features->zone as $zone) {
            if(strtolower($zone->id)==$ZoneName) {
                foreach($zone->func_list as $func) {
                    if(strtolower($func)==$Feature)
                        return true;
                }
            }
        }

        return false;
    }

    public function ValidInput(string $Input, string $ZoneName = 'main') {
        $Input = strtolower($Input);
        foreach($this->features->zone as $zone) {
            if(strtolower($zone->id)==$ZoneName) {
                foreach($zone->input_list as $input) {
                    if(strtolower($input)==$Input)
                        return true;
                }
            }
        }

        return false;
    }

    public function ValidateVolume(int $Level, string $ZoneName = 'main') {
        foreach($this->features->zone as $zone) {
            if(strtolower($zone->id)==$ZoneName) {
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

    public function ValidZone(string $ZoneName) {
        $ZoneName = strtolower($ZoneName);
        foreach($this->features->zone as $zone) {
            if($ZoneName==strtolower($zone->id)) {
                return true;
            }
        }

        return false;

    }

}