<?php

class AsyncManager {
	private $pluginDir;
	
	function __construct($user, $plugin) {
		$this->pluginDir = dirname(dirname(__FILE__)) . '/plugins/' . $plugin . '/';
	}
	
	function triggerAction($action, $value) {
		$actionFile = $this->pluginDir . 'listener/async.php';
		
		if(is_dir($this->pluginDir) && is_file($actionFile)) {
			include $actionFile;
		}
	}
}

?>