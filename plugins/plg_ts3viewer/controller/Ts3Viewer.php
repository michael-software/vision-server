<?php

class Ts3Viewer {
 	var $tshost = '127.0.0.1';
	var $tsqueryport = 10011; #Standard 10011
	var $tsquerypw = 'wRVu7Qyf';
	var $fp;
	
 	function connect() {
		$this->fp = @fsockopen($this->tshost, $this->tsqueryport, $errno, $errstr, 5);
		
		if(!empty($this->fp)) {
			fgets($this->fp);
			fgets($this->fp);
			
			fputs($this->fp,"login client_login_name=serveradmin client_login_password=".$this->tsquerypw."\n");
			fgets($this->fp);
			
			fputs($this->fp,"use sid=1\n");
			fgets($this->fp);
			
			return true;
		}
		
		return false;
 	}
	
	function setNickname($pNickname) {
		$pNickname = $this->getTsString($pNickname);
		
		fputs($this->fp,"clientupdate client_nickname=" . $pNickname . "\n");
		fgets($this->fp);
	}
	
	function clientList() {
		if(!empty($this->fp)) {
			fputs($this->fp,"clientlist\n");
			
			$clientList = $this->getLine();
			$statusLine = $this->getLine();
			
			return $this->parseClientList($clientList);
		}
	}
	
	function clientDbList() {
		if(!empty($this->fp)) {
			fputs($this->fp,"clientdblist\n");
			
			$clientList = $this->getLine();
			$statusLine = $this->getLine();
			
			return $this->parseInfo($clientList);
		}
	}
	
	function customInfo($client_database_id) {
		if(!empty($this->fp)) {
			fputs($this->fp,"custominfo cldbid=" . $client_database_id . "\n");
			
			$clientList = $this->getLine();
			
			echo "TEST" . PHP_EOL;
			
			return $clientList;
			
			if(!$this->isError($clientList)) {
				$statusLine = $this->getLine();
				
				return $this->parseInfo($clientList);
			}
		}
	}
	
	function clientListInfoByDatabaseId() {
		if(!empty($this->fp)) {
			$clients = $this->clientList();
			
			$list = null;
			
			if(!empty($clients) && is_array($clients))
			foreach($clients as $client) {
				$client_database_id = $client['client_database_id'];
				$client_id = $client['clid'];
				$client_info = $this->clientInfo($client_id);
				
				if(empty($list[$client_database_id]) || $list[$client_database_id]['client_lastconnected'] > $client_info['client_lastconnected']) {
					$list[$client_database_id] = $client_info;
				}
			}
			
			return $list;
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
			$return[$i]['client_type']        = str_replace('\s', ' ', $client_type[1]);
		}
		
		return $return;
	}
	
	function channelList($sorted = false) {
		if(!empty($this->fp)) {
			fputs($this->fp,"channellist\n");
			
			$clientList = $this->getLine();
			$statusLine = $this->getLine();
			
			return $this->parseChannelList($clientList, $sorted);
		}
	}
	
	function parseChannelList($pListString, $pSorted) {
		$return = array();
		$list = explode('|', $pListString);
		
		for($i = 0; $i < count($list); $i++) {
			$client_info = explode(' ', $list[$i]);
			
			$cid                = explode('=', $client_info[0]);
			$pid                = explode('=', $client_info[1]);
			$channel_order      = explode('=', $client_info[2]);
			$channel_name       = $this->decode(explode('=', $client_info[3]));
			$total_clients      = explode('=', $client_info[4]);
			
			$channel_needed_subscribe_power = explode('=', $client_info[5]);
			
			if($pSorted) {
				$cid = $cid[1];
				$return[$cid]['pid']                = $pid[1];
				$return[$cid]['channel_order']      = $channel_order[1];
				$return[$cid]['channel_name']       = str_replace('\s', ' ', $channel_name[1]);
				$return[$cid]['total_clients']      = $total_clients[1];
				
				$return[$cid]['channel_needed_subscribe_power'] = $channel_needed_subscribe_power[1];
			} else {
				$return[$i]['cid']                = $cid[1];
				$return[$i]['pid']                = $pid[1];
				$return[$i]['channel_order']      = $channel_order[1];
				$return[$i]['channel_name']       = str_replace('\s', ' ', $channel_name[1]);
				$return[$i]['total_clients']      = $total_clients[1];
				
				$return[$i]['channel_needed_subscribe_power'] = $channel_needed_subscribe_power[1];
			}
		}
		
		return $return;
	}
	
	function serverInfo() {
		if(!empty($this->fp)) {
			fputs($this->fp,"serverinfo\n");
			
			$clientList = $this->getLine();
			$statusLine = $this->getLine();
			
			return $this->parseSingleInfo($clientList);
		}
	}
	
	function serverGroupsByClientId($client_database_id) {
		if(!empty($this->fp)) {
			fputs($this->fp,"servergroupsbyclientid cldbid=" . $client_database_id . "\n");
			
			$clientList = $this->getLine();
			if(!$this->isError($clientList)) {
				$statusLine = $this->getLine();
				
				return $this->parseInfo($clientList);
			}
		}
	}
	
	function parseInfo($pListString) {
		$infos = explode('|', $pListString);
		
		$array = null;
		foreach ($infos as $info) {
			$array[] = $this->parseSingleInfo($info);
		}
		
		return $array;
	}
	
	function parseSingleInfo($pListString) {
		$infos = explode(' ', $pListString);
		
		$array = null;
		foreach($infos as $info) {
			$nameValue = explode('=', $info);
			
			$name  = $nameValue[0];
			$name  = str_replace(" ", "", $name);
			$name  = str_replace(array("\r\n","\r","\n"), "", $name);
			
			unset($nameValue[0]);
			$value = implode('=', $nameValue);
			
			$array[$name] = $this->decode($value);
		}
		
		return $array;
	}
	
	function channelClientList($cid) {
		$clients = $this->clientList();
		
		$array = null;
		
		if(!empty($clients))
		foreach($clients as $client) {
			if($client['cid'] == $cid) {
				$array[] = $client;
			}
		}
		
		return $array;
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
	
	function writeBroadcast($pMessage) {
		if(!empty($this->fp)) {
			$pMessage = $this->getTsString($pMessage);
			
			fputs($this->fp,"sendtextmessage targetmode=3 target=1 msg=" . $pMessage . "\n");
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
	
	function moveClient($clid, $cid) {
		if(!empty($this->fp)) {
			fputs($this->fp,"clientmove clid=" . $clid. " cid=" . $cid . "\n");
			$this->getLine();
		}
	}
	
	function clientInfo($clid) {
		if(!empty($this->fp)) {
			fputs($this->fp,"clientinfo clid=" . $clid. "\n");
			
			$clientinfo = $this->getLine();
			
			if(!$this->isError($clientinfo)) {
				$statusLine = $this->getLine();
				
				return $this->parseSingleInfo($clientinfo);
			}
		}
		
		return null;
	}
	
	function isError($call) {
		if(empty($call)) {
			return false;
		} else if(strpos($call, "error") !== FALSE && strpos($call, "id=0") !== FALSE) {
			return true;
		} else if(strpos($call, "error") === FALSE) {
			return false;
		}
		
		return true;
	}
	
	function parseClientInfo($pListString) {
		$return = array();
		$list = explode('|', $pListString);
		
		$returnArray = null;
		for($i = 0; $i < count($list); $i++) {
			$infos = explode(' ', $list[$i]);
			
			$array = null;
			foreach($infos as $info) {
				$pair = explode('=', $info);
				
				$name = $pair[0];
				$value = $pair[1];
				
				$array[$name] = $value;
			}
			
			if(!empty($array))
			$returnArray[] = $array;
		}
		
		return $returnArray;
	}
	
	function getTsString($pString) {
		$pString = str_replace(" ", "\s", $pString);
		$pString = str_replace("/", "\/", $pString);
		
		return $pString;
	}
	
	function decode($pString) {
		$pString = str_replace("\s", " ", $pString);
		$pString = str_replace("\/", "/", $pString);
		
		return $pString;
	}
	
	function getFileList($cid, $path='/') {
		if(!empty($this->fp)) {
			$path = $this->getTsString($path);
			fputs($this->fp,"ftgetfilelist cid=" . $cid . " cpw= path=" . $path . "\n");
			
			$clientList = $this->getLine();
			$statusLine = $this->getLine();
			
			return $this->parseInfo($clientList);
		}
	}
	
	function initDownload($cid, $path='/') {
		if(!empty($this->fp)) {
			$path = $this->getTsString($path);
			fputs($this->fp,"ftinitdownload clientftfid=1 name=" . $path . " cid=" . $cid . " cpw= seekpos=0\n");
			
			$clientList = $this->getLine();
			$statusLine = $this->getLine();
			
			return $this->parseInfo($clientList);
		}
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