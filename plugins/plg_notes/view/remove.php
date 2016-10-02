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
	
	$where['id']['value'] = $id;
	$where['id']['type'] = 'i';
	$pluginManager->databaseManager->remove($where);
}

$pluginManager->redirect( $pluginManager );
?>