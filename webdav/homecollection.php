<?php

namespace MyServer;

use Sabre\DAV\Collection;
use Sabre\DAV\FS;
use Sabre\DAV\Auth\Plugin as AuthPlugin;

class HomeCollection extends Collection {

    //protected $users = ['alice','bob','charly'];

    protected $path = 'data/';
    protected $authPlugin;

    function __construct(AuthPlugin $authPlugin) {
		$this->path = dirname(__FILE__) . '/data/';
        $this->authPlugin = $authPlugin;

    }
	
    function getChildren() {

       $result = [];
       //foreach($this->users as $user) {
            //$result[] = new FS\Directory($path . '/' . $user);

       //}
	   //die($this->authPlugin->getCurrentUser());
       $result[] = new FS\Directory($path . '/' . $this->authPlugin->getCurrentUser());
       return $result;

    }

    function getName() {
        return 'test';
    }

}

?>