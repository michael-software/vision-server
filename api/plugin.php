<?php

header('Content-Type: application/json');

require dirname(dirname(__FILE__)) . '/includes/PluginManager.php';
require dirname(dirname(__FILE__)) . '/includes/LoginManager.php';

if(!empty($_GET['plugin'])) {
	if(!empty($_GET['page'])) {
		$page = $_GET['page'];
	} else {
		$page = 'home';
	}
	
	if(!empty($_GET['cmd'])) {
		$cmd = $_GET['cmd'];
	} else {
		$cmd = '';
	}
	
	if(empty($loginManager->getShareManager()) || $loginManager->getShareManager()->isAllowed($_GET['plugin'], $page, $cmd)) {
		ob_start();
		$pluginManager = new PluginManager($_GET['plugin']);
		$pluginManager->getView($page);
		$out = ob_get_contents(); 
		ob_end_clean();
		
		$out = str_replace("\r\n", "", $out);
		$out = str_replace("\t", "", $out);
		$out = str_replace(" :", ":", $out);
		$out = str_replace(": ", ":", $out);
		$out = str_replace(" ,", ",", $out);
		$out = str_replace(", ", ",", $out);
		$out = str_replace(" }", "}", $out);
		$out = str_replace("} ", "}", $out);
		$out = str_replace(" {", "{", $out);
		$out = str_replace("{ ", "{", $out);
		
		echo $out;
	}
	
	if(!$loginManager->getShareManager()->isShared()) {
		$seamless = $loginManager->getUserPreference("seamless");
		
		if(!empty($seamless) && strtoupper($seamless) == "TRUE") {
			$input['name'] = $_GET['plugin'];
			$input['page'] = $page;
			$input['command'] = $cmd;
			$input = json_encode($input);
			
			$loginManager->setUserPreference("seamless-current", $input);
		}
	}
}

?>