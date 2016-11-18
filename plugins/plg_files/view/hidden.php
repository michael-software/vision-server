<?php
$command = $pluginManager->getCommand();

if(!empty($command)) {
	$folder = implode('/', $pluginManager->getCommand()).'/';
} else {
	$folder = "";
}

if($pluginManager->getTemporary('showHidden', false)) {
	$pluginManager->setTemporary('showHidden', false);
} else {
	$pluginManager->setTemporary('showHidden', true);
}

$pluginManager->redirect($pluginManager, 'home', $folder);

?>
