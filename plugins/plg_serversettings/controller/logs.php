<?php

if(!$loginManager->isAllowed(LoginManager::LOG_ACCESS)) {
	die();
}

function showTeamspeakLogs($jUI) {
	global $loginManager;
	global $pluginManager;
	
	$log = $pluginManager->getCommand(1);
	
	if(empty($log) || !is_string($log)) {
		$files = scandir("/home/teamspeak/logs/");
		$listView = new JUI\ListView();
		
		if(!empty($files) && is_array($files)) {
			$files = array_reverse($files);
			
			foreach ($files as $file) {
				if($file != '.' && $file != '..') {
					$click = new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'logs/ts3', urlencode($file) );
					$listView->addItem($file, $click);		
				}
			};
		}
		
		$jUI->add($listView);
	} else {
		$logfile = "/home/teamspeak/logs/" . urldecode($log);
		
		if(file_exists($logfile)) {
			$handle = fopen($logfile, "r");
			$contents = fread($handle, filesize($logfile));
			fclose($handle);
			
			$jUI->add( new JUI\Text($contents) );
		} else {
			$jUI->add( new JUI\Text("Die Logdatei ist nicht vorhanden") );
		}
	}
	
	//echo $contents;
}

function showServerLogs($jUI) {
	global $loginManager;
	global $pluginManager;
	global $logManager;
	
	$heading = new JUI\Heading("Log-Dateien");
	$jUI->add($heading);
	
	$apache2 = new JUI\Button("TeamSpeak3-Logs");
	$apache2->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'logs', 'ts3') );
	$jUI->add( $apache2 );
	
	$clean = new JUI\Button("Alte Log-Dateien (älter als 1 Monat) löschen");
	$clean->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'logs', 'server/clean') );
	$jUI->add( $clean );
	
	//$pluginManager->logManager->addLog("Admin hat Zugriff auf Log-Dateien angefordert.");
	$logs = $logManager->getLogs();
	
	$table = new JUI\Table();
	$table->setWidth('100%');
	
	$userlist = $loginManager->getUserList();
	$userlistId;
	foreach($userlist as $user) {
		$userlistId[$user['id']] = $user;
	}
	
	foreach($logs as $log) {
		// Create Row
		$row = new JUI\Table\Row();
		
		
		/* TIMESTAMP */
		$timestampText = date("Y-m-d H:i:s", $log['timestamp']);
		$message = $timestampText . '   ';
		
		/* PLUGIN */
		if(!empty($log['plugin']) && $log['plugin'] != "plg_serversettings") {
			$message .= '(' . $log['plugin'] . ') ';
		}
		
		/* MESSAGE */
		$message .= $log['text'];
		$messageText = new JUI\Text();
		if($log['plugin'] == "plg_serversettings")
			$messageText->setColor('00008B');
		
		if(empty($log['user']))
			$messageText->setColor('FF0000');
		
		$messageText->setText($message);
		$messageColumn = new JUI\Table\Column();
		$messageColumn->add($messageText);
		$row->addColumn($messageColumn);
		
		
		/* USER */
		$userText = new JUI\Text();
		if(empty($log['user']))
			$userText->setColor('FF0000');
		
		if(!empty($userlistId) && !empty($log['user']) && !empty($userlistId[$log['user']])) {
			$user = $log['user'];
			$userText->setText($userlistId[$user]['username']);
		} else {
			$userText->setText("Server");
		}
		
		$userText->setAlign(JUI\Text::RIGHT);
		$userColumn = new JUI\Table\Column();
		$userColumn->add($userText);
		$row->addColumn($userColumn);
		
		$row->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'logs', 'server/' . $log['id'] ) );
		
		// Adding the Row
		$table->addRow($row);
	}
	
	$jUI->add($table);
}

function showServerLog($jUI, $id) {
	global $logManager;
	global $pluginManager;
	
	$jUI->add(new JUI\Heading("Eintrag:  " . $id));
	
	$log = $logManager->getLog( $id );
	
	$table = new JUI\Table();
	
	$row4 = new JUI\Table\Row();
	$row4->addColumn("Plugin");
		if(empty($log['user'])) {
			$statusText = new JUI\Text("Warnung");
			$statusText->setColor('FF0000');
		} else if($log['plugin'] == "plg_serversettings") {
			$statusText = new JUI\Text("Info");
			$statusText->setColor('00008B');
		} else {
			$statusText = new JUI\Text("Normal");
		}
	$row4->addColumn( $statusText );
	$table->addRow($row4);
	
	$row1 = new JUI\Table\Row();
	$row1->addColumn("Zeit");
	$row1->addColumn( date("Y-m-d H:i:s", $log['timestamp']) );
	$table->addRow($row1);
	
	$row3 = new JUI\Table\Row();
	$row3->addColumn("Plugin");
		$pluginText = new JUI\Text($log['plugin']);
		$pluginText->setAppearance(JUI\Text::BOLD);
	$row3->addColumn( $pluginText );
	$table->addRow($row3);
	
	$row2 = new JUI\Table\Row();
	$row2->addColumn("Nachricht");
	$row2->addColumn( $log['text'] );
	$table->addRow($row2);
	
	$jUI->add( $table );
	
	$delete = new JUI\Button('Eintrag löschen');
	$delete->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'logs', 'server/' . $id . '/delete' ) );
	$jUI->add($delete);
}

?>