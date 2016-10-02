<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/includes/ConfigManager.php';
//require_once $pluginManager->getController('configeditor');

$configEditor = new ConfigManager(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
$config = $configEditor->loadConfig();

if(!empty($_POST)) {
	foreach($_POST as $key=>$value) {
		$configEditor->setValue($key, $value);
	}
	
	$configEditor->save();
	
	$jUI->setWarning('Eingaben gespeichert.');
}

$jUI->add( new JUI\Heading("Konfigurations-Editor") );

$table = new JUI\Table();

foreach($config as $key=>$value) {
	$keyText = new JUI\Text($key);
	$keyText->setAppearance( JUI\Text::BOLD );
	
	$keyDescription = new JUI\Text($value['description']);
	$keyDescription->setAppearance( JUI\Text::ITALIC );
	
	$keyColumn = new JUI\Table\Column();
	$keyColumn->add( $keyText );
	$keyColumn->add( $keyDescription );
	
	$row = new JUI\Table\Row();
	$row->addColumn($keyColumn);
	
	if($value['type'] == "STRING") {
		$view = new JUI\Input($key);
		$view->setValue($value['value']);
	} else if($value['type'] == "INTEGER") {
		$view = new JUI\Input($key);
		$view->setAccepted( JUI\Input::NUMBERS );
		$view->setValue($value['value']);
	} else if($value['type'] == "BOOLEAN") {
		$view = new JUI\Select($key);
		$view->addItem("TRUE");
		$view->addItem("FALSE");
		$view->setValue( strtoupper($value['value']) );
	} else if($value['type'] == "COLOR") {
		$view = new JUI\Color($key);
		$view->setValue($value['value']);
	} else if($value['type'] == "ARRAY") {
		$view = new JUI\Text(json_encode($value['value']));
	} else {
		if(is_string($value['value']))
		$view = new JUI\Text($value['value']);
	}
	
	$row->addColumn($view);
	$table->addRow($row);
}
$jUI->add($table);

$jUI->add( new JUI\Button("Speichern", true) );

?>