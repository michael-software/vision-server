<?php

require_once $pluginManager->getController('tools');

$jUI->add( new JUI\Heading('Kontakte') );


$create = new JUI\Button('Kontakt erstellen');
$create->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'create' ) );
$jUI->add($create);

$contacts = $pluginManager->databaseManager->getValues(null, array(array("name"=>"lastname"), array("name"=>"firstname")));

$list = new JUI\ListView();

if(!empty($contacts) && is_array($contacts))
foreach($contacts as $contact) {
	$name = $contact['lastname'] . ',  ' . $contact['firstname'];
	$click = new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'contact', $contact['id'] );
	
	$list->addItem($name, $click);
}

$jUI->add($list);

?>