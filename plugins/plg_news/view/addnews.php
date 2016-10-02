<?php

if(!empty($_POST['source'])) {
	$source = $_POST['source'];
	
	$name = "kein Name";
	if(!empty($_POST['name'])) {
		$name = $_POST['name'];
	}
	
	$image = "";
	if(!empty($_POST['image'])) {
		$image = $_POST['image'];
	}
	
	$pluginManager->databaseManager->insertValue(Array("name"=>Array("value"=>$name),"url"=>Array("value"=>$source),"image"=>Array("value"=>$image)));
	
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
}

?>

[
{
	"type":"heading",
	"value":"Nachrichtenquelle hinzufügen"
},{"type":"nl"},
{
	"type":"input",
	"name":"name",
	"label":"Name: "
},{"type":"nl"},
{
	"type":"input",
	"label":"URL (RSS-Feed): ",
	"name":"source"
},{"type":"nl"},{
	"type":"input",
	"label":"Bilddatei: ",
	"name":"image"
},{"type":"nl"},{"type":"nl"},
{
	"type":"submit",
	"value":"Hinzufügen"
}
]
