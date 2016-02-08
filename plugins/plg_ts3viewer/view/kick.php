<?php
include $pluginManager->getController('Ts3Viewer');
 
$ts3viewer = new Ts3Viewer();
if($ts3viewer->connect()) {
	$ts3viewer->setNickname("Vision");
	
	$command = $pluginManager->getCommand(0);
	
	if(!empty($command)) {
		$ts3viewer->kick($command, "Test");
		die('{"redirect":["' . $pluginManager->getPluginName() . '", "user", ""]}');
	}
	
	$ts3viewer->close();
}

?>