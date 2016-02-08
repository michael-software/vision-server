<?php
/*
class ArkViewer {
 	var $tshost = '127.0.0.1';
	var $tsqueryport = 27020; #Standard 10011
	var $tsquerypw = 'Mickel1997';
	var $fp;
	
 	function connect() {
		header("Content-Type: text/plain");
		
		$this->fp = fsockopen($this->tshost, $this->tsqueryport, $errno, $errstr, 30) or die("Unable to open socket: $errstr ($errno)\n");
		if(!empty($this->fp)) {
			sleep(1);
			fputs($this->fp,"enablecheats ".$this->tsquerypw."");
			sleep(1);
			//fputs($this->fp,"listplayers \n");
			echo fgets($this->fp);
		} else {
			echo "not connected";
		}
		
		//echo fgets($this->fp);
		//echo fgets($this->fp);
		
		//fputs($this->fp,"enablecheats ".$this->tsquerypw."\n");
		//echo fgets($this->fp);
		
		//fputs($this->fp,"use sid=1\n");
		//fgets($this->fp);
		
		return true;
 	}
	
	function clientList() {
		if(!empty($this->fp)) {
			fputs($this->fp,"clientlist\n");
			
			$clientList = $this->getLine();
			$statusLine = $this->getLine();
			
			return $this->parseClientList($clientList);
		}
	}
	
	function setNickname($pNickname) {
		$pNickname = $this->getTsString($pNickname);
		
		fputs($this->fp,"clientupdate client_nickname=" . $pNickname . "\n");
		fgets($this->fp);
	}
	
	function channelList() {
		if(!empty($this->fp)) {
			fputs($this->fp,"channellist\n");
			
			$clientList = $this->getLine();
			$statusLine = $this->getLine();
			
			return $clientList;
		}
	}
	
	function parseClientList($pListString) {
		$return = array();
		$list = explode('|', $pListString);
		
		for($i = 0; $i < count($list); $i++) {
			$client_info = explode(' ', $list[$i]);
			
			$clid               = explode('=', $client_info[0]);
			$cid                = explode('=', $client_info[1]);
			$client_database_id = explode('=', $client_info[2]);
			$client_nickname    = explode('=', $client_info[3]);
			$client_type        = explode('=', $client_info[4]);
			
			$return[$i]['clid']               = $clid[1];
			$return[$i]['cid']                = $cid[1];
			$return[$i]['client_database_id'] = $client_database_id[1];
			$return[$i]['client_nickname']    = str_replace('\s', ' ', $client_nickname[1]);
			$return[$i]['client_type']        = $client_type[1];
		}
		
		return $return;
	}
	
	function getLine() {
		if(!empty($this->fp))
			return fgets($this->fp);
	}
	
	function writeMessage($pTarget, $pMessage) {
		if(!empty($this->fp)) {
			$pMessage = $this->getTsString($pMessage);
			
			fputs($this->fp,"sendtextmessage targetmode=1 target=" . $pTarget. " msg=" . $pMessage . "\n");
			$this->getLine();
		}
	}
	
	function getTsString($pString) {
		$pString = str_replace(" ", "\s", $pString);
		
		return $pString;
	}
	
	function close() {
		if(!empty($this->fp)) {
			//fputs($this->fp, "quit\n");
			
			#Socket schliessen
			fclose($this->fp);
		}
	}
}*/

/**
     * Return a byte and split it out of the string
     *  - unsigned char
     *
     * @param string    $string String
     */
    function getByte(&$string)
    {
        $data = substr($string, 0, 1);

        $string = substr($string, 1);

        $data = unpack('Cvalue', $data);

        return $data['value'];
    }

    /**
     * Return an unsigned short and split it out of the string
     *  - unsigned short (16 bit, big endian byte order)
     *
     * @param string    $string String
     */
    function getShortUnsigned(&$string)
    {
        $data = substr($string, 0, 2);

        $string = substr($string, 2);

        $data = unpack('nvalue', $data);

        return $data['value'];
    }

    /**
     * Return a signed short and split it out of the string
     *  - signed short (16 bit, machine byte order)
     *
     * @param string    $string String
     */
    function getShortSigned(&$string)
    {
        $data = substr($string, 0, 2);

        $string = substr($string, 2);

        $data = unpack('svalue', $data);

        return $data['value'];
    }

    /**
     * Return a long and split it out of the string
     *  - unsigned long (32 bit, little endian byte order)
     *
     * @param string    $string String
     */
    function getLong(&$string)
    {
        $data = substr($string, 0, 4);

        $string = substr($string, 4);

        $data = unpack('Vvalue', $data);

        return $data['value'];
    }

    /**
     * Return a float and split it out of the string
     *
     * @param string    $string String
     */
    function getFloat(&$string)
    {
        $data = substr($string, 0, 4);

        $string = substr($string, 4);

        $array = unpack("fvalue", $data);

        return $array['value'];
    }

    /**
     * Return a string and split it out of the string
     *
     * @param string    $string String
     */
    function getString(&$string)
    {
        $data = "";

        $byte = substr($string, 0, 1);

        $string = substr($string, 1);

        while (ord($byte) != "0")
        {
                $data .= $byte;
                $byte = substr($string, 0, 1);
                $string = substr($string, 1);
        }

        return $data;
    }
	/*
// Constant
define('PACKET_SIZE', '1400');
define('SERVERQUERY_INFO', "\xFF\xFF\xFF\xFFTSource Engine Query");
define ('REPLY_INFO', "\x49");
define('SERVERQUERY_GETCHALLENGE', "\xFF\xFF\xFF\xFF\x57");
define ('REPLY_GETCHALLENGE', "\x41");
define('SERVERDATA_AUTH', 3) ;
define ('SERVERDATA_EXECCOMMAND', 2) ;

// Ip address and port
$_ip = '192.168.2.108' ; // server ip
$_port = '27020'; // server port
$_password = 'Mickel1997' ; // your rcon password
$s2 = '';
$command = 'listplayers'; // the rcon command! Put the command you want here
$requestId = 1;

// open connection with server
$socket = fsockopen ('tcp://'.$_ip, $_port, $errno, $errstr, 30) ;

// Send auth packet

// Construct packet
$data = pack("VV", $requestId, SERVERDATA_AUTH).$_password.chr(0).$s2.chr(0);

// Prefix the packet by its size
$data = pack("V",strlen($data)).$data;

// Send packet
fwrite ($socket, $data, strlen($data)) ;

$requestId++ ;

// Check if auth is successful
$junk = fread ($socket, PACKET_SIZE) ;

$string = fread ($socket, PACKET_SIZE) ;
$size = getLong($string) ;
$id = getLong ($string) ;

if ($id == -1)
{
  // Error
  die ('Auth failed: bad password !') ;
}

// Sending the command and getting the answer
$data = pack ("VV", $requestId, SERVERDATA_EXECCOMMAND).$command.chr(0).$s2.chr(0) ;

// Prefix the packet by its size
$data = pack ("V", strlen ($data)).$data ;

// Send packet
fwrite ($socket, $data, strlen($data)) ;

$requestId++ ;

// Read response
$i = 0 ;
$text = '' ;
while ($string = fread($socket, 4))
{
  $info[$i]['size'] = getLong($string) ;
  $string = fread($socket, $info[$i]['size']) ;
  $info[$i]['id'] = getLong ($string) ;
  $info[$i]['type'] = getLong ($string) ;
  $info[$i]['s1'] = getString ($string) ;
  $info[$i]['s2'] = getString ($string) ;
  $text .= $info[$i]['s1'] ;
  $i++ ;
}*/
?>