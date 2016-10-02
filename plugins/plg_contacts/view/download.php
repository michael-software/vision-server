<?php

include $pluginManager->getController('vcard');
$id = $pluginManager->getCommand(0);

if(!empty($id) && is_numeric($id)){
	
	$contact = $pluginManager->databaseManager->getValues( array('id'=>array('value'=>$id)) , 1);
	
	if(!empty($contact) && is_array($contact)) {
		$filename = uniqid() . '.vcf';
		
		if(!empty($contact['firstname']) && !empty($contact['lastname'])) {
			$filename = $contact['firstname'] . '_' . $contact['lastname'] . '.vcf';
		}
		
		header("Content-Type: application/download");
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		
		echo createVCard($contact);
	}

}
?>