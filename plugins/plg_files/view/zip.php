<?php

require_once $pluginManager->getController("ZipManager");

if(!empty($command)) {
	$folder = implode('/', $pluginManager->getCommand()).'/';
	zip($folder);
	
	$pluginManager->redirect($pluginManager->getPluginName(), 'home', dirname($pPath));
}

?>