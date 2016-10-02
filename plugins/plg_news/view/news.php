<?php
	if(empty($pluginManager->getCommand(0)) || !is_numeric($pluginManager->getCommand(0))) {
		die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
	}
	
	require_once $pluginManager->getController("RssReader");
	
	$id = $pluginManager->getCommand(0);
	
	$value = $pluginManager->databaseManager->getValues(Array("id"=>Array("operator"=>"=", "value"=>$id)),1);
	
	$rss = new RssReader();
	$feedArray = $rss->getFeedArray($value['url']);
	
	$returnString = "";
	foreach($feedArray as $feed) {
		if( strpos($feed['title'], "[Anzeige]") === false ) {
			$returnString .= '{"type":"headingSmall", "value":' . json_encode($feed['title']) . '},';
			$returnString .= '{"type":"text", "value":' . json_encode($feed['summary']) . '},';
			$returnString .= '{"type":"link", "value":"Weiterlesen ...", "click":"openUrl(\'' . $feed['link'] . '\')"},';
			$returnString .= '{"type":"nl"},{"type":"nl"},';
		}
	}
	
	$returnString = substr($returnString, 0, -1);
?>

[
<?php echo $returnString; ?>
]
