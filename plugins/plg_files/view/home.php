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

if(!empty($folder)) {
	$list = array("..");
	
	$upperFolder = dirname($folder);
	if($upperFolder == ".") {
		$upperFolder = "";
	}
	
	$actions = array('openPlugin("plg_files","home","' . $upperFolder . '")');
} else {
	$list = array();
	$actions = array();
	$actions2 = array();
}

foreach($pluginManager->fileManager->getFolder($folder) as $element) {
	$list[] = $element['name'];
	
	if($element['type'] == "dir") {
		$actions[] = 'openPlugin("plg_files","home","' . $folder . $element['name'] . '/")';
	} else {
		$actions[] = 'openMedia("' . $element['type'] . '","' . $folder . $element['name'] . '")';
	}
	
	if($element['type'] != "dir") {
		$actions2[] = 'openPlugin("plg_files","filesettings","' . $folder . $element['name'] . '/")';
	} else {
		$actions2[] = 'openPlugin("plg_files","foldersettings","' . $folder . $element['name'] . '/")';
	}
}
?>

[
	{
		"type":"heading",
		"value":"Dateien"
	},
	{
		"type":"button",
		"value":"Ordner erstellen",
		"click":<?php echo json_encode('openPlugin("plg_files","createfolder","' . $folder . '")'); ?>
	},
		{
		"type":"file",
		"name":"files",
		"multiple":"multiple"
	},
	{
		"type":"list",
		"value":<?php echo json_encode($list); ?>,
		"click":<?php echo json_encode($actions); ?>,
		"longclick":<?php echo json_encode($actions2); ?>
	}
]