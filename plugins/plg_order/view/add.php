<?php
$pluginName = $pluginManager->getCommand(0);
$loginManager->addMainPlugin($pluginName);

$pluginManager->redirect($pluginManager->getPluginName(), 'change');
?>