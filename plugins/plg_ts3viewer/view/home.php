<?php
$pluginManager->databaseManager->selectTable(1);

$jUI->add( new JUI\Widget($pluginManager->getPluginName(), "settings") );

$list = new JUI\ListView();
$list->addItem("Benutzer", new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'user'));
$list->addItem("Channel", new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'channel'));
$list->addItem("Broadcast senden", new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'broadcast'));
$list->addItem("Einstellungen", new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'settings'));

$jUI->add($list);
$jUI->hline();

$userId = $pluginManager->getSimpleStorage('connected_user_id', '', FALSE);
if(!empty($userId)) {
	require_once $pluginManager->getController( 'Ts3Viewer' );
	require_once $pluginManager->getController( 'userButtons' );
	
	$ts3viewer = new Ts3Viewer();
	if($ts3viewer->connect()) {
		$ts3viewer->setNickname("Vision (Webinterface)");
			
		$userId = $pluginManager->getSimpleStorage('connected_user_id', '', FALSE);
		
		if(!empty($userId)) {
			$users = $ts3viewer->clientList();
			
			if(!empty($users) && is_array($users))
			foreach($users as $user) {
				if($user['client_database_id'] == $userId) {
					$jUI->add( new JUI\Heading($user['client_nickname']) );
					addUserButtons($user['clid']);
					$jUI->newline();
				}
			}
		}
		
		$ts3viewer->close();
	}
}
?>