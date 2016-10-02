<?php
	$values = $pluginManager->databaseManager->getValues(Array("value"=>Array("operator"=>">", "value"=>"0", "type"=>"i")));
	
	$jUI->add( new JUI\Heading("Einnahmen") );
	
	$table = new JUI\Table();
	$table->setWidth("100%");
	
	if(!empty($values) && is_array($values))
	foreach ($values as $value) {
		$valuePrice = number_format($value['value']/100, 2, ',', '.');
		
		$name = new JUI\Text($value['name']);
		
		$text = new JUI\Text($valuePrice . ' €');
		$text->setAlignment( JUI\Text::RIGHT );
		
		$row = new JUI\Table\Row();
		$row->addColumn($name);
		$row->addColumn($text);
		$row->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'entry', $value['id'] ) );
		$table->addRow($row);
	}
	
	$jUI->add($table);
?>