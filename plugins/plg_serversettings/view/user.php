<?php
$command = $pluginManager->getCommand(0);

if(!empty($command)) {
	if($command == 'create') {
		if(!empty($_POST['username']) && !empty($_POST['password1']) && !empty($_POST['password2']) && $_POST['password1'] == $_POST['password2']) {
			$username = $_POST['username'];
			$password = $_POST['password1'];
			
			$userManager = $pluginManager->getUserManager();
			if($userManager->registerUser($username, $password)) {
				$pluginManager->redirect($pluginManager, "home");
			}
			
			$pluginManager->redirect($pluginManager, "user", "create");
		} else {
			$jUI->add(new JUI\Heading("Benutzer erstellen"));
			
			
			$username = new JUI\Input("username");
			$username->setLabel("Benutzername: ");
			$jUI->add($username);
			
			$jUI->nline(2);
			
			$password1 = new JUI\Input("password1");
			$password1->setLabel("Kennwort: ");
			$password1->setPreset(JUI\Input::PASSWORD);
			$jUI->add($password1);
			
			$jUI->nline();
			
			$password2 = new JUI\Input("password2");
			$password2->setLabel("Kennwort wiederholen: ");
			$password2->setPreset(JUI\Input::PASSWORD);
			$jUI->add($password2);
			
			$jUI->nline(2);
			
			$jUI->add(new JUI\Button("Benutzer erstellen", true));
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
			
			$pluginManager->redirect($pluginManager, "user", $_POST['userid'] . "/success");
		} else {
			$userPrivilegs = $loginManager->getPermissions($command);
			
			$jUI->add(new JUI\Heading("Rechte"));
			
			$access_files = new JUI\Checkbox("access_files");
			$access_files->setLabel("Zugriff auf Dateien");
			
			if(!empty($userPrivilegs['access_files']) && $userPrivilegs['access_files'] == 1)
				$access_files->setChecked(true);
			
			$jUI->add($access_files);
			$jUI->nline();
			
			
			
			$stop_server = new JUI\Checkbox("stop_server");
			$stop_server->setLabel("Server herunterfahren/neustarten");
			
			if(!empty($userPrivilegs['stop_server']) && $userPrivilegs['stop_server'] == 1)
				$stop_server->setChecked(true);
			
			$jUI->add($stop_server);
			$jUI->nline();
			
			
			
			$modify_users = new JUI\Checkbox("modify_users");
			$modify_users->setLabel("Benutzer editieren");
			
			if(!empty($userPrivilegs['modify_users']) && $userPrivilegs['modify_users'] == 1)
				$modify_users->setChecked(true);
			
			$jUI->add($modify_users);
			$jUI->nline();
			
			
			
			$log_access = new JUI\Checkbox("log_access");
			$log_access->setLabel("Log Dateien einsehen");
			
			if(!empty($userPrivilegs['log_access']) && $userPrivilegs['log_access'] == 1)
				$log_access->setChecked(true);
			
			$jUI->add($log_access);
			$jUI->nline();
			
			
			
			$server_notify = new JUI\Checkbox("server_notify");
			$server_notify->setLabel("Serverbenachrichtigungen erhalten");
			
			if(!empty($userPrivilegs['server_notify']) && $userPrivilegs['server_notify'] == 1)
				$server_notify->setChecked(true);
			
			$jUI->add($server_notify);
			$jUI->nline();
			
			
			
			$start_server = new JUI\Checkbox("start_server");
			$start_server->setLabel("Server starten");
			
			if(!empty($userPrivilegs['start_server']) && $userPrivilegs['start_server'] == 1)
				$start_server->setChecked(true);
			
			$jUI->add($start_server);
			$jUI->nline(2);
			
			
			$jUI->add(new JUI\Heading("Recht Plugin zu benutzen", true));
			
			$blacklistPluginName = array("plg_user", "plg_order", "plg_serversettings", "plg_license");
			
			$plugins = $pluginManager->getPluginTags();
			foreach($plugins as $plugin) {
				$pluginId   = $plugin['id'];
				$pluginName = $plugin['name'];
				
				if(!in_array($pluginId, $blacklistPluginName)) {
					$jUI->nline();
					
					$checkbox = new JUI\Checkbox("use_" . $pluginId);
					$checkbox->setLabel($pluginName);
					
					if(!empty($userPrivilegs['use_' . $pluginId]) && $userPrivilegs['use_' . $pluginId] == 1)
						$checkbox->setChecked(true);
					
					$jUI->add($checkbox);
				}
			}
			
			$jUI->nline();
			
			foreach($plugins as $plugin) {
				$id = $plugin['id'];
				$name = $plugin['name'];
				
				if(!empty($plugin['permissions']) && is_array($plugin['permissions'])) {
					$permissions = $plugin['permissions'];
					
					$jUI->add(new JUI\Heading($name, true));
					
					foreach($permissions as $permission) {
						$permissionId      = $permission['id'];
						$permissionName    = $permission['name'];
						$permissionDefault = $permission['default'];
						
						$jUI->nline();
						
						$checkbox = new JUI\Checkbox($permissionId);
						$checkbox->setLabel($permissionName);
						
						if(!empty($userPrivilegs[$permissionId]) && $userPrivilegs[$permissionId] == 1)
							$checkbox->setChecked(true);
						
						$jUI->add($checkbox);
					}
				}
			}
			
			if($pluginManager->getCommand(1) == "success") {
				//echo '{"type":"warning","value":"Änderungen gespeichert"},';
				$jUI->setWarning("Änderungen gespeichert");
			}
			
			
			$jUI->nline(2);
			
			$hidden = new JUI\Input("userid");
			$hidden->setVisible(JUI\View::GONE);
			$hidden->setValue($command);
			$jUI->add($hidden);
			
			$jUI->add(new JUI\Button("Speichern", true));
		}
	}
}
?>