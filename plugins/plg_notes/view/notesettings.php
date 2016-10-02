<?php

$id = $pluginManager->getCommand(0);
$password = $pluginManager->getCommand(1);

if(!empty($id)) {
	$value = $pluginManager->databaseManager->getValues(Array("id"=>Array("operator"=>"=", "value"=>$id)),1);
	
	if(!empty($value['password']) && !empty($password)) {
		if($value['password'] != $password){
			$pluginManager->redirect( $pluginManager, 'password', $id );
		}
	} else if(!empty($value['password']) && empty($password)) {
		$pluginManager->redirect( $pluginManager, 'password', $id );
	}
	
	
} else {
	$pluginManager->redirect( $pluginManager );
}

$jUI->add( new JUI\Heading("Notiz - Einstellungen") );

$delete = new JUI\Button("Notiz löschen");
$delete->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'remove', $id.'/'.$password ) );
$jUI->add($delete);

?>
