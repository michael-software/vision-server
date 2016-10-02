<?php

require_once dirname(__FILE__) . '/websocketClient.php';
require_once dirname(dirname(__FILE__)) . '/config.php';

class NotificationManager {
	private $pluginName;
	
	function __construct() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f), $a);
		}
	}
	
	public function __construct1($pPluginName) {
		$this->pluginName = $pPluginName;
	}
	
	public function addNotification($pNotification, $pUser, $forceReload=false) {
		global $pluginManager;
		
		if($pNotification instanceof Notification) {
			require_once dirname(__FILE__) . '/DatabaseManager.php';
			
			$databaseManager = new DatabaseManager();
			$databaseManager->openTable('notifications', json_decode(DatabaseManager::$table5) );
			
			$title     = $pNotification->getTitle();
			$message   = $pNotification->getMessage();
			$action    = $pNotification->getAction();
			$timestamp = $pNotification->getMaxTimestamp();
			$readed    = $pNotification->getReaded();
			$icon      = $pNotification->getIcon();
			
			$insert['title'] = Array( "value"=>$title );
			$insert['text'] = Array( "value"=>$message );
			$insert['action'] = Array( "value"=>$action );
			$insert['user'] = Array( "value"=>$pUser );
			$insert['timestamp'] = Array( "value"=>$timestamp, "type"=>"integer" );
			$insert['readed'] = Array( "value"=>$readed );
			$insert['icon'] = Array( "value"=>$icon, "type"=>"text" );
			$insert['plugin'] = Array( "value"=>$pluginManager->getPluginName(), "type"=>"text" );
			
			if($pNotification->isServerMessage()) {
				$insert['server'] = Array( "value"=>"1" );
			}
			
			$databaseManager->insertValue( $insert );
			
			if($forceReload) {
				$this->forceReload();
			}
		}
	}
	
	public function addAction($pActionNotification, $pUser, $forceReload=true) {
		if($pActionNotification instanceof ActionNotification) {
			require_once dirname(__FILE__) . '/DatabaseManager.php';
			
			$databaseManager = new DatabaseManager();
			$databaseManager->openTable('notifications', json_decode(DatabaseManager::$table5) );
			
			$action    = $pActionNotification->getAction();
			$timestamp = $pActionNotification->getMaxTimestamp();
			$readed    = $pActionNotification->getReaded();
			
			if(!empty($pActionNotification->getAdressed())) {
				$insert['text'] = Array( "value"=>$pActionNotification->getAdressed() );
			} else {
				$insert['text'] = Array( "value"=>'' );
			}
			
			$insert['title'] = Array( "value"=>'' );
			$insert['action'] = Array( "value"=>$action );
			$insert['user'] = Array( "value"=>$pUser );
			$insert['timestamp'] = Array( "value"=>$timestamp, "type"=>"integer" );
			$insert['type'] = Array( "value"=>1 );
			$insert['readed'] = Array( "value"=>$readed );
			$insert['icon'] = Array( "value"=>"", "type"=>"text" );
			
			$databaseManager->insertValue( $insert );
			
			if($forceReload) {
				$this->forceReload();
			}
		}
	}
	
	public function addServerNotification($pNotification, $forceReload=true) {
		if($pNotification instanceof Notification) {
			require_once dirname(__FILE__) . '/DatabaseManager.php';
			
			$pNotification->setServerMessage(TRUE);
			
			$databaseManager = new DatabaseManager();
			$databaseManager->openTable('user_permissions', json_decode(DatabaseManager::$table3) );
			$notifications = $databaseManager->getValues( Array("server_notify"=>Array("operator"=>"=", "value"=>"1")) );
			
			if(!empty($notifications) && is_array($notifications))
			foreach($notifications as $user) {
				$userId = $user['userid'];
				$this->addNotification($pNotification, $userId, false); // reload will be called later
			}
			
			if($forceReload) {
				$this->forceReload();
			}
		}
	}
	
	public function forceReload() {
		global $conf;
		
		if(constant('WEBSOCKET') != 1) {
			$sp = websocket_open('192.168.2.108:'.$conf['ws_port']);
			websocket_write($sp, "forcereload " . $conf['server_secure_hash']);
			if(websocket_read($sp, true) == "reload all") {
				websocket_write($sp, "disconnect ");
				return true;
			} else {
				websocket_write($sp, "disconnect ");
				return false;
			}
		} else {
			global $echo;
			
			$echo->refreshNotifications();
		}
	}
}

class Notification {
	private $title;
	private $message;
	private $action;
	private $maxTimestamp;
	private $server;
	private $readed;
	
	/* Constructors */
	public function __construct() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this,$f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		}
	}
	
	private function __construct1($pMessage) {
		$this->setMessage($pMessage);
	}
	
	private function __construct2($pMesage, $pTitle) {
		$this->setMessage($pMesage);
		$this->setTitle($pTitle);
	}
	
	private function __construct3($pMesage, $pTitle, $pMaxTimestamp) {
		$this->setMessage($pMesage);
		$this->setTitle($pTitle);
		
		if(is_int($pMaxTimestamp))
			$this->setMaxTimestamp($pMaxTimestamp);
	}
	
	/* Setter */
	public function setMessage($pMessage) {
		$this->message = $pMessage;
	}
	
	public function setTitle($pTitle) {
		$this->title = $pTitle;
	}
	
	public function setMaxTimestamp($pMaxTimestamp) {
		if(is_int($pMaxTimestamp))
			$this->maxTimestamp = $pMaxTimestamp;
	}
	
	public function setServerMessage($pServerMessage=true) {
		if(is_bool($pServerMessage) === TRUE) {
			$this->server = $pServerMessage;
		} else {
			$this->server = FALSE;
		}
	}
	
	public function setIcon($base64) {
		if(is_string($base64) && strpos($base64, 'base64') !== false) {
			$this->icon = $base64;
		}
	}
	
	/* Action-Setter */
	public function setAction($pActionString) {
		$this->action = $pActionString;
	}
	
	public function setActionOpenPlugin($pPlugin, $pView = "home", $pParameter = "") {
		$this->action = "openPlugin('" . $pPlugin . "','" . $pView . "','" . $pParameter . "')";
	}
	
	public function setActionOpenMedia($pType, $pUrl) {
		$this->action = "openMedia('" . $pType . "','" . $pUrl . "')";
	}
	
	public function addReaded($authtoken) {
		$this->readed[] = $authtoken;
	}
	
	/* Getter */
	public function getMessage() {
		if(!empty($this->message)) {
			return $this->message;
		}
		
		return "Keine Nachricht";
	}
	
	public function getTitle() {
		if(!empty($this->title)) {
			return $this->title;
		} else if(empty($this->title) && !empty($this->message)) {
			return $this->title;
		}
		
		return "Benachrichtigung";
	}
	
	public function getMaxTimestamp() {
		if(!empty($this->maxTimestamp) && is_int($this->maxTimestamp)) {
			return $this->maxTimestamp;
		}
		
		return 0;
	}
	
	public function getAction() {
		if(!empty($this->action)) {
			return $this->action;
		}
		
		return "";
	}
	
	public function getReaded() {
		if(!empty($this->readed)) {
			return implode(" || ", $this->readed) . ' || ';
		}
		
		return '';
	}
	
	public function getIcon() {
		if(!empty($this->icon)) {
			return $this->icon;
		}
		
		return '';
	}
	
	public function isServerMessage() {
		return $this->server;
	}
}

class ActionNotification {
	private $action;
	private $maxTimestamp;
	private $server;
	private $readed;
	private $adressed;
	
	/* Constructors */
	public function __construct() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this,$f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		}
	}
	
	private function __construct1($pAction) {
		$this->setAction($pAction);
	}
	
	private function __construct2($pAction, $pMaxTimestamp) {
		$this->setAction($pAction);
		$this->setMaxTimestamp($pMaxTimestamp);
	}
	
	/* Setter */
	public function setMaxTimestamp($pMaxTimestamp) {
		if(is_int($pMaxTimestamp))
			$this->maxTimestamp = $pMaxTimestamp;
	}
	
	/* Action-Setter */
	public function setAction($pActionString) {
		$this->action = $pActionString;
	}
	
	public function setActionOpenPlugin($pPlugin, $pView = "home", $pParameter = "") {
		$this->action = "openPlugin('" . $pPlugin . "','" . $pView . "','" . $pParameter . "')";
	}
	
	public function addReaded($authtoken) {
		$this->readed[] = $authtoken;
	}
	
	public function setAdressed($authtoken) {
		$this->adressed = $authtoken;
	}
	
	/* Getter */
	public function getMaxTimestamp() {
		if(!empty($this->maxTimestamp) && is_int($this->maxTimestamp)) {
			return $this->maxTimestamp;
		}
		
		return 0;
	}
	
	public function getAction() {
		if(!empty($this->action)) {
			return $this->action;
		}
		
		return "";
	}
	
	public function getReaded() {
		if(!empty($this->readed)) {
			return implode(" || ", $this->readed) . ' || ';
		}
		return '';
	}
	
	public function getAdressed() {
		if(!empty($this->adressed)) {
			return $this->adressed;
		}
		return null;
	}
}

?>