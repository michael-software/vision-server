<?php
	//$id = $pluginManager->getCommand(0);
	
	/*
	if(!empty($_POST['noteContent']) AND !empty($id)) {
		$content = $_POST['noteContent'];
		
		$pluginManager->databaseManager->setValue(Array("text"=>Array("value"=>$content)), Array("id"=>Array("operator"=>"=", "value"=>$id, "type"=>"i")));
	}*/
	
	$values = $pluginManager->databaseManager->getValues(Array("value"=>Array("operator"=>"<", "value"=>"0", "type"=>"i")));
	
	$array = null;
	foreach ($values as $value) {
		$valuePrice = number_format($value['value']/100, 2, ',', '.');
		$array[] = array($value['name'], array('value'=>$valuePrice.' €','type'=>'text','align'=>'right'));
	}
?>

[
	{
		"type":"heading",
		"value":"Ausgaben"
	},
	{
		"type":"table",
		"width":"100%",
		"rows":<?php echo json_encode($array); ?>
	}
]
