<?php

require 'includes/PluginManager.php';
require 'includes/LoginManager.php';
require_once 'includes/FileManager.php';
require_once dirname(__FILE__) . '/config.php';

if(!empty($_GET['show'])) {
	if($_GET['show'] == 'plugins') {
		$pluginManager = new PluginManager();
		echo $pluginManager->getPlugins();
	}
}

if(!empty($_GET['plugin']) AND !empty($_GET['get']) AND $_GET['get'] == 'api') {
	if(!empty($_GET['page'])) {
		$page = $_GET['page'];
	} else {
		$page = 'default';
	}
	
	if(!empty($_GET['cmd'])) {
		$cmd = $_GET['cmd'];
	} else {
		$cmd = '';
	}
	
	if(!$loginManager->getShareManager()->isShared()) {
		$pluginManager = new PluginManager($_GET['plugin']);
		$pluginManager->getApi($page);
	}
}

if(!empty($_GET['plugin']) AND !empty($_GET['get']) AND $_GET['get'] == 'view') {
	if(!empty($_GET['page'])) {
		$page = $_GET['page'];
	} else {
		$page = 'home';
	}
	
	if(!empty($_GET['cmd'])) {
		$cmd = $_GET['cmd'];
	} else {
		$cmd = '';
	}
	
	if(empty($loginManager->getShareManager()) || $loginManager->getShareManager()->isAllowed($_GET['plugin'], $page, $cmd)) {
		ob_start();
		$pluginManager = new PluginManager($_GET['plugin']);
		$pluginManager->getView($page);
		$out = ob_get_contents(); 
		ob_end_clean();
		
		$out = str_replace("\r\n", "", $out);
		$out = str_replace("\t", "", $out);
		$out = str_replace(" :", ":", $out);
		$out = str_replace(": ", ":", $out);
		$out = str_replace(" ,", ",", $out);
		$out = str_replace(", ", ",", $out);
		$out = str_replace(" }", "}", $out);
		$out = str_replace("} ", "}", $out);
		$out = str_replace(" {", "{", $out);
		$out = str_replace("{ ", "{", $out);
		
		echo $out;
	}
	
	if(!$loginManager->getShareManager()->isShared()) {
		$seamless = $loginManager->getUserPreference("seamless");
		
		if(!empty($seamless) && strtoupper($seamless) == "TRUE") {
			$input['name'] = $_GET['plugin'];
			$input['page'] = $page;
			$input['command'] = $cmd;
			$input = json_encode($input);
			
			$loginManager->setUserPreference("seamless-current", $input);
		}
	}
}

if(!empty($_GET['get']) && $_GET['get'] == 'seamless') {
	if(!$loginManager->getShareManager()->isShared()) {
		$seamless = $loginManager->getUserPreference("seamless");
		
		if(!empty($seamless) && strtoupper($seamless) == "TRUE") {
			$seamlessString = $loginManager->getUserPreference("seamless-current");
			
			if(!empty($seamlessString)) {
				die('{"seamless":' . $seamlessString . '}');
			}
		}
	}
	
	die('{}');
}

if(!empty($_GET['action']) AND $_GET['action'] == "login") {
	$privateKey = "1234567891234567";
	$iv = "1234567891234567";
	
	if(!empty($_POST['key']) AND !empty($_POST['iv']) AND !empty($_POST['username']) AND !empty($_POST['password'])) {
		$key = $_POST['key'];
		$ivGet = $_POST['iv'];
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		$decryptedKey = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $privateKey, base64_decode($key), MCRYPT_MODE_CBC, $iv);
		$decryptedKey = trim($decryptedKey);
		
		$decryptedIv = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $decryptedKey, base64_decode($ivGet), MCRYPT_MODE_CBC, $iv);
		$decryptedIv = trim($decryptedIv);
		
		$userName = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $decryptedKey, base64_decode($username), MCRYPT_MODE_CBC, $decryptedIv);
		$userName = trim($userName);
		
		$password = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $decryptedKey, base64_decode($password), MCRYPT_MODE_CBC, $decryptedIv);
		$password = trim($password);
		
		$securityToken = $loginManager->loginUserByPassword($userName, $password, 'android');
		if(!empty($securityToken)) {
			$pluginManager = new PluginManager();
			$pluginManager->enableNotifications(TRUE);
			
			$notificationManager = $pluginManager->getNotificationManager();
			$notification = new Notification("Sie haben sich mit ihrem Benutzerkonto auf einem neuen Endgerät angemeldet.", "Neue Anmeldung", time()+86400);
			$notification->addReaded($securityToken);
			$notification->setActionOpenPlugin("plg_user");
			$notification->setIcon(FileManager::getImageHashByPath('images/user-add.png'));
			$notificationManager->addNotification($notification, $loginManager->getId());
			
			
			$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $decryptedKey, $securityToken, MCRYPT_MODE_CBC, $decryptedIv);
			$encryptedStatus = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $decryptedKey, "login", MCRYPT_MODE_CBC, $decryptedIv);
			
			$output['status'] = base64_encode($encryptedStatus);
			//$output['key'] = $decryptedKey;
			//$output['iv'] = $decryptedIv;
			$output['username'] = $userName;
			//$output['password'] = $password;
			$output['authtoken'] = base64_encode($encrypted);
			$output['mainplugins'] = $pluginManager->getMainPlugins();
			
			$fgcolor = $loginManager->getUserPreference("fgcolor", null);
			if(empty($fgcolor)) $fgcolor = $conf['fgcolor'];
			$output['fgcolor'] = $fgcolor;
			
			$bgcolor = $loginManager->getUserPreference("bgcolor", null);
			if(empty($bgcolor)) $bgcolor = $conf['bgcolor'];
			$output['bgcolor'] = $bgcolor;
			
			echo json_encode($output);
		}
	} else if(!empty($_POST['username']) AND !empty($_POST['password'])) {
		$authtoken = $loginManager->loginUserByPassword($_POST['username'], $_POST['password'], 'Webinterface');
		
		if(!empty($authtoken)) {
			$pluginManager = new PluginManager();
			$pluginManager->enableNotifications(TRUE);
			
			$notificationManager = $pluginManager->getNotificationManager();
			$notification = new Notification("Sie haben sich mit ihrem Benutzerkonto auf einem neuen Endgerät angemeldet.", "Neue Anmeldung", time()+86400);
			$notification->addReaded($authtoken);
			$notification->setActionOpenPlugin("plg_user");
			$notification->setIcon(FileManager::getImageHashByPath('images/user-add.png'));
			$notificationManager->addNotification($notification, $loginManager->getId());
			
			echo '{"token":' . json_encode($authtoken) . ',"username":"' . $_POST['username'] . '",';
			echo '"mainplugins":' . $pluginManager->getMainPlugins(); /* TODO */
			
			
			if(!$loginManager->getShareManager()->isShared()) {
				$seamless = $loginManager->getUserPreference("seamless");
				
				if(!empty($seamless) && strtoupper($seamless) == "TRUE") {
					$seamlessString = $loginManager->getUserPreference("seamless-current");
					
					if(!empty($seamlessString))
						echo ',"seamless":' . $seamlessString;
				}
			}
			echo '}';
		} else {
			die("failure 0"); // bad Username/Password
		}
	} else if(!empty($_POST['authtoken']) || !empty($_POST['token'])) {
		if(!empty($_POST['authtoken'])) {
			$decryptedKey = $loginManager->decryptToken($_POST['authtoken']);
		} else {
			$decryptedKey = $_POST['token'];
		}
		
		if($loginManager->loginUserByToken($decryptedKey)) {
			$pluginManager = new PluginManager();
			
			echo '{"status":"login"';
			echo ',"mac":"' . getMacLinux() . '"';
			echo ',"host":' . json_encode($_SERVER['SERVER_NAME']);
			
			if($pluginManager->useWolServer) {
				echo ',"wolserver":"' . $pluginManager->getWolUrl() . '"';
			}
			
			if($conf['ws_enabled'] && !empty($conf['ws_port'])) {
				echo ',"wsport":"' . $conf['ws_port'] . '"';
			}
			echo ',"mainplugins":' . $pluginManager->getMainPlugins(); /* TODO */
			echo ',"username":"' . $loginManager->getUsername() . '"';
			
			$fgcolor = $loginManager->getUserPreference("fgcolor", null);
			if(empty($fgcolor)) $fgcolor = $conf['fgcolor'];
			echo ',"fgcolor":' . json_encode($fgcolor) ;
			
			$bgcolor = $loginManager->getUserPreference("bgcolor", null);
			if(empty($bgcolor)) $bgcolor = $conf['bgcolor'];
			echo ',"bgcolor":' . json_encode($bgcolor);
			
			if(!$loginManager->getShareManager()->isShared()) {
				$seamless = $loginManager->getUserPreference("seamless");
				
				if(!empty($seamless) && strtoupper($seamless) == "TRUE") {
					$seamlessString = $loginManager->getUserPreference("seamless-current");
					
					if(!empty($seamlessString))
						echo ',"seamless":' . $seamlessString;
				}
			}
			
			echo '}';
		} else {
			echo '{"status":"notloggedin"}';
		}
	} else if(!empty($_POST['share'])) {
		if($loginManager->isShared($_POST['share'])) {
			echo '{"status":"login","share":' . json_encode($_POST['share']) . ',"hash":' . json_encode($loginManager->getShareManager()->getStartView()) . '}';
		} else {
			echo '{"status":"notloggedin"}';
		}
	}
} else if(!empty($_GET['action']) AND $_GET['action'] == "share") {
	$pluginManager = new PluginManager();
	
	$url = $loginManager->getShareManager()->getUrl($_GET['plugin'], $_GET['page'], $_GET['cmd']);
	echo '{"url":' . json_encode( $url ) . '}';
}

if(!empty($_GET['action']) AND $_GET['action'] == "getFile" && !empty($_GET['file'])) {
	require_once 'includes/FileManager.php';
	
	$fileManager = new FileManager();
	
	$fileManager->getFile($_GET['file']);
}

if(!empty($_GET['action']) && $_GET['action'] == "search" && !empty($_POST['search'])) {
	require_once dirname(__FILE__).'/includes/SearchManager.php';
	
	$searchManager = new SearchManager();
	$array = $searchManager->getArray($_POST['search']);
	
	if(!empty($array) && $array != null) {
		echo json_encode($array);
	}
}

function getMacLinux() {
	exec('netstat -ie', $result);
	
	if(is_array($result)) {
		$iface = array();
		
		foreach($result as $key => $line) {
			if($key > 0) {
				$tmp = str_replace(" ", "", substr($line, 0, 10));
				
				if($tmp <> "") {
					$macpos = strpos($line, "HWaddr");
					
					if($macpos !== false) {
						$iface[] = array('iface' => $tmp, 'mac' => strtolower(substr($line, $macpos+7, 17)));
					}
				}
			}
		}
		
		return $iface[0]['mac'];
	} else {
		return "notfound";
	}
}

?>