<?php
$command = $pluginManager->getCommand(0);

if($command == "csgo") {
	ob_start();
	passthru('sudo /var/www/sh/csgostatus.sh');
	$status = ob_get_clean();
	
	if(strpos($status, "csgo") === false) {
	} else {
		passthru('sudo /var/www/sh/csgostop.sh');
	}
} else if($command == "arkse") {
	ob_start();
	passthru('sudo /var/www/sh/csgostatus.sh');
	$status = ob_get_clean();
	
	if(strpos($status, "arkse") === false) {
	} else {
		passthru('sudo /var/www/sh/arkstop.sh');
	}
}

?>

{"redirect":["plg_serversettings", "home", ""]}