<?php

//#!/usr/bin/env php

define('WEBSOCKET', '1');

require_once(dirname(__FILE__) . '/includes/LoginManager.php');
require_once(dirname(__FILE__) . '/includes/DatabaseManager.php');
require_once(dirname(__FILE__) . '/includes/PluginManager.php');
require_once(dirname(__FILE__) . '/includes/AsyncManager.php');
require_once(dirname(__FILE__) . '/websockets/websockets.php');
require_once(dirname(__FILE__) . '/config.php');

$pluginManager = new PluginManager();
$pluginManager->enableNotifications(true);

require_once $pluginManager->getController('plg_serversettings', 'servertools');

function shutdown()
{
    exec('sudo /var/www/sh/wsrestart.sh');
    
    echo 'Script executed with success', PHP_EOL;
}

function myErrorHandler($fehlercode, $fehlertext, $fehlerdatei, $fehlerzeile) {
	echo $fehlertext . ' (' . $fehlercode . ') in ' . $fehlerdatei . ' on line ' . $fehlerzeile;
	
	return true;
}

function triggerhourly() {
	global $pluginManager;
	$pluginManager->triggerHourly();
}

function triggerminutely() {
	global $pluginManager;
	$pluginManager->triggerMinutely();
}

register_shutdown_function('shutdown');
$alter_error_handler = set_error_handler("myErrorHandler");

class echoServer extends WebSocketServer {
	private $time;
	private $servertime;
	private $databaseManager;
	
	public function __construct($ip, $port) {
		parent::__construct($ip, $port);
		
		$this->databaseManager = new DatabaseManager();
		$this->databaseManager->openTable('notifications', json_decode(DatabaseManager::$table5) );
	}
	
	protected function process ($user, $message) {
		global $loginManager;
		global $pluginManager;
		global $ws_interval;
		global $server_interval;
		global $server_secure_hash;
		
		$parts = explode(" ", $message);
		
		if(strtoupper($parts[0]) === strtoupper('login') && !empty($parts[1])) {
			if($loginManager->loginUserByJwt($parts[1])) {
				$user->userid = $loginManager->getId();
				$user->allowedServerNotify = $loginManager->isAllowed(LoginManager::SERVER_NOTIFY);
				$user->group = $loginManager->getGroup();
				$user->authtoken = $parts[1];
				
				$authtoken = $this->getConnectedAuthtoken($user);
				if(in_array($parts[1], $authtoken)) {
					echo date("H:i:s") . " :: Two Logins: " . $parts[1];
					
					if(empty($parts[2]) || strtoupper($parts[2]) != 'DESKTOP') {
						$this->disconnectAllByAuthtoken($parts[1], $user);
					} else {
						$this->send($user, 'double login');
						$user->desktop = true;
					}
				}
				
				$this->send($user, 'login ok');
				echo date("H:i:s") . '>> ' . 'login successful: ' . $loginManager->getUsername() . PHP_EOL;
			} else {
				$this->send($user, 'login bad');
				echo date("H:i:s") . '>> ' . 'login failed: ' . $parts[1] . PHP_EOL;
			}
		} else if(strtoupper($parts[0]) === strtoupper('userinfo')) {
			$this->send($user, $user->userid . ' ' . $user->group . ' ' . $user->allowedServerNotify);
		} else if(strtoupper($parts[0]) === strtoupper('userlist')){
			$users = $this->getUsers();
			
			if(!empty($users))
			foreach($users as $singleUser) {
				$userid = $singleUser->userid;
				$authtoken = $singleUser->authtoken;
				
				if(!empty($userid) && !empty($authtoken)) {
					$this->send($user, $userid . ': ' . $authtoken);
				} else if(!empty($userid)) {
					$this->send($user, $userid);
				} else if(!empty($authtoken)) {
					$this->send($user, $authtoken);
				} else {
					$this->send($user, 'Anonymous (' . $user->ip . ')');
				}
			}
		} else if(strtoupper($parts[0]) === strtoupper('forcereload') && !empty($parts[1])) {
			if($parts[1] == $server_secure_hash) {
				if($server_interval > $ws_interval) {
					$this->time = time() - $server_interval;
				} else {
					$this->time = time() - $ws_interval;
				}
				
				$this->send($user, "reload all");
			} else {
				$this->send($user, "bad login");
			}
		} else if(strtoupper($parts[0]) === strtoupper('triggerhourly')) {
			triggerhourly();
		} else if(strtoupper($parts[0]) === strtoupper('triggerminutely')) {
			triggerminutely();
		} else if(strtoupper($parts[0]) === strtoupper('disconnect')) {
			$this->disconnect($user->socket);
		} else if(strtoupper($parts[0]) === strtoupper('exit')) {
			exit(0);
		} else {
			$json = json_decode($message);
			
			if(json_last_error() == JSON_ERROR_NONE) {
				if(!empty($json->plugin) && !empty($json->action)) {
					$value = 0;
					if(!empty($json->value)) {
						$value = $json->value;
					}
					
					$asyncManager = new AsyncManager($user->userid, $json->plugin);
					$asyncManager->triggerAction($json->action, $json->value);
					
					echo date("H:i:s") . ':: ' . $json->plugin . ' : ' . $json->action . ' - ' . $json->value . PHP_EOL;
				}
			}
		}
	}
	
	protected function connected ($user) {
		// Do nothing: This is just an echo server, there's no need to track the user.
		// However, if we did care about the users, we would probably have a cookie to
		// parse at this step, would be looking them up in permanent storage, etc.
	}
	
	protected function closed ($user) {
		// Do nothing: This is where cleanup would go, in case the user had any sort of
		// open files or other objects associated with them.  This runs after the socket
		// has been closed, so there is no need to clean up the socket itself here.
	}
	
	protected function tick () {
		global $ws_interval;
		global $server_interval;
		global $pluginManager;
		global $echo;
		
		if($this->time < time() - $ws_interval) {
			$this->refreshNotifications();
		}

		$time = time();

		if($this->servertime <= $time - $server_interval) {
			$hour   = date("G", $time);
			$minute = date("i", $time);
			$second = date("s", $time);
			
			if($second == '0') {
				/*
				if( ($hour == '1' || $hour == '2' || $hour == '3' || $hour == '4') && $minute == '0' ) {
					$notification = new Notification("Der Server wird in ca. 15 Minuten heruntergefahren.", "Serverbenachrichtigung", $time + 900);
					$notification->setActionOpenPlugin("plg_serversettings");
					$notification->setServerMessage();
					
					broadcast($notification, FALSE);
					
					$this->refreshNotifications();
					
					exec('sudo /var/www/sh/shutdown.sh 15');
				}
				*/
				if($minute == '0') {
					$pluginManager->triggerHourly();
				}
				
				if($server_interval >= 60) {
					$pluginManager->triggerMinutely();
				}
				
				$this->servertime = $time;
			}
		}
	}
	
	public function refreshNotifications() {
		global $loginManager;
		global $pluginManager;
		
		$users = $this->getUsers();
		
		if(!empty($users))
		foreach($users as $singleUser) {
			$authtoken = $singleUser->authtoken;
			if(!empty($authtoken) && !$loginManager->loginUserByToken($authtoken)) {
				$socket = $singleUser->socket;
				$this->disconnect($socket);
				
				echo date("H:i:s") . " :: Authtoken abgelaufen: " . $authtoken . PHP_EOL;
			}
		}
		
		$values = $this->databaseManager->getValues();
		
		if(!empty($values))
		foreach($values as $value) {
			if(empty($value['type']) || $value['type'] == "0") {
				$userid = $value['user'];
				$users = $this->getUsersById($userid);
				
				$notification = null;
				if(!empty($value['title'])) {
					$notification['title'] = $value['title'];
				} else { $notification['title'] = ''; }
				
				if(!empty($value['text'])) {
					$notification['text'] = $value['text'];
				} else { $notification['text'] = ''; }
				
				if(!empty($value['action'])) {
					$notification['action'] = $value['action'];
				} else { $notification['action'] = ''; }
				
				if(!empty($value['icon'])) {
					$notification['icon'] = $value['icon'];
				} else { $notification['icon'] = ''; }
				
				if(!empty($value['plugin'])) {
					$notification['plugin'] = $pluginManager->getPluginName($value['plugin']);
				}
				
				if(!empty($value['server'])) {
					$notification['server'] = '1';
				}
				
				$notification['type'] = "notification";
				
				$id = $value['id'];
				
				if(!empty($users))
				foreach($users as $singleUser) {
					if(!empty($value['server']) && !$singleUser->allowedServerNotify) {
						continue;
					}
					
					$authtoken = $singleUser->authtoken;
					
					if(!empty($value['timestamp']) && $value['timestamp'] < time()) {
						
						$this->databaseManager->remove( Array("id"=>Array("operator"=>"=", "value"=>$id, "type"=>"i")) );
						
						echo date("H:i:s") . " :: Benachrichtigung " . $id . "(" . $value['title'] . ") wurde gelöscht." . PHP_EOL;
					} else if(empty($value['readed']) || strpos($value['readed'], $authtoken) === false) {
						$value['readed'] .= $authtoken . ' || ';
						
						$this->send($singleUser, json_encode($notification));
						
						echo date("H:i:s") . " :: sended message to " . $singleUser->authtoken . PHP_EOL;
					}
				}
				
				$this->databaseManager->setValue(Array("readed"=>Array("value"=>$value['readed'])), Array("id"=>Array("operator"=>"=", "value"=>$id, "type"=>"i")));
			} else if($value['type'] == "1") {
				$userid = $value['user'];
				$users = $this->getUsersById($userid);
				
				$notification = null;
				if(!empty($value['action'])) {
					$notification['action'] = $value['action'];
				}
				
				if(!empty($users))
				foreach($users as $singleUser) {
					if(!empty($value['server']) && !$singleUser->allowedServerNotify) {
						continue;
					}
					
					$authtoken = $singleUser->authtoken;
					
					if(!empty($value['timestamp']) && $value['timestamp'] < time()) {
						$id = $value['id'];
						
						$this->databaseManager->remove( Array("id"=>Array("operator"=>"=", "value"=>$id, "type"=>"i")) );
						
						echo "Benachrichtigung " . $id . "(" . $value['title'] . ") wurde gelöscht." . PHP_EOL;
					} else if(empty($value['readed']) || strpos($value['readed'], $authtoken) === false) {
						if(!empty($value['text']) && $authtoken != $value['text']) {
							continue;
						}
						
						if(!empty($notification)) {
							$notification['type'] = "action";
							$this->send($singleUser, json_encode($notification));
						}
						
						if(!empty($value['text']) && $authtoken == $value['text']) {
							$id = $value['id'];
							$this->databaseManager->remove(array("id"=>array("value"=>$id,"type"=>'i')));
						} else {
							$readed = $value['readed'] . $authtoken . ' || ';
							$id = $value['id'];
							$this->databaseManager->setValue(Array("readed"=>Array("value"=>$readed)), Array("id"=>Array("operator"=>"=", "value"=>$id, "type"=>"i")));
						}
					}
				}
			}
		}
			
		$this->time = time();
	}

	private function getConnectedAuthtoken($user) {
		$users = $this->getUsers();
		$array = array();
		
		if(!empty($users))
		foreach($users as $singleUser) {
			if($singleUser->id != $user->id)
			$array[] = $singleUser->authtoken;
		}
		
		return $array;
	}
	
	private function disconnectAllByAuthtoken($authtoken, $user) {
		$users = $this->getUsers();
		
		if(!empty($users))
		foreach($users as $singleUser) {
			if($singleUser->authtoken == $authtoken && $singleUser->id != $user->id) {
				$this->disconnect($singleUser->socket);
			}
		}
	}
}

if(!empty($conf['ws_enabled']) && is_bool($conf['ws_enabled']) && $conf['ws_enabled']
	&& !empty($conf['ws_port']) && is_numeric($conf['ws_port'])) {
	$echo = new echoServer("192.168.2.107", $conf['ws_port']);
	
	try {
		$echo->run();
	}
	catch (Exception $e) {
		$echo->stdout($e->getMessage());
	}
}
?>