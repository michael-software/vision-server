<?php

function broadcast($pNotification, $forcereload=TRUE) {
	global $pluginManager;
	$notificationManager = $pluginManager->getNotificationManager();
	
	if($pNotification instanceof Notification) {
		$pMessage = $pNotification->getMessage();
		
		if($pluginManager->isInstalled('plg_ts3viewer')) {
			require_once $pluginManager->getController('plg_ts3viewer', 'Ts3Viewer');
			$ts3Viewer = new Ts3Viewer();
			$ts3Viewer->connect();
			$ts3Viewer->setNickname("Vision (Webinterface)");
			$ts3Viewer->writeBroadcast($pMessage);
			$ts3Viewer->close();
		}
		
		$notificationManager->addServerNotification($pNotification, $forcereload);
	}
}

?>