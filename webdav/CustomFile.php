<?php

use Sabre\DAV;

class MyFile extends DAV\File {
	private $myPath;
  
	function __construct($myPath) {
		$this->myPath = $myPath;
	}
  
	function put($data) {
		file_put_contents($this->myPath, $data);
		clearstatcache(true, $this->myPath);
	}
	
	function getName() {
		return basename($this->myPath);
	}
	
	function get() {
		return fopen($this->myPath,'r');
	}
	
	function getSize() {
		return filesize($this->myPath);
	}
	
	function getETag() {
		return '"' . md5($this->myPath) . md5($this->getLastModified()) . '"';
	}
	
	function delete() {
		unlink($this->myPath);
	}
	
	function setName($name) {
		$newPath = dirname($this->myPath) . '/' . $name;
		rename($this->myPath, $newPath);
		
		$this->myPath = $newPath;
	}
	
	function getLastModified() {
		return filemtime($this->myPath);
	}

}

?>