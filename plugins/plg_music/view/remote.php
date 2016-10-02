<?php

require_once $pluginManager->getController('tools');

$command = $pluginManager->getCommand(0);
$command1 = $pluginManager->getCommand(1);

if(!empty($command) && empty($command1)) {
	$jUI->add( new JUI\Heading('Gerät auswählen') );
	
	$authtokens = $loginManager->getAuthtokens();
	$list = new JUI\ListView();
	
	if(!empty($authtokens) && is_array($authtokens))
	foreach($authtokens as $authtoken) {
		if(!empty($authtoken['name'])) {
			$name = $authtoken['name'];
		} else {
			$name = "kein Name - " . $authtoken['authtoken'];
		}
		
		$click = new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'remote', $authtoken['id'] . '/' . $command );
		
		$list->addItem($name, $click);
	}
	
	$jUI->add($list);
} else if(!empty($command) && is_numeric($command) && !empty($command1)) {
	$authtoken = $loginManager->getAuthtokenInfoById($command);
	
	$action = new ActionNotification("openMedia('music', '" . decode($command1) . "')", time()+30);
	$action->setAdressed($authtoken['authtoken']);
	
	$pluginManager->getNotificationManager()->addAction($action, $loginManager->getId());
	
	$pluginManager->redirect( $pluginManager );
}

?>