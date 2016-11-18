<?php

require_once(dirname(__FILE__) . '/PluginManager.php');

class AsyncManager {
	private $pluginDir;
	private $plugin;
	
	function __construct($user, $plugin) {
		$this->pluginDir = dirname(dirname(__FILE__)) . '/plugins/' . $plugin . '/';
		$this->plugin = $plugin;
	}
	
	function triggerAction($action, $value) {
		$pluginManager =  new PluginManager($this->plugin);

		$actionFile = $this->pluginDir . 'listener/async.php';

		if(is_dir($this->pluginDir) && is_file($actionFile)) {
			include $actionFile;
		}
	}
}

?>