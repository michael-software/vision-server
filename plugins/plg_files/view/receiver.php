<?php

if($pluginManager->getDataType() == PluginManager::TYPE_FILE) {
	if(!$pluginManager->fileManager->isFolder('tmp', FileManager::FILESYSTEM_PLUGIN_PRIVATE)) {
		$pluginManager->fileManager->createFolder('tmp', '', FileManager::FILESYSTEM_PLUGIN_PRIVATE);
	} else {
		$pluginManager->fileManager->delete('tmp', FileManager::FILESYSTEM_PLUGIN_PRIVATE);
		$pluginManager->fileManager->createFolder('tmp', '', FileManager::FILESYSTEM_PLUGIN_PRIVATE);
	}
	
	if( $pluginManager->fileManager->uploadFiles($_FILES['data'], 'tmp', FileManager::FILESYSTEM_PLUGIN_PRIVATE) ) {
		die('{"redirect":["' . $pluginManager->getPluginName() . '", "moveshare", ""]}');
	}
} else if($pluginManager->getDataType() == PluginManager::TYPE_STRING) {
	if(!$pluginManager->fileManager->isFolder('tmp', true)) {
		$pluginManager->fileManager->createFolder('tmp', '', true);
	} else {
		$pluginManager->fileManager->delete('tmp', true);
		$pluginManager->fileManager->createFolder('tmp', '', true);
	}
	
	$datei = fopen("data.txt","w");
	fwrite($datei, $_POST['data']);
	fclose($datei);
	
	/*
	 * $pluginManager->setTemporary('data', $_POST['data']);
	 * $pluginManager->getTemporary('data');
	 */
	

	
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "moveshare", ""]}');
}


$debug = var_export($_POST, true);
$jUI->add( new JUI\Heading('$_POST') );
$jUI->add( new JUI\Text($debug) );

$debug = var_export($_FILES, true);
$jUI->add( new JUI\Heading('$_FILES') );
$jUI->add( new JUI\Text($debug) );

?>