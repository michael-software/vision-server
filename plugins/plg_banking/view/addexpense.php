<?php
	if(!empty($_POST['name']) AND !empty($_POST['value'])) {
		$name  = $_POST['name'];
		$value = str_replace(',', '.', $_POST['value']);
		$value = -1*round(floatval($value)*100);

		$timestamp = time();
		if(!empty($_POST['timestamp'])) {
			$timestamp = $_POST['timestamp'];
		}
		
		$pluginManager->databaseManager->insertValue(Array("name"=>Array("value"=>$name),"value"=>Array("value"=>$value), "timestamp"=>Array("value"=>$timestamp, "type"=>"integer")));
		
		die('{"redirect":["plg_banking", "home", ""]}');
	}

	$jUI->add(new JUI\Heading('Ausgabe hinzufügen'));

	$name = new JUI\Input('name');
	$name->setLabel('Name: ');
	$jUI->add($name);

	$jUI->nline();

	$price = new JUI\Input('value');
	$price->setPreset(JUI\Input::NUMBERS);
	$price->setLabel('Preis: ');
	$jUI->add($price);

	$jUI->nline();

	$date = new JUI\Input('timestamp');
	$date->setPreset(JUI\Input::DATE);
	$date->setLabel('Datum: ');
	$jUI->add($date);

	$jUI->nline(2);

	$submit = new JUI\Button('Speichern', true);
	$jUI->add($submit);
?>