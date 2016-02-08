<?php
include $pluginManager->getController('ArkViewer');
 
//$arkviewer = new ArkViewer();
//echo "Test";
//if($arkviewer->connect()) {
	/*$arkviewer->setNickname("Vision");
	
	$command = $pluginManager->getCommand(0);
	
	if(empty($command)) {
		$list = $arkviewer->clientList();
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
			$arkviewer->writeMessage($command, $_POST['message']);
			die('{"redirect":["' . $pluginManager->getPluginName() . '", "user", ""]}');
		} else {
			echo '[{"type":"heading","value":"Nachricht verschicken"},{"type":"input","name":"message","value":""},{"type":"nl"},{"type":"nl"},{"type":"submit","value":"Abschicken"}]';
		}
	}*/
	
	//$arkviewer->close();
//}

$rcon = new Rcon("192.168.2.108", 27020, "Mickel1997", 30);

if ($rcon->connect())
{
	echo "connected";
	$rcon->send_command("quit");
	echo $rcon->getLine();
}

?>