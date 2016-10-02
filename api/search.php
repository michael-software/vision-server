<?php

require dirname(dirname(__FILE__)) . '/includes/LoginManager.php';

if(!empty($_GET['query'])) {
	require_once dirname(dirname(__FILE__)).'/includes/SearchManager.php';
	
	$searchManager = new SearchManager();
	$array = $searchManager->getArray($_GET['query']);
	
	if(!empty($array) && $array != null) {
		echo json_encode($array);
	}
}

?>