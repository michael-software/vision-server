<?php
	if(!empty($_POST['noteName']) AND !empty($_POST['noteContent'])) {
		$name  = $_POST['noteName'];
		$content = $_POST['noteContent'];
		
		if(!empty($_POST['notePassword'])) {
			$password = $_POST['notePassword'];
		} else {
			$password = '';
		}
		
		$pluginManager->databaseManager->insertValue(Array("name"=>Array("value"=>$name),"text"=>Array("value"=>$content),"password"=>Array("value"=>$password)));
		
		die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
	}
?>

[
	{
		"type":"heading",
		"value":"Notiz erstellen"
	},
	{
		"type":"input",
		"label":"Name:",
		"name":"noteName"
	},
	{ "type":"nl" },
	{
		"type":"input",
		"label":"Kennwort:",
		"name":"notePassword"
	},
	{ "type":"nl" },
	{ "type":"nl" },
	{
		"type":"textarea",
		"name":"noteContent",
		"width":500,
		"height":200,
		"label":"Notiz:"
	},
	{ "type":"nl" },
	{ "type":"nl" },
	{
		"type":"submit",
		"value":"Speichern"
	}
]
