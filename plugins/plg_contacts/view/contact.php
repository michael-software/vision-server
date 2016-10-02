<?php

$command = $pluginManager->getCommand(0);

if(!empty($command) && is_numeric($command)) {
	require_once $pluginManager->getController('tools');
	
	$contact = $pluginManager->databaseManager->getValues( array('id'=>array('value'=>$command)) , 1);
	
	if(!empty($contact) && is_array($contact)) {
		$name = $contact['lastname'] . ',  ' . $contact['firstname'];
		$jUI->add( new JUI\Heading($name) );
		
		$table = new JUI\Table();
		
		if(!empty($contact['telephone'])) {
			$table->addRow( nameValueRow('Telefon: ', $contact['telephone']) );
		}
		
		if(!empty($contact['mobile'])) {
			$table->addRow( nameValueRow('Mobil: ', $contact['mobile']) );
		}
		
		if(!empty($contact['email'])) {
			$table->addRow( nameValueRow('E-Mail: ', $contact['email']) );
		}
		
		if(!empty($contact['address'])) {
			$addressArray = json_decode($contact['address']);
			
			$addressString = '';
			
			if(!empty($addressArray)) {
				if(!empty($addressArray->address)) {
					$addressString .= $addressArray->address . PHP_EOL;
				}
				
				if(!empty($addressArray->zipcode)) {
					$addressString .= $addressArray->zipcode . ' ';
				}
				
				if(!empty($addressArray->city)) {
					$addressString .= $addressArray->city . PHP_EOL;
				}
				
				if(!empty($addressArray->state)) {
					$addressString .= $addressArray->state . PHP_EOL;
				}
				
				if(!empty($addressArray->country)) {
					$addressString .= $addressArray->country;
				}
			}
			
			$table->addRow( nameValueRow('Adresse: ', $addressString) );
		}
		
		if(!empty($contact['birthday'])) {
			$table->addRow( nameValueRow('Geburtstag: ', $contact['birthday']) );
		}
		
		$jUI->add($table);
		
		//$jUI->setSwipe(JUI\Manager::SWIPE_BOTTOM, new JUI\Click( JUI\Click::openPlugin, 'plg_serversettings', 'home', '' ));
		
		$edit = new JUI\Button('Bearbeiten');
		$edit->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'edit', $command ) );
		$jUI->add($edit);
		
		$download = new JUI\Button('Als vCard herunterladen');
		$download->setClick( new JUI\Click( JUI\Click::openMedia, 'file', FileManager::FILESYSTEM_PLUGIN_DOWNLOAD.'://plg_contacts/' . $command ) );
		$jUI->add($download);
	}
	
} else {
	$pluginManager->redirect( $pluginManager );
}

?>