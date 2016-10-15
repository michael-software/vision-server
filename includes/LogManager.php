<?php

class LogManager {
	private $plugin;
	private $databaseManager;
	
	function __construct() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f), $a);
		}
		
		//require_once dirname(__FILE__) . '/DatabaseManager.php';
		
		$this->databaseManager = new DatabaseManager();
		$this->databaseManager->openTable('logs', json_decode(DatabaseManager::$table7) );
	}
	
	function __construct1($pPlugin) {
		if(is_string($pPlugin)) {
			$this->plugin = $pPlugin;
		}
	}
	
	function log($text) {
		$this->addLog($text);
	}
	
	function addLog($text) {
		global $loginManager;
		
		$insert['text'] = Array( "value"=>$text );
		$insert['timestamp'] = Array( "value"=>time() );
		
		if(!empty($loginManager) && !empty($loginManager->getId())) {
			$insert['user'] = Array( "value"=>$loginManager->getId() );
		} else {
			$insert['user'] = Array( "value"=>0 );
		}
		
		if(!empty($this->plugin)) {
			$insert['plugin'] = Array( "value"=>$this->plugin );
		} else {
			$insert['plugin'] = Array( "value"=>"plg_serversettings" );
		}
		
		$this->databaseManager->insertValue($insert);
	}
	
	function setPlugin($pPlugin) {
		$this->plugin = $pPlugin;
	}
	
	function getLogs() {
		global $loginManager;
		
		if($loginManager->isAllowed(LoginManager::LOG_ACCESS))
			return $this->databaseManager->getValues(null, Array( Array("name"=>"timestamp", "desc"=>"true") ) );
	}
	
	function getLog($id) {
		global $loginManager;
		
		if(is_numeric($id)) {
			if($loginManager->isAllowed(LoginManager::LOG_ACCESS))
				return $this->databaseManager->getValues(Array("id"=>Array("value"=>$id, "type"=>"i")), 1);
		}
	}
	
	function delete($id) {
		global $loginManager;
		
		if(is_numeric($id)) {
			if($loginManager->isAllowed(LoginManager::LOG_ACCESS))
				return $this->databaseManager->remove(Array("id"=>Array("value"=>$id, "type"=>"i")));
		}
	}
	
	function clean() {
		global $loginManager;
		
		if($loginManager->isAllowed(LoginManager::LOG_ACCESS))
			return $this->databaseManager->remove(Array("timestamp"=>Array("value"=>time()-2678400, "type"=>"i", "operator"=>'<')));
	}
}

?>