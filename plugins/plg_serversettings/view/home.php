<?php
$command = $pluginManager->getCommand(0);

if($pluginManager->isInstalled('plg_arkviewer')) {
	require_once $pluginManager->getController('plg_arkviewer', 'ArkViewer');
}

if($loginManager->isAllowed(LoginManager::STOP_SERVER) && $command == "restart") {
	exec('sudo /var/www/sh/restart.sh');
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
} else if ($loginManager->isAllowed(LoginManager::STOP_SERVER) && $command == "shutdown") {
	require_once $pluginManager->getController('servertools');
	
	$logManager->addLog("Der Benutzer " . $loginManager->getUsername() . " hat einen Shutdown gestartet (1 Minute).");
	
	$notification = new Notification("Der Server wird in einer Minute heruntergefahren.", "Serverbenachrichtigung", 0);
	$notification->setActionOpenPlugin("plg_serversettings");
	$notification->setMaxTimestamp(time() + 60);
	$notification->setServerMessage();
	
	broadcast($notification);
	
	exec('sudo /var/www/sh/shutdown.sh');
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
} else if ($loginManager->isAllowed(LoginManager::STOP_SERVER) && $command == "shutdown30") {
	require_once $pluginManager->getController('servertools');
	
	$logManager->addLog("Der Benutzer " . $loginManager->getUsername() . " hat einen Shutdown gestartet (30 Minuten).");
	
	$notification = new Notification("Der Server wird in ca. 30 Minuten heruntergefahren.", "Serverbenachrichtigung", 0);
	$notification->setActionOpenPlugin("plg_serversettings");
	$notification->setMaxTimestamp(time() + 1800);
	$notification->setServerMessage();
	
	broadcast($notification);
	
	exec('sudo /var/www/sh/shutdown.sh 30');
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
} else if ($loginManager->isAllowed(LoginManager::STOP_SERVER) && $command == "shutdown60") {
	require_once $pluginManager->getController('servertools');
	
	$logManager->addLog("Der Benutzer " . $loginManager->getUsername() . " hat einen Shutdown gestartet (1 Stunde).");
	
	$notification = new Notification("Der Server wird in ca. 60 Minuten heruntergefahren.", "Serverbenachrichtigung", 0);
	$notification->setActionOpenPlugin("plg_serversettings");
	$notification->setMaxTimestamp(time() + 3600);
	$notification->setServerMessage();
	
	broadcast($notification);
	
	exec('sudo /var/www/sh/shutdown.sh 60');
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
} else if ($loginManager->isAllowed(LoginManager::STOP_SERVER) && $command == "shutdown90") {
	require_once $pluginManager->getController('servertools');
	
	$logManager->addLog("Der Benutzer " . $loginManager->getUsername() . " hat einen Shutdown gestartet (90 Minuten).");
	
	$notification = new Notification("Der Server wird in ca. 90 Minuten heruntergefahren.", "Serverbenachrichtigung", 0);
	$notification->setActionOpenPlugin("plg_serversettings");
	$notification->setMaxTimestamp(time() + 5400);
	$notification->setServerMessage();
	
	broadcast($notification);
	
	exec('sudo /var/www/sh/shutdown.sh 90');
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
} else if ($loginManager->isAllowed(LoginManager::STOP_SERVER) && $command == "shutdown120") {
	require_once $pluginManager->getController('servertools');
	
	$logManager->addLog("Der Benutzer " . $loginManager->getUsername() . " hat einen Shutdown gestartet (2 Stunden).");
	
	$notification = new Notification("Der Server wird in ca. 2 Stunden heruntergefahren.", "Serverbenachrichtigung", 0);
	$notification->setActionOpenPlugin("plg_serversettings");
	$notification->setMaxTimestamp(time() + 7200);
	$notification->setServerMessage();
	
	broadcast($notification);
	
	exec('sudo /var/www/sh/shutdown.sh 120');
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
} else if ($loginManager->isAllowed(LoginManager::STOP_SERVER) && $command == "shutdownstop") {
	exec('sudo /var/www/sh/shutdown.sh stop');
	
	$logManager->addLog("Der Benutzer " . $loginManager->getUsername() . " hat einen Shutdown gestoppt.");
	
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
} else if ($loginManager->isAllowed(LoginManager::STOP_SERVER) && $command == "update") {
	exec('sudo /var/www/sh/serverupdate.sh');
	
	$logManager->addLog("Der Benutzer " . $loginManager->getUsername() . " hat ein Update gestartet.");
	
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
}

if($loginManager->isAllowed(LoginManager::STOP_SERVER)) {
	
	$jUI->add(new JUI\Heading("Servereinstellungen"));
	
	$restart = new JUI\Button("Server neustarten");
	$restart->setColor('FF0000');
	$restart->setLongClick(new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'home', 'restart'));
	$jUI->add($restart);
	
	$shutdown = new JUI\Button("Server herunterfahren");
	$shutdown->setColor('FF0000');
	$shutdown->setLongClick(new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'home', 'shutdown'));
	$jUI->add($shutdown);
	 
	$shutdownStop = new JUI\Button("Server herunterfahren abbrechen");
	$shutdownStop->setColor('FF0000');
	$shutdownStop->setLongClick(new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'home', 'shutdownstop'));
	$jUI->add($shutdownStop);
	
	$update = new JUI\Button("Server updaten");
	$update->setClick(new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'home', 'update'));
	$jUI->add($update);
	
	$jUI->newline();
	$jUI->newline();
	
	$shutdown30 = new JUI\Button("Server in 30 Minuten herunterfahren");
	$shutdown30->setColor('FF0000');
	$shutdown30->setLongClick(new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'home', 'shutdown30'));
	$jUI->add($shutdown30);
	
	$jUI->newline();
	
	$shutdown60 = new JUI\Button("Server in 60 Minuten herunterfahren");
	$shutdown60->setColor('FF0000');
	$shutdown60->setLongClick(new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'home', 'shutdown60'));
	$jUI->add($shutdown60);
	
	$jUI->newline();
	
	$shutdown90 = new JUI\Button("Server in 90 Minuten herunterfahren");
	$shutdown90->setColor('FF0000');
	$shutdown90->setLongClick(new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'home', 'shutdown90'));
	$jUI->add($shutdown90);
	
	$jUI->newline();
	
	$shutdown120 = new JUI\Button("Server in 120 Minuten herunterfahren");
	$shutdown120->setColor('FF0000');
	$shutdown120->setLongClick(new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'home', 'shutdown120'));
	$jUI->add($shutdown120);
}

if($loginManager->isAllowed(LoginManager::LOG_ACCESS)) {
	if($loginManager->isAllowed(LoginManager::STOP_SERVER)) {
		$jUI->newline();
		$jUI->newline();
	}
	
	$logButton = new JUI\Button("Log einsehen");
	$logButton->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'logs', '' ) );
	$jUI->add( $logButton );
}

$about = new JUI\Button("Über diesen Server");
$about->setClick( new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'about') );
$jUI->add($about);

if($loginManager->isAllowed(LoginManager::STOP_SERVER) || $loginManager->isAllowed(LoginManager::LOG_ACCESS)) {
	$jUI->newline();
	$jUI->hline();
}

$jUI->add( new JUI\Heading("Gameserver") );

if( $pluginManager->isInstalled('plg_ts3viewer') ) {
	$ts3widget = new JUI\Widget('plg_ts3viewer', 'settings');
	$jUI->add( $ts3widget );
	
	$jUI->newline();
	$jUI->newline();
}

if( $pluginManager->isInstalled('plg_mcviewer') ) {
	$mcwidget = new JUI\Widget('plg_mcviewer', 'settings');
	$jUI->add( $mcwidget );
	
	$jUI->newline();
	$jUI->newline();
}

/* CSGO-Server */
$csgoHeading = new JUI\Heading("CS:GO");
$csgoHeading->setSmall(TRUE);
$jUI->add( $csgoHeading );

$table = new JUI\Table();
	$row = new JUI\Table\Row();
	$row->addColumn("Status:");
	
	ob_start();
	passthru('sudo /var/www/sh/csgostatus.sh');
	$status = ob_get_clean();
	
	if(strpos($status, "csgo") === false) {
		$csgoStatus = new JUI\Text("Server offline");
		$csgoStatus->setColor("FF0000");
	} else {
		$csgoStatus = new JUI\Text("Server offline");
		$csgoStatus->setColor("FF0000");
	}
	
	$csgoStatus->setAlign( JUI\Text::RIGHT );
	$row->addColumn( $csgoStatus );
$table->addRow($row);
$jUI->add($table);

if($loginManager->isAllowed(LoginManager::STOP_SERVER)) {
	$csgoStart = new JUI\Button("CS:GO Server starten");
	$csgoStart->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'start', 'csgo') );
	$jUI->add( $csgoStart );
	
	$csgoStop = new JUI\Button("CS:GO Server stoppen");
	$csgoStop->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'stop', 'csgo') );
	$jUI->add( $csgoStop );
	
	$csgoUpdate = new JUI\Button("CS:GO Server updaten");
	$csgoUpdate->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'update', 'csgo') );
	$jUI->add( $csgoUpdate );
}

$jUI->newline();
$jUI->newline();


/* ARK-Server */
if( $pluginManager->isInstalled('plg_arkviewer') ) {
	$ts3widget = new JUI\Widget('plg_arkviewer', 'settings');
	$jUI->add( $ts3widget );
}

$jUI->newline();
$jUI->newline();

if($loginManager->isAllowed(LoginManager::MODIFY_USERS)) {
	
	$jUI->add( new JUI\Heading("Benutzer"));
	
	$addUser = new JUI\Button("Benutzer erstellen");
	$addUser->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'user', 'create' ) );
	$jUI->add( $addUser );
	
	$users = $pluginManager->getUserManager()->getUserList();
	$userList = new JUI\ListView();
	
	foreach($users as $user) {
		$userClick = new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'user', $user['id'] );
		$userList->addItem($user['username'], $userClick);
	}
	
	$jUI->add( $userList );
}

?>