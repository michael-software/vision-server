<?php
$command = $pluginManager->getCommand(0);

if($command == "teamspeak") {
	$status = exec('sudo /var/www/sh/teamspeakstatus.sh');
	
	if($status == "Server is running") {
		exec('sudo /var/www/sh/teamspeakstop.sh');
	}
} else if($command == "csgo") {
	ob_start();
	passthru('sudo /var/www/sh/csgostatus.sh');
	$status = ob_get_clean();
	
	if(strpos($status, "csgo") === false) {
	} else {
		exec('sudo /var/www/sh/csgostop.sh');
		exec('sudo /var/www/sh/csgoupdate.sh');
	}
}

?>

{"redirect":["plg_serversettings", "home", ""]}