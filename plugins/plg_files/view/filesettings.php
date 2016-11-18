<?php

$file = '';
if(!empty($pluginManager->getCommand())) {
	$file = trim( implode('/', $pluginManager->getCommand()), '/');
}

$jUI->add(new JUI\Heading('Datei'));

$jUI->add(new JUI\Heading('Speicherort:', true));
$jUI->add($file);

$jUI->nline(2);


$delete = new JUI\Button('Datei löschen');
$delete->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'delete', $file ) );
$delete->setMarginTop(10);
$delete->setMarginBottom(20);
$jUI->add($delete);

$jUI->nline();

$download = new JUI\Button('Herunterladen');
$download->setClick( new JUI\Click( JUI\Click::openMedia, 'file', $file ) );
$jUI->add($download);

$jUI->nline(2);

$back = new JUI\Button('Zurück');
$back->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'home', dirname($file) ) );
$jUI->add($back);

?>
