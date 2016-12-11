<?php
	if(!empty($_POST['noteName']) AND !empty($_POST['noteContent'])) {
		$name  = $_POST['noteName'];
		$content = $_POST['noteContent'];
		
		if(!empty($_POST['notePassword'])) {
			$password = LoginManager::getSaltedPassword($loginManager->getUsername(), $_POST['notePassword']);
		} else {
			$password = '';
		}
		
		$pluginManager->databaseManager->insertValue(Array("name"=>Array("value"=>$name),"text"=>Array("value"=>$content),"password"=>Array("value"=>$password)));
		
		$pluginManager->redirect( $pluginManager );
	}

	$jUI->add(new JUI\Heading("Notiz erstellen"));

	$name = new JUI\Input("noteName");
	$name->setLabel("Name: ");
	$jUI->add($name);

	$jUI->nline();

	$passwordInput = new JUI\Input("notePassword");
	$passwordInput->setPreset(JUI\Input::PASSWORD);
    $passwordInput->setLabel("Kennwort: ");
    $jUI->add($passwordInput);

	$jUI->nline(2);

	$textarea = new JUI\Editor("noteContent");
	$textarea->setWidth('100%');
	$textarea->setHeight(200);
	$jUI->add($textarea);

	$jUI->nline(2);

	$jUI->add( new JUI\Button("Speichern", true) );

	$back = new JUI\Button("Zurück");
	$back->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager ) );
	$jUI->add($back);
?>