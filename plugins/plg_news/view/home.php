<?php
	$sourceArray = $pluginManager->databaseManager->getValues();
	
	$sources = null;
	$sourcesId = null;
	
	foreach($sourceArray as $source) {
		$sources[] = $source['name'];
		
		$sourcesId[] = "openPlugin('" . $pluginManager->getPluginName() . "', 'news','" . $source['id'] . "')";
	}
?>

[
	{
		"type":"heading",
		"value":"Nachrichten"
	},
	{
		"type":"list",
		"value":<?php echo json_encode($sources); ?>,
		"click":<?php echo json_encode($sourcesId); ?>
	},
	{
		"type":"button",
		"value":"Neue Nachrichtenquelle hinzufügen",
		"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','addnews','')"
	}
]