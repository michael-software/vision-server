<?php
// die maximale Ausführzeit erhöhen
ini_set("max_execution_time", 300);

function zip($pPath) {
	global $pluginManager;
	
	$folder = $pluginManager->fileManager->userFiles . $pPath;
	
	shell_exec('cd ' . $folder . ' && zip -r ' . $folder . '.zip . -x .\* "*/\.*"');
	
	return $folder . '.zip';
}
?>