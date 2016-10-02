<?php

if(!empty($pluginManager->getCommand(0)) && empty($pluginManager->getCommand(1)) && empty($pluginManager->getCommand(2))) {
	if(!empty($pluginManager->getTemporary('file'))) {
		$device = $pluginManager->getCommand(0);
		
		$actionString = "openMedia('image','" . FileManager::FILESYSTEM_PLUGIN_PRIVATE . '://' . $pluginManager->getPluginName() . '/' . $pluginManager->getTemporary('file') . "')";
		//die($actionString);
		
		$action = new ActionNotification();
		$action->setAction($actionString);
		$action->setMaxTimestamp(time() + 60);
		$action->setAdressed($device);
		
		$notificationManager = $pluginManager->getNotificationManager();
		$notificationManager->addAction($action, $loginManager->getId());
		
		$pluginManager->unsetTemporary('file');
		
		$pluginManager->redirect( $pluginManager, 'device', $device);
	}
	
	$plugins = $pluginManager->getPluginTags();
	
	$name;
	$click;
	
	if(!empty($plugins))
	foreach($plugins as $plugin) {
		$name[] = $plugin['name'];
		$click[] = "openPlugin('" . $pluginManager->getPluginName() . "', 'device', '" . $pluginManager->getCommand(0) . "/" . $plugin['id'] . "')";
	}
	
	?>
	[
		{
			"type":"heading",
			"value":"Plugin auswählen"
		},{
			"type":"list",
			"value":<?php echo json_encode($name); ?>,
			"click":<?php echo json_encode($click); ?>
		}
	]
	<?php
} else if (!empty($pluginManager->getCommand(0)) && !empty($pluginManager->getCommand(1)) && empty($pluginManager->getCommand(2))) {
	$plugin = $pluginManager->getCommand(1);
	$views = $pluginManager->getViews($plugin);
	
	$name;
	$click;
	
	if(!empty($views))
	foreach($views as $view) {
		$name[] = $view;
		$click[] = "openPlugin('" . $pluginManager->getPluginName() . "', 'device', '" . $pluginManager->getCommand(0) . "/" . $plugin . "/" . $view . "')";
	}
	
	?>
	[
		{
			"type":"heading",
			"value":"Pluginseite auswählen"
		},{
			"type":"list",
			"value":<?php echo json_encode($name); ?>,
			"click":<?php echo json_encode($click); ?>
		}
	]
	<?php
} else if (!empty($pluginManager->getCommand(0)) && !empty($pluginManager->getCommand(1)) && !empty($pluginManager->getCommand(2))) {
	$device = $pluginManager->getCommand(0);
	
	$action = new ActionNotification();
	$action->setActionOpenPlugin($pluginManager->getCommand(1), $pluginManager->getCommand(2));
	$action->setMaxTimestamp(time() + 60);
	$action->setAdressed($device);
	
	$notificationManager = $pluginManager->getNotificationManager();
	$notificationManager->addAction($action, $loginManager->getId());
	
	echo '{"redirect":["' . $pluginManager->getPluginName() . '", "device", "' . $device . '"]}';
}

?>