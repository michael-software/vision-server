<?php

$command = $pluginManager->getCommand(0);

if(!empty($command) && is_numeric($command)) {
	if(!empty($_POST['name'])) {
		$array['name'] = array("value"=>$_POST['name'], "type"=>"varchar");
		
		if(!empty($_POST['earning'])) {
			$value = str_replace(',', '.', $_POST['earning']);
			$value = round(floatval($value)*100);
			
			$array['value'] = array("value"=>$value, "type"=>"integer");
		} else if(!empty($_POST['expense'])) {
			$value = str_replace(',', '.', $_POST['expense']);
			$value = -1 * round(floatval($value)*100);
			
			$array['value'] = array("value"=>$value, "type"=>"integer");
		} else {
			$array['value'] = array("value"=>0, "type"=>"integer");
		}

		$timestamp = time();
		if(!empty($_POST['timestamp'])) {
			$timestamp = $_POST['timestamp'];
		}
		$array['timestamp'] = array("value"=>$timestamp, "type"=>"integer");
		
		$pluginManager->databaseManager->setValue($array, array("id"=>array("value"=>$command, "type"=>"integer")));
		
		$pluginManager->redirect( $pluginManager );
	}
	
	$entry = $pluginManager->databaseManager->getValues(array("id"=>array("value"=>$command, "type"=>"integer")), 1);
	
	if($entry['value'] < 0) {
		$valueString = -1 * $entry['value'] / 100;
		$valueString = number_format($valueString, 2, '.', ',');
		
		$jUI->add( new JUI\Heading("Ausgabe bearbeiten") );
		
		$name = new JUI\Input("name");
		$name->setLabel("Name:");
		$name->setValue($entry['name']);
		$jUI->add($name);
		
		$jUI->nline();
		
		$expense = new JUI\Input("expense");
		$expense->setAccepted( JUI\Input::NUMBERS );
		$expense->setValue( $valueString );
		$expense->setLabel("Preis:");
		$jUI->add($expense);
	} else {
		$valueString = $entry['value'] / 100;
		$valueString = number_format($valueString, 2, '.', ',');
		
		$jUI->add( new JUI\Heading("Einnahme bearbeiten") );
		
		$name = new JUI\Input("name");
		$name->setLabel("Name:");
		$name->setValue($entry['name']);
		$jUI->add($name);
		
		$jUI->nline();
		
		$earning = new JUI\Input("earning");
		$earning->setAccepted( JUI\Input::NUMBERS );
		$earning->setValue( $valueString );
		$earning->setLabel("Preis:");
		$jUI->add($earning);
	}
	
	$jUI->nline();

	$date = new JUI\Input('timestamp');
	$date->setPreset(JUI\Input::DATE);
	$date->setLabel('Datum: ');
	$date->setValue($entry['timestamp']);
	$jUI->add($date);

	$jUI->nline(2);
	
	$jUI->add( new JUI\Button("Speichern", TRUE) );
}

?>