<?php
require_once 'wolclass.php';

if(!empty($_POST['authkey']) || !empty($_POST['authtoken'])) {
	if(!empty($_POST['authkey'])) {
		$authtoken = $_POST['authkey'];
	} else {
		$authtoken = $_POST['authtoken'];
	}
	
	$handle = fopen("log.json", "a+");
	
	$inhalt .= '{"authtoken":' . json_encode($authtoken) . ',"timestamp":' . json_encode(time()) . ',"ip":' . json_encode($_SERVER['REMOTE_ADDR']) . ',"host":' . json_encode($_SERVER['REMOTE_HOST']) . '},';
	
	fwrite($handle, $inhalt);
	fclose($handle);
	
	WOL::send("255.255.255.255", "D0:50:99:75:55:BC");
}

?>