<?php

class UserManager {
	private $databaseManager;
	
	function __construct () {
		$json = json_decode(DatabaseManager::$table1);
		
		$this->databaseManager = new DatabaseManager();
		$this->databaseManager->openTable('users', $json);
	}
	
	function getUserList() {
		$pReturn = $this->databaseManager->getValues();
		
		for($i = 0, $x = count($pReturn); $i < $x; $i++) {
			$pReturn[$i]['digesta1'] = md5($pReturn[$i]['digesta1']);
		}
		
		return $pReturn;
	}
	
	function getUserInfo($pId) {
		
	}
	
	function getUserId($pUsername) {
		$arrayToken['username'] = array('operator'=>'=', 'value'=>$pUsername);
		$pReturn = $this->databaseManager->getValues($arrayToken, 1);
		
		if(!empty($pReturn)) {
			return $pReturn['id'];
		}
		
		return 0;
	}
	
	function registerUser($pUsername, $pPassword) {
		global $pluginManager;
		global $conf;
		
		if(empty($this->getUserId($pUsername))) {
			$password = md5(strtolower($pUsername) . ':' . $conf['dav_realm'] . ':' . $pPassword);
			
			if( $this->databaseManager->insertValue(Array('username'=>Array('value'=>$pUsername),'digesta1'=>Array('value'=>$password))) ) {
				$id = $this->databaseManager->getInsertId();
				
				$json = DatabaseManager::$table3;
				$json = json_decode($json); /* USED TOO LoginManager.php */
				$this->databaseManager->openTable('user_permissions', $json);
				if( $this->databaseManager->insertValue(Array('userid'=>Array('value'=>$id,'type'=>'i'))) ) {
					
					$plugins = $pluginManager->getPluginTags();
					foreach($plugins as $plugin) {
						$pluginId   = $plugin['id'];
						$pluginName = $plugin['name'];
						
						if($pluginId != 'plg_serversettings') {
							$this->setPermission($id, 'use_' . $pluginId, true);
						} else {
							$this->setPermission($id, 'use_' . $pluginId, false);
						}
					}
					return true;
				}
			}
		}
		
		return false;
	}

	function setPermission($pUserid, $pPermission, $pBool, $autosync=TRUE) {
		global $loginManager;
		
		if(!$loginManager->isAllowed(LoginManager::MODIFY_USERS)) {
			die('[{"type":"heading","value":'.json_encode('Sie dürfen diese Aktion nicht ausführen, da die Rechte welche sie besitzen für diese Aktion nicht ausreichen. Bitte wenden sie sich an den Administrator.') . '}]');
		}
		
		$pInput = 0;
		if($pBool) {
			$pInput = 1;
		}
		
		$database = new DatabaseManager();
		
		if($pPermission == LoginManager::STOP_SERVER || $pPermission == LoginManager::MODIFY_USERS || $pPermission == LoginManager::FILE_ACCESS
				|| $pPermission == LoginManager::LOG_ACCESS || $pPermission == LoginManager::SERVER_NOTIFY || $pPermission == LoginManager::START_SERVER
				|| $pPermission == LoginManager::SERVER_CONFIG) {
			$json = json_decode(DatabaseManager::$table3);
			$database->openTable('user_permissions', $json);
			
			if($pPermission == LoginManager::STOP_SERVER) {
				if( $database->setValue(Array('stop_server'=>Array('value'=>$pInput,'type'=>'i')), Array('userid'=>Array('value'=>$pUserid,'type'=>'i'))) ) {
					return true;
				}
			} else if($pPermission == LoginManager::MODIFY_USERS) {
				if( $database->setValue(Array('modify_users'=>Array('value'=>$pInput)), Array('userid'=>Array('value'=>$pUserid))) ) {
					return true;
				}
			} else if($pPermission == LoginManager::FILE_ACCESS) {
				if( $database->setValue(Array('access_files'=>Array('value'=>$pInput,'type'=>'i')), Array('userid'=>Array('value'=>$pUserid,'type'=>'i'))) ) {
					return true;
				}
			} else if($pPermission == LoginManager::LOG_ACCESS) {
				if( $database->setValue(Array('log_access'=>Array('value'=>$pInput,'type'=>'i')), Array('userid'=>Array('value'=>$pUserid,'type'=>'i'))) ) {
					return true;
				}
			} else if($pPermission == LoginManager::SERVER_NOTIFY) {
				if( $database->setValue(Array('server_notify'=>Array('value'=>$pInput,'type'=>'i')), Array('userid'=>Array('value'=>$pUserid,'type'=>'i'))) ) {
					return true;
				}
			} else if($pPermission == LoginManager::START_SERVER) {
				if( $database->setValue(Array('start_server'=>Array('value'=>$pInput,'type'=>'i')), Array('userid'=>Array('value'=>$pUserid,'type'=>'i'))) ) {
					return true;
				}
			} else if($pPermission == LoginManager::SERVER_CONFIG) {
				if( $database->setValue(Array('server_config'=>Array('value'=>$pInput,'type'=>'i')), Array('userid'=>Array('value'=>$pUserid,'type'=>'i'))) ) {
					return true;
				}
			}
			
			if($autosync)
				sync(USER_PERMISSIONS);
		} else if(!empty($pPermission)) {
			$json = DatabaseManager::$table4;
			$json = json_decode($json);
			$database->openTable('custom_user_permissions', $json);
			
			if( $database->insertOrUpdateValue(Array('value'=>Array('value'=>$pInput,'type'=>'i')), Array('user'=>Array('value'=>$pUserid,'type'=>'i'), 'permission_name'=>Array('value'=>$pPermission,'type'=>'s'))) ) {
				return true;
			}
		}
		
		return false;
	}
}

?>