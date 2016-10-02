<?php

require_once $pluginManager->getController('tools');

if( !empty($pluginManager->getCommand(0)) && is_numeric($pluginManager->getCommand(0)) ) {
	$recipient = $pluginManager->getCommand(0);
	$senderList = $loginManager->getUserList();
	
	if(!empty($pluginManager->getTemporary('files'))) {
		$send = $pluginManager->getCommand(1);
		
		if(empty($send)) {
			$files = json_decode($pluginManager->getTemporary('files'));
			$jUI->add( new JUI\Heading("Wollen sie die folgende/n Datei/en senden?") );
			
			$pluginManager->getFileManager();
			
			if(!empty($files) && is_array($files))
			foreach($files as $file) {
				$hash = $pluginManager->fileManager->getImageHash($file, FileManager::FILESYSTEM_PLUGIN_PUBLIC);
				
				if(!empty($hash)) {
					$jUI->add( new JUI\Image($hash) );
				}
			}
			
			$yes = new JUI\Button("Ja");
			$yes->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'user', $recipient . '/send') );
			$jUI->add( $yes );
			
			$no = new JUI\Button("Nein");
			$no->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'user', $recipient . '/delete') );
			$jUI->add( $no );
		} else if($send == "send") {
			$pluginManager->getFileManager();
			$files = json_decode($pluginManager->getTemporary('files'));
			
			if(!empty($files) && is_array($files))
			foreach($files as $file) {
				$fileNew = uniqid() . '.' . $pluginManager->fileManager->getExtension($file);
				$pluginManager->fileManager->move($file, $fileNew, FileManager::FILESYSTEM_PLUGIN_PUBLIC);
				$pluginManager->fileManager->resizeImage($fileNew, FileManager::FILESYSTEM_PLUGIN_PUBLIC, 'thumb/'.$fileNew, "300", "300");
				
				$pluginManager->databaseManager->insertValue(Array("text"=>Array("value"=>$fileNew),"sender"=>Array("value"=>$loginManager->getId()),"recipient"=>Array("value"=>$recipient),"type"=>Array("value"=>'1')));
				$pluginManager->databaseManager->insertValue(Array("text"=>Array("value"=>$fileNew),"sender"=>Array("value"=>$loginManager->getId()),"recipient"=>Array("value"=>$recipient),"type"=>Array("value"=>'1'), "user"=>Array("value"=>$recipient)));
				
				$pNotification = new Notification($loginManager->getUsername() . " hat ihnen ein Bild geschickt.", "Bild von " . $loginManager->getUsername(), time()+86400);
				$pNotification->setIcon( $pluginManager->getImage('image.png') );
				$notificationManager = $pluginManager->getNotificationManager();
				$notificationManager->addNotification($pNotification, $recipient, true);
			}
			
			$pluginManager->unsetTemporary('files');
			$pluginManager->redirect( $pluginManager, 'user', $recipient );
		} else if($send == "delete") {
			$files = json_decode($pluginManager->getTemporary('files'));
			$pluginManager->getFileManager();
			
			if(!empty($files) && is_array($files))
			foreach($files as $file) {
				$pluginManager->fileManager->delete($file, FileManager::FILESYSTEM_PLUGIN_PUBLIC);
			}
			
			$pluginManager->unsetTemporary('files');
			$pluginManager->redirect( $pluginManager, 'user', $recipient );
		}
	} else if(!empty($pluginManager->getTemporary('data'))) {
		$nachricht = $pluginManager->getTemporary('data');
		
		$pluginManager->databaseManager->insertValue(Array("text"=>Array("value"=>$nachricht),"sender"=>Array("value"=>$loginManager->getId()),"recipient"=>Array("value"=>$recipient)));
		$pluginManager->databaseManager->insertValue(Array("text"=>Array("value"=>$nachricht),"sender"=>Array("value"=>$loginManager->getId()),"recipient"=>Array("value"=>$recipient), "user"=>Array("value"=>$recipient)));
		
		$pNotification = new Notification($nachricht, "Nachricht von " . $loginManager->getUsername(), time()+86400);
		$pNotification->setIcon( $pluginManager->getImage('icon.png') );
		$notificationManager = $pluginManager->getNotificationManager();
		$notificationManager->addNotification($pNotification, $recipient, true);
		
		$pluginManager->unsetTemporary('data');
		
		$pluginManager->redirect($pluginManager, 'user', $recipient);
		
	} else {
		if(!empty($_POST['nachricht'])) {
			$nachricht = $_POST['nachricht'];
			
			$pluginManager->databaseManager->insertValue(Array("text"=>Array("value"=>$nachricht),"sender"=>Array("value"=>$loginManager->getId()),"recipient"=>Array("value"=>$recipient)));
			$pluginManager->databaseManager->insertValue(Array("text"=>Array("value"=>$nachricht),"sender"=>Array("value"=>$loginManager->getId()),"recipient"=>Array("value"=>$recipient), "user"=>Array("value"=>$recipient)));
			
			$pNotification = new Notification($nachricht, "Nachricht von " . $loginManager->getUsername(), time()+86400);
			$pNotification->setIcon( $pluginManager->getImage('icon.png') );
			$notificationManager = $pluginManager->getNotificationManager();
			$notificationManager->addNotification($pNotification, $recipient, true);
			
			$pluginManager->redirect($pluginManager, 'user', $recipient);
		}
		
		$jUI->scrollBottom(true);
		
		$heading = new JUI\Heading("Chat mit " . $senderList[$recipient]['username']);
		$jUI->add($heading);
		
		$pluginManager->getFileManager();
		
		$data = $pluginManager->getTemporary('data');
		$pluginManager->unsetTemporary('data');
		$pluginManager->unsetTemporary('files');
		
		$notifyInput = new JUI\Input('nachricht');
		$notifyInput->setLabel('Nachricht: ');
		$notifyInput->setValue($data);
		$jUI->add( $notifyInput );
	
		$submitButton = new JUI\Button('Nachricht senden', TRUE);
		$jUI->add( $submitButton );
		//$nachrichten = $pluginManager->databaseManager->getValues(Array("recipient"=>Array("value"=>$recipient, "operator"=>"=", "or"=>array("name"=>"sender","value"=>$recipient))));
		$nachrichten = $pluginManager->databaseManager->getValues(Array('((`recipient`=? AND `sender`=?) OR (`recipient`=? AND `sender`=?))', 'iiii', Array($recipient, $loginManager->getId(), $loginManager->getId(), $recipient) ));
	
	    if(!empty($nachrichten))
		foreach ($nachrichten as $nachricht) {
			$sender = $nachricht['sender'];
			$type = $nachricht['type'];
			
			$senderView = new JUI\Text($senderList[$sender]['username']);
			$senderView->setAppearance(JUI\Text::BOLDITALIC);
			$senderView->setColor('#' . stringToColorCode($sender));
			
			
			if(!empty($type) && $type == '1') {
				$file = $nachricht['text'];
				if($pluginManager->fileManager->fileExists('thumb/' . $file, TRUE, FileManager::FILESYSTEM_PLUGIN_PUBLIC)) {
					$file = 'thumb/' . $file;
				}
				
				$hash = $pluginManager->fileManager->getImageHash($file, FileManager::FILESYSTEM_PLUGIN_PUBLIC);
				$contentView = new JUI\Image( $hash );
				$contentView->setClick( new JUI\Click( JUI\Click::openMedia, 'image', FileManager::FILESYSTEM_PLUGIN_PUBLIC.'://plg_messenger/' . $nachricht['text'] ) );
			} else {
				$contentView = new JUI\Text($nachricht['text']);
				if($nachricht['sender'] != $loginManager->getId()) {
					$senderView->setAlign(JUI\Text::RIGHT);
					$contentView->setAlign(JUI\Text::RIGHT);
				}
			}
			
			$jUI->add($senderView);
			$jUI->add($contentView);
		}
	}
} else {
	$pluginManager->redirect($pluginManager);
}

?>