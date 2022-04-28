<?PHP 

declare(strict_types=1);

class Cd {
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
            throw new Exception(sprintf('Failed to initilize the Tuner object. Ivalid zone "%s"', $ZoneName));
        }
    }

    public function PlayInfo() : PlayInfo {
        $playInfoJson = self::httpGetJson($this->ipAddress, 'YamahaExtendedControl/v1/cd/getPlayInfo');

        if($playInfoJson!==false) {
            $albumartURL = '';
            $playInfo = new PlayInfo(
                            Input::CD,
                            $playInfoJson->artist,
                            $playInfoJson->album,
                            $playInfoJson->track,
                            $albumartURL,
                            $playInfoJson->playback,
                            $playInfoJson->total_time,
                            $playInfoJson->play_time
                            );
            
            return $playInfo;
        }
        
        return false;
    }

    public function Playback(string $State) {
        if($this->ValidPlaybackState($State)) {
            $status = self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/cd/setPlayback?playback='.$State);
        } else {
            throw new Exception(sprintf('Invalid playback state "%s"', $State));
        }
    }

    private function ValidPlaybackState(string $State) : bool {
        switch(strtolower($State)) {
            case PlaybackState::PLAY:
            case PlaybackState::STOP:
            case PlaybackState::PAUSE:
            case PlaybackState::PREVIOUS:
            case PlaybackState::NEXT:
                return true;
            default:
                return false;

        }
    }


}