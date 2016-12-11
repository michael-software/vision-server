<?php

require_once dirname(dirname(__FILE__)) . '/config.php';

use Sabre\DAV;
use Sabre\DAV\Auth\Plugin as AuthPlugin;

class MyDirectory extends DAV\Collection {

  private $myPath;
  private $authPlugin;
  private $userDir;
  private $userId;

  function __construct(AuthPlugin $authPlugin, $myPath) {
  	$this->authPlugin = $authPlugin;
	
	if(empty($myPath)){
		$myPath = '.';
	}
	
	$this->userDir = $myPath;
  }

  function getChildren() {
	$this->updateUserDir();

    $children = array();
    // Loop through the directory, and create objects for each node 
    foreach(scandir($this->userDir) as $node) {

      // Ignoring files staring with .
      if ($node[0]==='.') continue;
      $children[] = $this->getChild($node);

    }

    return $children;

  }

  function getChild($name) {
  	$this->updateUserDir();
	
      $path = $this->userDir . '/' . $name;
      // We have to throw a NotFound exception if the file didn't exist
      if (!file_exists($path)) {
        throw new DAV\Exception\NotFound('The file with name: ' . $name . ' could not be found');
      }

      // Some added security
      if ($name[0]=='.')  throw new DAV\Exception\NotFound('Access denied');

      if (is_dir($path)) {
          return new MyDirectory($this->authPlugin, $path);

      } else {
          return new MyFile($path);
      }

  }
  
  function updateUserDir()  {
  	global $db_host, $db_username, $db_password, $db_database;
		
	if(empty($this->userDir) || $this->userDir == '.') {
		$db = new mysqli($db_host, $db_username, $db_password, $db_database);
		$sql = 'SELECT * FROM users WHERE username=? LIMIT 0,1';
		$username = $this->authPlugin->getCurrentUser();
		
		$stmt = $db->prepare($sql);
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		
		$return = null;
		while($row = $result->fetch_assoc()) {
			$return = $row;
		}
		
		if(empty($return)) {
			die('User doesn\'t exist');
		}
		
		$this->userId = $return['id'];
		$stmt->close();
		
		$sql = 'SELECT * FROM user_permissions WHERE userid=? LIMIT 0,1';
		$username = $this->authPlugin->getCurrentUser();
		
		$stmt = $db->prepare($sql);
		$stmt->bind_param("i", $this->userId);
		$stmt->execute();
		$result = $stmt->get_result();
		
		$returnPerm = null;
		while($row = $result->fetch_assoc()) {
			$returnPerm = $row;
		}
		
		if(empty($returnPerm) || $returnPerm['access_files'] != 1) {
			die('User isn\'t allowed to access the filesystem');
		}
		
		
		$this->createHomeDirectory();
		$this->userDir = 'data/user_' . $this->userId . '/files';
		$db->close();
		
		
	}
  }
  
  function createHomeDirectory() {
  		if(!is_dir('data/user_'.$this->userId.'/') && !file_exists('data/user_'.$this->userId)) {
			mkdir('data/user_'.$this->userId.'/', 0744, true);
		}
		
		if(!is_dir('data/user_'.$this->userId.'/files/') && !file_exists('data/user_'.$this->userId.'/files')) {
			mkdir('data/user_'.$this->userId.'/files/', 0744, true);
		}
		
		if(!is_dir('data/user_'.$this->userId.'/.userfiles/') && !file_exists('data/user_'.$this->userId.'/.userfiles')) {
			mkdir('data/user_'.$this->userId.'/.userfiles/', 0744, true);
		}
  }

	function childExists($name) {
		return file_exists($this->userDir . '/' . $name);
	}
	
	function createDirectory($name) {
		$newPath = $this->userDir . '/' . $name;
		mkdir($newPath);
		clearstatcache(true, $newPath);
	}
	
	function createFile($name, $data = null) {
		$this->updateUserDir();
		$newPath = $this->userDir . '/' . $name;
		file_put_contents($newPath, $data);
		clearstatcache(true, $newPath);
	}
	
	function delete() {
		foreach ($this->getChildren() as $child) $child->delete();
		rmdir($this->userDir);
	}
	
	function getName() {
		return basename($this->userDir);
	}
	
	function setName($name) {
		//list($parentPath, ) = URLUtil::splitPath($this->userDir);
		//list(, $newName) = URLUtil::splitPath($name);
		
		$newPath = dirname($this->userDir) . '/' . $name;
		rename($this->userDir, $newPath);
		
		$this->userDir = $newPath;
	}

	function getLastModified() {
		return filemtime($this->userDir);
	}
}

?>