<?php

if(!empty($_POST['about'])) {
	$pluginManager->setPluginStorage('about', $_POST['about']);
}

$jUI->add( new JUI\Heading('Über diesen Server') );

$text = new JUI\Text($pluginManager->getPluginStorage('about'));
$jUI->add($text);

if($loginManager->getGroup() == LoginManager::GROUP_SERVER_ADMIN) {
	$jUI->nline();
	$jUI->hline();
	$jUI->nline();
	
	$jUI->add( new JUI\Heading('Bearbeiten', TRUE) );
	
	$input = new JUI\Input('about');
	$input->setPreset(JUI\Input::MULTILINE);
	$input->setValue( $pluginManager->getPluginStorage('about') );
	$jUI->add($input);
	
	$jUI->nline(2);
	
	$jUI->add( new JUI\Button('Speichern', TRUE) );
}

?>