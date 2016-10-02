<?php

class Email_reader {

	// imap server connection
	public $conn;

	// inbox storage and inbox message count
	private $inbox;
	private $msg_cnt;

	// email login credentials
	private $server = 'web4brauns.de';
	private $user   = 'web1254p2';
	private $pass   = '';
	private $port   = 143; // adjust according to server settings
	
	private $connected = false;

	// connect to the server and get the inbox emails
	function __construct() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f), $a);
		}
		
		$this->connect();
		$this->inbox();
	}
	
	private function __construct3($server, $username, $password) {
		$this->__construct4($server, $username, $password, 143);
	}
	
	private function __construct4($server, $username, $password, $port) {
		$this->server = $server;
		$this->user   = $username;
		$this->pass   = $password;
		$this->port   = $port;
	}

	// close the server connection
	function close() {
		$this->inbox = array();
		$this->msg_cnt = 0;

		imap_close($this->conn);
	}

	// open the server connection
	// the imap_open function parameters will need to be changed for the particular server
	// these are laid out to connect to a Dreamhost IMAP server
	function connect() {
		$this->conn = @imap_open('{'.$this->server.'/notls}', $this->user, $this->pass, null, 1);
		
		// Supress alerts
		imap_alerts();
		$errors = imap_errors();
		
		$searchword = 'AUTHENTICATIONFAILED';
		$matches = array_filter($errors, function($var) use ($searchword) { return preg_match("/\b$searchword\b/i", $var); });
		
		if(is_bool($this->conn) || !empty($matches)) {
			$this->connected = false;
			return false;
		} else {
			$this->connected = true;
			return true;
		}
	}

	// move the message to a new folder
	function move($msg_index, $folder='INBOX.Processed') {
		// move on server
		imap_mail_move($this->conn, $msg_index, $folder);
		imap_expunge($this->conn);

		// re-read the inbox
		$this->inbox();
	}

	// get a specific message (1 = first email, 2 = second email, etc.)
	function get($msg_index=NULL) {
		if (count($this->inbox) <= 0) {
			return array();
		}
		elseif ( ! is_null($msg_index) && isset($this->inbox[$msg_index])) {
			return $this->inbox[$msg_index];
		}

		return $this->inbox[0];
	}

	// read the inbox
	function inbox($expanded = false) {
		if(is_bool($this->conn)) {
			return null;
		}
		
		$this->msg_cnt = @imap_num_msg($this->conn);

		@imap_headers($this->conn);

		$in = array();
		for($i = 1; $i <= $this->msg_cnt; $i++) {
			if($expanded === TRUE) {
				$in[] = array(
					'index'     => $i,
					'header'    => imap_headerinfo($this->conn, $i),
					'body'      => imap_body($this->conn, $i),
					'structure' => imap_fetchstructure($this->conn, $i)
				);
			} else {
				$in[] = array(
					'index'     => $i,
					'header'    => imap_headerinfo($this->conn, $i),
				);
			}
		}

		$this->inbox = $in;
		
		return $this->inbox;
	}
	
	function headerInfo($msg_index=NULL) {
		if (count($this->inbox) <= 0) {
			return array();
		}
		elseif ( ! is_null($msg_index) && isset($this->inbox[$msg_index])) {
			return $this->inbox[$msg_index]['header'];
		}

		return $this->inbox[0]['header'];
	}
	
	function headers() {
		if(!empty($this->inbox) && is_array($this->inbox)) {
			$return = array();
			
			foreach($this->inbox as $id=>$in) {
				$array = $in['header'];
				$array->id = $in['index'];
				$return[] = $array;
			}
			
			return $return;
		}
		
		return array();
	}
	
	function getBody($msg_index=0) {
		
		$body = null;
		
		$st = imap_fetchstructure($this->conn, $msg_index);
		if (!empty($st->parts)) {
		    for ($i = 0, $j = count($st->parts); $i < $j; $i++) {
		        $part = $st->parts[$i];
				
		        if ($part->subtype == 'HTML'
		        	|| (empty($body) && $part->subtype == 'PLAIN')) {
		        	$body = imap_fetchbody($this->conn, $msg_index, $i+1);
					$body = $this->decode($body, $part->encoding);
		        }
				
				if ($part->subtype == 'ALTERNATIVE') {
					if (!empty($part->parts)) {
			    		for ($k = 0, $l = count($part->parts); $k < $l; $k++) {
			    			$alternativePart = $part->parts[$k];
							
			    			if ($alternativePart->subtype == 'HTML'
					        	|| (empty($body) && $alternativePart->subtype == 'PLAIN')) {
					        	$body = imap_fetchbody($this->conn, $msg_index, ($i+1) . '.' . ($k+1));
								$body = $this->decode($body, $alternativePart->encoding);
					        }
						}
					}
		        }
		     }
		} else {
			$body = imap_body($this->conn, $msg_index);
			$body = $this->decode($body, $st->encoding);
		}
		
		return $body;
	}
	
	function decode($body, $encoding) {
		if($encoding == ENCBASE64) {
			$body = imap_base64($body);
		} else if($encoding == ENC8BIT) {
			$body = quoted_printable_decode(imap_8bit($body));
		} else if($encoding == ENCQUOTEDPRINTABLE) {
			$body = quoted_printable_decode($body);
		} else if($encoding == ENCBINARY) {
			$body = imap_base64(imap_binary($body));
		} else if($encoding == ENC7BIT) {
			$body = mb_convert_encoding($body, "UTF-8", "auto");
		} else {
			$body = imap_qprint($body);
		}
		
		if(mb_detect_encoding($body, 'UTF-8', true) != 'UTF-8') {
			$body = utf8_encode($body);
		}
		
		if(strpos($body,"<br>") === false && strpos($body,"<br />") === false && strpos($body,"<br/>") === false) {
			$body = nl2br($body);
		}
		
		return $body;
	}

	function isConnected() {
		return $this->connected;
	}
}

?>