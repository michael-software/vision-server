<?php
	//$id = $pluginManager->getCommand(0);
	
	/*
	if(!empty($_POST['noteContent']) AND !empty($id)) {
		$content = $_POST['noteContent'];
		
		$pluginManager->databaseManager->setValue(Array("text"=>Array("value"=>$content)), Array("id"=>Array("operator"=>"=", "value"=>$id, "type"=>"i")));
	}*/
	
	$values = $pluginManager->databaseManager->getValues();
	
	$array = null;
	$overview = 0;
	
	foreach ($values as $value) {
		//$array[] = array($value['name'],$value['value']);
		$valuePrice = number_format($value['value']/100, 2, ',', '.');
		
		if($valuePrice >= 0) {
			$array[] = array($value['name'],array('value'=>$valuePrice.' €','type'=>'text','color'=>'#00FF00','align'=>'right'));
		} else {
			$array[] = array($value['name'],array('value'=>$valuePrice.' €','type'=>'text','color'=>'#FF0000','align'=>'right'));
		}
		
		$overview += $value['value']/100;
	}
	
	if($overview < 0) {
		$valuePrice = number_format($overview, 2, ',', '.');
		$overview = array(array("Insgesamt",array('value'=>$valuePrice.' €','type'=>'text','color'=>'#FF0000','align'=>'right')));
	}
?>

[
	{
		"type":"heading",
		"value":"Überblick"
	},
	{
		"type":"table",
		"width":"100%",
		"rows":<?php echo json_encode($overview); ?>
	},
	{ "type":"hline" },
	{
		"type":"table",
		"width":"100%",
		"rows":<?php echo json_encode($array); ?>
	}
]
