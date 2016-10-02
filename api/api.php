<?php

require dirname(dirname(__FILE__)) . '/includes/PluginManager.php';
require dirname(dirname(__FILE__)) . '/includes/LoginManager.php';

if(!empty($_GET['plugin']) AND !empty($_GET['get']) AND $_GET['get'] == 'api') {
	if(!empty($_GET['page'])) {
		$page = $_GET['page'];
	} else {
		$page = 'default';
	}
	
	if(!empty($_GET['cmd'])) {
		$cmd = $_GET['cmd'];
	} else {
		$cmd = '';
	}
	
	if(!$loginManager->getShareManager()->isShared()) {
		$pluginManager = new PluginManager($_GET['plugin']);
		$pluginManager->getApi($page);
	}
}

?>