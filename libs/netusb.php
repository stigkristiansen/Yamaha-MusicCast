<?PHP 

declare(strict_types=1);

class NetUSB {
    use HttpRequest;

    private System $system;
    private $ipAddress;
    private $zoneName;

    public function __construct($System) {
        //$this->system = $System;
        $this->zoneName = $System->ZoneName();
        $this->ipAddress = $System->IpAddress();
    }

    public function PlayInfo() {
        $playInfoJson = self::httpGetJson('/YamahaExtendedControl/v1/netusb/getPlayInfo');

        if($playInfoJson!==false) {
            $albumartURL = 'http://' . $this->ipAddress . $playInfoJson->albumart_url;
            $playInfo = new PlayInfo(
                            $playInfoJson->input,
                            $playInfoJson->artist,
                            $playInfoJson->album,
                            $playInfoJson->track,
                            $albumartURL,
                            $playInfoJson->playback
                            );
            return $playInfo;
        }

        return false;
    }

    public function Playback(string $State) {
        $status = self::httpGetJson('/YamahaExtendedControl/v1/netusb/setPlayback?playback='.$State);
    }

    public function MCPlaylists() {
        $result = self::httpGetJson('/YamahaExtendedControl/v1/netusb/getMcPlaylistName');

        if(count($result->name_list)>0)
            return $result->name_list;
        else    
            return [];
    }

    public function SelectMCPlaylist (string $Playlist, $index=1) {
        $Playlist = strtolower($Playlist);
        $bank = 1;
        $playlists = self::MCPlaylists();
        
        foreach($playlists as $playlist) {
            if(strtolower($playlist) == $Playlist)
                break;
            $bank++;
        }

        if($bank>count($playlists))
            throw new Exception('Unkonown playlist!');

        self::httpGetJson('/YamahaExtendedControl/v1/netusb/manageMcPlaylist?bank='.$bank.'&type=play&index='.$index.'&zone='.$this->zoneName);
    }

    public function Favourites(){
        $result = self::httpGetJson('/YamahaExtendedControl/v1/netusb/getPresetInfo');
        
        $favourites;
        foreach($result->preset_info as $favourite) {
            if($favourite->text!="")
               $favourites[] =  $favourite->text;
        }

        if(count($favourites)>0)
            return $favourites;
        else
            return [];
    }

    public function SelectFavourite(string $Favourite) {
        $Favourite = strtolower($Favourite);
        $num = 1;
        $favourites = self::Favourites();

        foreach($favourites as $favourite) {
            if(strtolower($favourite)==$Favourite)
                break;
            $num++;
        }

        if($num>count($favourites))
            throw new Exception('Unkonwn favourite!');

        self::httpGetJson('/YamahaExtendedControl/v1/netusb/recallPreset?zone='.$this->zoneName.'&num='.$num);    
    }

}
