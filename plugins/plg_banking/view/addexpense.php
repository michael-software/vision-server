<?php
	if(!empty($_POST['name']) AND !empty($_POST['value'])) {
		$name  = $_POST['name'];
		$value = str_replace(',', '.', $_POST['value']);
		$value = -1*round(floatval($value)*100);
		
		$pluginManager->databaseManager->insertValue(Array("name"=>Array("value"=>$name),"value"=>Array("value"=>$value)));
		
		die('{"redirect":["plg_banking", "home", ""]}');
	}
?>

[
	{
		"type":"heading",
		"value":"Ausgabe hinzufügen"
	},
	{
		"type":"input",
		"name":"name",
		"label":"Name:"
	},
	{ "type":"nl" },
	{
		"type":"input",
		"name":"value",
		"accept":"numbers",
		"label":"Preis:"
	},
	{ "type":"nl" },
	{ "type":"nl" },
	{
		"type":"submit",
		"value":"Speichern"
	}
]
