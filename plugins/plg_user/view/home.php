<?php
	$command = $pluginManager->getCommand(0);
	if(!empty($command)) {
		if($command == 'deactivateSeamless') {
			$loginManager->setUserPreference("seamless", "FALSE");
			
			$pluginManager->redirect( $pluginManager );
		} else if($command == 'activateSeamless') {
			$loginManager->setUserPreference("seamless", "TRUE");
			
			$pluginManager->redirect( $pluginManager );
		} else if($command == 'resetColors') {
			$loginManager->setUserPreference("fgcolor", $conf['fgcolor']);
			$loginManager->setUserPreference("bgcolor", $conf['bgcolor']);
			
			$pluginManager->redirect( $pluginManager );
		}
	}
	
	if(!empty($_POST['fgcolor']) && !empty($_POST['bgcolor'])) {
		$loginManager->setUserPreference("fgcolor", $_POST['fgcolor']);
		$loginManager->setUserPreference("bgcolor", $_POST['bgcolor']);
	}

	if(!empty($_POST['encryptionKey'])) {
		$loginManager->setEncryptionKey($_POST['encryptionKey']);
	}

	$heading = new JUI\Heading($loginManager->getUsername());
	$jUI->add($heading);
	
	$userContainer = new JUI\Container();
	$userContainer->setBackgroundColor('#A8A8A8');
	$userContainer->setPadding(20);
	$userContainer->setMarginTop(15);
	$userContainer->setMarginBottom(15);

	$changePassword = new JUI\Button('Kennwort ändern');
	$changePassword->setClick( new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'changepassword') );
	$userContainer->add($changePassword);
	
	$userContainer->nline(2);
	
	$logout = new JUI\Button('Logout');
	$logout->setClick( new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'logout') );
	$userContainer->add($logout);
	
	$seamless = $loginManager->getUserPreference("seamless");
	
	if(!empty($seamless) && strtoupper($seamless) == "TRUE") {
		$seamless = new JUI\Button('Seamlessmodus deaktivieren');
		$seamless->setClick( new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'home', 'deactivateSeamless') );
	} else {
		$seamless = new JUI\Button('Seamlessmodus aktivieren');
		$seamless->setClick( new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'home', 'activateSeamless') );
	}
	$userContainer->add($seamless);
	
	$jUI->add($userContainer);

	$colorContainer = new JUI\Container();
	$colorContainer->setBackgroundColor('#E8E8E8');
	$colorContainer->setPadding(20);
	$colorContainer->setMarginTop(15);
	$colorContainer->setMarginBottom(15);
	
		$colors = new JUI\Heading("Benutzerdefinierte Farben", true);
		$colorContainer->add($colors);
		
		$fgcolorString = $loginManager->getUserPreference("fgcolor");
		if(!empty($fgcolorString)) {
			$fgcolor = new JUI\Color('fgcolor');
			$fgcolor->setValue($fgcolorString);
		} else {
			$fgcolor = new JUI\Color('fgcolor');
			$fgcolor->setValue($conf['fgcolor']);
		}
		$fgcolor->setLabel("Vordergrundfarbe: ");
		$colorContainer->add($fgcolor);
		
		$colorContainer->nline();
		
		$bgcolorString = $loginManager->getUserPreference("bgcolor");
		if(!empty($bgcolorString)) {
			$bgcolor = new JUI\Color('bgcolor');
			$bgcolor->setValue($bgcolorString);
		} else {
			$bgcolor = new JUI\Color('bgcolor');
			$bgcolor->setValue($conf['bgcolor']);
		}
		$bgcolor->setLabel("Hintergrundfarbe: ");
		$colorContainer->add($bgcolor);

		$colorContainer->nline(2);
		
		$submit = new JUI\Button("Speichern", TRUE);
		$colorContainer->add($submit);
		
		$reset = new JUI\Button("Zurücksetzen");
		$reset->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'home', 'resetColors' ) );
		$colorContainer->add($reset);

	$jUI->add($colorContainer);



	$encryptionContainer = new JUI\Container();
	$encryptionContainer->setBackgroundColor('#E8E8E8');
	$encryptionContainer->setPadding(20);
	$encryptionContainer->setMarginTop(15);
	$encryptionContainer->setMarginBottom(15);
	
		$encryptionContainer->add( new JUI\Heading("Entschlüsselung von Daten", true) );

		$encryptionInput = new JUI\Input('encryptionKey');
		$encryptionInput->setLabel('Kennwort (leer lassen fürs beibehalten des alten Kennworts): ');
		$encryptionContainer->add($encryptionInput);

		$encryptionContainer->nline();

		$encryptionButton = new JUI\Button('Speichern', true);
		$encryptionContainer->add($encryptionButton);

	$jUI->add($encryptionContainer);
	


	$authtokenContainer = new JUI\Container();
	$authtokenContainer->setBackgroundColor('#E8E8E8');
	$authtokenContainer->setPadding(20);
	$authtokenContainer->setMarginTop(15);
	$authtokenContainer->setMarginBottom(15);

		$headingAuthtokens = new JUI\Heading('Authtokens');
		$headingAuthtokens->setSmall();
		$authtokenContainer->add($headingAuthtokens);
		
		$deleteAuthtokens = new JUI\Button('Alle Authtokens löschen');
		$deleteAuthtokens->setClick( new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'authtoken', 'removeAll') );
		$authtokenContainer->add($deleteAuthtokens);
		
		$list = new JUI\ListView();
		
		$authtokens = $loginManager->getAuthtokens();
		
		if(!empty($authtokens))
		foreach($authtokens as $authtoken) {
			if(!empty($authtoken['name'])) {
				$name = $authtoken['name'];
			} else {
				$name = "kein Name";
			}
			
			$click = new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'authtoken', $authtoken['id'] );
			
			$list->addItem($name, $click);
		}
		
		$authtokenContainer->add($list);

	$jUI->add($authtokenContainer);

?>
