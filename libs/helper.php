<?PHP

declare(strict_types=1);

class Zones {
    const MAIN = "main";
    const ZONE2 = "zone2";
    const ZONE3 = "zone3";
    const ZONE4 = "zone4";
}

class PlaybackState {
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
    const TIDAL = 'Tidal';
    const NETRADIO = 'Network Radio';
    const LINK = 'Link';
}

class PlayInfo {
    private $input;
    private $artist;
    private $album;
    private $track;
    private $albumartUrl;
    private $playback;

    public function __construct(string $Input, string $Artist, string $Album, string $Track, string $AlbumartUrl, string $Playback) {
        switch($Input) {
            case 'tidal':
                $this->input = Input::TIDAL;
                break;
            case 'net_radio':
                    $this->input = Input::NETRADIO;
                    break; 
            case 'mc_link':
                    $this->input = Input::LINK;
                    break;
            default:
                $this->input = $Input;
        }

        $this->artist = $Artist;
        $this->album = $Album;
        $this->track = $Track;
        $this->albumartUrl = $AlbumartUrl;
        
        switch($Playback) {
            case PlaybackState::Play:
                $this->playback = 1;
                break;
            case PlaybackState:PAUSE:
                $this->playback = 2;
                break;
            case PlaybackState::STOP:
                $this->playback = 3;
                break;
            default:
                $this->playback = 1;
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
