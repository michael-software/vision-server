<?php
	if(!empty($pluginManager->getCommand(0))) {
		$upperfolder = $pluginManager->getCommand(0);
	} else {
		$upperfolder = '';
	}

	if($pluginManager->fileManager->isFolder($upperfolder)) {
		if(!empty($_POST['folder'])) {
			if( $pluginManager->fileManager->createFolder($_POST['folder'], $upperfolder) ) {
				$pluginManager->redirect( $pluginManager, 'home', $upperfolder . $_POST['folder'] );
			}
			
			$pluginManager->redirect( $pluginManager, 'home', $upperfolder );
		}
	} else {
		$pluginManager->redirect( $pluginManager, 'home' );
	}


	$jUI->add(new JUI\Heading('Ordner erstellen'));


	$input = new JUI\Input('folder');
	$input->setLabel('Ordnername:');
	$jUI->add($input);

	$jUI->nline(2);

	$jUI->add(new JUI\Button('Ordner erstellen', true));

	$back = new JUI\Button('Zurück');
	$back->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'home', $upperfolder) );
	$jUI->add($back);


?>
