<?php

header('Content-Type: application/json');

require dirname(dirname(__FILE__)) . '/includes/PluginManager.php';
require dirname(dirname(__FILE__)) . '/includes/LoginManager.php';

$pluginManager = new PluginManager();
echo $pluginManager->getPlugins();

?>