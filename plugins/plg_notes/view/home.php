<?php
	$notesArray = $pluginManager->databaseManager->getValues();
	$notes = null;
	$noteIds = null;
	$noteLong = null;


	$jUI->add(new JUI\Heading("Notizen"));


	$list = new JUI\ListView();
	foreach($notesArray as $note) {
		$name = $note['name'];
		
		if(empty($note['password'])) {
			$click = new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'notes', $note['id'] );
			$longClick = new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'notesettings', $note['id'] );
		} else {
			$click = $longClick = new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'password', $note['id'] );
		}

		$list->addItem($name, $click, $longClick);
	}
	$jUI->add($list);


	$button = new JUI\Button("Neue Notiz erstellen");
	$button->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'addnote' ) );
	$jUI->add($button);
?>