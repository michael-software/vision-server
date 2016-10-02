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

if(!empty($_POST['upload'])) {
	$files = $pluginManager->fileManager->getFolder('tmp/', FileManager::FILESYSTEM_PLUGIN_PRIVATE);
	//die($folder);
	foreach($files as $file) {
		if($file['type'] != "dir") {
			$pluginManager->fileManager->moveToUserPublic('tmp/'.$file['name'], $folder);
		}
	}
	
	$pluginManager->redirect($pluginManager, 'home', $folder);
}

if(!empty($folder)) {
	$list = array("..");
	
	$upperFolder = dirname($folder);
	if($upperFolder == ".") {
		$upperFolder = "";
	}
	
	$actions = array('openPlugin("plg_files","moveshare","' . $upperFolder . '")');
} else {
	$list = array();
	$actions = array();
}

foreach($pluginManager->fileManager->getFolder($folder) as $element) {
	if(FileManager::isVisible($element['name']) && $element['type'] == "dir") {
		$list[] = $element['name'];
		$actions[] = 'openPlugin("plg_files","moveshare","' . $folder . $element['name'] . '/")';
	}
}
?>

[
	{
		"type":"heading",
		"value":"Dateien hochladen"
	},{
		"type":"list",
		"value":<?php echo json_encode($list); ?>,
		"click":<?php echo json_encode($actions); ?>
	},{
		"type":"input",
		"name":"upload",
		"value":"yes",
		"visible":"away"
	},{
		"type":"button",
		"click":"submit()",
		"value":"Hochladen"
	}
]