<?php
session_start();

require_once dirname(dirname(__FILE__)) . '/config.php';

spl_autoload_register(function ($class_name) {
	global $conf, $loginManager, $pluginManager, $logManager;

	$file = dirname(__FILE__) . '/' . $class_name . '.php';

	if(strpos(strtolower($class_name), 'jui') !== FALSE) {
		$file = dirname(__FILE__) . '/JUIManager.php';
		
	}

	if(file_exists($file)) {
		include_once $file;
	}
});

//require_once dirname(__FILE__).'/DatabaseManager.php';
//require_once dirname(__FILE__).'/LogManager.php';
//require_once dirname(__FILE__).'/ShareManager.php';
//require_once dirname(__FILE__).'/JwtManager.php';

if (!defined('WEBSOCKET')) {
    define('WEBSOCKET', '2');
}

define('AUTHTOKENS', 1);
define('USER_PERMISSIONS', 2);

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

class LoginManager {
	private $databaseManager;
	
	const STOP_SERVER   = 1;
	const FILE_ACCESS   = 2;
	const MODIFY_USERS  = 3;
	const LOG_ACCESS    = 4;
	const SERVER_NOTIFY = 5;
	const START_SERVER  = 6;
	const SERVER_CONFIG = 7;
	
	const GROUP_SERVER = 1;
	const GROUP_SERVER_ADMIN = 2;
	
	private $shareManager;
	private $user = array();
	private $revalidate = true, $updateJwt = false;
	private $jwtManager = null;
	private $secret = 'secret';
	
	public function __construct() {
		$this->jwtManager = new JwtManager();

		$this->secure(); // secure the component
		
		$json = json_decode(DatabaseManager::$table1);
		
		$this->databaseManager = new DatabaseManager();
		$this->databaseManager->openTable('users', $json);
		
		if(!empty($_POST['share'])) {
			$this->shareManager = new ShareManager($_POST['share']);
		} else if(!empty($_GET['share'])) {
			$this->shareManager = new ShareManager($_GET['share']);
		} else if(!empty($_SERVER['HTTP_AUTHORIZATION']) && $this->startsWith($_SERVER['HTTP_AUTHORIZATION'], 'bearer ')) {
			$jwtRequest = substr($_SERVER['HTTP_AUTHORIZATION'], 7);

			$this->proofJwt($jwtRequest);
		} else if(!empty($_GET['jwt'])) {
			$this->proofJwt(urldecode($_GET['jwt']));
		} else if(!empty($_SESSION['username']) AND !empty($_SESSION['id']) AND !empty($_SESSION['authtoken'])) {
			$this->shareManager = new ShareManager($_SESSION['id']);
		} else if(!empty($_GET['action']) AND $_GET['action'] == 'login' && !empty($_POST['username']) && !empty($_POST['password'])) {
			
		} else if(constant('WEBSOCKET') == 1) {
		} else if(empty($_SESSION['username']) || empty($_SESSION['id']) || empty($_SESSION['authtoken'])) {
			die('{"status":"needrelogin"}');
		}
	}

	function revalidate() {
		if($this->revalidate) {
			if(!empty($_SERVER['HTTP_AUTHORIZATION'])  && $this->startsWith($_SERVER['HTTP_AUTHORIZATION'], 'bearer ')) {
				$jwtRequest = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
				$data = $this->jwtManager->getJwtData($jwtRequest);

				if(!empty($data->sub) && $this->jwtManager->validateJwt($jwtRequest, $this->secret) == 2) {
					$jwt = $this->jwtManager->createJwt($data->name, $data->_sek, $this->secret, array('sub'=>$data->sub, 'jti'=>$data->jti), 60);
					$jwtSignature = $this->jwtManager->getSignature($jwt);

					$this->addJwtSignature($jwtSignature, $name='Endgerät', $data->sub);
					$this->removeJwtSignature($this->jwtManager->getSignature($jwtRequest), $data->sub);

					return $jwt;
				}
			}
		} else if($this->updateJwt) {
			return $this->user['jwt'];
		}
	}

	function setUserId($userid) {
        if(constant('WEBSOCKET') == 1) {
            $this->user['id'] = $userid;
        }
    }

	function needRevalidation() {
		return $this->revalidate || $this->updateJwt;
	}

	function startsWith($haystack, $needle) {
		// search backwards starting from haystack length characters from the end
		return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
	}

	function setEncryptionKey($key) {
		global $pluginManager;

		if($pluginManager->getPluginName() == 'plg_user') {
			$key = hash('sha512', $key);

			if($key != $this->user['key']) {
				$this->user['jwt'] = $this->jwtManager->setData($this->user['jwt'], array('_sek', $key), $this->secret);
				$this->updateJwt = true;
			}
		}
	}

	function getLogManager() {
		global $logManager;

		if(empty($logManager)) {
			$logManager = new LogManager();
		}

		return $logManager;
	}

	function proofJwt($jwtRequest) {
		if($this->jwtManager->validateJwt($jwtRequest, $this->secret) == 2) {
			$data = $this->jwtManager->getJwtData($jwtRequest);

			$_SESSION['id'] = $data->sub;
			$this->user['id'] = $data->sub;

			if(!empty($data->name))
			    $this->user['username'] = $data->name;
            else if(!empty($data->username))
                $this->user['username'] = $data->username;

			$this->user['jwt'] = $jwtRequest;

			if(!empty($data->_sek)) {
				$this->user['key'] = $data->_sek;
			}

			$jwtInfo =  $this->getJwtInfoBySignature( $this->jwtManager->getSignature($jwtRequest), $data->sub);

			if( !empty($jwtInfo) && empty($jwtInfo['refused']) ) {
				$this->revalidate = true;
			} else {
				header('HTTP/1.1 401 Unauthorized');
				die('{"head":{"status":401}}');
			}
		} else if($this->jwtManager->validateJwt($jwtRequest, $this->secret) != 1) {
			header('HTTP/1.1 401 Unauthorized');
			die('{"head":{"status":401}}');
		} else {
			$data = $this->jwtManager->getJwtData($jwtRequest);

			if(empty($data->jti)) {
				$data->jti = uniqid();
			}

			$_SESSION['id'] = $data->sub;
			$this->user['id'] = $data->sub;

            if(!empty($data->name))
                $this->user['username'] = $data->name;
            else if(!empty($data->username))
                $this->user['username'] = $data->username;

			$this->user['jwt'] = $jwtRequest;
			$this->user['jwtId'] = $data->jti;

			if(!empty($data->_sek)) {
				$this->user['key'] = $data->_sek;
			}

			$this->revalidate = false;
		}
	}

	function getUserList() {
		$pReturn = $this->databaseManager->getValues();
		
		$userList = [];
		
		for($i = 0, $x = count($pReturn); $i < $x; $i++) {
			$pReturn[$i]['digesta1'] = md5($pReturn[$i]['digesta1']);
			$id = $pReturn[$i]['id'];
			
			$userList[$id] = $pReturn[$i];
		}
		
		return $userList;
	}
	
	private function crypto_rand_secure($min, $max) {
	    $range = $max - $min;
	    if ($range < 1) return $min; // not so random...
	    $log = ceil(log($range, 2));
	    $bytes = (int) ($log / 8) + 1; // length in bytes
	    $bits = (int) $log + 1; // length in bits
	    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
	    do {
	        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
	        $rnd = $rnd & $filter; // discard irrelevant bits
	    } while ($rnd >= $range);
	    return $min + $rnd;
	}

	function getSecurityToken($name='Endgerät', $pUsername, $pPassword, $userid) {
		/*
		$length = 64;
	    $token = "";
	    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	    $codeAlphabet.= "0123456789";
	    $max = strlen($codeAlphabet) - 1;
	    for ($i=0; $i < $length; $i++) {
	        $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
	    }*/

		require_once dirname(__FILE__) . '/CryptManager.php';

		$jwtManager = new JwtManager();
		$token = $jwtManager->createJwt($pUsername, hash('sha512', $pPassword), $this->secret, array('sub'=>$userid), 60);
		
		$this->addSecurityToken($token, $name);
		$this->addJwtSignature($jwtManager->getSignature($token), $name, $userid);
		
		//return "Test";
	    return $token;
	}

	function base64url_encode($data) { 
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	} 

	function base64url_decode($data) { 
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
	} 
	
	function loginUserByPassword($pUsername, $pPassword, $name='Endgerät') {
		$pUsername = trim(strtolower($pUsername));
		$array['username'] = array('operator'=>'=', 'value'=>$pUsername);
		$result = $this->databaseManager->getValues($array, 1);
		
		if($result != null) {
			if(!empty($result['id']) && $result['username'] == $pUsername && $this->isSame($result['digesta1'], $result['username'], $pPassword)) {
				$id = $result['id'];
				
				$permissions = $this->getPermissions($id);
				
				$_SESSION['id'] = $id;
				$securityToken = $this->getSecurityToken($name, $pUsername, $pPassword, $id);
				
				$this->setSessions($pUsername, $id, $permissions['group'], $securityToken);
				
				if(constant('WEBSOCKET') != 1)
					$this->getLogManager()->addLog('Der Benutzer ' . $result['username'] . ' hat sich angemeldet. (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				return $securityToken;
			}
		}
		
		if(constant('WEBSOCKET') != 1) {
			$message = 'Ein Benutzer hat versucht sich mit fehlerhaften Anmeldedaten (Benutzername: \'' . $pUsername . '\') anzumelden (IP: ' . $_SERVER['REMOTE_ADDR'] . ').';
			$this->getLogManager()->addLog($message);
		}
		return null;
	}

	function isShared($shareCode) {
		$array['id'] = array('operator'=>'=', 'value'=>$shareCode);
		$shareDatabaseManager = new DatabaseManager();
		$shareDatabaseManager->openTable('share', json_decode(DatabaseManager::$table9));
		$result = $shareDatabaseManager->getValues($array, 1);
		
		if($result != null) {
			if(!empty($result['id']) && $result['id'] == $shareCode) {
				if(empty($this->shareManager)) {
					$this->shareManager = new ShareManager($shareCode);
				}
				
				return true;
			}
		}
		
		return false;
	}
	
	private function addSecurityToken($token, $name='Endgerät') {
		$json = DatabaseManager::$table2;
		$json = json_decode($json);
		
		$databaseManagerSecurityToken = new DatabaseManager();
		$databaseManagerSecurityToken->openTable('authtokens', $json);
		
		$userId = $this->getId();
		
		if(!empty($_SERVER['REMOTE_ADDR'])) {
			$array['name'] = array('value'=> $name . ' - ' . $_SERVER['REMOTE_ADDR'] );
		} else {
			$array['name'] = array('value'=> $name . ' - ' . uniquid() );
		}
		
		$array['authtoken'] = array('value'=>$token);
		$array['user']      = array('value'=>$userId);
		$databaseManagerSecurityToken->insertValue($array);
		
		sync(AUTHTOKENS);
	}
	
	private function setSessions($pUsername, $pId, $pGroup, $pAuthtoken) {
		$_SESSION['id'] = $pId;
		$_SESSION['username'] = $pUsername;
		$_SESSION['group'] = $pGroup;
		$_SESSION['authtoken'] = $pAuthtoken;
		
		$this->shareManager = new ShareManager($pId);
	}
	
	function loginUserByToken($pToken) {
		
		/*
		$json = json_decode(DatabaseManager::$table2);
		
		
		$databaseManagerSecurityToken = new DatabaseManager();
		$databaseManagerSecurityToken->openTable('authtokens', $json);
		
		$arrayToken['authtoken'] = array('operator'=>'=', 'value'=>$pToken);
		$resultToken = $databaseManagerSecurityToken->getValues($arrayToken, 1);
		
		if(!empty($resultToken) && $resultToken['authtoken'] == $pToken) {
			if(!empty($resultToken['user'])) {
				$userId = $resultToken['user'];
				$array['id'] = array('operator'=>'=', 'value'=>$userId);
				$result = $this->databaseManager->getValues($array, 1);
				
				if(!empty($result) AND !empty($result['username'])) {
					$_SESSION['authtoken'] = $pToken;
					
					$permissions = $this->getPermissions($userId);
					$this->setSessions($result['username'], $userId, $permissions['group'], $pToken);
					
					return true;
				}
			}
		}
		*/


		if($this->jwtManager->validateJwt($pToken, $this->secret) == 2) {
			$data = $this->jwtManager->getJwtData($pToken);

			$_SESSION['id'] = $data->sub;
			$this->user['id'] = $data->sub;

            if(!empty($data->name))
                $this->user['username'] = $data->name;
            else if(!empty($data->username))
                $this->user['username'] = $data->username;


			$this->user['jwt'] = $pToken;

			if(!empty($data->_sek)) {
				$this->user['key'] = $data->_sek;
			}

			$jwtInfo =  $this->getJwtInfoBySignature( $this->jwtManager->getSignature($pToken), $data->sub);

			if( !empty($jwtInfo) && empty($jwtInfo['refused']) ) {
				return true;
			}
		} else if($this->jwtManager->validateJwt($pToken, $this->secret) == 1) {
			$data = $this->jwtManager->getJwtData($pToken);

			if(empty($data->jti)) {
				$data->jti = uniqid();
			}

			$_SESSION['id'] = $data->sub;
			$this->user['id'] = $data->sub;

            if(!empty($data->name))
                $this->user['username'] = $data->name;
            else if(!empty($data->username))
                $this->user['username'] = $data->username;

			$this->user['jwt'] = $pToken;
			$this->user['jwtId'] = $data->jti;

			if(!empty($data->_sek)) {
				$this->user['key'] = $data->_sek;
			}

			return true;
		}



		if(constant('WEBSOCKET') != 1)
			$this->getLogManager()->addLog('Ein Benutzer versuchte sich mit einem fehlerhaften Authtoken anzumelden (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
		return false;
	}

	function loginUserByJwt($jwt) {
		$data = $this->jwtManager->getJwtData($jwt);

		if(!empty($data->username)) {
		    $data->name = $data->username;
        }

		$validate = $this->jwtManager->validateJwt($jwt, $this->secret);
		if($validate == 1 || $validate == 2) {

			if($validate == 2) {
				$jwtInfo =  $this->getJwtInfoBySignature( $this->jwtManager->getSignature($jwt), $data->sub);
			}

			if( $validate == 1 || (!empty($jwtInfo) && empty($jwtInfo['refused'])) ) {
			
				$userId = $data->sub;

				$permissions = $this->getPermissions($userId);
				$this->setSessions($data->name, $userId, $permissions['group'], $jwt);

				return true;
			}
		} else {
			return false;
		}
		
		if(constant('WEBSOCKET') != 1)
			$this->getLogManager()->addLog('Ein Benutzer versuchte sich mit einem fehlerhaften Authtoken anzumelden (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
		return false;
	}
	
	function getPermissions($pId) {
		$databasePermissions = new DatabaseManager();
		$json = DatabaseManager::$table3;
		$json = json_decode($json);
		$databasePermissions->openTable('user_permissions', $json);
		
		$arrayToken['userid'] = array('operator'=>'=', 'value'=>$pId);
		$resultToken1 = $databasePermissions->getValues($arrayToken, 1);
		
		
		$json = DatabaseManager::$table4;
		$json = json_decode($json);
		$databasePermissions->openTable('custom_user_permissions', $json);
		
		$arrayToken2['user'] = array('operator'=>'=', 'value'=>$pId);
		$resultTokenTemp = $databasePermissions->getValues($arrayToken2);
		$resultToken2;
		
		if(!empty($resultTokenTemp))
		foreach($resultTokenTemp as $resultToken) {
			$permissionName = $resultToken['permission_name'];
			$permissionValue = $resultToken['value'];
			$resultToken2[$permissionName] = $permissionValue;
		}
		
		
		$resultToken2['use_plg_serversettings'] = '1';
		$resultToken2['use_plg_license'] = '1';
		$resultToken2['use_plg_order'] = '1';
		$resultToken2['use_plg_user'] = '1';
		
		if(!empty($resultToken1) && !empty($resultToken2)) {
			return array_merge($resultToken2, $resultToken1);
		} else if(!empty($resultToken2)) {
			return $resultToken2;
		} else if(!empty($resultToken1)) {
			return $resultToken1;
		}
		
		return null;
	}
	
	function decryptToken($pToken) {
		$privateKey = '1234567891234567';
		$iv = '1234567891234567';
		
		$authkey = $_POST['authtoken'];
		
		$decryptedKey = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $privateKey, base64_decode($authkey), MCRYPT_MODE_CBC, $iv);
		$decryptedKey = trim($decryptedKey);
		
		return $decryptedKey;
	}
	
	function getUserPreference($name, $default=null) {
		if( is_string($name) ) {
			$databasePreferences = new DatabaseManager();
			$json = json_decode(DatabaseManager::$table6);
			$databasePreferences->openTable('user_preferences', $json);
			
			$result = $databasePreferences->getValues(Array('name'=>Array('value'=>$name, 'type'=>'s', 'operator'=>'=')), 1);
			
			if(!empty($result) && !empty($result['value'])) {
				return $result['value'];
			}
		}
		
		return $default;
	}
	
	function setUserPreference($name, $value) {
		if( is_string($name) ) {
			$databasePreferences = new DatabaseManager();
			$json = json_decode(DatabaseManager::$table6);
			$databasePreferences->openTable('user_preferences', $json);
			
			$result = $databasePreferences->insertOrUpdateValue(Array('value'=>Array('value'=>$value, 'type'=>'s')), Array('name'=>Array('value'=>$name, 'type'=>'s', 'operator'=>'=')) );
		}
	}
	
	function addMainPlugin($pluginId) {
		$mainPlugins = $this->getUserPreference('mainplugins', null);
		
		if(!empty($mainPlugins)) {
			$mainPlugins .= '|';
		}
		$mainPlugins .= $pluginId;
		
		if(empty($mainPlugins) || strpos($this->getUserPreference('mainplugins', null), $pluginId) === FALSE) {
			$this->setUserPreference('mainplugins', $mainPlugins);
		}
		
		return null;
	}
	
	function removeMainPlugin($pluginId) {
		$mainPlugins = $this->getUserPreference('mainplugins', null);
		
		$mainPlugins = str_replace($pluginId . '|', '', $mainPlugins);
		$mainPlugins = str_replace('|' . $pluginId, '', $mainPlugins);
		
		$this->setUserPreference('mainplugins', $mainPlugins);
		return null;
	}
	
	function getGroup() {
		if(!empty($_SESSION['group'])) {
			return $_SESSION['group'];
		}
		return 0;
	}
	
	function getUsername() {
		if(!empty($this->shareManager) && !empty($this->shareManager->getUsername())) {
			return $this->shareManager->getUsername();
		} else if(!empty($_SESSION['username'])) {
			return $_SESSION['username'];
		} else if(!empty($this->user['username'])) {
			return $this->user['username'];
		}
		
		return '';
	}
	
	function getId() {
		if(!empty($this->shareManager) && !empty($this->shareManager->getId())) {
			return $this->shareManager->getId();
		} else if(!empty($_SESSION['id'])) {
			return $_SESSION['id'];
		} else if(!empty($this->user['id'])) {
			return $this->user['id'];
		}
	}

	function getEncryptionPassword() {
		if(!empty($this->user['key'])) {
			return $this->user['key'];
		}

		return null;
	}

	function getPluginKey() {
		global $pluginManager;

		require_once dirname(__FILE__) . '/CryptManager.php';

		if(!empty($this->user['key'])) {
			$key = $this->getPluginEncryptionKey($pluginManager->getPluginName(), $this->user['key']);

			if(empty($key) || empty($key['key'])) {
				return null;
			}

			return $pluginManager->getCryptManager()->unlockAsciiKey($key['key'], $this->user['key']);
		}

		return null;
	}

	private function getPluginEncryptionKey($pluginName, $key) {
		global $pluginManager;
		require_once dirname(__FILE__) . '/CryptManager.php';

		$databaseAuthtokens = new DatabaseManager();
		$json = json_decode(DatabaseManager::$table12);
		$databaseAuthtokens->openTable('keys', $json);
		
		$arrayToken['user'] = array('operator'=>'=', 'value'=>$this->getId(), 'type'=>'i');
		$arrayToken['plugin'] = array('operator'=>'=', 'value'=>$pluginName, 'type'=>'s');
		$resultToken1 = $databaseAuthtokens->getValues($arrayToken, 1);
		
		if(empty($resultToken1)) {
			$asciiKey = $pluginManager->getCryptManager()->getPwKey($key)->saveToAsciiSafeString();

			$arrayToken['key'] = array('operator'=>'=', 'value'=>$asciiKey, 'type'=>'s');
			if($databaseAuthtokens->insertValue($arrayToken)) {
				return array('user'=>$this->getId(), 'plugin'=>$pluginName, 'key'=>$asciiKey);
			}

			return null;
		}

		return $resultToken1;
	}
	
	function getPermission($pPermission) {
		if(!empty($this->getId())) {
			$permission = $this->getPermissions($this->getId());
			
			if($pPermission == LoginManager::FILE_ACCESS) {
				if(!empty($permission['access_files']) && $permission['access_files'] == 1) {
					return true;
				}
			} else if($pPermission == LoginManager::STOP_SERVER) {
				if(!empty($permission['stop_server']) && $permission['stop_server'] == 1) {
					return true;
				}
			} else if($pPermission == LoginManager::MODIFY_USERS) {
				if(!empty($permission['modify_users']) && $permission['modify_users'] == 1) {
					return true;
				}
			} else if($pPermission == LoginManager::LOG_ACCESS) {
				if(!empty($permission['log_access']) && $permission['log_access'] == 1) {
					return true;
				}
			} else if($pPermission == LoginManager::SERVER_NOTIFY) {
				if(!empty($permission['server_notify']) && $permission['server_notify'] == 1) {
					return true;
				}
			} else if($pPermission == LoginManager::START_SERVER) {
				if(!empty($permission['start_server']) && $permission['start_server'] == 1) {
					return true;
				}
			} else if($pPermission == LoginManager::SERVER_CONFIG) {
				if(!empty($permission['server_config']) && $permission['server_config'] == 1) {
					return true;
				}
			} else {
				if(!empty($permission[$pPermission]) && $permission[$pPermission] == 1) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	function isAllowed($pPermission) {
		if($pPermission == LoginManager::STOP_SERVER) {
			if($this->getPermission(LoginManager::STOP_SERVER))
				return true;
		} else if($pPermission == LoginManager::FILE_ACCESS) {
			if($this->getPermission(LoginManager::FILE_ACCESS))
				return true;
		} else if($pPermission == LoginManager::MODIFY_USERS) {
			if($this->getPermission(LoginManager::MODIFY_USERS))
				return true;
		} else if($pPermission == LoginManager::LOG_ACCESS) {
			if($this->getPermission(LoginManager::LOG_ACCESS))
				return true;
		} else if($pPermission == LoginManager::SERVER_NOTIFY) {
			if($this->getPermission(LoginManager::SERVER_NOTIFY))
				return true;
		} else if($pPermission == LoginManager::START_SERVER) {
			if($this->getPermission(LoginManager::START_SERVER))
				return true;
		} else if($pPermission == LoginManager::SERVER_CONFIG) {
			if($this->getPermission(LoginManager::SERVER_CONFIG))
				return true;
		} else {
			if($this->getPermission($pPermission))
				return true;
		}
		
		return false;
	}
	
	function changePassword($pPasswordOld, $pPasswordNew) {
		$array['username'] = array('operator'=>'=', 'value'=>$this->getUsername());
		$result = $this->databaseManager->getValues($array, 1);
		
		if($result != null) {
			if($this->isSame($result['digesta1'], $this->getUsername(), $pPasswordOld) AND !empty($result['id'])) {
				$id = $result['id'];
				$password = $this->getSaltedPassword($this->getUsername(), $pPasswordNew);
				
				if($this->databaseManager->setValue(Array('digesta1'=>Array('value'=>$password)), Array('id'=>Array('operator'=>'=', 'value'=>$id, 'type'=>'i'))))				
				return true;
			}
		}
		
		return false;
	}
	
	static function getSaltedPassword($pUsername, $pPassword) {
		global $conf;
		
		return md5(strtolower($pUsername) . ':' . $conf['dav_realm'] . ':' . $pPassword);
	}
	
	public function getAuthtokens() {
		$databaseAuthtokens = new DatabaseManager();
		$json = json_decode(DatabaseManager::$table2);
		$databaseAuthtokens->openTable('authtokens', $json);
		
		$arrayToken['user'] = array('operator'=>'=', 'value'=>$this->getId(), 'type'=>'i');
		$resultToken1 = $databaseAuthtokens->getValues($arrayToken);
		
		return $resultToken1;
	}
	
	public function getAuthtoken() {
		if(!empty($this->user) && !empty($this->user['jwtId'])) {
			return $this->user['jwtId'];
		} else if(!empty($_SESSION['authtoken'])) {
			return $_SESSION['authtoken'];
		}
		
		return '';
	}
	
	public function getAuthtokenInfoById($id) {
		$databaseAuthtokens = new DatabaseManager();
		$json = json_decode(DatabaseManager::$table2);
		$databaseAuthtokens->openTable('authtokens', $json);
		
		$arrayToken['user'] = array('operator'=>'=', 'value'=>$this->getId(), 'type'=>'i');
		$arrayToken['id'] = array('operator'=>'=', 'value'=>$id, 'type'=>'i');
		$resultToken1 = $databaseAuthtokens->getValues($arrayToken, 1);
		
		return $resultToken1;
	}
	
	public function setAuthtokenName($id, $name) {
		$databaseAuthtokens = new DatabaseManager();
		$json = json_decode(DatabaseManager::$table2);
		$databaseAuthtokens->openTable('authtokens', $json);
		
		$arrayToken['user'] = array('operator'=>'=', 'value'=>$this->getId(), 'type'=>'i');
		$arrayToken['id'] = array('operator'=>'=', 'value'=>$id, 'type'=>'i');
		$resultToken1 = $databaseAuthtokens->setValue(array('name'=>$name), $arrayToken);
		
		return $resultToken1;
	}
	
	public function removeAuthtokenById($id) {
		$databaseAuthtokens = new DatabaseManager();
		$json = json_decode(DatabaseManager::$table2);
		$databaseAuthtokens->openTable('authtokens', $json);
		
		$arrayToken['user'] = array('operator'=>'=', 'value'=>$this->getId(), 'type'=>'i');
		$arrayToken['id'] = array('operator'=>'=', 'value'=>$id, 'type'=>'i');
		$resultToken1 = $databaseAuthtokens->remove($arrayToken);
		
		return true;
	}
	
	public function removeAuthtoken($authtoken) {
		$databaseAuthtokens = new DatabaseManager();
		$json = json_decode(DatabaseManager::$table2);
		$databaseAuthtokens->openTable('authtokens', $json);
		
		$arrayToken['user'] = array('operator'=>'=', 'value'=>$this->getId(), 'type'=>'i');
		$arrayToken['authtoken'] = array('operator'=>'=', 'value'=>$authtoken, 'type'=>'s');
		$resultToken1 = $databaseAuthtokens->remove($arrayToken);
		
		return true;
	}
	
	public function removeAllAuthtokens() {
		$databaseAuthtokens = new DatabaseManager();
		$json = json_decode(DatabaseManager::$table2);
		$databaseAuthtokens->openTable('authtokens', $json);
		
		$arrayToken['user'] = array('operator'=>'=', 'value'=>$this->getId(), 'type'=>'i');
		$resultToken1 = $databaseAuthtokens->remove($arrayToken);
		
		return true;
	}

	/* JWT */
	public function getJwtInfoBySignature($signature, $userid) {
		$databaseAuthtokens = new DatabaseManager();
		$json = json_decode(DatabaseManager::$table11);
		$databaseAuthtokens->openTable('jwt', $json);
		
		$arrayToken['user'] = array('operator'=>'=', 'value'=>$userid, 'type'=>'i');
		$arrayToken['signature'] = array('operator'=>'=', 'value'=>$signature, 'type'=>'s');
		$resultToken1 = $databaseAuthtokens->getValues($arrayToken, 1);
		
		return $resultToken1;
	}

	public function addJwtSignature($signature, $name='Endgerät', $userid) {
		$databaseAuthtokens = new DatabaseManager();
		$json = json_decode(DatabaseManager::$table11);
		$databaseAuthtokens->openTable('jwt', $json);
		
		$arrayToken['user'] = array('operator'=>'=', 'value'=>$userid, 'type'=>'i');
		$arrayToken['signature'] = array('operator'=>'=', 'value'=>$signature, 'type'=>'s');
		$arrayToken['name'] = array('operator'=>'=', 'value'=>$name, 'type'=>'s');
		$resultToken1 = $databaseAuthtokens->insertValue($arrayToken);
		
		return $resultToken1;
	}

	public function removeJwtSignature($signature, $userid) {
		$databaseAuthtokens = new DatabaseManager();
		$json = json_decode(DatabaseManager::$table11);
		$databaseAuthtokens->openTable('jwt', $json);
		
		$arrayToken['user'] = array('operator'=>'=', 'value'=>$userid, 'type'=>'i');
		$arrayToken['signature'] = array('operator'=>'=', 'value'=>$signature, 'type'=>'s');
		$resultToken1 = $databaseAuthtokens->remove($arrayToken);
		
		return true;
	}
	
	function isSame($pPassword1, $pUsername, $pPassword2) {
		if($pPassword1 == LoginManager::getSaltedPassword($pUsername, $pPassword2)) {
			return true;
		}
		
		return false;
	}
	
	function logout() {
		$authtoken = $_SESSION['authtoken'];
		$this->removeAuthtoken($authtoken);
		
		unset($_SESSION['id']);
		unset($_SESSION['username']);
		unset($_SESSION['group']);
		unset($_SESSION['authtoken']);
		
		die('{"action":"logout"}');
	}
	
	function getShareManager() {
		if(!empty($this->shareManager)) {
			return $this->shareManager;
		} else {
			$this->shareManager = new ShareManager($this->getId());
			return $this->shareManager;
		}
	}
	
	private function secure() {
		if(defined('LOGIN_MANAGER_SET')) {
			die('Failure 01 - Double call to LoginManager');
		}
	}
}


function sync($type) {
	global $conf;
	$database = new DatabaseManager();
	
	if(!empty($type)) {
		if($type == USER_PERMISSIONS) {
			$database->openTable('user_permissions', json_decode(DatabaseManager::$table3));
			$permissionsDb = $database->getValues();
			
			$permissions;
			
			if(!empty($permissionsDb))
			foreach($permissionsDb as $permission) {
				$user = $permission['userid'];
				
				if(!empty($permission['access_files'])) {
					$permissions[] = Array('user'=>$user, 'permission'=>LoginManager::FILE_ACCESS, 'value'=>$permission['access_files']);
				}
				
				if(!empty($permission['stop_server'])) {
					$permissions[] = Array('user'=>$user, 'permission'=>LoginManager::STOP_SERVER, 'value'=>$permission['stop_server']);
				}
				
				if(!empty($permission['log_access'])) {
					$permissions[] = Array('user'=>$user, 'permission'=>LoginManager::LOG_ACCESS, 'value'=>$permission['log_access']);
				}
				
				if(!empty($permission['modify_users'])){
					$permissions[] = Array('user'=>$user, 'permission'=>LoginManager::MODIFY_USERS, 'value'=>$permission['modify_users']);
				}
				
				if(!empty($permission['server_notify'])) {
					$permissions[] = Array('user'=>$user, 'permission'=>LoginManager::SERVER_NOTIFY, 'value'=>$permission['server_notify']);
				}
				
				if(!empty($permission['start_server'])) {
					$permissions[] = Array('user'=>$user, 'permission'=>LoginManager::START_SERVER, 'value'=>$permission['start_server']);
				}
			}
			
			$json_encoded = json_encode($permissions);
			
			syncData($json_encoded, USER_PERMISSIONS);
		} else if($type == AUTHTOKENS) {
			$database->openTable('authtokens', json_decode(DatabaseManager::$table2));
			$authtokens = $database->getValues();
			
			$json_encoded = json_encode($authtokens);
			
			syncData($json_encoded, AUTHTOKENS);
		}
	}
}

function syncData($data, $type) {
	global $conf;
	
	if(!empty($conf['login_sync_server']) && is_array($conf['login_sync_server']))
		foreach($conf['login_sync_server'] as $server) {
		
		$postdata = http_build_query(
			array(
				'data' => $data,
				'type' => $type,
				 )
				);
				
				$opts = array('http' =>
				    array(
				        'method'  => 'POST',
				        'header'  => 'Content-type: application/x-www-form-urlencoded',
				        'content' => $postdata
				    )
				);
				
				$context  = stream_context_create($opts);
				
				$result = @file_get_contents('http://' . $server . '/api.php', false, $context);
		}
}

$loginManager = new LoginManager();
define('LOGIN_MANAGER_SET', TRUE);

?>