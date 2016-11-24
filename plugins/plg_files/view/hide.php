<?php

function startsWith($haystack, $needle) {
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}



$folder = '';
$parent = '';
$commands = $pluginManager->getCommand();
if(!empty($commands)) {
	if(count($commands)-2 > 0) {
		$parent = implode('/', array_slice($commands, 0, count($commands)-2)) . '/' ;
	} else {
		$parent = '';
	}
	$folder = $commands[count($commands)-2];
}

if(startsWith($folder, '.')) {
	$hideFolder = $parent . trim($folder, '.');
} else {
	$hideFolder = $parent . '.' . trim($folder, '.');
}

/*var_dump($parent.$folder);
var_dump($hideFolder);
*/
$pluginManager->getFileManager()->rename($parent.$folder, $hideFolder, FileManager::FILESYSTEM_PRIVATE);

$pluginManager->redirect($pluginManager, 'home', dirname($folder));

?>
