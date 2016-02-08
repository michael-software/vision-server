<?php
	require_once 'includes/PluginManager.php';
	$pluginManager = new PluginManager();

	if(!empty($_GET['server'])) {
		$server = $_GET['server'];
		
		if($server == "ts3") {
			header('Location: ts3server://' . $pluginManager->getIp());
		}
	}
?>