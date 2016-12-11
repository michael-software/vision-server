<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');

if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

require dirname(dirname(__FILE__)) . '/includes/LoginManager.php';

if (!empty($_GET['file'])) {
	require_once dirname(dirname(__FILE__)) . '/includes/FileManager.php';
	
	$fileManager = new FileManager();
	
	$fileManager->getFile($_GET['file']);
}

?>