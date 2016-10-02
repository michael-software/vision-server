<?php
$command = $pluginManager->getCommand(0);

if(!empty($command)) {
	if($command == 'create') {
		if(!empty($_POST['username']) && !empty($_POST['password1']) && !empty($_POST['password2']) && $_POST['password1'] == $_POST['password2']) {
			$username = $_POST['username'];
			$password = $_POST['password1'];
			
			$userManager = $pluginManager->getUserManager();
			if($userManager->registerUser($username, $password)) {
				die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
			}
			
			die('{"redirect":["' . $pluginManager->getPluginName() . '", "user", "create"]}');
		} else {
			echo '[{"type":"heading","value":"Benutzer erstellen"},';
			echo '{"type":"input","name":"username","label":"Benutzername: "},{"type":"nl"},{"type":"nl"},';
			echo '{"type":"password","name":"password1","label":"Kennwort: "},{"type":"nl"},';
			echo '{"type":"password","name":"password2","label":"Kennwort wiederholen: "},{"type":"nl"},{"type":"nl"},';
			echo '{"type":"submit","value":"Benutzer erstellen"}]';
		}
	} else {
		if(!empty($_POST['userid'])) {
			$userManager = $pluginManager->getUserManager();
			
			if(!empty($_POST['stop_server'])) {
				$userManager->setPermission($_POST['userid'], LoginManager::STOP_SERVER, true, FALSE);
			} else {
				$userManager->setPermission($_POST['userid'], LoginManager::STOP_SERVER, false, FALSE);
			}
			
			if(!empty($_POST['modify_users'])) {
				$userManager->setPermission($_POST['userid'], LoginManager::MODIFY_USERS, true, FALSE);
			} else {
				$userManager->setPermission($_POST['userid'], LoginManager::MODIFY_USERS, false, FALSE);
			}
			
			if(!empty($_POST['access_files'])) {
				$userManager->setPermission($_POST['userid'], LoginManager::FILE_ACCESS, true, FALSE);
			} else {
				$userManager->setPermission($_POST['userid'], LoginManager::FILE_ACCESS, false, FALSE);
			}

			if(!empty($_POST['log_access'])) {
				$userManager->setPermission($_POST['userid'], LoginManager::LOG_ACCESS, true, FALSE);
			} else {
				$userManager->setPermission($_POST['userid'], LoginManager::LOG_ACCESS, false, FALSE);
			}

			if(!empty($_POST['server_notify'])) {
				$userManager->setPermission($_POST['userid'], LoginManager::SERVER_NOTIFY, true, FALSE);
			} else {
				$userManager->setPermission($_POST['userid'], LoginManager::SERVER_NOTIFY, false, FALSE);
			}
			
			if(!empty($_POST['start_server'])) {
				$userManager->setPermission($_POST['userid'], LoginManager::START_SERVER, true, FALSE);
			} else {
				$userManager->setPermission($_POST['userid'], LoginManager::START_SERVER, false, FALSE);
			}
			
			sync(USER_PERMISSIONS);

			$plugins = $pluginManager->getPluginTags();
			foreach($plugins as $plugin) {
				$pluginId   = $plugin['id'];
				$pluginName = $plugin['name'];
				
				if(!empty($_POST['use_' . $pluginId])) {
					$userManager->setPermission($_POST['userid'], 'use_' . $pluginId, true);
				} else {
					$userManager->setPermission($_POST['userid'], 'use_' . $pluginId, false);
				}

				if(!empty($plugin['permissions']) && is_array($plugin['permissions'])) {
					$permissions = $plugin['permissions'];
					
					foreach($permissions as $permission) {
						$permissionId      = $permission['id'];
						
						if(!empty($_POST[$permissionId])) {
							$userManager->setPermission($_POST['userid'], $permissionId, true);
						} else {
							$userManager->setPermission($_POST['userid'], $permissionId, false);
						}
					}
				}
			}
			
			die('{"redirect":["' . $pluginManager->getPluginName() . '", "user", "' . $_POST['userid'] . '/success"]}');
		} else {
			$userPrivilegs = $loginManager->getPermissions($command);
			
			echo '[{"type":"heading","value":"Rechte"},';
			echo '{"type":"checkbox","name":"access_files","label":"Zugriff auf Dateien"';
			
			if(!empty($userPrivilegs['access_files']) && $userPrivilegs['access_files'] == 1)
				echo ',"checked":"checked"';
			
			echo '},{"type":"nl"},';
			echo '{"type":"checkbox","name":"stop_server","label":"Server herunterfahren/neustarten"';
			
			if(!empty($userPrivilegs['stop_server']) && $userPrivilegs['stop_server'] == 1)
				echo ',"checked":"checked"';
			
			echo '},{"type":"nl"},';
			echo '{"type":"checkbox","name":"modify_users","label":"Benutzer editieren"';
			
			if(!empty($userPrivilegs['modify_users']) && $userPrivilegs['modify_users'] == 1)
				echo ',"checked":"checked"';
			
			echo '},{"type":"nl"},';
			echo '{"type":"checkbox","name":"log_access","label":"Log Dateien einsehen"';
			
			if(!empty($userPrivilegs['log_access']) && $userPrivilegs['log_access'] == 1)
				echo ',"checked":"checked"';
			
			echo '},{"type":"nl"},';
			echo '{"type":"checkbox","name":"server_notify","label":"Serverbenachrichtigungen erhalten"';
			
			if(!empty($userPrivilegs['server_notify']) && $userPrivilegs['server_notify'] == 1)
				echo ',"checked":"checked"';
			
			echo '},{"type":"nl"},';
			echo '{"type":"checkbox","name":"start_server","label":"Server starten"';
			
			if(!empty($userPrivilegs['start_server']) && $userPrivilegs['start_server'] == 1)
				echo ',"checked":"checked"';
			
			echo '},';
			
			echo '{"type":"nl"},{"type":"nl"},{"type":"headingSmall","value":"Recht Plugin zu benutzen"';
			
			$blacklistPluginName = array("plg_user", "plg_order", "plg_serversettings", "plg_license");
			
			$plugins = $pluginManager->getPluginTags();
			foreach($plugins as $plugin) {
				$pluginId   = $plugin['id'];
				$pluginName = $plugin['name'];
				
				if(!in_array($pluginId, $blacklistPluginName)) {
					echo '},{"type":"nl"},';
					echo '{"type":"checkbox","name":"use_' . $pluginId . '","label":"' . $pluginName . '"';
					
					if(!empty($userPrivilegs['use_' . $pluginId]) && $userPrivilegs['use_' . $pluginId] == 1)
						echo ',"checked":"checked"';
				}
			}
			
			echo '},';
			echo '{"type":"nl"},';
			
			foreach($plugins as $plugin) {
				$id = $plugin['id'];
				$name = $plugin['name'];
				
				if(!empty($plugin['permissions']) && is_array($plugin['permissions'])) {
					$permissions = $plugin['permissions'];
					
					echo '{"type":"headingSmall","value":' . json_encode($name);
					
					foreach($permissions as $permission) {
						$permissionId      = $permission['id'];
						$permissionName    = $permission['name'];
						$permissionDefault = $permission['default'];
						
						echo '},{"type":"nl"},';
						echo '{"type":"checkbox","name":"' . $permissionId . '","label":"' . $permissionName . '"';
						
						if(!empty($userPrivilegs[$permissionId]) && $userPrivilegs[$permissionId] == 1)
							echo ',"checked":"checked"';
					}
					
					echo '},';
				}
			}
			
			if($pluginManager->getCommand(1) == "success") {
				echo '{"type":"warning","value":"Änderungen gespeichert"},';
			}
			
			echo '{"type":"nl"},{"type":"nl"},{"type":"input","name":"userid","value":"' . $command . '","visible":"away"},{"type":"submit","value":"Speichern"}]';
		}
	}
}
?>