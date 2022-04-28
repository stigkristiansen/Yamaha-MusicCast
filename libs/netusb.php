<?PHP 

declare(strict_types=1);

class NetUSB {
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
            throw new Exception('Failed to initilize the NetUSB object. Ivalid zone "' . $ZoneName . '"');
        }
    }

    public function PlayInfo() {
        $playInfoJson = self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/netusb/getPlayInfo');

        if($playInfoJson!==false) {
            $albumartURL = 'http://' . $this->ipAddress . $playInfoJson->albumart_url;
            $playInfo = new PlayInfo(
                            $playInfoJson->input,
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
        $this->SendDebug(__FUNCTION__, 'Class NetUSB and function Playback is called', 0);
        if($this->ValidPlaybackState($State)) {
            $status = self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/netusb/setPlayback?playback='.$State);
        } else {
            $this->SendDebug(__FUNCTION__, 'Invalit playback state. Throwing an error', 0);
            throw new Exception(sprintf('Invalid playback state "%s"', $State));
        }
        
    }

    public function MCPlaylists() {
        $result = self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/netusb/getMcPlaylistName');
    
        $playlists[]=' ';
        foreach($result->name_list as $list) {
            $playlists[] = $list;
        }

        
        return $playlists;
    }

    public function SelectMCPlaylistByName (string $Playlist, $index=0) {
        $Playlist = strtolower($Playlist);
        $bank = 0;
        $playlists = self::MCPlaylists();
        
        foreach($playlists as $playlist) {
            if(strtolower($playlist) == $Playlist)
                break;
            $bank++;
        }

        if($bank>count($playlists)-1)
            throw new Exception('Unkonown playlist!');
        
        self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/netusb/manageMcPlaylist?bank='.$bank.'&type=play&index='.$index.'&zone='.$this->zoneName);
    }

    public function SelectMCPlaylistById (int $Id, $index=0) {
        $playlists = self::MCPlaylists();
        
        if($Id>count($playlists)-1 || $Id<0)
            throw new Exception('Unkonown playlist!');
        
        self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/netusb/manageMcPlaylist?bank='.$Id.'&type=play&index='.$index.'&zone='.$this->zoneName);
    }

    public function Favourites(){
        $result = self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/netusb/getPresetInfo');
        
        $favourites[]=' ';
        foreach($result->preset_info as $favourite) {
            if($favourite->text!="")
               $favourites[] =  $favourite->text;
        }

        return $favourites;
    }

    public function SelectFavouriteByName(string $Favourite) {
        $Favourite = strtolower($Favourite);
        $num = 0;
        $favourites = self::Favourites();

        foreach($favourites as $favourite) {
            if(strtolower($favourite)==$Favourite)
                break;
            $num++;
        }

        if($num>count($favourites)-1)
            throw new Exception('Unkonwn favourite!');

        self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/netusb/recallPreset?zone='.$this->zoneName.'&num='.$num);    
    }

    public function SelectFavouriteById(int $Id) {
        $favourites = self::Favourites();

        if($Id>count($favourites)-1 || $Id<0)
            throw new Exception('Unkonwn favourite!');

        self::httpGetJson($this->ipAddress, '/YamahaExtendedControl/v1/netusb/recallPreset?zone='.$this->zoneName.'&num='.$Id);    
    }

}
