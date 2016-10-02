<?php
//$_SERVER["PHP_AUTH_DIGEST"] = "admin";
set_time_limit(3600);


require_once dirname(__FILE__) . '/config.php';

require 'webdav/vendor/autoload.php';
require 'webdav/CustomDirectory.php';
require 'webdav/CustomFile.php';
//require_once dirname(__FILE__) . '/homecollection.php';
use Sabre\DAV;
use Sabre\DAV\Auth;

$lockBackend = new DAV\Locks\Backend\File('data/locks');
$lockPlugin = new DAV\Locks\Plugin($lockBackend);

$pdo = new \PDO('mysql:dbname=' . $conf['db_database'], $conf['db_username'], $conf['db_password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$authBackend = new Auth\Backend\PDO($pdo);
$authBackend->setRealm($conf['dav_realm']);
$authPlugin = new Auth\Plugin($authBackend);

/*
$tree = [
    new MyServer\HomeCollection($authPlugin)
];

$server = new DAV\Server($tree);
*/
$publicDir = new MyDirectory($authPlugin, '.');
$server = new DAV\Server($publicDir);

$server->setBaseUri('/webdav/');
// Adding the plugin to the server.
$server->addPlugin($authPlugin);
$server->addPlugin($lockPlugin);
$server->addPlugin(new DAV\Browser\Plugin());
$server->exec();

?>
