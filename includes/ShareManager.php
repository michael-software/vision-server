<?php

require_once dirname(dirname(__FILE__)) . '/config.php';

class ShareManager {
	private $id;
	private $username;
	private $sharekey;
	private $allowedViews;
	private $parameters;
	private $plugin;
	private $isShared = false;
	private $database;
	
	function __construct($parameter) {
		$this->database = new DatabaseManager();
		$this->database->openTable('share', json_decode(DatabaseManager::$table9));
			
		if(!empty($_SESSION['id']) || $parameter != $_SESSION['id']) {
			$this->isShared = true;
			
			$array['id'] = array('operator'=>'=', 'value'=>$parameter);
			$result = $this->database->getValues($array, 1);
			
			if($result != null) {
				if(!empty($result['id']) && $result['id'] == $parameter) {
					
					$this->id = $result['user'];
					$this->username = 'share';
					$this->sharekey = $parameter;
					$this->plugin = $result['plugin'];
					
					$this->allowedViews = $this->parseViews($result['views']);
					$this->parameters = $this->parseParameters($result['parameter']);
					
					return true;
				}
			}
			
			return false;
		} else {
			
		}
	}
	
	public function isShared() {
		return $this->isShared;
	}
	
	private function parseViews($views) {
		$views = explode(',', trim($views, ','));
		
		$returnArray = null;
		
		if(!empty($views) && is_array($views))
		foreach($views as $view) {
			$returnArray[] = trim($view);
		}
		
		return $returnArray;
	}
	
	private function parseParameters($params) {
		$json = json_decode($params);
		
		if(!empty($json)) {
			return $json;
		}
		
		return $params;
	}
	
	public function isAllowed($pPlugin, $view, $parameter, $die=true) {
		if(!$this->isShared) {
			return true;
		}
		
		if($pPlugin == $this->plugin) {
			$viewString = trim($view) . '/' . trim($parameter);
			$viewString = trim($viewString, '/');
			
			if(in_array($viewString, $this->allowedViews)) {
				return true;
			}
			
			if(in_array($viewString . '/', $this->allowedViews)) {
				return true;
			}
			
			if(in_array(trim($view, '/') . '/', $this->allowedViews)) {
				return true;
			}
		}
		
		if($die)
			die('[{"type":"warning","value":'.json_encode('Sie dürfen leider nicht auf dieses Plugin zugreifen. Bitte wenden sie sich an einen Administrator.') . '}]');
	}
	
	public function getParameter() {
		return $this->parameters;
	}
	
	function getId() {
		return $this->id;
	}
	
	function getUsername() {
		return $this->username;
	}
	
	function getSharekey() {
		return $this->sharekey;
	}
	
	function getPlugin() {
		return $this->plugin;
	}
	
	function getStartView() {
		if($this->isAllowed($this->plugin, 'home', '', false)) {
			return $this->plugin;
		} else {
			return $this->plugin . '/' . $this->allowedViews[0];
		}
	}
	
	function getUrl($pPlugin, $pView, $pCommand) {
		global $loginManager;
		global $conf;
		global $pluginManager;
		
		if($pluginManager->isInstalled($pPlugin)) {
			$insert['plugin'] = Array("value"=>$pPlugin);
			$insert['parameter'] = Array("value"=>'');
			$insert['user'] = Array("value"=>$loginManager->getId());
			
			if(!empty($pView) && !empty($pCommand)) {
				$insert['views'] = Array("value"=>$pView.'/'.$pCommand.'/');
			} else if(!empty($pView)) {
				$insert['views'] = Array("value"=>$pView.'/');
			} else {
				$insert['views'] = Array("value"=>'home/');
			}
			
			require_once $pluginManager->getController($pPlugin, 'share');
			
			$a[0] = $pPlugin;
			$a[1] = $pView;
			$a[2] = $pCommand;
			
			if (function_exists($f='share_'.$pPlugin)) {
				$controller = call_user_func_array($f,$a);
				
				if(!empty($controller) && is_array($controller)) {
					if(!empty($controller['parameter'])) {
						$parameter = $controller['parameter'];
						
						if(is_string($parameter)) {
							$parameter = array('value' => $parameter);
						}
						
						$insert['parameter']['value'] = json_encode($parameter);
					}
					
					if(!empty($controller['views'])) {
						$views = $controller['views'];
						
						if(is_string($parameter)) {
							$parameter = array($views);
						}
						
						$insert['views']['value'] = implode(',', $views);
					}
				}
			}
			
			$share = $this->database->getValues($insert, 1);
			
			if(!empty($share) && !empty($share['id'])) {
				return 'http://' . $conf['serverurl'] . '/#share/' . $share['id'];
			}
			
			$this->database->insertValue($insert);
			
			return 'http://' . $conf['serverurl'] . '/#share/' . $this->database->getInsertId();
		}
	}
}

?>