<?php

$command = $pluginManager->getCommand();
if(!empty($command)) {
	
	if(!empty($command)) {
		$folder = implode('/', $pluginManager->getCommand()).'/';
	} else {
		$folder = "";
	}
	
	if($folder == "./") {
		$folder = "";
	}
	
	$folder = str_replace('//', '/', $folder);
	$folder = str_replace('..', '.', $folder);
	
	if($pluginManager->fileManager->fileExists($folder)) {
		$pluginManager->fileManager->delete($folder);
	}
	
	$folder = $pluginManager->fileManager->getFolderFromPath($folder);
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", "' . $folder . '"]}');
}

die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');

?>