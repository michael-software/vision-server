<?php

require 'includes/PluginManager.php';
require 'includes/LoginManager.php';
require_once 'includes/FileManager.php';
require_once dirname(__FILE__) . '/config.php';


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

if(!empty($_GET['get']) && $_GET['get'] == 'seamless') {
	if(!$loginManager->getShareManager()->isShared()) {
		$seamless = $loginManager->getUserPreference("seamless");
		
		if(!empty($seamless) && strtoupper($seamless) == "TRUE") {
			$seamlessString = $loginManager->getUserPreference("seamless-current");
			
			if(!empty($seamlessString)) {
				die('{"seamless":' . $seamlessString . '}');
			}
		}
	}
	
	die('{}');
}

?>