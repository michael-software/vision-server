<?php
include $pluginManager->getController('Ts3Viewer');

$afkshort = 21;
$afklong = 22;
 
$ts3viewer = new Ts3Viewer();
if($ts3viewer->connect()) {
	$ts3viewer->setNickname("Vision (Webinterface: " . $loginManager->getUsername() . ")");
	
	$command = $pluginManager->getCommand(0);
	
	if(empty($command)) {
		$list = $ts3viewer->clientList();
		$channellist = $ts3viewer->channelList(true);
		$nameArray = array();
		$clickArray = array();
		
		if(!empty($list))
		foreach($list as $user) {
			if($user['client_type'] == '0') {
				$cid = $user['cid'];
				$nameArray[] = $user['client_nickname'] . ' - ' . $channellist[$cid]['channel_name'];
				$clickArray[] = "openPlugin('" . $pluginManager->getPluginName() . "','user','" . $user['clid'] . "')";
			}
		}
		
		echo '[{"type":"list","value":' . json_encode($nameArray) . ',"click":' . json_encode($clickArray) . '}]';
	} else if(is_numeric($command)) {
		if(!empty($_POST['message'])) {
			$ts3viewer->writeMessage($command, $_POST['message']);
			die('{"redirect":["' . $pluginManager->getPluginName() . '", "user", ""]}');
		} else if(!empty($pluginManager->getCommand(1))) {
			$command2 = $pluginManager->getCommand(1);
			
			if($command2 == "afkshort") {
				$ts3viewer->moveClient($command, $afkshort);
			} else if($command2 == "afklong") {
				$ts3viewer->moveClient($command, $afklong);
			}
			
			$pluginManager->redirect($pluginManager, 'user', $command);
		} else {
			
			$clientinfo = $ts3viewer->clientInfo($command);
			
			echo '[';
			echo '{"type":"heading", "value":"' . $clientinfo['client_nickname'] . '"},';
			echo '{"type":"headingSmall","value":"Nachricht verschicken"},{"type":"input","name":"message","value":"","focus":true, "hint":"Nachricht"},';
			echo '{"type":"button","value":"Abschicken","click":"submit()"},{"type":"nl"},{"type":"nl"},';
			echo '{"type":"button","value":"Client kicken","click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'kick\',\'' . $command . '\')"},';
			echo '{"type":"button","value":"Client vom Server kicken","click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'serverkick\',\'' . $command . '\')"},';
			echo '{"type":"button","value":"Client verschieben","click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'move\',\'' . $command . '\')"},';
			echo '{"type":"nl"},{"type":"nl"},';
			
			if(!empty($afkshort))
				echo '{"type":"button","value":"AFK kurz","click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'user\',\'' . $command . '/afkshort\')"},';
			if(!empty($afklong))
				echo '{"type":"button","value":"AFK lang","click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'user\',\'' . $command . '/afklong\')"},';
			
			$pluginManager->databaseManager->selectTable(1);
			$value = $pluginManager->databaseManager->getValues(array("client_database_id"=>array("value"=>$clientinfo['client_database_id']), "type"=>1), 1);
			
			if(empty($value)) {
				echo '{"type":"button","value":"Bei Anmeldung benachrichtigen","click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'user\',\'notifyJoin/' . $clientinfo['client_database_id'] . '\')"},';
			} else {
				echo '{"type":"button","value":"Bei Anmeldung nicht benachrichtigen","click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'user\',\'deleteNotifyJoin/' . $value['id'] . '\')"},';
			}
			
			echo '{"type":"nl"},{"type":"nl"},';
			echo '{"type":"text", "value":' . json_encode('Client IP: ' . $clientinfo['connection_client_ip']) . '},';
			echo '{"type":"text", "value":' . json_encode('bisherige Verbindungen: ' . $clientinfo['client_totalconnections']) . '},';
			echo '{"type":"text", "value":' . json_encode('Teamspeak Version: ' . $clientinfo['client_version']) . '},';
			echo '{"type":"text", "value":' . json_encode('Client Database Id: ' . $clientinfo['client_database_id']) . '},';
						
			if($clientinfo['client_input_muted'] != '0') {
				echo '{"type":"image", "value":"' . $pluginManager->getImage("microphone-muted.png") . '", "width":"200"},';
			}

			if($clientinfo['client_output_muted'] != '0') {
				echo '{"type":"image", "value":"' . $pluginManager->getImage("volume-muted.png") . '", "width":"200"},';
			}
			
			$client_database_id = $clientinfo['client_database_id'];
			$groupsinfo = $ts3viewer->serverGroupsByClientId($client_database_id);
			
			$groups = null;
			foreach($groupsinfo as $group) {
				$groups[] = $group['name'];
			}
			
			echo '{"type":"list", "value":' . json_encode($groups) . '},';
			echo '{"type":"heading","value":"Befehl ausführen"},';
			
			$pluginManager->databaseManager->selectTable(0);
			$buttons = $pluginManager->databaseManager->getValues();
			
			if(!empty($buttons) && is_array($buttons))
			foreach($buttons as $button) {
				if($button['type'] == 1) {
					echo '{"type":"button","value":' . json_encode($button['name']) . ',"click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'move\',\'' . $command . '/' . $button['parameter'] . '\')"},';
				} else if($button['type'] == 2) {
					echo '{"type":"button","value":' . json_encode($button['name']) . ',"click":"openPlugin(\'' . $pluginManager->getPluginName() . '\',\'say\',\'' . $command . '/' . urlencode($button['parameter']) . '\')"},';
				}
			}
			
			echo '{"type":"nline"}';
			echo ']';
		}
	} else if(is_string($command) && !empty($pluginManager->getCommand(1))) {
		$command1 = $pluginManager->getCommand(1);
		
		if($command == "deleteNotifyJoin") {
			$pluginManager->databaseManager->selectTable(1);
			$pluginManager->databaseManager->remove(array("id"=>array("value"=>$command1)));
			
			$pluginManager->redirect($pluginManager, 'user');
		} else if($command == "notifyJoin") {
			$array = array("client_database_id"=>array("value"=>$command1), "type"=>array("value"=>1));
			
			$pluginManager->databaseManager->selectTable(1);
			$pluginManager->databaseManager->insertOrUpdateValue($array, $array);
			$pluginManager->databaseManager->remove(array("id"=>array("value"=>$command1)));
			
			$pluginManager->redirect($pluginManager, 'user');
		}
	}
	
	$ts3viewer->close();
}

?>