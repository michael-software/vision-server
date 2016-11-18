<?php

$folder = '';
$commands = $pluginManager->getCommand();
if(!empty($commands)) {
	$folder = implode('/', $commands);
}

$jUI->add(new JUI\Heading('Ordner'));

$jUI->add(new JUI\Heading('Speicherort:', true));
$jUI->add($folder);

$jUI->nline(2);

$warning = new JUI\Text('Bitte beachten sie, dass beim Löschen eines Ordners auch sämtlicher Inhalt gelöscht wird !');
$warning->setColor('#FF0000');
$jUI->add($warning);


$delete = new JUI\Button('Ordner löschen');
$delete->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'delete', $folder ) );
$delete->setMarginTop(10);
$delete->setMarginBottom(20);
$jUI->add($delete);

if(FileManager::isVisible($commands[count($commands)-2])) {
	$hide = new JUI\Button('Ordner verstecken');
} else {
	$hide = new JUI\Button('Ordner anzeigen');
}
$hide->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'hide', $folder ) );
$hide->setMarginTop(10);
$hide->setMarginBottom(20);
$jUI->add($hide);

$jUI->nline();

$zip = new JUI\Button('Zu Zip Datei');
$zip->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'zip', $folder ) );
$jUI->add($zip);

$download = new JUI\Button('Als ZIP-Datei herunterladen');
$download->setClick( new JUI\Click( JUI\Click::openMedia, 'file', $folder ) );
$jUI->add($download);

$jUI->nline(2);

$back = new JUI\Button('Zurück');
$back->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'home', dirname($folder) ) );
$jUI->add($back);

?>
