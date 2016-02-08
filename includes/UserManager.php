<?php

class UserManager {
	private $databaseManager;
	
	function __construct () {
		$json = '[{"type":"int","name":"id"},{"type":"varchar","name":"username"},{"type":"varchar","name":"password"},{"type":"timestamp","name":"timestamp","default":"current_timestamp"}]';
		$json = json_decode($json);
		
		$this->databaseManager = new DatabaseManager();
		$this->databaseManager->openTable("user", $json);
	}
	
	function getUserList() {
		$pReturn = $this->databaseManager->getValues();
		
		for($i = 0; $i < count($pReturn); $i++) {
			$pReturn[$i]['password'] = md5($pReturn[$i]['password']);
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
		
		if(empty($this->getUserId($pUsername))) {
			if( $this->databaseManager->insertValue(Array("username"=>Array("value"=>$pUsername),"password"=>Array("value"=>$pPassword))) ) {
				$id = $this->databaseManager->getInsertId();
				
				$json = DatabaseManager::$table3;
				$json = json_decode($json); /* USED TOO LoginManager.php */
				$this->databaseManager->openTable("user_permissions", $json);
				if( $this->databaseManager->insertValue(Array("userid"=>Array("value"=>$id,"type"=>"i"))) ) {
					
					$plugins = $pluginManager->getPluginTags();
					foreach($plugins as $plugin) {
						$pluginId   = $plugin['id'];
						$pluginName = $plugin['name'];
						
						if($pluginId != "plg_serversettings") {
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

	function setPermission($pUserid, $pPermission, $pBool) {
		global $loginManager;
		
		if(!$loginManager->isAllowed(LoginManager::MODIFY_USERS)) {
			die('[{"type":"heading","value":'.json_encode('Sie dürfen diese Aktion nicht ausführen, da die Rechte welche sie besitzen für diese Aktion nicht ausreichen. Bitte wenden sie sich an den Administrator.') . '}]');
		}
		
		$pInput = 0;
		if($pBool) {
			$pInput = 1;
		}
		
		$database = new DatabaseManager();
		
		if($pPermission == LoginManager::STOP_SERVER || $pPermission == LoginManager::MODIFY_USERS || $pPermission == LoginManager::FILE_ACCESS) {
			$json = DatabaseManager::$table3;
			$json = json_decode($json); /* USED TOO LoginManager.php */
			$database->openTable("user_permissions", $json);
			
			if($pPermission == LoginManager::STOP_SERVER) {
				if( $database->setValue(Array("stop_server"=>Array("value"=>$pInput,"type"=>"i")), Array("userid"=>Array("value"=>$pUserid,"type"=>"i"))) ) {
					return true;
				}
			} else if($pPermission == LoginManager::MODIFY_USERS) {
				if( $database->setValue(Array("modify_users"=>Array("value"=>$pInput)), Array("userid"=>Array("value"=>$pUserid))) ) {
					echo $database->getErrors();
					return true;
				}
			} else if($pPermission == LoginManager::FILE_ACCESS) {
				if( $database->setValue(Array("access_files"=>Array("value"=>$pInput,"type"=>"i")), Array("userid"=>Array("value"=>$pUserid,"type"=>"i"))) ) {
					return true;
				}
			}
		} else if(!empty($pPermission)) {
			$json = DatabaseManager::$table4;
			$json = json_decode($json);
			$database->openTable("custom_user_permissions", $json);
			
			if( $database->insertOrUpdateValue(Array("value"=>Array("value"=>$pInput,"type"=>"i")), Array("user"=>Array("value"=>$pUserid,"type"=>"i"), "permission_name"=>Array("value"=>$pPermission,"type"=>"s"))) ) {
				return true;
			}
		}
		
		return false;
	}
}

?>