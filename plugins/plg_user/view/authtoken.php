<?php

$id = $pluginManager->getCommand(0);
$action = $pluginManager->getCommand(1);

if(!empty($id) && !empty($action)) {
	if(is_numeric($id)) {
		if($action == 'remove') {
			$loginManager->removeAuthtokenById($id);
			die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
		}
	} else if($id == 'removeAll' && $action == 'yes') {
		$loginManager->removeAllAuthtokens();
		$loginManager->logout();
	}
} else if(!empty($id) && empty($action)) {
	if(is_numeric($id)) {
		if(!empty($_POST['name'])) {
			$name = $_POST['name'];
			
			$loginManager->setAuthtokenName($id, $name);
			
			$pluginManager->redirect( $pluginManager );
		}
		
		$authtokenInfo = $loginManager->getAuthtokenInfoById($id);
		
		if(!empty($authtokenInfo['name'])) {
			$name = $authtokenInfo['name'];
		} else {
			$name = "kein Name";
		}
		
		$timestamp = $authtokenInfo['timestamp'];
		$authtoken = $authtokenInfo['authtoken'];
		
	
		$heading = new JUI\Heading('Authtoken bearbeiten');
		$jUI->add($heading);
		
		$input = new JUI\Input('name');
		$input->setValue($name);
		$input->setLabel('Bezeichnung: ');
		$jUI->add($input);
		
		$jUI->add("Zeitpunkt: " . $timestamp);
		$jUI->add("Authtoken: " . $authtoken);
		
		$submit = new JUI\Button('Speichern', TRUE);
		$jUI->add($submit);
		
		$delete = new JUI\Button('Authtoken entfernen');
		$delete->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'authtoken', $id.'/remove' ) );
		$jUI->add($delete);

	} else {
		if($id == 'removeAll') {
			$heading = new JUI\Heading('Sind sie sich sicher, dass sie alle Authtokens löschen wollen?');
			$jUI->add($heading);
			
			$jUI->add('Nach der Löschung kann sich keines ihrer Endgeräte mehr mit dem Server verbinden. Sie werden auch von diesem Gerät abgemeldet. Wollen sie fortfahren?');
			
			$yes = new JUI\Button('Ja');
			$yes->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'authtoken', 'removeAll/yes' ) );
			$jUI->add($yes);
			
			$no = new JUI\Button('Nein');
			$no->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager ) );
			$jUI->add($no);
		}
	}
}
?>