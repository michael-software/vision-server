<?php

if (!defined('WEBSOCKET')) {
    define('WEBSOCKET', '2');
}

require_once dirname(__FILE__) . '/FileManager.php';
require_once dirname(__FILE__) . '/JUIManager.php';
require_once dirname(dirname(__FILE__)) . '/config.php';

class PluginManager {
	private $plugin = null;
	private $dirView = '';
	private $dirApi = '';
	public $databaseManager;
	private $notificationManager;
	private $juiManager;
	public $logManager;
	public $fileManager;
	public $config = null;
	private $commands = null;
	private $ipServiceUrl = "http://www.web4brauns.de/vision/getip.php";
	public $useWolServer = TRUE;
	private $wolPort = "3685"; // you can choose any port. You need a server that run the WolServer (found on GitHub)
	private $wolHost = ""; // only use the IP-Address or Hostname. No Protocol!! (e.g. allowed: 192.165.72.54, example.org, wol.example.org; forbidden: http://example.org, 45.45.48.68/, example.org/). It's set to your Ip if an IpService is set. It's important that you can access this Url from the WWW.
	private $basedir = './';
	
	const TYPE_FILE = 1;
	const TYPE_STRING = 2;
	const TYPE_NONE = 0;
	
	function __construct() {
		$this->basedir = dirname(dirname(__FILE__));
		$this->secure();
		
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f), $a);
		}
	}
	
	function __construct1($pPlugin) {
		global $loginManager;
		global $logManager;
		
		if(!$loginManager->isAllowed('use_' . $pPlugin) && $pPlugin != "plg_user" && constant('WEBSOCKET') != 1) {
			die('[{"type":"warning","value":'.json_encode('Sie dürfen leider nicht auf dieses Plugin zugreifen. Bitte wenden sie sich an einen Administrator.') . '}]');
		}
		
		$this->plugin = $pPlugin;
		$this->dirView = dirname(dirname(__FILE__)) . '/plugins/'.$pPlugin.'/view/';
		$this->dirApi = dirname(dirname(__FILE__)) . '/plugins/'.$pPlugin.'/api/';
		
		$this->config = $this->getConfig();
		
		if(!empty($this->config->database) ) {
			require_once dirname(__FILE__).'/DatabaseManager.php';
			
			$this->databaseManager = new DatabaseManager();
			
			if(!empty($this->config->database[0]->name)) {
				$this->databaseManager->openTable($pPlugin, $this->config->database);
			} else if(!empty($this->config->database[0][0]->name)) {
				$this->databaseManager->openTables($pPlugin, $this->config->database);
			}
		}
		
		if(!empty($this->config->filesystem) && $this->config->filesystem && constant('WEBSOCKET') != 1) {
			if(!$loginManager->isAllowed(LoginManager::FILE_ACCESS)) {
				die('[{"type":"warning","value":'.json_encode('Sie dürfen leider nicht auf dieses Plugin zugreifen, da dieses Dateirechte benötigt, welche sie nicht besitzen. Bitte wenden sie sich an einen Administrator.') . '}]');
			}
			require_once dirname(__FILE__).'/FileManager.php';
			
			$this->fileManager = new FileManager($pPlugin);
		}
		
		if(!empty($this->config->notifications) && $this->config->notifications) {
			require_once dirname(__FILE__) . '/NotificationManager.php';
			
			$this->notificationManager = new NotificationManager($pPlugin);
		}
		
		$this->juiManager = new JUI\Manager($pPlugin);
		
		$logManager->setPlugin($pPlugin);
	}
	
	function getPlugins($json = TRUE) {
		global $loginManager;
		
		$return = Array();
		
		$handle = opendir($this->basedir . '/plugins');
		$i = 0;
		
		$extraSort = Array("plg_serversettings", "plg_license", "plg_order", "plg_user");
		
		while ($file = readdir($handle)) {
			if($file != '.' && $file != '..' && is_dir($this->basedir . '/plugins/'.$file)) {
				if(in_array($file, $extraSort)) {
					continue;
				}
				
				if($loginManager->isAllowed('use_' . $file)) {
					$return[$i] = $this->pluginToArray($file);
					$i++;
				}
			}
		}
		
		if(is_bool($json) && $json)
		foreach($extraSort as $file) {
			if($file != '.' && $file != '..' && is_dir($this->basedir . '/plugins/'.$file)) {
				
				if($loginManager->isAllowed('use_' . $file)) {
					$return[$i] = $this->pluginToArray($file);
					$i++;
				}
			}
		}
		
		if(is_bool($json) && $json) {
			return json_encode($return);
		}
		
		return $return;
	}
	
	function getPluginTags() {
		$return = Array();
		
		$handle = opendir($this->basedir . '/plugins');
		$i = 0;
		
		while ($file = readdir($handle)) {
			if($file != '.' && $file != '..' && is_dir($this->basedir . '/plugins/'.$file)) {
				$return[$i] = $this->pluginToArrayAll($file);

				$i++;
			}
		}
		
		return $return;
	}

	function getPluginName() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='getPluginName'.$i)) {
			return call_user_func_array(array($this,$f), $a);
		}
	}

	private function getPluginName0() {
		if(!empty($this->plugin)) {
			return $this->plugin;
		}
		
		return '';
	}
	
	private function getPluginName1($pluginId) {
		if(is_dir( $this->basedir . '/plugins/' . $pluginId . '/' )) {
			return $this->pluginToArrayAll($pluginId)['name'];
		}
		
		return $pluginId;
	}
	
	function getController() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='getController'.$i)) {
			return call_user_func_array(array($this,$f), $a);
		}
	}
	
	private function getController1($pController) {
		$path = $this->basedir . '/plugins/'.$this->plugin.'/controller/' . $pController . '.php';
		
		if(file_exists($path)) {
			return $path;
		}
		
		return $this->basedir . '/includes/empty.php';
	}

	private function getController2($pPluginName, $pController) {
		$path = $this->basedir . '/plugins/'.$pPluginName.'/controller/' . $pController . '.php';
		
		if(file_exists($path)) {
			return $path;
		}
		
		return $this->basedir . '/includes/empty.php';
	}

	function pluginToArray($pPlugin) {
		if(file_exists($this->basedir . '/plugins/'.$pPlugin.'/'.'config.json')) {
			$jsonArray = file($this->basedir . '/plugins/'.$pPlugin.'/'.'config.json');
			$json = implode('', $jsonArray);
			$config = json_decode($json);
			
			if(!empty($config) && !empty($config->name)) {
				$pluginName = $config->name;
			}
			
			if(!empty($config) && !empty($config->offline)) {
				$return['offline'] = $config->offline;
			}
			
			if(!empty($config) && !empty($config->viewCache)) {
				$return['viewCache'] = $config->viewCache;
			}
			
			if(!empty($config) && !empty($config->visible)) {
				$return['visible'] = $config->visible;
			}
			
			if(!empty($config) && !empty($config->mime)) {
				$return['mime'] = $config->mime;
			}
			
			if(!empty($config) && !empty($config->shareable) && strtoupper($config->shareable) == 'TRUE') {
				$return['shareable'] = "TRUE";
			}
		}
		
		if(empty($pluginName)) {
			$pluginName = $pPlugin;
		}
		
		$return['id']   = $pPlugin;
		$return['name'] = $pluginName;
		
		if(file_exists($this->basedir . '/plugins/'.$pPlugin.'/images/'.'icon.png') AND file_exists($this->basedir . '/plugins/'.$pPlugin.'/images/'.'icon-color.png')) {
			$return['icon'] = FileManager::getImageHashByPath($this->basedir . '/plugins/'.$pPlugin.'/images/'.'icon.png');
			$return['icon-color'] = FileManager::getImageHashByPath($this->basedir . '/plugins/'.$pPlugin.'/images/'.'icon-color.png');
		} else if(file_exists($this->basedir . '/plugins/'.$pPlugin.'/images/'.'icon.png')) {
			$return['icon'] = FileManager::getImageHashByPath($this->basedir . '/plugins/'.$pPlugin.'/images/'.'icon.png');
			$return['icon-color'] = FileManager::getImageHashByPath($this->basedir . '/plugins/'.$pPlugin.'/images/'.'icon.png');
		}
		
		return $return;
	}

	function pluginToArrayAll($pPlugin) {
		if(file_exists($this->basedir . '/plugins/'.$pPlugin.'/'.'config.json')) {
			$jsonArray = file($this->basedir . '/plugins/'.$pPlugin.'/'.'config.json');
			$json = implode('', $jsonArray);
			$config = json_decode($json);
			
			if(!empty($config) && !empty($config->name)) {
				$pluginName = $config->name;
			}
			
			if(!empty($config) && !empty($config->tags)) {
				$return['tags'] = $config->tags;
			}
			
			if(!empty($config) && !empty($config->permissions)) {
				$return['permissions'] = $this->parsePermission($pPlugin, $config->permissions);
			}
		}
		
		if(empty($pluginName)) {
			$pluginName = $pPlugin;
		}
		
		$return['id']   = $pPlugin;
		$return['name'] = $pluginName;
		
		if(file_exists($this->basedir . '/plugins/'.$pPlugin.'/images/'.'icon.png') AND file_exists('plugins/'.$pPlugin.'/images/'.'icon-color.png')) {
			$return['icon'] = 'plugins/'.$pPlugin.'/images/'.'icon.png';
			$return['icon-color'] = 'plugins/'.$pPlugin.'/images/'.'icon-color.png';
		} else if(file_exists($this->basedir . '/plugins/'.$pPlugin.'/images/'.'icon.png')) {
			$return['icon'] = 'plugins/'.$pPlugin.'/images/'.'icon.png';
			$return['icon-color'] = 'plugins/'.$pPlugin.'/images/'.'icon.png';
		}
		
		return $return;
	}
	
	function parsePermission($pluginId, $json) {
		$output = NULL;
		
		foreach($json as $permission) {
			if(is_array($permission) && count($permission) == 3) {
				if(!empty($permission['name']) && !empty($permission['id'])) {
					$id = $pluginId . '_' . $permission['id'];
					$name = $permission['name'];
					
					if(!empty($permission['default'])) {
						$default = $permission['default'];
					} else {
						$default = 0;
					}
					
					$output[] = array("id"=>$id, "name"=>$name, "default"=>$default);
				} else if(!empty($permission[0]) && !empty($permission[1])) {
					$id = $pluginId . '_' . $permission[1];
					$name = $permission[0];
					
					if(!empty($permission[2])) {
						$default = $permission[2];
					} else {
						$default = 0;
					}
					
					$output[] = array("id"=>$id, "name"=>$name, "default"=>$default);
				}
			}
		}

		return $output;
	}
	
	function getCustomPermission($permissionName) {
		return $this->plugin . '_' . $permissionName;
	}
	
	function getView($pView, $command=null) {
		//header('Content-Type: application/json; charset=utf-8');
		global $loginManager;
		global $logManager;
		global $jUI;
		global $conf;
		
		if(!empty($command) && is_string($command)) {
			$this->setCommand($command);
		}
		
		$filename1 = $this->dirView.$pView.'.json';
		$filename2 = $this->dirView.$pView.'.php';
		
		if(file_exists($filename1)) {
			$jsonArray = file($filename1);
			$json = implode('', $jsonArray);
			
			echo $json;
		} else if(file_exists($filename2)) {
			$pluginManager = $this;
			$jUI = $this->juiManager;
			
			include($filename2);
			
			if($this->juiManager->hasChanged()) {
				echo $this->juiManager->getJsonString();
			}
		} else {
			echo '{}';
		}
		
		return null;
	}
	
	function getViews() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='getViews'.$i)) {
			return call_user_func_array(array($this,$f), $a);
		}
	}
	
	private function getViews0() {
		return $this->getViews1($this->getPluginName());
	}
	
	private function getViews1($pluginId) {
		$return = Array();
		$path = 'plugins/' . $pluginId . '/view/';
		
		if(is_dir($path)) {
			$handle = opendir($path);
			
			while ($file = readdir($handle)) {
				if($file != '.' && $file != '..' && !is_dir($path.$file)) {
					if(strtoupper(pathinfo($file, PATHINFO_EXTENSION)) == "PHP" || strtoupper(pathinfo($file, PATHINFO_EXTENSION)) == "JSON") {
						$return[] = pathinfo($file, PATHINFO_FILENAME);
					}
				}
			}
			
			return $return;
		}
		
		return null;
	}

	function getConfig() {
		if(!empty($this->plugin) && file_exists($this->basedir . '/plugins/'.$this->plugin.'/'.'config.json')) {
			$jsonArray = file($this->basedir . '/plugins/'.$this->plugin.'/'.'config.json');
			$json = implode('', $jsonArray);
			
			return json_decode($json);
		}
		
		return null;
	}
	
	function getWidgetArray() {
		global $loginManager;
		
		if(empty($loginManager->getShareManager()) || !$loginManager->getShareManager()->isShared()) {
			$a = func_get_args(); 
			$i = func_num_args();
			
			if (method_exists($this, $f='getWidgetArray'.$i)) {
				return call_user_func_array(array($this,$f), $a);
			}
		}
		
		return null;
	}
	
	function getWidgetArray1($pPlugin) {
		return $this->getWidgetArray2($pPlugin, "default");
	}
	
	function getWidgetArray2($pPlugin, $pName) {
		global $loginManager;
		global $conf;
		
		$oldPluginId = $this->getPluginName();
		
		$pluginManager = $this;
		$pluginManager->setPluginName($pPlugin);
		
		$path = $this->basedir . '/plugins/' . $pPlugin . '/widget/' . $pName . '.php';
		
		$return = null;
		
		if(file_exists($path)) {
			$jUI = new JUI\Manager($pPlugin);
			
			ob_start();
				include $path;
			$out = ob_get_contents(); 
			ob_end_clean();
			
			if($jUI->hasChanged()) {
				$return = $jUI->getArray();
			} else {
				$return = json_decode($out);
			}
		}
		
		$pluginManager = $this;
		$pluginManager->setPluginName($oldPluginId);
		
		return $return;
	}
	
	function getWidget() {
		global $loginManager;
		
		if(empty($loginManager->getShareManager()) || !$loginManager->getShareManager()->isShared()) {
			$a = func_get_args(); 
			$i = func_num_args();
			
			if (method_exists($this, $f='getWidget'.$i)) {
				call_user_func_array(array($this,$f), $a);
			}
		}
	}
	
	private function getWidget1($pPlugin) {
		$this->getWidget2($pPlugin, "default");
	}
	
	private function getWidget2($pPlugin, $pName) {
		global $loginManager;
		
		$oldPluginId = $this->getPluginName();
		
		$pluginManager = $this;
		$pluginManager->setPluginName($pPlugin);
		
		$path = $this->basedir . '/plugins/' . $pPlugin . '/widget/' . $pName . '.php';
		
		if(file_exists($path)) {
			$jUI = new JUI\Manager($pPlugin);
			include $path;
			
			if($jUI->hasChanged()) {
				echo $jUI->getJsonString();
			}
		}
		
		$pluginManager = $this;
		$pluginManager->setPluginName($oldPluginId);
	}
	
	function isInstalled($pPluginName) {
		$path = $this->basedir . '/plugins/' . $pPluginName;
		
		if(is_dir($path)) {
			return true;
		}
		
		return false;
	}
	
	function getImage($name) {
		$path = $this->basedir . '/plugins/' . $this->getPluginName() . '/images/' . $name;
		
		if(file_exists($path)) {
			return FileManager::getImageHashByPath($path);
		}
		
		return null;
	}
	
	private function setPluginName($pName) {
		$this->plugin = $pName;
	}
	
	function getSettingsName() {
		return "plg_serversettings";
	}

	function getCommand() {
		if(!empty($_GET['cmd']) && empty($this->commands)) {
			$this->commands = explode("/", $_GET['cmd']);
		}
		
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='getCommand'.$i)) {
			return call_user_func_array(array($this,$f), $a);
		}
	}
	
	private function getCommand0() {
		if(!empty($this->commands)) {
			return $this->commands;
		}
		
		return null;
	}
	
	private function getCommand1($pId) {
		if(!empty($this->commands[$pId])) {
			return $this->commands[$pId];
		} else {
			return null;
		}
	}
	
	private function setCommand($cmd) {
		$this->commands = explode("/", $cmd);
	}
	
	function getUserManager() {
		require_once dirname(__FILE__).'/UserManager.php';
		return new UserManager();
	}
	
	function getIp() {
		return file_get_contents($this->ipServiceUrl);
	}
	
	function getUrl() {
		return $_SERVER['SERVER_NAME'];
	}
	
	function getWolUrl() {
		if(empty($this->wolHost)) {
			return "http://" . $this->getUrl() . ":" . $this->wolPort . "/";
		}
		
		return "http://" . $this->wolHost . ":" . $this->wolPort . "/";
	}
	
	function getNotificationManager() {
		return $this->notificationManager;
	}
	
	function getMainPlugins() {
		global $loginManager;
		
		$mainPlugins = $loginManager->getUserPreference("mainplugins", null);
		
		if(!empty($mainPlugins)) {
			return json_encode($mainPlugins);
		}
		
		return '""';
	}
	
	function isMainPlugin($id) {
		global $loginManager;
		
		$id = str_replace(' ', '', $id);
		$id = str_replace("\r", '', $id);
		$id = str_replace("\n", '', $id);
		$mainPlugins = $loginManager->getUserPreference("mainplugins", null);
		
		if(!empty($mainPlugins) && is_string($mainPlugins)) {
			return (strpos($mainPlugins, $id) !== FALSE);
		}
		
		return false;
	}
	
	function getMainPluginsArray() {
		global $loginManager;
		
		$mainPlugins = $loginManager->getUserPreference("mainplugins", null);
		
		if(!empty($mainPlugins)) {
			$returnArray = null;
			$mainPlugins = explode("|", $mainPlugins);
			
			if(!empty($mainPlugins) && is_array($mainPlugins))
			foreach($mainPlugins as $mainPlugin) {
				$pluginInfo = $this->pluginToArray($mainPlugin);
				
				if(!empty($pluginInfo) && is_array($pluginInfo))
					$returnArray[] = $pluginInfo;
			}
			
			return $returnArray;
		}
		
		return null;
	}
	
	function enableNotifications($enable) {
		if($enable && empty($this->notificationManager)) {
			require_once dirname(__FILE__) . '/NotificationManager.php';
			
			$this->notificationManager = new NotificationManager($this->plugin);
		}
	}
	
	function redirect() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='redirect'.$i)) {
			return call_user_func_array(array($this,$f), $a);
		}
	}
	
	private function redirect1($pName) {
		$this->redirect3($pName, 'home', '');
	}
	
	private function redirect2($pName, $pView) {
		$this->redirect3($pName, $pView, '');
	}
	
	private function redirect3($pName, $pView, $pParameter) {
		if($pName instanceof PluginManager) {
			$pName = $pName->getPluginName();
		}
		
		if(constant('WEBSOCKET') != 1) {
			die('{"redirect":["' . $pName . '", "' . $pView . '", "' . $pParameter . '"]}');
		}
	}
	
	function getDataType($name="data") {
		if(!empty($_FILES[$name])) {
			return PluginManager::TYPE_FILE;
		} else if(!empty($_POST[$name])) {
			return PluginManager::TYPE_STRING;
		} else {
			return PluginManager::TYPE_EMPTY;
		}
	}
	
	function setPluginStorage($name, $value) {
		global $loginManager;
		
		$databaseManager = new DatabaseManager();
		$databaseManager->openTable("plugin_storage", DatabaseManager::$table10);
		
		$pluginId = $this->getPluginName();
		
		$insert = array( "name"=>array("value"=>$name), "value"=>array("value"=>$value), "plugin"=>array("value"=>$pluginId) );
		$where = array( "name"=>array("value"=>$name), "plugin"=>array("value"=>$pluginId) );
		
		$databaseManager->insertOrUpdateValue($insert, $where);
	}
	
	function getPluginStorage($name, $default="") {
		global $loginManager;
		
		$databaseManager = new DatabaseManager();
		$databaseManager->openTable("plugin_storage", DatabaseManager::$table10);
		
		$pluginId = $this->getPluginName();
		
		$where = array( "name"=>array("value"=>$name), "plugin"=>array("value"=>$pluginId) );
		$result = $databaseManager->getValues($where, 1);
		
		if(!empty($result) && !empty($result['value'])) {
			return $result['value'];
		}
		
		return $default;
	}
	
	function unsetPluginStorage($name) {
		global $loginManager;
		
		$databaseManager = new DatabaseManager();
		$databaseManager->openTable("plugin_storage", DatabaseManager::$table10);
		
		$pluginId = $this->getPluginName();
		
		$where = array( "name"=>array("value"=>$name), "plugin"=>array("value"=>$pluginId) );
		
		$result = $databaseManager->remove($where);
	}
	
	function setSimpleStorage($name, $value, $device=TRUE, $timestamp=FALSE) {
		global $loginManager;
		
		$databaseManager = new DatabaseManager();
		$databaseManager->openTable("plugin_settings", DatabaseManager::$table8);
		
		$pluginId = $this->getPluginName();
		$authtoken = $loginManager->getAuthtoken();
		
		$insert = array( "name"=>array("value"=>$name), "value"=>array("value"=>$value), "plugin"=>array("value"=>$pluginId) );
		if($timestamp) {
			$insert["timestamp"] = array("value"=>time());
		} else {
			$insert["timestamp"] = array("value"=>"");
		}
		
		if($device) {
			$insert["authtoken"] = array("value"=>$authtoken);
		}
		
		$where = array( "name"=>array("value"=>$name), "plugin"=>array("value"=>$pluginId) );
		if($device) {
			$where["authtoken"] = array("value"=>$authtoken);
		}
		
		$databaseManager->insertOrUpdateValue($insert, $where);
	}
	
	function getSimpleStorage($name, $default="", $device=TRUE, $timestamp=FALSE) {
		global $loginManager;
		
		$databaseManager = new DatabaseManager();
		$databaseManager->openTable("plugin_settings", DatabaseManager::$table8);
		
		$pluginId = $this->getPluginName();
		$authtoken = $loginManager->getAuthtoken();
		
		$where = array( "name"=>array("value"=>$name), "plugin"=>array("value"=>$pluginId) );
		if($device) {
			$where["authtoken"] = array("value"=>$authtoken);
		}
		
		$result = $databaseManager->getValues($where, 1);
		
		if(!empty($result) && !empty($result['value'])) {
			return $result['value'];
		}
		
		return $default;
	}
	
	function unsetSimpleStorage($name, $device=TRUE, $timestamp=FALSE) {
		global $loginManager;
		
		$databaseManager = new DatabaseManager();
		$databaseManager->openTable("plugin_settings", DatabaseManager::$table8);
		
		$pluginId = $this->getPluginName();
		$authtoken = $loginManager->getAuthtoken();
		
		$where = array( "name"=>array("value"=>$name), "plugin"=>array("value"=>$pluginId) );
		if($device) {
			$where["authtoken"] = array("value"=>$authtoken);
		}
		
		$result = $databaseManager->remove($where);
	}
	
	function setTemporary($name, $value="", $device=TRUE) {
		return $this->setSimpleStorage($name, $value, $device, TRUE);
	}
	
	function getTemporary($name, $default="", $device=TRUE) {
		return $this->getSimpleStorage($name, $default, $device, TRUE);
	}
	
	function unsetTemporary($name, $device=TRUE) {
		return $this->unsetSimpleStorage($name, $device, TRUE);
	}
	
	function getFileManager() {
		if(empty($this->fileManager)) {
			$this->fileManager = new FileManager($this->plugin);
			$this->fileManager->forbidFilesystem();
		}
	}
	
	function triggerHourly() {
		global $loginManager;
		
		if(constant('WEBSOCKET') == 1) {
			$users = $loginManager->getUserList();
			
			if(!empty($users) && is_array($users))
			foreach($users as $id=>$user) {
				if( empty($loginManager->getPermissions($id)['access_files']) ) continue;
				
				$filesystem = $loginManager->getPermissions($id)['access_files'];
				
				if(!empty($filesystem) && $filesystem != '0' && file_exists($this->basedir . '/data/user_' . $id . '/files/') ) {
					FileManager::updateUserFileList($id);
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	function triggerMinutely() {
		if(constant('WEBSOCKET') == 1) {
			$pluginDir = $this->basedir . '/plugins/';
			$handle = opendir($pluginDir);
			
			while ($file = readdir($handle)) {
				if($file != '.' && $file != '..' && is_dir($pluginDir.$file) && is_dir($pluginDir.$file.'/listener') && file_exists($pluginDir.$file.'/listener/minutely.php')) {
					$pluginManager = new PluginManager($file);
					
					$pluginManager->minutely();
				}
			}
			
			echo date("H:i:s") . ' >> ' . "\033[0;31mTRIGGERED MINUTELY\033[0m" . PHP_EOL;
		}
	}
	
	function minutely() {
		global $logManager;
		$pluginManager = &$this;

		$pluginDir = $this->basedir . '/plugins/';
		
		if(is_dir($pluginDir . $this->plugin . '/listener') && file_exists($pluginDir . $this->plugin . '/listener/minutely.php')) {
			include $pluginDir . $this->plugin . '/listener/minutely.php';
		}
	}
	
	private function secure() {
		if(constant('WEBSOCKET') != 1) {
			if(defined('PLUGIN_MANAGER_SET')) {
				die('Failure 02 - Double call to PluginManager');
			}
			
			define('PLUGIN_MANAGER_SET', TRUE);
		}
	}
	
	function getApi($pView, $command=null) {
		//header('Content-Type: application/json; charset=utf-8');
		global $loginManager;
		global $logManager;
		global $output;
		
		if(!empty($command) && is_string($command)) {
			$this->setCommand($command);
		}
		
		$filename1 = $this->dirApi.$pView.'.json';
		$filename2 = $this->dirApi.$pView.'.php';
		
		if(file_exists($filename1)) {
			$jsonArray = file($filename1);
			$json = implode('', $jsonArray);
			
			echo $json;
		} else if(file_exists($filename2)) {
			$pluginManager = $this;
			$output;
			
			ob_start();
			include($filename2);
			$out = ob_get_contents(); 
			ob_end_clean();
			
			if($output != null && is_string($output)) {
				echo $output;
			} else if($output != null && is_array($output)) {
				echo json_encode($output);
			} else {
				echo $out;
			}
			
		} else {
			echo '{}';
		}
		
		return null;
	}
}

?>