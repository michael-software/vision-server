<?php

require_once(dirname(__FILE__) . '/PluginManager.php');
require_once(dirname(__FILE__) . '/LoginManagerAsync.php');

class AsyncManager {
	private $pluginDir;
	private $plugin;
    private $user;
	
	function __construct($user, $plugin) {
		$this->pluginDir = dirname(dirname(__FILE__)) . '/plugins/' . $plugin . '/';
		$this->plugin = $plugin;
        $this->user = $user;
	}
	
	function triggerAction($action, $value) {
		$pluginManager =  new PluginManager($this->plugin);
        $loginManager = new LoginManagerAsync($this->user);

		$actionFile = $this->pluginDir . 'listener/async.php';

		if(is_dir($this->pluginDir) && is_file($actionFile)) {
			include $actionFile;
		}
	}
}

?>