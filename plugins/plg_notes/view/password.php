<?php
if(!empty($_POST['notePassword']) && !empty($_POST['noteId']))
$pluginManager->redirect( $pluginManager, 'notes', $_POST['noteId'] . '/' . LoginManager::getSaltedPassword($loginManager->getUsername(), $_POST['notePassword']) );

$noteId = $pluginManager->getCommand(0);

$jUI->add( new JUI\Heading("Anmeldung für Notiz") );

$idInput = new JUI\Input("noteId");
$idInput->setValue( $noteId );
$idInput->setVisible( JUI\View::GONE );
$jUI->add($idInput);

$password = new JUI\Input("notePassword");
$password->setLabel("Kennwort");
$password->setPreset( JUI\Input::PASSWORD );
$jUI->add($password);

$jUI->nline(2);

$submit = new JUI\Button("Speichern");
$submit->setClick( new JUI\Click( JUI\Click::submit ) );
$jUI->add( $submit );

?>