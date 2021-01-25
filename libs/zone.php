<?PHP

declare(strict_types=1);

class Zone {
    use HttpRequest;

    private System $system;
    private $ipAddress;
    private $zoneName;

    public function __construct(System $System) {
        $this->system = $System;
        $this->ipAddress = $this->system->IpAddress();
        $this->zoneName = $this->system->ZoneName();
    }

    public function ZoneName() {
        return $this->zoneName;
    }

    public function Status() {
        return self::HttpGetJson('/YamahaExtendedControl/v1/'.$this->zoneName.'/getStatus');
    }

    public function Power(bool $Status) {
        if(!self::ValidFeature('power'))
            throw new Exception('Power(): Invalid feature \"power\"');

        if($Status)
            $value = 'on';
        else 
            $value = 'standby';

        self::HttpGetJson('/YamahaExtendedControl/v1/'.$this->zoneName.'/setPower?power='.$value);    
    }

    public function Mute(bool $Status) {
        if(!$this->system->ValidFeature('mute'))
            throw new Exception('Mute(): Invalid feature \"mute\"');
        
        if($Status)
            $value = 'true';
        else 
            $value = 'false';

        self::HttpGetJson('/YamahaExtendedControl/v1/'.$this->zoneName.'/setMute?enable='.$value);    
    }

    public function Input(string $Input) {
        if(!$this->system->ValidInput($Input))
            throw new Exception('Input(): Invalid input \"'.$Input.'\"');

        self::HttpGetJson('/YamahaExtendedControl/v1/'.$this->zoneName.'/setInput?input='.$Input);   
    }

    public function InputList(){
        foreach($this->system->Zones() as $zone) {
            if(strtolower($zone->id)==$this->zoneName) {
                return $zone->input_list;
            }
        }

        return false;
    }
}