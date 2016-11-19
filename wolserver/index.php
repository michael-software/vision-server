<?php
require_once 'wolclass.php';

if(file_exists('authtokens.php'))
	require_once 'authtokens.php';

if(file_exists('permissions.php'))
	require_once 'permissions.php';

if(!empty($_POST['authkey']) || !empty($_POST['authtoken'])) {
	if(!empty($_POST['authtoken'])) {
		$decryptedKey = decryptToken($_POST['authtoken']);
	} else {
		$decryptedKey = $_POST['token'];
	}
	
	if(!empty($authtokens) && is_array($authtokens) && !empty($allowed) && is_array($allowed)) {
		if(empty($authtokens[$decryptedKey]))
			die("denied");
		
		$user = $authtokens[$decryptedKey];
		
		if(empty($allowed[$user]) || !$allowed[$user])
			die("denied");
	} else {
		die("denied");
	}
	
	$handle = fopen("log.json", "a+");
	
	$inhalt = '{"user":' . json_encode($user) . ', "authtoken":' . json_encode($decryptedKey) . ',"timestamp":' . json_encode(time()) . ',"ip":' . json_encode($_SERVER['REMOTE_ADDR']) . '},';
	
	fwrite($handle, $inhalt);
	fclose($handle);
	
	WOL::send("255.255.255.255", "D0:50:99:75:55:BC");
}

function decryptToken($pToken) {
	$privateKey = "1234567891234567";
	$iv = "1234567891234567";
	
	$authkey = $_POST['authtoken'];
	
	$decryptedKey = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $privateKey, base64_decode($authkey), MCRYPT_MODE_CBC, $iv);
	$decryptedKey = trim($decryptedKey);
	
	return $decryptedKey;
}

?>