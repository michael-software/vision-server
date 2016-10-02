<?php
	$month = 0;
	if(!empty($pluginManager->getCommand(0))) {
		$month = $pluginManager->getCommand(0);
	}

	$currentMonth = date('m');
	$currentYear = date('Y');
	$current = mktime(0, 0, 0, $currentMonth-1, 1, $currentYear);

	$currentMonth -= $month-2;
	$years = floor( abs($currentMonth)/12 );
	$currentMonth += $years*12;


	$selectedStart = mktime(0, 0, 0, $currentMonth-1, 1, date('Y')-$years);
	$selectedEnd = mktime(0, 0, 0, $currentMonth, 0, date('Y')-$years);

	$statement = array(
		array("name"=>"timestamp", "value"=>$selectedStart, "operator"=>">", "type"=>"integer"),
		array("name"=>"timestamp", "value"=>$selectedEnd,   "operator"=>"<", "type"=>"integer")
	);

	
	if($month == 0) {
		$values = $pluginManager->databaseManager->getValues();
	} else {
		$values = $pluginManager->databaseManager->getValues($statement);
	}

	if($month > 0) {
		$jUI->add( new JUI\Heading("Überblick (" . date('m Y', $selectedStart) . ")") );
	} else {
		$jUI->add( new JUI\Heading("Überblick") );
	}

	$select = new JUI\Select('month');
	if($month == 0) {
		$select->addItem('Alles', '0');
	}
	for($i = 1; $i < 7; $i++) {
		if($currentMonth-$i < 1) {
			$ts = mktime(0, 0, 0, $currentMonth-$i+12, 1, $currentYear-1);
		} else {
			$ts = mktime(0, 0, 0, $currentMonth-$i, 1, $currentYear);
		}

		if(($month == 0 && $i != 1) || $month != 0)
			$select->addItem(date('m Y', $ts), $month+$i-1);
	}
	if($month > 0) {
		$select->addItem('Alles', '0');
	}
	
	$select->setOnChange( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'overview', 'this.value') );
	$jUI->add($select);
	
	$table = new JUI\Table();
	$table->setWidth("100%");
	
	$array = null;
	$overview = 0;
	
	if(!empty($values) && is_array($values))
	foreach ($values as $value) {
		$valuePrice = number_format($value['value']/100, 2, ',', '.');
		
		$name = new JUI\Text($value['name']);
		
		if($valuePrice >= 0) {
			$text = new JUI\Text($valuePrice . ' €');
			$text->setAlignment( JUI\Text::RIGHT );
			$text->setColor("#00FF00");
		} else {
			$text = new JUI\Text($valuePrice . ' €');
			$text->setAlignment( JUI\Text::RIGHT );
			$text->setColor("#FF0000");
		}


		$timestamp = new JUI\Text( date('j.n.Y', $value['timestamp']) );
		$timestamp->setAlignment( JUI\Text::RIGHT );
		
		
		$row = new JUI\Table\Row();
		$row->addColumn($name);
		$row->addColumn($timestamp);
		$row->addColumn($text);
		$row->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'entry', $value['id'] ) );
		$table->addRow($row);
		
		$overview += $value['value']/100;
	}
	
	
	// ALL
	$valuePrice = number_format($overview, 2, ',', '.');
	$name = new JUI\Text("Insgesamt");
	$text = new JUI\Text($valuePrice . ' €');
	$text->setAlignment( JUI\Text::RIGHT );
		
	if($overview < 0) {
		$text->setColor("#FF0000");
	} else {
		$text->setColor("#00FF00");
	}
	
	$table2 = new JUI\Table();
	$table2->setWidth("100%");
	$row = new JUI\Table\Row();
	
	$row->addColumn($name);
	$row->addColumn($text);
	$table2->addRow($row);
	$jUI->add($table2);
	// END ALL
	$jUI->hline();
	
	$jUI->add($table);

	$jUI->nline();

	$table3 = new JUI\Table();
	$table3->setWidth('100%');
	$buttonRow = new JUI\Table\Row();
		if($month > 1) {
			$buttonNext = new JUI\Button( date('m Y', $selectedEnd+3600) );
			$buttonNext->setClick( new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'overview', $month-1) );
			$buttonNext->setWidth('100%');
			$buttonRow->addColumn($buttonNext);
		} else if($month == 1) {
			$buttonNext = new JUI\Button("Zeige alles");
			$buttonNext->setClick( new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'overview') );
			$buttonNext->setWidth('100%');
			$buttonRow->addColumn($buttonNext);
		}

		$buttonLast = new JUI\Button( date('m Y', $selectedStart-3600) );
		$buttonLast->setClick( new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'overview', $month+1) );
		$buttonLast->setWidth('100%');
		$buttonRow->addColumn($buttonLast);
	$table3->addRow($buttonRow);
	$jUI->add($table3);
?>

