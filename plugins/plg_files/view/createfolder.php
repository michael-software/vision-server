<?php
	if(!empty($_POST['upperfolder']) || !empty($_POST['folder'])) {
		if($pluginManager->fileManager->isFolder($_POST['upperfolder'])) {
			if(!empty($_POST['folder'])) {
				if( $pluginManager->fileManager->createFolder($_POST['folder'], $_POST['upperfolder']) ) {
					die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", "' . $_POST['upperfolder'] . $_POST['folder'] . '"]}');
				}
				
				die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", "' . $_POST['upperfolder'] . '"]}');
			} else {
				die('{"redirect":["' . $pluginManager->getPluginName() . '", "createfolder", "' . $_POST['upperfolder'] . '"]}');
			}
		}
		
		die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
	}
?>

[
	{
		"type":"heading",
		"value":"Ordner erstellen"
	},
	{
		"type":"input",
		"label":"Ordnername: ",
		"name":"folder"
	},
	{
		"type":"input",
		"value":"%!#|params|#!%",
		"visible":"away",
		"name":"upperfolder"
	},
	{ "type":"nl" }, { "type":"nl" },
	{
		"type":"submit",
		"value":"Ordner erstellen"
	},
	{
		"type":"button",
		"value":"Zurück",
		"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','home','%!#|params|#!%')"
	}
]