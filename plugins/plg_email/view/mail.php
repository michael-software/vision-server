<?php

$account  = $pluginManager->getCommand(0);
$command1 = $pluginManager->getCommand(1);

if(!empty($account) && is_numeric($account)) {
	
	require_once $pluginManager->getController('email');
	
	$accountData = $pluginManager->databaseManager->getValues(array("id"=>array("value"=>$account)), 1);
	
	if(empty($accountData)
		|| empty($accountData['server']) || empty($accountData['port'])
		|| empty($accountData['username']) || empty($accountData['password'])) {
		
		$pluginManager->redirect($pluginManager, 'home');	
	}
		
	$reader = new Email_reader($accountData['server'], $accountData['username'], $accountData['password'], $accountData['port']);
	
	if(!$reader->isConnected()) {
		$pluginManager->redirect($pluginManager, 'failed', $account);
	}
	
	if(!empty($command1) && is_numeric($command1)) {
		$mail = $reader->getBody($command1);
		
		if(!empty($mail)) {
			$frame = new JUI\Frame();
			
			$frame->setHtml($mail);
			
			$frame->setWidth("100%");
			$frame->setHeight("100%");
			
			$jUI->add($frame);
		}
	} else {
		$list = new JUI\ListView();
		
		foreach($reader->headers() as $header) {
			$click = new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'mail', $account . '/' . $header->id);
			$list->addItem(mb_decode_mimeheader($header->subject), $click);
		}
		
		$jUI->add($list);
	}
	
}

?>