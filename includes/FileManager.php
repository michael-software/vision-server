<?php

class FileManager {
	private $userId;
	private $userFolder;
	
	function __construct() {
		$basePath = dirname(dirname(__FILE__));
		
		$this->userId = $_SESSION['id'];
		$this->userFolder = $basePath.'/data/user_'.$this->userId.'/';
		$this->userFiles = $basePath.'/data/user_'.$this->userId.'/files/';
		$this->userInfo = $basePath.'/data/user_'.$this->userId.'/.userfiles/';
		
		if(!is_dir($basePath.'/data/user_'.$this->userId.'/') && !file_exists($basePath.'/data/user_'.$this->userId)) {
			mkdir($basePath.'/data/user_'.$this->userId.'/', 0664, true);
		}
		
		if(!is_dir($basePath.'/data/user_'.$this->userId.'/files/') && !file_exists($basePath.'/data/user_'.$this->userId.'/files')) {
			mkdir('data/user_'.$this->userId.'/files/', 0664, true);
		}
		
		if(!is_dir($basePath.'/data/user_'.$this->userId.'/.userfiles/') && !file_exists($basePath.'/data/user_'.$this->userId.'/.userfiles')) {
			mkdir($basePath.'/data/user_'.$this->userId.'/.userfiles/', 0644, true);
		}
	}
	
	function getFolder($pPath) {
		$returnFolder = array();
		$returnFiles = array();
		
		$folder = $this->userFiles.$pPath;
		
		if (is_dir($folder)) {
			if ($handle = opendir($folder)) {
				while (($file = readdir($handle)) !== false) {
					if($file != '.' && $file != '..' && $file != '.userfiles') {
						$filetype = filetype($folder.$file);
						
						if($filetype == 'dir') {
							$returnFolder[] = array("name"=>$file, "type"=>$filetype, "path"=>$pPath.$file);
						} else {
							$returnFiles[] = array("name"=>$file, "type"=>$this->getFileType($pPath.$file), "path"=>urlencode($pPath.$file));
						}
					}
        		}
        		closedir($handle);
    		}
		}
		
		return array_merge($returnFolder, $returnFiles);
	}
	
	function getFileType($pPath) {
		$pathinfo = pathinfo($pPath);
		$extension = $pathinfo['extension'];
		
		if(!empty($extension)) {
			if($extension == "mp3") {
				return "music";
			} else if($extension == "mp4") {
				return "video";
			}
		}
		
		return "file";
	}
	
	function getFile($pPath) {
		$pPath = urldecode($pPath);
		echo $pPath;
		$type = $this->getFileType($pPath);
		$path = $this->userFiles.$pPath;
		
		if(file_exists($path)) {
			if($type == "music") {
				header('Content-Disposition: inline; filename="'.basename($path).'"');
				header("Content-type: audio/mpeg");
				header("Content-Transfer-Encoding: binary"); 
				//header("Content-Length: ".filesize($path).""); // Probleme mit Android App
				//header('X-Pad: avoid browser bug');
				header('Accept-Ranges: bytes'); // It's there to move forward/backwards in track
				//header("Cache-Control: no-store, no-cache, must-revalidate");
				//header("Cache-Control: post-check=0, pre-check=0", false);
				//header("Pragma: no-cache");
				//header("Content-Type: audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3");
				header("X-Sendfile: $path");
				
				//readfile($path);
			}else if($type == "video") {
				header('Content-Disposition: inline; filename="'.basename($path).'"');
				header("Content-Transfer-Encoding: binary"); 
				header("X-Sendfile: $path");
				header("Content-type: video/mp4");
				header('Accept-Ranges: bytes'); // It's there to move forward/backwards in track
			} else {
				header('Content-Type: application/octet-stream');
				header('Content-Transfer-Encoding: Binary'); 
				header('Content-disposition: attachment; filename="' . basename($path) . '"'); 
				readfile($path);
			}
		}
	}
}

?>