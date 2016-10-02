<?php
$pluginName = $pluginManager->getCommand(0);
$loginManager->removeMainPlugin($pluginName);

$pluginManager->redirect($pluginManager->getPluginName(), 'change');
?>