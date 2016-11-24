<?php

set_time_limit(3600);

require_once dirname(dirname(__FILE__)) . '/config.php';

use
    Sabre\DAV,
    Sabre\DAV\Auth,
    Sabre\DAVACL;

// The autoloader
require dirname(__FILE__) . '/SabreDAV/vendor/autoload.php';

require_once dirname(__FILE__) . '/trait.php';
require_once dirname(__FILE__) . '/CustomDirectory.php';
require_once dirname(__FILE__) . '/CustomFile.php';
require_once dirname(__FILE__) . '/homecollection.php';
require_once dirname(__FILE__) . '/CustomPDO.php';

$lockBackend = new DAV\Locks\Backend\File('data/locks');
$lockPlugin = new DAV\Locks\Plugin($lockBackend);

$pdo = new \PDO('mysql:dbname=' . $conf['db_database'], $conf['db_username'], $conf['db_password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$authBackend = new Auth\Backend\PDO($pdo);
$authBackend->setRealm($conf['dav_realm']);
$authPlugin = new Auth\Plugin($authBackend);


$principalBackend = new MyServer\CustomPDO($pdo);

$aclPlugin = new \Sabre\DAVACL\Plugin();
$aclPlugin->hideNodesFromListings = true;
$aclPlugin->allowAccessToNodesWithoutACL = false;
$aclPlugin->defaultUsernamePath = 'principals/users';

$server = new DAV\Server(new MyServer\HomeCollection($principalBackend, $authPlugin));

$server->setBaseUri('/webdav2/index.php');
// Adding the plugin to the server.
$server->addPlugin($authPlugin);
$server->addPlugin($lockPlugin);
$server->addPlugin($aclPlugin);

$server->addPlugin(new DAV\Browser\Plugin());
$server->exec();


?>