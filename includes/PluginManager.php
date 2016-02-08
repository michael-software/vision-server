<?php

class PluginManager {
	private $plugin = null;
	private $dirView = '';
	public $databaseManager;
	public $fileManager;
	public $config = null;
	private $commands = null;
	private $ipServiceUrl = "http://www.web4brauns.de/vision/getip.php";
	public $useWolServer = TRUE;
	private $wolPort = "3685"; // you can choose any port. You need a server that run the WolServer (found on GitHub)
	private $wolHost = ""; // only use the IP-Address or Hostname. No Protocol!! (e.g. allowed: 192.165.72.54, example.org, wol.example.org; forbidden: http://example.org, 45.45.48.68/, example.org/). It's set to your Ip if an IpService is set. It's important that you can access this Url from the WWW.
	
	function __construct() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f), $a);
		}
	}
	
	function __construct1($pPlugin) {
		global $loginManager;
		
		if(!$loginManager->isAllowed('use_' . $pPlugin)) {
			die('[{"type":"warning","value":'.json_encode('Sie dürfen leider nicht auf dieses Plugin zugreifen. Bitte wenden sie sich an den Administrator.') . '}]');
		}
		
		$this->plugin = $pPlugin;
		$this->dirView = 'plugins/'.$pPlugin.'/view/';
		
		$this->config = $this->getConfig();
		
		if(!empty($this->config->database)) {
			require_once dirname(__FILE__).'/DatabaseManager.php';
			
			$this->databaseManager = new DatabaseManager();
			$this->databaseManager->openTable($pPlugin, $this->config->database);
		}
		
		if(!empty($this->config->filesystem)) {
			if(!$loginManager->isAllowed(LoginManager::FILE_ACCESS)) {
				die('[{"type":"warning","value":'.json_encode('Sie dürfen leider nicht auf dieses Plugin zugreifen, da dieses Dateirechte benötigt, welche sie nicht besitzen. Bitte wenden sie sich an den Administrator.') . '}]');
			}
			require_once dirname(__FILE__).'/FileManager.php';
			
			$this->fileManager = new FileManager();
		}
	}
	
	function getPlugins() {
		global $loginManager;
		
		$return = Array();
		
		$handle = opendir('plugins');
		$i = 0;
		
		$extraSort = Array("plg_serversettings");
		
		while ($file = readdir($handle)) {
			if($file != '.' && $file != '..' && is_dir('plugins/'.$file)) {
				if(in_array($file, $extraSort)) {
					continue;
				}
				
				if($loginManager->isAllowed('use_' . $file)) {
					$return[$i] = $this->pluginToArray($file);
					$i++;
				}
			}
		}
		
		foreach($extraSort as $file) {
			if($file != '.' && $file != '..' && is_dir('plugins/'.$file)) {
				
				if($loginManager->isAllowed('use_' . $file)) {
					$return[$i] = $this->pluginToArray($file);
					$i++;
				}
			}
		}
		
		return json_encode($return);
	}
	
	function getPluginTags() {
		$return = Array();
		
		$handle = opendir('plugins');
		$i = 0;
		
		while ($file = readdir($handle)) {
			if($file != '.' && $file != '..' && is_dir('plugins/'.$file)) {
				$return[$i] = $this->pluginToArrayAll($file);

				$i++;
			}
		}
		
		return $return;
	}

	function getPluginName() {
		if(!empty($this->plugin)) {
			return $this->plugin;
		}
		
		return '';
	}
	
	function getController() {
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='getController'.$i)) {
			return call_user_func_array(array($this,$f), $a);
		}
	}
	
	function getController1($pController) {
		return dirname(dirname(__FILE__)) . '/plugins/'.$this->plugin.'/controller/' . $pController . '.php';
	}

	function getController2($pPluginName, $pController) {
		$path = dirname(dirname(__FILE__)) . '/plugins/'.$pPluginName.'/controller/' . $pController . '.php';
		
		if(file_exists($path)) {
			return $path;
		}
		
		return dirname(dirname(__FILE__)) . '/includes/empty.php';
	}

	function pluginToArray($pPlugin) {
		if(file_exists('plugins/'.$pPlugin.'/'.'config.json')) {
			$jsonArray = file('plugins/'.$pPlugin.'/'.'config.json');
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
		}
		
		$return['id']   = $pPlugin;
		$return['name'] = $pluginName;
		
		if(file_exists('plugins/'.$pPlugin.'/images/'.'icon.png') AND file_exists('plugins/'.$pPlugin.'/images/'.'icon-color.png')) {
			$return['icon'] = 'plugins/'.$pPlugin.'/images/'.'icon.png';
			$return['icon-color'] = 'plugins/'.$pPlugin.'/images/'.'icon-color.png';
		} else if(file_exists('plugins/'.$pPlugin.'/images/'.'icon.png')) {
			$return['icon'] = 'plugins/'.$pPlugin.'/images/'.'icon.png';
			$return['icon-color'] = 'plugins/'.$pPlugin.'/images/'.'icon.png';
		}
		
		return $return;
	}

	function pluginToArrayAll($pPlugin) {
		if(file_exists('plugins/'.$pPlugin.'/'.'config.json')) {
			$jsonArray = file('plugins/'.$pPlugin.'/'.'config.json');
			$json = implode('', $jsonArray);
			$config = json_decode($json);
			
			if(!empty($config) && !empty($config->name)) {
				$pluginName = $config->name;
			}
			
			if(!empty($config) && !empty($config->tags)) {
				$return['tags'] = $config->tags;
			}
		}
		
		$return['id']   = $pPlugin;
		$return['name'] = $pluginName;
		
		if(file_exists('plugins/'.$pPlugin.'/images/'.'icon.png') AND file_exists('plugins/'.$pPlugin.'/images/'.'icon-color.png')) {
			$return['icon'] = 'plugins/'.$pPlugin.'/images/'.'icon.png';
			$return['icon-color'] = 'plugins/'.$pPlugin.'/images/'.'icon-color.png';
		} else if(file_exists('plugins/'.$pPlugin.'/images/'.'icon.png')) {
			$return['icon'] = 'plugins/'.$pPlugin.'/images/'.'icon.png';
			$return['icon-color'] = 'plugins/'.$pPlugin.'/images/'.'icon.png';
		}
		
		return $return;
	}
	
	function getView($pView) {
		//header('Content-Type: application/json; charset=utf-8');
		global $loginManager;
		
		$filename1 = $this->dirView.$pView.'.json';
		$filename2 = $this->dirView.$pView.'.php';
		
		if(file_exists($filename1)) {
			$jsonArray = file($filename1);
			$json = implode('', $jsonArray);
			
			echo $json;
		} else if(file_exists($filename2)) {
			$pluginManager = $this;
			
			include($filename2);
		} else {
			echo '{}';
		}
		
		return null;
	}

	function getConfig() {
		if(!empty($this->plugin) && file_exists('plugins/'.$this->plugin.'/'.'config.json')) {
			$jsonArray = file('plugins/'.$this->plugin.'/'.'config.json');
			$json = implode('', $jsonArray);
			
			return json_decode($json);
		}
		
		return null;
	}
	
	function isInstalled($pPluginName) {
		$path = dirname(dirname(__FILE__))."/plugins/" . $pPluginName;
		
		if(is_dir($path)) {
			return true;
		}
		
		return false;
	}

	function getCommand() {
		if(!empty($_GET['cmd'])) {
			$this->commands = explode("/", $_GET['cmd']);
		}
		
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='getCommand'.$i)) {
			return call_user_func_array(array($this,$f), $a);
		}
	}
	
	function getCommand0() {
		if(!empty($this->commands)) {
			return $this->commands;
		}
		
		return null;
	}
	
	function getCommand1($pId) {
		if(!empty($this->commands[$pId])) {
			return $this->commands[$pId];
		} else {
			return null;
		}
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
}

?>