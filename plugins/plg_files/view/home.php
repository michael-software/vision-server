<?php
$command = $pluginManager->getCommand();

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

if(!empty($_FILES)) {
	if( $pluginManager->fileManager->uploadFiles($_FILES['files'], $folder) ) {
		$pluginManager->redirect($pluginManager, 'home', $folder);
	}
}

if(!$loginManager->getShareManager()->isShared())
	$jUI->setShare('share', $folder);

$jUI->add( new JUI\Heading('Dateien') );

if(!$loginManager->getShareManager()->isShared() || $loginManager->getShareManager()->getParameter()->allowCreate === TRUE) {
	$createFolder = new JUI\Button('Ordner erstellen');
	$createFolder->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'createfolder', $folder ) );
	$jUI->add( $createFolder );
}

if(!$loginManager->getShareManager()->isShared() || $loginManager->getShareManager()->getParameter()->allowCreate === TRUE) {
	if($pluginManager->getTemporary('showHidden', false)) {
		$showHidden = new JUI\Button('Versteckte ausblenden');
	} else {
		$showHidden = new JUI\Button('Versteckte anzeigen');
	}
	$showHidden->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'hidden', $folder ) );
	$jUI->add( $showHidden );
}

if(!$loginManager->getShareManager()->isShared() || $loginManager->getShareManager()->getParameter()->allowUpload === TRUE) {
	$fileupload = new JUI\File('files');
	$fileupload->setMultiple();
	$jUI->add($fileupload);

	$jUI->add( new JUI\Button('Hochladen', TRUE) );
}

$list = new JUI\ListView();

if(!empty($folder)) {
	//$list = array("..");
	
	$upperFolder = dirname($folder);
	if($upperFolder == ".") {
		$upperFolder = "";
	}
	
	$list->addItem("..", new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'home', $upperFolder ) );
}

$folders = null;
$files = null;
$imageId = 0;
$gallery = new JUI\Gallery();

foreach($pluginManager->fileManager->getFolder($folder) as $element) {
	if(FileManager::isVisible($element['name']) || $pluginManager->getTemporary('showHidden', false)) {
		if($element['type'] == "dir") {
			$name = $element['name'];
			
			$click = new JUI\Click( JUI\Click::openPlugin, $pluginManager, "home", $folder . $element['name'] . '/' );
			$longclick = new JUI\Click( JUI\Click::openPlugin, $pluginManager, "foldersettings", $folder . $element['name'] . '/' );
			
			
			$key = strtolower($name);
			if(!empty($folders[$key])) {
				$key .= count($folders);
			}
			
			$folders[strtolower($name)] = array("name"=>$name, "click"=>$click, "longclick"=>$longclick);
		} else if($element['type'] == "tmpdl") {
			$name = $element['name'];
			
			$click = new JUI\Click( JUI\Click::openPlugin, $pluginManager, "temp", $folder . $element['name'] . '/' );
			$longclick = new JUI\Click( JUI\Click::openPlugin, $pluginManager, "temp", $folder . $element['name'] . '/' );
			
			
			$key = strtolower($name);
			if(!empty($files[$key])) {
				$key .= count($files);
			}
			
			$files[$key] = array("name"=>$name, "click"=>$click, "longclick"=>$longclick);
		} else {
			$bytes = filesize($pluginManager->fileManager->userFiles . $folder . $element['name']);
			$bytesString = $pluginManager->fileManager->getBytesString($bytes);
			
			$name = $element['name'] . ' (' . $bytesString . ')';
			

			$click = null;
			if($element['type'] != 'image') {
				$click = new JUI\Click( JUI\Click::openMedia, $element['type'], $folder . $element['name'] );
			}
			
			$longclick = new JUI\Click( JUI\Click::openPlugin, $pluginManager, "filesettings", $folder . $element['name'] . '/' );
			
			
			$key = strtolower($name);
			if(!empty($files[$key])) {
				$key .= count($files);
			}
			
			$files[$key] = array("name"=>$name, "click"=>$click, "longclick"=>$longclick, "path"=>$folder . $element['name'], "type"=>$element['type']);
		}
	}
}

if(!empty($folders) && is_array($folders)) {
	ksort($folders, SORT_STRING);
	
	if(!empty($folders) && is_array($folders))
	foreach($folders as $folder) {
		$name = $folder['name'];
		$click = $folder['click'];
		$longclick = $folder['longclick'];
		
		$list->addItem($name, $click, $longclick);
	}
}

if(!empty($files) && is_array($files)) {
	ksort($files, SORT_STRING);
	
	if(!empty($files) && is_array($files))
	foreach($files as $file) {
		$name = $file['name'];
		$click = $file['click'];
		$longclick = $file['longclick'];

		if($file['type'] == 'image') {
			$gallery->add( $file['path'] );

			$click = new JUI\Click( JUI\Click::openGallery, $gallery, $imageId);
			//$click = new JUI\Click( JUI\Click::openMedia, $element['type'], $folder . $element['name'] );
			$imageId++;
		}
		
		$list->addItem($name, $click, $longclick);
	}
}

$jUI->add($gallery);

$jUI->add( $list );


?>
