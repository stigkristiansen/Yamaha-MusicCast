<?PHP

declare(strict_types=1);

class Zone {
    use HttpRequest;

    private System $system;
    private $ipAddress;
    private $zoneName;

    public function __construct(System $System, string $ZoneName) {
        $this->system = $System;
        
        if(!self::ValidZone($ZoneName))
            throw new Exception('Invalid Zone Name');

        $this->ipAddress = $this->system->IpAddress();
        $this->zoneName = strtolower($ZoneName);
    }

    public function ZoneName() {
        return $this->zoneName;
    }

    public function Status() {
        return self::httpGet('YamahaExtendedControl/v1/'.$this->zoneName.'/getStatus');
    }

    public function Power(bool $Status) {
        if(!self::ValidFeature('power'))
            throw new Exception('Power(): Invalid feature \"power\"');

        if($Status)
            $value = 'on';
        else 
            $value = 'standby';

        self::httpGet('YamahaExtendedControl/v1/'.$this->zoneName.'/setPower?power='.$value);    
    }

    public function Mute(bool $Status) {
        if(!self::ValidFeature('mute'))
            throw new Exception('Mute(): Invalid feature \"mute\"');
        
        if($Status)
            $value = 'true';
        else 
            $value = 'false';

        self::httpGet('YamahaExtendedControl/v1/'.$this->zoneName.'/setMute?enable='.$value);    
    }

    public function Input(string $Input) {
        if(!self::ValidInput($Input))
            throw new Exception('Input(): Invalid input \"'.$Input.'\"');

        self::httpGet('YamahaExtendedControl/v1/'.$this->zoneName.'/setInput?input='.$Input);   
    }

    public function InputList(){
        foreach($this->system->Zones() as $zone) {
            if(strtolower($zone->id)==$this->zoneName) {
                return $zone->input_list;
            }
        }

        return false;
    }

    private function ValidZone(string $ZoneName) {
        $ZoneName = strtolower($ZoneName);
        foreach($this->system->ZoneList() as $zone) {
            if(strtolower($zone) == $ZoneName)
                return true;
        }

        return false;
    }

    private function ValidFeature(string $Feature) {
        $Feature = strtolower($Feature);
        foreach($this->system->Zones() as $zone) {
            if(strtolower($zone->id)==$this->zoneName) {
                foreach($zone->func_list as $func) {
                    if(strtolower($func)==$Feature)
                        return true;
                }
            }
        }

        return false;
    }

    private function ValidInput(string $Input) {
        $Input = strtolower($Input);
        foreach($this->system->Zones() as $zone) {
            if(strtolower($zone->id)==$this->zoneName) {
                foreach($zone->input_list as $input) {
                    if(strtolower($input)==$Input)
                        return true;
                }
            }
        }

        return false;
    }
}
