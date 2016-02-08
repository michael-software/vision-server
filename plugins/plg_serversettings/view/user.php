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
			echo '{"type":"input","name":"password1","label":"Kennwort: "},{"type":"nl"},';
			echo '{"type":"input","name":"password2","label":"Kennwort wiederholen: "},{"type":"nl"},{"type":"nl"},';
			echo '{"type":"submit","value":"Benutzer erstellen"}]';
		}
	} else {
		if(!empty($_POST['userid'])) {
			$userManager = $pluginManager->getUserManager();
			
			if(!empty($_POST['stop_server'])) {
				$userManager->setPermission($_POST['userid'], LoginManager::STOP_SERVER, true);
			} else {
				$userManager->setPermission($_POST['userid'], LoginManager::STOP_SERVER, false);
			}
			
			if(!empty($_POST['modify_users'])) {
				$userManager->setPermission($_POST['userid'], LoginManager::MODIFY_USERS, true);
			} else {
				$userManager->setPermission($_POST['userid'], LoginManager::MODIFY_USERS, false);
			}
			
			if(!empty($_POST['access_files'])) {
				$userManager->setPermission($_POST['userid'], LoginManager::FILE_ACCESS, true);
			} else {
				$userManager->setPermission($_POST['userid'], LoginManager::FILE_ACCESS, false);
			}

			$plugins = $pluginManager->getPluginTags();
			foreach($plugins as $plugin) {
				$pluginId   = $plugin['id'];
				$pluginName = $plugin['name'];
				
				if(!empty($_POST['use_' . $pluginId])) {
					$userManager->setPermission($_POST['userid'], 'use_' . $pluginId, true);
				} else {
					$userManager->setPermission($_POST['userid'], 'use_' . $pluginId, false);
				}
			}
			
			die('{"redirect":["' . $pluginManager->getPluginName() . '", "user", "' . $_POST['userid'] . '"]}');
		} else {
			$userPrivilegs = $loginManager->getPermissions($command);
			
			echo '[{"type":"heading","value":"Rechte"},';
			echo '{"type":"checkbox","name":"access_files","label":"Zugriff auf Dateien"';
			
			if($userPrivilegs['access_files'] == 1)
				echo ',"checked":"checked"';
			
			echo '},{"type":"nl"},';
			echo '{"type":"checkbox","name":"stop_server","label":"Server herunterfahren/neustarten"';
			
			if($userPrivilegs['stop_server'] == 1)
				echo ',"checked":"checked"';
			
			echo '},{"type":"nl"},';
			echo '{"type":"checkbox","name":"modify_users","label":"Benutzer editieren"';
			
			if($userPrivilegs['modify_users'] == 1)
				echo ',"checked":"checked"';
			
			echo '},';
			
			echo '{"type":"nl"},{"type":"nl"},{"type":"headingSmall","value":"Recht Plugin zu benutzen"';
			
			$plugins = $pluginManager->getPluginTags();
			foreach($plugins as $plugin) {
				$pluginId   = $plugin['id'];
				$pluginName = $plugin['name'];
				
				echo '},{"type":"nl"},';
				echo '{"type":"checkbox","name":"use_' . $pluginId . '","label":"' . $pluginName . '"';
				
				if(!empty($userPrivilegs['use_' . $pluginId]) && $userPrivilegs['use_' . $pluginId] == 1)
					echo ',"checked":"checked"';
			}
			
			echo '},';
			
			echo '{"type":"nl"},{"type":"nl"},{"type":"input","name":"userid","value":"' . $command . '","visible":"away"},{"type":"submit","value":"Speichern"}]';
		}
	}
}
?>