<?PHP 

declare(strict_types=1);

class Tuner {
    use HttpRequest;

    private System $system;
    private $ipAddress;
    private $zoneName;

    public function __construct($System, string $ZoneName = 'main') {
        $this->ipAddress = $System->IpAddress();

        $ZoneName = strtolower($ZoneName);
        if($System->ValidZone($ZoneName)) {
            $this->zoneName = $ZoneName;
        } else {
            throw new Exception('Failed to initilize the Tuner object. Ivalid zone "' . $ZoneName . '"');
        }
    }

    public function PresetInfo(string $Band) : array {
        $presetInfo = self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/tuner/getPresetInfo?band=' . $Band);

        if($presetInfo!==false) {
            if(isset($presetInfo->preset_info)) {
                foreach($presetInfo->preset_info as $presetInfo) {
                    $preset[] = [
                        'band'=>$presetInfo->band,
                        'number'=>$presetInfo->number
                    ];
                }
                return $preset;
            }
        }  

        return false;
    }

    public function RecallPreset(string $Band, int $Number) : void  {
        $Band = strtolower($Band);

        self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/tuner/recallPreset?zone=' . $this->zoneName . '&band=' . $Band . '&num=' . $Number);
    }

    public function SwitchPreset(bool $Next=true) : void  {
        if($Next) {
            $direction = 'next';
        } else {
            $direction = 'previous';
        }

        self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/tuner/switchPreset?dir=' . $direction.);
    }

    switchPreset

    public function PlayInfo() : PlayInfo {
        $playInfoJson = self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/tuner/getPlayInfo');

        if($playInfoJson!==false) {
            $albumart = '';
            $albumartURL = '';
            $playback = PlaybackState::NOTHING_ID;
            $totalTime = 0;
            $playTime = 0;
            $input = PlayInfo::MapInput($playInfoJson->band);
            $album = '';
            $track = '';
            
            switch($input) {
                case 'FM':
                    if(isset($playInfoJson->rds)) {
                        $artist = $playInfoJson->rds->program_service;
                        $album = $playInfoJson->rds->program_type; 
                        $track = sprintf('%s | %s ', $playInfoJson->rds->radio_text_a, $playInfoJson->rds->radio_text_b);
                    } else  {
                        $artist = $playInfoJson->fm->freq;
                    }
                    break;
                case 'AM':
                    $artist = $playInfoJson->am->freq;
                    break;
                case 'DAB':
                    $artist = $playInfoJson->dab->service_label;
                    $album = $playInfoJson->dab->program_type;
                    $track = $playInfoJson->dab->dls;
                    break;
                default:
                    $msg = sprintf('Invalid band returend from getPlayInfo: %s', $input);
                    throw new Exception($msg);
            }
                
            $playInfoObject = new PlayInfo(
                    $input,
                    $artist,
                    $album,
                    $track,
                    $albumartURL,
                    $playback,
                    $totalTime,
                    $playTime
                    );
            
            return $playInfoObject;
        }

        return false;
    }
}