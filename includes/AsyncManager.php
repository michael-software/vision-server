<?php

require_once(dirname(__FILE__) . '/PluginManager.php');

if(constant('WEBSOCKET') == 1)
    require_once(dirname(__FILE__) . '/LoginManagerAsync.php');
else
    require_once(dirname(__FILE__) . '/LoginManager.php');

class AsyncManager {
	private $pluginDir;
	private $plugin;
    private $user;
	
	function __construct() {
        $a = func_get_args();
        $i = func_num_args();

        if (method_exists($this, $f='__construct'.$i)) {
            call_user_func_array(array($this,$f), $a);
        }
	}

    function __construct1($plugin) {
        $this->pluginDir = dirname(dirname(__FILE__)) . '/plugins/' . $plugin . '/';
        $this->plugin = $plugin;
    }

    function __construct2($user, $plugin) {
        $this->pluginDir = dirname(dirname(__FILE__)) . '/plugins/' . $plugin . '/';
        $this->plugin = $plugin;
        $this->user = $user;
    }


	
	function triggerAction($action, $value) {
		$pluginManager =  new PluginManager($this->plugin);

        if(constant('WEBSOCKET') == 1)
            $loginManager = new LoginManagerAsync($this->user);
        else
            global $loginManager;

		$actionFile = $this->pluginDir . 'listener/async.php';

		if(is_dir($this->pluginDir) && is_file($actionFile)) {
			include $actionFile;
		}
	}
}

?>