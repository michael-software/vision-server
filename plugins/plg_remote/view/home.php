<?php

$authtokens = $loginManager->getAuthtokens();

$names;
$clicks;

if(!empty($authtokens))
foreach($authtokens as $authtoken) {
	if(!empty($authtoken['name'])) {
		$names[] = $authtoken['name'];
	} else {
		$names[] = "kein Name - " . $authtoken['authtoken'];
	}
	
	$clicks[] = "openPlugin('" . $pluginManager->getPluginName() . "','device','" . $authtoken['authtoken'] . "')";
}

?>
[
	{
		"type":"heading",
		"value":"Mit Gerät verbinden"
	},{
		"type":"list",
		"value":<?php echo json_encode($names); ?>,
		"click":<?php echo json_encode($clicks); ?>
	}
]
