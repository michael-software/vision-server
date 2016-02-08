<?php

require 'includes/PluginManager.php';
require 'includes/LoginManager.php';

if(!empty($_GET['show'])) {
	if($_GET['show'] == 'plugins') {
		$pluginManager = new PluginManager();
		echo $pluginManager->getPlugins();
	}
}

if(!empty($_GET['plugin']) AND !empty($_GET['get']) AND $_GET['get'] == 'view') {
	if(!empty($_GET['page'])) {
		$page = $_GET['page'];
	} else {
		$page = 'home';
	}
	
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
		
		$loginManager = new LoginManager();
		
		if($loginManager->loginUserByPassword($userName, $password)) {
			$securityToken = $loginManager->getSecurityToken();
			
			$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $decryptedKey, $securityToken, MCRYPT_MODE_CBC, $decryptedIv);
			$encryptedStatus = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $decryptedKey, "login", MCRYPT_MODE_CBC, $decryptedIv);
			
			echo '{';
			echo '"status":' . json_encode(base64_encode($encryptedStatus)) . ',';
			echo '"key":'.json_encode($decryptedKey).',';
			echo '"iv":'.json_encode($decryptedIv).',';
			echo '"username":'.json_encode($userName).',';
			echo '"password":'.json_encode($password).',';
			echo '"authtoken":' . json_encode(base64_encode($encrypted));
			echo '}';
		}
	} else if(!empty($_POST['username']) AND !empty($_POST['password'])) {
		$loginManager = new LoginManager();
		
		if($loginManager->loginUserByPassword($_POST['username'], $_POST['password'])) {
			$securityToken = $loginManager->getSecurityToken();
			echo '{"token":' . json_encode($securityToken) . ',"username":"' . $_POST['username'] . '"}';
		} else {
			echo "failure 0"; // false Username/Password
		}
	} else if(!empty($_POST['authtoken']) || !empty($_POST['token'])) {
		$loginManager = new LoginManager();
		
		if(!empty($_POST['authtoken'])) {
			$decryptedKey = $loginManager->decryptToken($_POST['authtoken']);
		} else {
			$decryptedKey = $_POST['token'];
		}
		
		if($loginManager->loginUserByToken($decryptedKey)) {
			$pluginManager = new PluginManager();
			
			echo '{"status":"login"';
			echo ',"mac":"' . getMacLinux() . '"';
			
			if($pluginManager->useWolServer) {
				echo ',"wolserver":"' . $pluginManager->getWolUrl() . '"';
			}
			echo ',"username":"' . $loginManager->getUsername() . '"}';
		} else {
			echo '{"status":"notloggedin"}';
		}
	}
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