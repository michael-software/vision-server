<?php
	$id = $pluginManager->getCommand(0);
	$password = $pluginManager->getCommand(1);
	$editable = $pluginManager->getCommand(2);
	
	if(!empty($_POST['noteContent']) AND !empty($id)) {
		$content = $_POST['noteContent'];
		$change = Array("text"=>Array("value"=>$content));
		
		if(!empty($_POST['password'])) {
			$change['password']['value'] = LoginManager::getSaltedPassword($loginManager->getUsername(), $_POST['password']);
		}
		
		$pluginManager->databaseManager->setValue($change, Array("id"=>Array("operator"=>"=", "value"=>$id, "type"=>"i")));
		
		$pluginManager->redirect( $pluginManager );
	}
	
	$value = $pluginManager->databaseManager->getValues(Array("id"=>Array("operator"=>"=", "value"=>$id)),1);
	
	if(!empty($value['password']) && !empty($password)) {
		if($value['password'] != $password){
			$pluginManager->redirect( $pluginManager, 'password', $id );
		}
	} else if(!empty($value['password']) && empty($password)) {
		$pluginManager->redirect( $pluginManager, 'password', $id );
	}

	$jUI->add(new JUI\Heading($value['name']));

	if(empty($editable)) {
		$content = new JUI\Text($value['text'], true);
		$content->setClick(new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'notes/' . $id . '/' . $password . '/true' ));

		$jUI->add($content);


		$edit = new JUI\Button('Bearbeiten');
		$edit->setClick(new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'notes/' . $id . '/' . $password . '/true' ));
		$jUI->add($edit);

		$jUI->nline(2);
	} else {
		$textarea = new JUI\Editor("noteContent");
		$textarea->setWidth('100%');
		$textarea->setHeight(200);
		$textarea->setValue($value['text']);
		$jUI->add($textarea);

		$warning = new JUI\Text("Wenn das Kennwortfeld leer gelassen wird, so wird das bisherige Kennwort benutzt.");
		$warning->setColor("#FF0000");
		$jUI->add($warning);

		$passwordInput = new JUI\Input("password");
		$passwordInput->setPreset(JUI\Input::PASSWORD);
		$jUI->add($passwordInput);

		$jUI->nline(2);

		$settings = new JUI\Button("Einstellungen");
		$tmpId = $id;
		if (!empty($password)) $tmpId = $id . '/' . $password;
		$settings->setClick(new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'notesettings', $tmpId));
		$jUI->add($settings);

		$jUI->add(new JUI\Button("Speichern", true));
	}

	$back = new JUI\Button("Zurück");
	$back->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager ) );
	$jUI->add($back);
?>
