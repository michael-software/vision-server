<?php
$command = $pluginManager->getCommand();

if(!empty($command)) {
	$folder = implode('/', $command);
} else {
	$folder = "";
}


$jUI->add( new JUI\Heading($command[count($command)-1]) );

$string = $pluginManager->getFileManager()->getFileString($folder);

$text = new JUI\Text($string);
$jUI->add($text);


?>
