<?php
	$notes[] = "Ausgaben";
	$noteIds[] = "openPlugin('plg_banking', 'expenses','')";
	$notes[] = "Einnahmen";
	$noteIds[] = "openPlugin('plg_banking', 'earnings','')";
	$notes[] = "Überblick";
	$noteIds[] = "openPlugin('plg_banking', 'overview','')";
	$notes[] = "Ausgaben hinzufügen";
	$noteIds[] = "openPlugin('plg_banking', 'addexpense','')";
	$notes[] = "Einnahmen hinzufügen";
	$noteIds[] = "openPlugin('plg_banking', 'addearning','')";
?>

[
	{
		"type":"heading",
		"value":"Haushaltsbuch"
	},
	{
		"type":"list",
		"value":<?php echo json_encode($notes); ?>,
		"click":<?php echo json_encode($noteIds); ?>
	}
]