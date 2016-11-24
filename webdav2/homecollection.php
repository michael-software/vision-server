<?php

namespace MyServer;

use Sabre\DAV\Collection;

class HomeCollection extends Collection {

    private $principalBackend;
    private $authPlugin;
    private $userdir;

    function __construct($principalBackend, $authPlugin) {
        $this->principalBackend = $principalBackend;
        $this->authPlugin = $authPlugin;

        $this->path = dirname(dirname(__FILE__)) . '/data/';
    }

    private $users = ['admin'];

    protected $path;

    function getChildren() {

        $currentPrinciple = $this->authPlugin->getCurrentPrincipal();
        $this->users = $this->principalBackend->getPrincipalsByPrefix('principals/users');


        $result = [];
        if(!empty($this->users) && is_array($this->users))
        foreach($this->users as $user) {

            $uri = $user["uri"];
            $username = explode('/', $user["uri"]);
            $userid = $user['userid'];

            $userdir = $this->path . 'user_' . $userid;

            if( empty($userid) || $username[0] != 'principals' || $username[1] != 'users' || empty($username[2]) ) {
                continue;
            }

            $username = $username[2];


            if($currentPrinciple === 'principals/' .  $username) {

                $this->_createHomeDirectories($userdir);
                $this->userdir = $userdir;


                $directory = new ACLDirectory($this->path . 'user_' . $userid . '/files', 'principals/' . $username);

                $result = array_merge($directory->getChildren(), $result);
            }

        }

        return $result;

    }

    function createDirectory($name) {
        mkdir($this->userdir . '/files/' . $name, 0744, true);
    }

    function createFile($name, $data = null) {
        file_put_contents($this->userdir . '/files/' . $name, $data);
        clearstatcache(true, $this->userdir . '/files/' . $name);
    }

    function _createHomeDirectories($userdir) {
        if(!is_dir($userdir .'/') && !file_exists($userdir)) {
            mkdir($userdir . '/', 0744, true);
        }

        if(!is_dir($userdir . '/files/') && !file_exists($userdir . '/files')) {
            mkdir($userdir . '/files/', 0744, true);
        }

        if(!is_dir($userdir . '/.userfiles/') && !file_exists($userdir . '/.userfiles')) {
            mkdir($userdir . '/.userfiles/', 0744, true);
        }
    }

    function getName() {

        return 'home';

    }

}
?>