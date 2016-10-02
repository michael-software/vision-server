<?php

if($loginManager->isAllowed(LoginManager::LOG_ACCESS)) {
	require_once $pluginManager->getController('logs');
	
	$logType = $pluginManager->getCommand(0);
	
	if(!empty($logType) && $logType == 'ts3') {
		showTeamspeakLogs($jUI);
	} else {
		if(!empty($pluginManager->getCommand(1)) && is_numeric($pluginManager->getCommand(1))) {
			if(empty($pluginManager->getCommand(2)) || $pluginManager->getCommand(2) != 'delete') {
				showServerLog($jUI, $pluginManager->getCommand(1));
			} else {
				$logManager->delete($pluginManager->getCommand(1));
				
				$pluginManager->redirect($pluginManager, 'logs', 'server');
			}
		} else {
			if(empty($pluginManager->getCommand(1)) || $pluginManager->getCommand(1) != 'clean') {
				showServerLogs($jUI);
			} else {
				$logManager->clean();
				
				$pluginManager->redirect($pluginManager, 'logs', 'server');
			}
		}
	}
}

?>