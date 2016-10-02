<?php
include $pluginManager->getController('Ts3Viewer');
 
$ts3viewer = new Ts3Viewer();
if($ts3viewer->connect()) {
	$ts3viewer->setNickname("Vision (Webinterface: " . $loginManager->getUsername() . ")");
	
	$command = $pluginManager->getCommand(0);
	
	if(!empty($command)) {
		$logManager->addLog($loginManager->getUsername() . " hat den Benutzer mit der Nummer " . $command . " aus dem Channel gekickt.");
		$ts3viewer->kick($command, "Sie wurden aus dem Channel gekickt");
		die('{"redirect":["' . $pluginManager->getPluginName() . '", "user", ""]}');
	}
	
	$ts3viewer->close();
}

?>