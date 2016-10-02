<?php

$pluginManager->databaseManager->selectTable(0);

$values = $pluginManager->databaseManager->getValues();

$jUI->add(new JUI\Heading("Konten"));

if(!empty($values) && is_array($values)) {
	$list = new JUI\ListView();
	
	foreach($values as $account) {
		$click = new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'mail', $account['id']);
		$list->addItem($account['server'], $click);
	}
	
	$jUI->add($list);
} else {
	$jUI->add(new JUI\Text("Keine Konten vorhanden."));
}

$create_account = new JUI\Button("Konto hinzufügen");
$create_account->setClick(new JUI\Click( JUI\Click::openPlugin, $pluginManager, "create"));
$jUI->add($create_account);

?>