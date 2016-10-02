<?php

if(!empty($pluginManager->getTemporary('file'))) {
	$pluginManager->getFileManager();
	$file = $pluginManager->getTemporary('file');
	
	$pluginManager->fileManager->delete($file, FileManager::FILESYSTEM_PLUGIN_PRIVATE);
	
	$pluginManager->unsetTemporary('file');
}

if($pluginManager->getDataType('data') == PluginManager::TYPE_FILE) {
	$pluginManager->getFileManager();
	$file = $pluginManager->fileManager->uploadFiles($_FILES['data'], '', FileManager::FILESYSTEM_PLUGIN_PRIVATE);
	
	$pluginManager->setTemporary('file', $file[0]);
	
	$pluginManager->redirect( $pluginManager );
}

?>