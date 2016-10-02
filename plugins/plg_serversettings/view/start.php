<?php

$command = $pluginManager->getCommand(0);

if($command == "csgo") {
	ob_start();
	passthru('sudo /var/www/sh/csgostatus.sh');
	$status = ob_get_clean();
	
	if(strpos($status, "csgo") === false) {
		exec('sudo /var/www/sh/csgostart.sh');
	}
} else if($command == "arkse") {
	ob_start();
	passthru('sudo /var/www/sh/csgostatus.sh');
	$status = ob_get_clean();
	
	if(strpos($status, "arkse") === false) {
		passthru('sudo /var/www/sh/arkstart.sh');
	}
}

?>

{"redirect":["plg_serversettings", "home", ""]}
