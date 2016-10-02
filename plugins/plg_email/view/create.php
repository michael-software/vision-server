<?php

if(!empty($_POST['server']) && !empty($_POST['username']) && !empty($_POST['password'])) {
	$server   = $_POST['server'];
	$username = $_POST['username'];
	$password = $_POST['password'];
	$port = 143;
	
	$description = $username . '@' . $server;
	
	if(!empty($_POST['port']) && is_numeric($_POST['port'])) {
		$port = $_POST['port'];
	}
	
	if(!empty($_POST['description'])) {
		$description = $_POST['description'];
	}
	
	$pluginManager->databaseManager->selectTable(0);
	
	$array['server']   = array("type"=>"varchar", "value"=>$server);
	$array['port']     = array("type"=>"integer", "value"=>$port);
	$array['username'] = array("type"=>"varchar", "value"=>$username);
	$array['password'] = array("type"=>"varchar", "value"=>$password);
	
	$pluginManager->databaseManager->insertValue($array);
	
	$pluginManager->redirect($pluginManager, 'mail', $pluginManager->databaseManager->getInsertId());
}

$jUI->add(new JUI\Heading("Konto hinzufügen"));

$description = new JUI\Input("description");
$description->setLabel("Beschreibung:");
$jUI->add($description);

$jUI->nline();

$server = new JUI\Input("server");
$server->setLabel("Server:");
$jUI->add($server);

$port = new JUI\Input("port");
$port->setLabel("Port:");
$jUI->add($port);

$jUI->nline();

$username = new JUI\Input("username");
$username->setLabel("Benutzername:");
$jUI->add($username);

$jUI->nline();

$password = new JUI\Input("password");
$password->setLabel("Kennwort:");
$password->setPreset(JUI\Input::PASSWORD);
$jUI->add($password);

$jUI->nline(2);

$submit = new JUI\Button("Absenden", true);
$jUI->add($submit);

?>