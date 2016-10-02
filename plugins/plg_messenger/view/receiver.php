<?php

$pluginManager->unsetTemporary('data');

if(!empty($pluginManager->getTemporary('files'))) {
	$pluginManager->getFileManager();
	$files = json_decode($pluginManager->getTemporary('files'));
	
	if(!empty($files) && is_array($files))
	foreach($files as $file) {
		$pluginManager->fileManager->delete($file, FileManager::FILESYSTEM_PLUGIN_PUBLIC);
	}
	
	$pluginManager->unsetTemporary('files');
}

if($pluginManager->getDataType('data') == PluginManager::TYPE_STRING) {
	$pluginManager->setTemporary('data', $_POST['data']);
	
	$pluginManager->redirect( $pluginManager );
} else if($pluginManager->getDataType('data') == PluginManager::TYPE_FILE) {
	$pluginManager->getFileManager();
	$files = $pluginManager->fileManager->uploadFiles($_FILES['data'], '', FileManager::FILESYSTEM_PLUGIN_PUBLIC);
	
	if(!$pluginManager->fileManager->isFolder('thumb', FileManager::FILESYSTEM_PLUGIN_PUBLIC)) {
		$pluginManager->fileManager->createFolder('thumb', '',FileManager::FILESYSTEM_PLUGIN_PUBLIC);
	}
	
	$pluginManager->setTemporary('files', json_encode($files));
	
	$pluginManager->redirect( $pluginManager );
}

?>