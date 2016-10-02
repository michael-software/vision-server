<?php

require_once $pluginManager->getController('tools');

if(!empty($pluginManager->getCommand(0)) && $pluginManager->getCommand(0) == "reload") {
	FileManager::updateUserFileList();
}

$jUI->add( new JUI\Heading('Musik') );

$reload = new JUI\Button("Musik aktualisieren");
$reload->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, "home", "reload") );
$jUI->add($reload);

$remote = new JUI\Button("Musik-Remote öffnen");
$remote->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, "remote") );
$jUI->add($remote);

$audio = $pluginManager->fileManager->getAudioList();

if(!empty($audio) && is_array($audio)) {
	$list = new JUI\ListView();
	
	$musicArray = null;
	
	foreach($audio as $path) {
		if(is_string($path) && $pluginManager->fileManager->fileExists($path, TRUE, FileManager::FILESYSTEM_PRIVATE)) {
			
			$name = FileManager::getBaseName($path);
			$array['name'] = $name;
			$array['click'] = new JUI\Click( JUI\Click::openMedia, 'music', $path );
			$array['longclick'] = new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'remote', encode($path) );
			
			if(empty($musicArray[strtolower($name)])) {
				$musicArray[strtolower($name)] = $array;
			} else {
				$musicArray[strtolower($name) . '_' . count($musicArray)] = $array;
			}
		}
	}
	
	ksort($musicArray);
	
	if(!empty($musicArray) && is_array($musicArray))
	foreach($musicArray as $music) {
		$title = $music['name'];
		$click = $music['click'];
		$longclick = $array['longclick'];
		
		$list->addItem($title, $click, $longclick);
	}
	
	$jUI->add($list);
	
} else {
	$input = new JUI\Input('readmusic');
	$input->setValue('TRUE');
	$input->setVisible( JUI\View::GONE );
	$jUI->add($input);
	
	$button = new JUI\Button('Musik aus Dateien einlesen (Vorgang kann etwas dauern).', TRUE);
	$jUI->add($button);
}


?>