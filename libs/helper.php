<?PHP

declare(strict_types=1);

class Zones {
    const MAIN = "main";
    const ZONE2 = "zone2";
    const ZONE3 = "zone3";
    const ZONE4 = "zone4";
}

class Types {
    const NETUSB = 'netusb';
    const TUNER = 'tuner';
    const CD = 'cd';
}

class Errors {
    const UNEXPECTED = 'An unexpected error occured. The error was "%s"';
    const UNKNOWNROOM = 'Did not find the room specified (%s)';
    const NOTRESPONDING = 'The device %s is not responding (%s)';
    const MISSINGIP = "The device %s is missing information about it's ip address";
    const ROOMERROR = 'Unable to read room list in profile "%s" or invalid room value %d';
    const INVALIDDATA = 'The data received was invalid';
    const STOPLINK = 'Stopping link...';
    const INVALIDTYPE = 'Invalid type (%s)';
}

class Debug {
    const UPDATEALLLISTS = 'Starting scheduled updating of all lists...';
    const UPDATELINK = 'Starting scheduled updating the Link list...';
    const ESTABLISHLINK = 'Establishing link with room "%s"...';
    const UPDATINGVARIABLES = 'Updating variables...';
    const GETINFORMATION = 'Starting scheduled retrieving of information...';
    const STOPSTATUSUPDATED = 'Skipping processing status_updated!';
    const GETSTATUS = 'Retrieving status...';
    const STARTSTATUSUPDATED = 'Processing event status_updated...';
    const STOPPLAYINFO = 'Skipping processing play_info_updated!';
    const STARTPLAYINFO = 'Processing event play_info_updated...';
    const HANDLEPLAYINFO = 'Handling play_info_updated in own thread...';
    const HANDLESTATUSUPDATED = 'Handling status_updated in own thread...';
    const ALLLISTS = 'Updated all list(s)';
    const LINKLIST = 'Updated Link list';
    const GETPLAYINFO = 'Retrieving PlayInfo for "%s"';
    const STOPLINK = 'Stopping the link!';
    const SEARCHFORROOM = 'Seaching for value %d in profile "%s"';
}

class Properties {
    const MODEL = 'Model';
    const SERIALNUMBER = 'SerialNumber';
    const NAME = 'Name';
    const IPADDRESS = 'IPAddress';
    const AUTOUPDATELISTS= 'AutoUpdateLists';
    const AUTOUPDATELISTINTERVAL= 'UpdateListInterval';
    const ZONENAME = 'ZoneName';
    const INPUTS = 'Inputs';
    const SOUNDPROGRAMS = 'SoundPrograms';
}

class Timers {
    const UPDATE = 'Update';
    const UPDATELISTS = 'UpdateLists';
    const RESETFAVOURITE = 'ResetFavourite';
    const RESETMCPLAYLIST = 'ResetMCPLaylist';
    const RESETCONTROL = 'ResetControl';
    const RESETINPUTS = 'ResetInputs';
    const RESETSOUNDPROGRAMS = 'ResetSoundPrograms';
}

class Profiles {
    const CONTROL = 'YMC.Control';
    const CONTROL_ICON = 'Execute';
    const INFORMATION = 'YMC.Information';
    const INFORMATION_ICON = 'Information';
    const MUTE = 'YMC.Mute';
    const MUTE_ICON = 'Speaker';
    const SLEEP = 'YMC.Sleep';
    const SLEEP_ICON = 'Sleep';
    const SLEEP_SUFIX = 'min.';
    const FAVORITES = 'YMC.%s.Favorites';
    const FAVORITES_ICON = 'Music';
    const MCPLAYLISTS = 'YMC.%s.Playlists';
    const MCPLAYLISTS_ICON = 'Music'; 
    const LINK = 'YMC.%s.Link'; 
    const LINK_ICON = 'Link'; 
    const POSITION = 'YMC.Position';
    const POSITION_ICON = 'Distance';
    const TIME = 'YMC.Time';
    const TIME_ICON = 'Hourglass';
    const MUSIC = 'YMC.Music';
    const MUSIC_ICON = 'Music';
    const INPUTS = 'YMC.%s.Inputs';
    const INPUTS_ICON = 'Music';
    const SOUNDPROGRAMS = 'YMC.%s.SoundPrograms';
    const SOUNDPROGRAMS_ICON = 'Music';
}

class Buffers {
    const REPORT = 'report';
    const INPUTS = 'inputs';
    const SOUNDPROGRAMS = 'soundprograms';
}

class Variables {
    const SLEEP_IDENT = 'Sleep';
    const SLEEP_TEXT = 'Sleep';
    const VOLUME_IDENT = 'Volume';
    const VOLUME_TEXT = 'Volume';
    const MUTE_IDENT = 'Mute';
    const MUTE_TEXT = 'Mute';
    const CONTROL_IDENT = 'Control';
    const CONTROL_TEXT = 'Action';
    const STATUS_IDENT = 'Status';
    const STATUS_TEXT = 'Status';
    const POWER_IDENT = 'Power';
    const POWER_TEXT = 'Power';
    const LINK_IDENT = 'Link';
    const LINK_TEXT = 'Link';
    const INPUT_IDENT = 'Input';
    const INPUT_TEXT = 'Input';
    const ARTIST_IDENT = 'Artist';
    const ARTIST_TEXT = 'Artist';
    const TRACK_IDENT = 'Track';
    const TRACK_TEXT = 'Track';
    const ALBUM_IDENT = 'Album';
    const ALBUM_TEXT = 'Album';
    const ALBUMART_IDENT = 'Albumart';
    const ALBUMART_TEXT = 'Album art';
    const FAVOURITE_IDENT = 'Favourite';
    const FAVOURITE_TEXT = 'Favourite';
    const MCPLAYLIST_IDENT = 'MCPlaylist';
    const MCPLAYLIST_TEXT = 'Playlist';
    const PLAYTIME_IDENT = 'CurrentTime';
    const PLAYTIME_TEXT = 'Current';
    const TOTALTIME_IDENT = 'Duration';
    const TOTALTIME_TEXT = 'Duration';
    const POSITION_IDENT = 'Position';
    const POSITION_TEXT = 'Position'; 
    const TIME_LEFT_IDENT = 'TimeLeft';
    const TIME_LEFT_TEXT = 'Time left';
    const INPUTS_IDENT = 'Inputs';
    const INPUTS_TEXT = 'Inputs';
    const SOUNDPROGRAMS_IDENT = 'SoundPrograms';
    const SOUNDPROGRAMS_TEXT = 'Sound Programs';
    const SOUNDPROGRAM_IDENT = 'SoundProgram';
    const SOUNDPROGRAM_TEXT = 'Sound Program';
}

class Sleep {
    const DISABLED = 0;
    const DISABLED_TEXT = 'OFF';
    const STEP1 = 30;
    const STEP1_TEXT = '30 min.';
    const STEP2 = 60;
    const STEP2_TEXT = '60 min.';
    const STEP3 = 90;
    const STEP3_TEXT = '90 min.';
    const STEP4 = 120;
    const STEP4_TEXT = '120 min';
}

class Mute {
    const MUTED = 'Muted';
    const UNMUTED = 'Unmuted';
}

class SoundProgram {
    const NOTHING = 'none';
}

class PlaybackState {
    const NOTHING = 'nothing';
    const NOTHING_TEXT = ' ';
    const NOTHING_ID = -1;
    const PLAY = 'play';
    const PLAY_TEXT = 'Play';
    const PLAY_ID = 1;
    const STOP = 'stop';
    const STOP_TEXT = 'Stop';
    const STOP_ID = 3;
    const PREVIOUS = 'previous';
    const PREVIOUS_TEXT = 'Previous';
    const PREVIOUS_ID = 0;
    const NEXT = 'next';
    const NEXT_TEXT = 'Next';
    const NEXT_ID = 4;
    const PAUSE = 'pause';
    const PAUSE_TEXT = 'Pause';
    const PAUSE_ID = 2;
}

class Input {
    const NOTHING = 'none';
    const TIDAL = 'Tidal';
    const NETRADIO = 'Network Radio';
    const LINK = 'Link';
    const AM = 'AM';
    const FM = 'FM';
    const DAB = 'DAB';
    const CD = 'CD';
    const NAPSTER = 'Napster';
    const SPOTIFY = 'Spotify';
    const QOBUZ = 'Qobuz';
    const DEEZER = 'Deezer';
    const AMZON_MUSIC = 'Amazon Music';
    const ALEXA = 'Alexa';
    const AIRPLAY = 'Airplay';
    const SERVER = 'Server';
    const NET_SERVER = 'Net Server';
    const BLUETOOTH = 'Bluetooth';
    const USB = 'Usb';
    const TUNER = 'Tuner';
    const HDMI1 = 'HDMI1';
    const HDMI2 = 'HDMI2';
    const HDMI3 = 'HDMI3';
    const HDMI4 = 'HDMI4';
    const AV1 = 'AV1';
    const AV2 = 'AV2';
    const AV3 = 'AV3';
    const AUDIO1 = 'Audio1';
    const AUDIO2 = 'Audio2';
    const AUDIO3 = 'Audio3';
    const AUX = 'Aux';
}

class PlayInfo {
    private $input;
    private $artist;
    private $album;
    private $track;
    private $albumartUrl;
    private $playback;
    private $totalTime;
    private $playTime;

    /*static function MapInput(string $Input) {
        switch(strtolower($Input)) {
            case 'tidal':
                return Input::TIDAL;
            case 'net_radio':
                return Input::NETRADIO;
            case 'mc_link':
                return Input::LINK;
            case 'fm':
                return Input::FM;
            case 'am':
                return Input::AM;
            case 'dab':
                return Input::DAB;
            case 'cd':
                return Input::CD;
            case 'napster':
                return Input::NAPSTER;
            case 'spotify':
                return Input::SPOTIFY;
            case 'qobuz':
                return Input::QOBUZ;
            case 'deezer':
                return Input::DEEZER;
            case 'amazon_music':
                return Input::AMZON_MUSIC;
            case 'alexa':
                return Input::ALEXA;
            case 'airplay':
                return Input::AIRPLAY;
            case 'server':
                return Input::SERVER;
            case 'bluetooth':
                return Input::BLUETOOTH;
            case 'usb':
                return Input::USB;
            case 'tuner':
                return Input::TUNER;
            case 'hdmi1':
                return Input::HDMI1;
            case 'hdmi2':
                return Input::HDMI2;
            case 'hdmi3':
                return Input::HDMI3;
            case 'hdmi4':
                return Input::HDMI4;
            case 'av1':
                return Input::AV1;
            case 'av2':
                return Input::AV2;
            case 'av3':
                return Input::AV3;
            case 'audio1':
                return Input::AUDIO1;
            case 'audio2':
                return Input::AUDIO2;
            case 'audio3':
                return Input::AUDIO3;
            case 'aux':
                return Input::AUX;
            default:
                return $Input;
        }
    }*/

    public function __construct(string $Input, string $Artist, string $Album, string $Track, string $AlbumartUrl, string $Playback, int $TotalTime = 0, int $PlayTime = 0) {
        $this->input = $Input;
        $this->artist = $Artist;
        $this->album = $Album;
        $this->track = $Track;
        $this->albumartUrl = $AlbumartUrl;
        $this->totalTime = $TotalTime;
        $this->playTime = $PlayTime;
        
        switch($Playback) {
            case PlaybackState::PLAY:
                $this->playback = PlaybackState::PLAY_ID;
                break;
            case PlaybackState::PAUSE:
                $this->playback = PlaybackState::PAUSE_ID;
                break;
            case PlaybackState::STOP:
                $this->playback = PlaybackState::STOP_ID;
                break;
            default:
                $this->playback = PlaybackState::PLAY_ID;
        }
    }

    public function Input(){
        return $this->input;
    }

    public function Artist(){
        return $this->artist;
    }

    public function Album(){
        return $this->album;
    }

    public function Track(){
        return $this->track;
    }

    public function AlbumartUrl(){
        return $this->albumartUrl;
    }

    public function Playback(){
        return $this->playback;
    }

    public function TotalTime() {
        return $this->totalTime;
    }

    public function PlayTime() {
        return $this->playTime;
    }
}

class ResponseCodes {
    const SUCCESSFUL_REQUEST = 0;
    const INITIALIZING = 1;
    const INTERNAL_ERROR = 2;
    const INVALID_REQUEST = 3;
    const INVALID_PARAMETER = 4;
    const GUARDED = 5;
    const TIME_OUT = 6;
    const FIRMWARE_UPDATING = 99;
    const ACCESS_ERROR = 100;
    const OTHER_ERRORS = 101;
    const WRONG_USER_NAME = 102;
    const WRONG_PASSWORD = 103;
    const ACCOUNT_EXPIRED = 104;
    const ACCOUNT_DISCONNECTED = 105;
    const ACCOUNT_NUMBER_REACHED_LIMIT = 106;
    const SERVER_MAINTENANCE = 107;
    const INVALID_ACCOUNT = 108;
    const LICENSE_ERROR = 109;
    const READ_ONLY_MODE = 110;
    const MAX_STATIONS = 111;
    const ACCESS_DENIED = 112;

    public static function GetMessage($code) {
        switch ($code) {
            case self::SUCCESSFUL_REQUEST:
                return 'Successful request';
            case self::INITIALIZING:
                return 'Initializing';
            case self::INTERNAL_ERROR:
                return 'Internal Error';
            case self::INVALID_REQUEST:
                return 'Invalid Request (A method did not exist, a method wasn’t appropriate etc.)';
            case self::INVALID_PARAMETER:
                return 'Invalid Parameter (Out of range, invalid characters etc.)';
            case self::GUARDED:
                return 'Guarded (Unable to setup in current status etc.)';
            case self::TIME_OUT:
                return 'Time Out';
            case self::FIRMWARE_UPDATING:
                return 'Firmware Updating';
            case self::ACCESS_ERROR:
                return 'Access Error';
            case self::OTHER_ERRORS:
                return 'Other Errors';
            case self::WRONG_USER_NAME:
                return 'Wrong User Nam';
            case self::WRONG_PASSWORD:
                return 'Wrong Password';
            case self::ACCOUNT_EXPIRED:
                return 'Account Expired';
            case self::ACCOUNT_DISCONNECTED:
                return 'Account Disconnected/Gone Off/Shut Down';
            case self::ACCOUNT_NUMBER_REACHED_LIMIT:
                return 'Account Number Reached to the Limit';
            case self::SERVER_MAINTENANCE:
                return 'Server Maintenance';
            case self::INVALID_ACCOUNT:
                return 'Invalid Account';
            case self::LICENSE_ERROR:
                return 'License Error';
            case self::READ_ONLY_MODE:
                return 'Read Only Mode';
            case self::MAX_STATIONS:
                return 'Max Stations';
            case self::ACCESS_DENIED:
                return 'Access Denied';
            default:
                return 'Unknown error';
        }
    }
}

