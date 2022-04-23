<?PHP

declare(strict_types=1);

class Zone {
    use HttpRequest;

    private System $system;
    private $ipAddress;
    private $zoneName;

    public function __construct(System $System, string $ZoneName='main') {
        $this->system = $System;
        $this->ipAddress = $this->system->IpAddress();

        if($this->system->ValidZone($ZoneName)) {
            $this->zoneName = $ZoneName;
        } else {
            throw new Exception('Failed to initilize the Zone object. Ivalid zone "' . $ZoneName . '"');
        }
    }

    public function ZoneName() {
        return $this->zoneName;
    }

    public function Status() {
        return self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/'.$this->zoneName.'/getStatus');
    }
    

    public function Power(bool $Status) {
        if(!$this->system->ValidFeature('power'))
            throw new Exception('Power(): Invalid feature "power"');

        if($Status)
            $value = 'on';
        else 
            $value = 'standby';

        self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/'.$this->zoneName.'/setPower?power='.$value);    
    }

    public function Sleep(int $Minutes) {
        if(!$this->ValidateSleep($Minutes)) 
            throw new Exception('Sleep(): Invalid level "'.$Minutes.'"');
        
        self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/'.$this->zoneName.'/setSleep?sleep='.$Minutes);    
    }

    public function Mute(bool $Status) {
        if(!$this->system->ValidFeature('mute'))
            throw new Exception('Mute(): Invalid feature \"mute\"');
        
        if($Status)
            $value = 'true';
        else 
            $value = 'false';

        self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/'.$this->zoneName.'/setMute?enable='.$value);    
    }

    public function Volume(int $Level) {
        if(!$this->system->ValidateVolume($Level)) 
            throw new Exception('Volume(): Invalid level \"'.$Level.'\"');
        
        self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/'.$this->zoneName.'/setVolume?volume='.$Level);    
    }

    public function Input(string $Input) {
        if(!$this->system->ValidInput($Input))
            throw new Exception('Input(): Invalid input \"'.$Input.'\"');

        self::HttpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/'.$this->zoneName.'/setInput?input='.$Input);   
    }

    private function ValidateSleep(int $Minutes) {
        switch($Minutes) {
            case 0:
            case 30:
            case 60:
            case 90:
            case 120:
                return true;
        }

        return false;
    }

}