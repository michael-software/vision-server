<?php
if(!empty($pluginManager->getCommand())) {
	$file = implode('/', $pluginManager->getCommand());

	$jUI->add(new JUI\Heading("Dies ist eine temporäre Datei"));
	$jUI->add(new JUI\Text("Diese Dateien werden für gewöhnlich vom System erstellt um Funktionen, wie beispielsweise den Download von Dateien und Ordnern anzubieten."));
	$jUI->add(new JUI\Text("Nach erfolgreichem Download werden sie für gewöhnlich gelöscht. Sollten sie aktuell keinen Download von diesem Server ausführen, so können sie sie bedenkenlos löschen."));
	$jUI->add( new JUI\Button("Datei löschen", new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'delete', $file)) );
}
?>