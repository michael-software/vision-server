<?php

require dirname(dirname(__FILE__)) . '/includes/LoginManager.php';

if (!empty($_GET['file'])) {
	require_once dirname(dirname(__FILE__)) . '/includes/FileManager.php';
	
	$fileManager = new FileManager();
	
	$fileManager->getFile($_GET['file']);
}

?>