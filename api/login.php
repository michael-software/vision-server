<?php

require dirname(dirname(__FILE__)) . '/includes/LoginManager.php';
require dirname(dirname(__FILE__)) . '/includes/PluginManager.php';

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
        $output['username'] = $userName;
        $output['authtoken'] = base64_encode($encrypted);
        $output['mainplugins'] = $pluginManager->getMainPlugins();
        
        $fgcolor = $loginManager->getUserPreference("fgcolor", null);
        if(empty($fgcolor)) $fgcolor = $conf['fgcolor'];
        $output['fgcolor'] = $fgcolor;
        
        $bgcolor = $loginManager->getUserPreference("bgcolor", null);
        if(empty($bgcolor)) $bgcolor = $conf['bgcolor'];
        $output['bgcolor'] = $bgcolor;

        $output['id'] = $loginManager->getId();
        
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
        echo ',"id":' . $loginManager->getId();
        
        
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
} else if(!empty($_SERVER['HTTP_AUTHORIZATION'])  && $loginManager->startsWith($_SERVER['HTTP_AUTHORIZATION'], 'bearer ')) {
	
    $jwtRequest = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
    
    if($loginManager->loginUserByJwt($jwtRequest)) {
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

        echo ',"id":' . $loginManager->getId();
        
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