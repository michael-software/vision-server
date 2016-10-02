<?php
require_once $pluginManager->getController('Ts3Viewer');

$ts3viewer = new Ts3Viewer();
if($ts3viewer->connect()) {
	$ts3viewer->setNickname("Vision (Webinterface: " . $loginManager->getUsername() . ")");
	
	$command = $pluginManager->getCommand(0);
	
	if(!empty($command)) {
		$logManager->addLog($loginManager->getUsername() . " hat den Benutzer mit der Nummer " . $command . " vom Server gekickt.");
		$ts3viewer->kick($command, "Bye bye (kicked by " . $loginManager->getUsername() . ")", true);
		die('{"redirect":["' . $pluginManager->getPluginName() . '", "user", ""]}');
	}
	
	$ts3viewer->close();
}

?>