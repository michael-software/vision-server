<?php
	$id = $pluginManager->getCommand(0);
	$password = $pluginManager->getCommand(1);
	
	if(!empty($_POST['noteContent']) AND !empty($id)) {
		$content = $_POST['noteContent'];
		
		$pluginManager->databaseManager->setValue(Array("text"=>Array("value"=>$content)), Array("id"=>Array("operator"=>"=", "value"=>$id, "type"=>"i")));
		
		die('{"redirect":["' . $pluginManager->getPluginName() . '","home",""]}');
	}
	
	$value = $pluginManager->databaseManager->getValues(Array("id"=>Array("operator"=>"=", "value"=>$id)),1);
	
	if(!empty($value['password']) && !empty($password)) {
		if($value['password'] != $password){
			die('{"redirect":["' . $pluginManager->getPluginName() . '", "password", "' . $id . '"]}');
		}
	} else if(!empty($value['password']) && empty($password)) {
		die('{"redirect":["' . $pluginManager->getPluginName() . '", "password", "' . $id . '"]}');
	}
?>

[
	{
		"type":"heading",
		"value":<?php echo json_encode($value['name']); ?>
	},
	{
		"type":"textarea",
		"value":<?php echo json_encode($value['text']); ?>,
		"name":"noteContent",
		"width":500,
		"height":200
	},
	{ "type":"nl" },{ "type":"nl" },
	{
		"type":"submit",
		"value":"Speichern"
	}
]
