<?php
include $pluginManager->getController('Ts3Viewer');
 
$ts3viewer = new Ts3Viewer();
if($ts3viewer->connect()) {
	$ts3viewer->setNickname("Vision");
	
	$command = $pluginManager->getCommand(0);
	
	if(empty($command)) {
		$list = $ts3viewer->clientList();
		$nameArray = array();
		$clickArray = array();
		
		foreach($list as $user) {
			if($user['client_type'] == '0')
			$nameArray[] = $user['client_nickname'];
			$clickArray[] = "openPlugin('" . $pluginManager->getPluginName() . "','user','" . $user['clid'] . "')";
		}
		
		echo '[{"type":"list","value":' . json_encode($nameArray) . ',"click":' . json_encode($clickArray) . '}]';
	} else {
		if(!empty($_POST['message'])) {
			$ts3viewer->writeMessage($command, $_POST['message']);
			die('{"redirect":["' . $pluginManager->getPluginName() . '", "user", ""]}');
		} else {
			echo '[{"type":"heading","value":"Nachricht verschicken"},{"type":"input","name":"message","value":""},{"type":"nl"},{"type":"nl"},{"type":"submit","value":"Abschicken"},{"type":"button","value":"Client kicken","click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'kick\',\'' . $command . '\')"},{"type":"button","value":"Client vom Server kicken","click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'serverkick\',\'' . $command . '\')"}]';
		}
	}
	
	$ts3viewer->close();
}

?>