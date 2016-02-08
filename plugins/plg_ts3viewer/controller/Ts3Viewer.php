<?php

class Ts3Viewer {
 	var $tshost = '127.0.0.1';
	var $tsqueryport = 10011; #Standard 10011
	var $tsquerypw = 'wRVu7Qyf';
	var $fp;
	
 	function connect() {
		header("Content-Type: text/plain");
		
		$this->fp = fsockopen($this->tshost, $this->tsqueryport, $errno, $errstr, 5);
		fgets($this->fp);
		fgets($this->fp);
		
		fputs($this->fp,"login client_login_name=serveradmin client_login_password=".$this->tsquerypw."\n");
		fgets($this->fp);
		
		fputs($this->fp,"use sid=1\n");
		fgets($this->fp);
		
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
	
	function kick($pTarget, $pReason, $serverKick = false) {
		if(!empty($this->fp)) {
			$pReason = $this->getTsString($pReason);
			
			if($serverKick) {
				fputs($this->fp,"clientkick clid=" . $pTarget. " reasonid=5 reasonmsg=" . $pReason . "\n");
			} else {
				fputs($this->fp,"clientkick clid=" . $pTarget. " reasonid=4 reasonmsg=" . $pReason . "\n");
			}
			
			$this->getLine();
		}
	}
	
	function getTsString($pString) {
		$pString = str_replace(" ", "\s", $pString);
		
		return $pString;
	}
	
	function close() {
		if(!empty($this->fp)) {
			fputs($this->fp, "quit\n");
			
			#Socket schliessen
			fclose($this->fp);
		}
	}
}
?>