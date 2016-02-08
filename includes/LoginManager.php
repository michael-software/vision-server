<?php
session_start();
require_once dirname(__FILE__).'/DatabaseManager.php';

class LoginManager {
	private $databaseManager;
	const STOP_SERVER  = 1;
	const FILE_ACCESS  = 2;
	const MODIFY_USERS = 3;
	
	public function __construct() {
		/*
		$json[] = array('type'=>'int', 'name'=>'id');
		$json[] = array('type'=>'varchar', 'name'=>'username');
		$json[] = array('type'=>'varchar', 'name'=>'password');
		$json[] = array('type'=>'timestamp', 'name'=>'timestamp', 'default'=>'current_timestamp');
		
		$json = json_encode($json);
		echo $json;*/
		$json = '[{"type":"int","name":"id"},{"type":"varchar","name":"username"},{"type":"varchar","name":"password"},{"type":"timestamp","name":"timestamp","default":"current_timestamp"}]';
		$json = json_decode($json);
		
		$this->databaseManager = new DatabaseManager();
		$this->databaseManager->openTable("user", $json);
		
		if(!empty($_SESSION['username']) AND !empty($_SESSION['id']) AND !empty($_SESSION['authtoken'])) {
		} else if(!empty($_POST['token'])) {
			if(! $this->loginUserByToken($_POST['token'])) {
				die('{"status":"needlogin"}');
			}
		} else if(!empty($_POST['authtoken'])) {
			$decryptedKey = $this->decryptToken($_POST['authtoken']);
			
			if(! $this->loginUserByToken($decryptedKey)) {
				die("test2");
			}
		} else if(!empty($_GET['authtoken'])) {
			//$decryptedKey = $this->decryptToken($_POST['authtoken']);
			
			if(! $this->loginUserByToken($_GET['authtoken'])) {
				die("test2");
			}
		}
	}
	
	private function crypto_rand_secure($min, $max)
	{
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

	function getSecurityToken()
	{
		$length = 64;
	    $token = "";
	    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	    $codeAlphabet.= "0123456789";
	    $max = strlen($codeAlphabet) - 1;
	    for ($i=0; $i < $length; $i++) {
	        $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
	    }
		
		$this->addSecurityToken($token);
		
		//return "Test";
	    return $token;
	}
	
	function loginUserByPassword($pUsername, $pPassword) {
		$array['username'] = array('operator'=>'=', 'value'=>$pUsername);
		$result = $this->databaseManager->getValues($array, 1);
		
		if($result != null) {
			if($result['username'] == $pUsername && $this->isSame($result['password'], $pPassword) AND !empty($result['id'])) {
				$id = $result['id'];
				
				$permissions = $this->getPermissions($id);
				
				$this->setSessions($pUsername, $id, $permissions['group']);
				
				return true;
			}
		}
		
		return false;
	}
	
	function addSecurityToken($token) {
		$json = DatabaseManager::$table2;
		$json = json_decode($json);
		
		$databaseManagerSecurityToken = new DatabaseManager();
		$databaseManagerSecurityToken->openTable("authtokens", $json);
		
		$userId = LoginManager::getId();
		
		$array['authtoken'] = array('value'=>$token);
		$array['user']      = array('value'=>$userId);
		$databaseManagerSecurityToken->insertValue($array);
	}
	
	function setSessions($pUsername, $pId, $pGroup) {
		$_SESSION['id'] = $pId;
		$_SESSION['username'] = $pUsername;
		$_SESSION['group'] = $pGroup;
	}
	
	static function getId() {
		if(!empty($_SESSION['id'])) {
			return $_SESSION['id'];
		}
	}
	
	function loginUserByToken($pToken) {
		$json = DatabaseManager::$table2;
		$json = json_decode($json);
		
		$databaseManagerSecurityToken = new DatabaseManager();
		$databaseManagerSecurityToken->openTable("authtokens", $json);
		
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
					$this->setSessions($result['username'], $userId, $permissions['group']);
					
					return true;
				}
			}
		}
		
		return false;
	}
	
	function getPermissions($pId) {
		$databasePermissions = new DatabaseManager();
		$json = DatabaseManager::$table3;
		$json = json_decode($json);
		$databasePermissions->openTable("user_permissions", $json);
		
		$arrayToken['userid'] = array('operator'=>'=', 'value'=>$pId);
		$resultToken1 = $databasePermissions->getValues($arrayToken, 1);
		
		
		$json = DatabaseManager::$table4;
		$json = json_decode($json);
		$databasePermissions->openTable("custom_user_permissions", $json);
		
		$arrayToken2['user'] = array('operator'=>'=', 'value'=>$pId);
		$resultTokenTemp = $databasePermissions->getValues($arrayToken2);
		$resultToken2;
		
		if(!empty($resultTokenTemp))
		foreach($resultTokenTemp as $resultToken) {
			$permissionName = $resultToken['permission_name'];
			$permissionValue = $resultToken['value'];
			$resultToken2[$permissionName] = $permissionValue;
		}
		
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
		$privateKey = "1234567891234567";
		$iv = "1234567891234567";
		
		$authkey = $_POST['authtoken'];
		
		$decryptedKey = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $privateKey, base64_decode($authkey), MCRYPT_MODE_CBC, $iv);
		$decryptedKey = trim($decryptedKey);
		
		return $decryptedKey;
	}
	
	function getGroup() {
		if(!empty($_SESSION['group'])) {
			return $_SESSION['group'];
		}
		return 0;
	}
	
	function getUsername() {
		if(!empty($_SESSION['username'])) {
			return $_SESSION['username'];
		}
		
		return "";
	}
	
	function getPermission($pPermission) {
		if(!empty($_SESSION['id'])) {
			$permission = $this->getPermissions($_SESSION['id']);
			
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
			if($this->getGroup() >= 2)
				return true;
		} else if($pPermission == LoginManager::FILE_ACCESS) {
			if($this->getPermission(LoginManager::FILE_ACCESS))
				return true;
		} else if($pPermission == LoginManager::MODIFY_USERS) {
			if($this->getPermission(LoginManager::MODIFY_USERS))
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
			if($this->isSame($result['password'], $pPasswordOld) AND !empty($result['id'])) {
				$id = $result['id'];
				
				if($this->databaseManager->setValue(Array("password"=>Array("value"=>$pPasswordNew)), Array("id"=>Array("operator"=>"=", "value"=>$id, "type"=>"i"))))				
				return true;
			}
		}
		
		return false;
	}
	
	function isSame($pPassword1, $pPassword2) {
		if($pPassword1 == $pPassword2) {
			return true;
		}
		
		return false;
	}
}

$loginManager = new LoginManager();

?>