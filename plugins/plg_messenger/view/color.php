<?php

require_once $pluginManager->getController('tools');

if(!empty($_POST['color'])) {
	$color = stringToColorCode($_POST['color']);
	
	$text = new JUI\Text("#" . $color);
	$text->setColor("#" . $color);
	
	$jUI->add($text);
}

$jUI->add( new JUI\Input("color") );
$button = new JUI\Button("Senden", TRUE);
$jUI->add( $button );

?>