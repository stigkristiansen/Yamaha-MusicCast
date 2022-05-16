<?PHP 

declare(strict_types=1);

class CD {
    use HttpRequest;
    use MusicCast;
    
    private System $system;
    private $ipAddress;
    private $zoneName;

    public function __construct($System) {
        $this->ipAddress = $System->IpAddress();
        $this->zoneName = $System.->ZoneName();
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
}