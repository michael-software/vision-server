<?php

function stringToColorCode($str) {
	$code = dechex(crc32($str));
	$code = substr($code, 0, 6);
	
	return $code;
}

function nameValueRow($name, $value) {
	$row = new JUI\Table\Row();
	$row->setClick( new JUI\Click( JUI\Click::openPlugin, 'plg_serversettings', 'home', '' ) );
	
	$columnName = new JUI\Table\Column();
	$nameText = new JUI\Text($name);
	$nameText->setAppearance(JUI\Text::BOLD);
	$columnName->add($nameText);
	
	$columnValue = new JUI\Table\Column();
	$valueText = new JUI\Text($value);
	$columnValue->add($valueText);
	
	$row->addColumn($columnName);
	$row->addColumn($columnValue);
	
	return $row;
}

?>