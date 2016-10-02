<?php

$command = $pluginManager->getCommand();

if(!empty($command)) {
	$folder = implode('/', $pluginManager->getCommand()).'/';
} else {
	$folder = "";
}

if($folder == "./") {
	$folder = "";
}

$folder = str_replace('//', '/', $folder);
$folder = str_replace('..', '.', $folder);
$folder = rtrim($folder, '/');

if(!empty($_POST['shareFiles'])) {
	global $plg_files_parameter;
	
	if(!empty($_POST['allowUpload'])) {
		$plg_files_parameter['allowUpload'] = TRUE;
	} else {
		$plg_files_parameter['allowUpload'] = FALSE;
	}
	
	if(!empty($_POST['allowCreate'])) {
		$plg_files_parameter['allowCreate'] = TRUE;
	} else {
		$plg_files_parameter['allowCreate'] = FALSE;
	}
	
	if(!empty($_POST['allowDeeper'])) {
		$plg_files_parameter['allowDeeper'] = TRUE;
	} else {
		$plg_files_parameter['allowDeeper'] = FALSE;
	}
	
	$url = $loginManager->getShareManager()->getUrl($pluginManager->getPluginName(), 'home', $folder);
	
	$jUI->add( new JUI\Heading('Freigabelink') );
	
	$warning = new JUI\Text('Dieser Link ist ab sofort gültig. Sollten sie ihn doch nicht gebrauchen, so empfehlen wir ihnen diesen zu löschen.');
	$warning->setColor('#FF0000');
	$jUI->add( $warning );
	
	$input = new JUI\Input('url');
	$input->setValue($url);
	$jUI->add($input);
	
	$jUI->nline(2);
	
	$ok = new JUI\Button('OK');
	$ok->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'home', $folder ) );
	$jUI->add($ok);
} else {
	$jUI->add( new JUI\Heading('Freigabe einrichten') );
	
	$allowUpload = new JUI\Checkbox('allowUpload');
	$allowUpload->setLabel('Upload erlauben');
	$jUI->add($allowUpload);
	
	$jUI->nline();
	
	$allowCreate = new JUI\Checkbox('allowCreate');
	$allowCreate->setLabel('Ordner erstellen erlauben');
	$jUI->add($allowCreate);
	
	$jUI->nline();
	
	$allowDeeper = new JUI\Checkbox('allowDeeper');
	$allowDeeper->setLabel('Darf in Unterordner schauen');
	$jUI->add($allowDeeper);
	
	$input = new JUI\Input('shareFiles');
	$input->setValue('true');
	$input->setVisible( JUI\View::GONE );
	$jUI->add($input);
	
	$jUI->nline(2);
	
	$jUI->add( new JUI\Button('Freigeben', TRUE) );
}

//echo $loginManager->getShareManager()->getUrl($pluginManager->getPluginName(), 'home', $folder);

?>