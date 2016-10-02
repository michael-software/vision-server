<?php

$jUI->add(new JUI\Heading("Chats"));

$list = new JUI\ListView();

$userList = $loginManager->GetUserList();
foreach ($userList as $user) { 
	$id = $user['id'];
	$username = $user['username'];
	
	$list->addItem( $username, new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'user', $id) );
}

$jUI->add($list);


?>