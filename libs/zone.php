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
        IPS_LogMessage("MusicCast", '/YamahaExtendedControl/v1/'.$this->zoneName.'/setPower?power=???');
        
        if(!self::ValidFeature('power'))
            throw new Exception('Power(): Invalid feature \"power\"');

        if($Status)
            $value = 'on';
        else 
            $value = 'standby';

        IPS_LogMessage("MusicCast", '/YamahaExtendedControl/v1/'.$this->zoneName.'/setPower?power='.$value);

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

    public function Volume(int $Level) {
        if(!$this->system->ValidateVolume($Level)) 
            throw new Exception('Volume(): Invalid level \"'.$Level.'\"');
        
        self::HttpGetJson('/YamahaExtendedControl/v1/'.$this->zoneName.'/setVolume?volume='.$Level);    
    }

    public function Input(string $Input) {
        if(!$this->system->ValidInput($Input))
            throw new Exception('Input(): Invalid input \"'.$Input.'\"');

        self::HttpGetJson('/YamahaExtendedControl/v1/'.$this->zoneName.'/setInput?input='.$Input);   
    }

}