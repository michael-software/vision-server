<?php

class ViewModel {
    function __construct() {
        global $pluginManager;

        $commands = $pluginManager->getCommand();

        if(empty($commands[0])) {
            if (method_exists($this, $f='_default')) {
                return call_user_func_array(array($this,$f), array());
            }
        } else if(is_numeric($commands[0])) {
            if (method_exists($this, $f='_numeric')) {
                return call_user_func_array(array($this,$f), $commands);
            }
        } else {
            $command = $commands[0];
            unset($commands[0]);
            
            if (method_exists($this, $f=$command)) {
                return call_user_func_array(array($this,$f), $commands);
            }
        }
    }
}

?>