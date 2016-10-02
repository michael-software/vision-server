<?php

ini_set("memory_limit", "256M");
set_time_limit(1800);

class FileManager {
	private $userId;
	private $userFolder;
	private $pluginFiles;
	private $plugin = '.';
	private $secure = true;
	
	const FILESYSTEM_PLUGIN_PRIVATE = "PLUGIN_PRIVATE";
	const FILESYSTEM_PLUGIN_PUBLIC = "PLUGIN_PUBLIC";
	const FILESYSTEM_PRIVATE = "PRIVATE";
	const FILESYSTEM_PLUGIN_DOWNLOAD = "PLUGIN_DOWNLOAD";
	
	const EXTENSION_AUDIO = array("MP3");
	const EXTENSION_IMAGES = array("JPG", "JPEG", "PNG", "GIF");
	const EXTENSION_VIDEO = array("MP4", "WEBM");
	
	function __construct() {
		global $loginManager;
		
		$basePath = dirname(dirname(__FILE__));
		
		$this->userId = $loginManager->getId();
		$this->userInfo = $basePath.'/data/user_'.$this->userId.'/.userfiles/';
		if(!is_dir($basePath.'/data/user_'.$this->userId.'/.userfiles/') && !file_exists($basePath.'/data/user_'.$this->userId.'/.userfiles')) {
			mkdir($basePath.'/data/user_'.$this->userId.'/.userfiles/', 0744, true);
		}
		
		if($loginManager->isAllowed(LoginManager::FILE_ACCESS)) {
			$this->secure = false;
			
			$this->userFolder = $basePath.'/data/user_'.$this->userId.'/';
			$this->userFiles = $basePath.'/data/user_'.$this->userId.'/files/';
			
			
			if(!is_dir($basePath.'/data/user_'.$this->userId.'/') && !file_exists($basePath.'/data/user_'.$this->userId)) {
				mkdir($basePath.'/data/user_'.$this->userId.'/', 0744, true);
			}
			
			if(!is_dir($basePath.'/data/user_'.$this->userId.'/files/') && !file_exists($basePath.'/data/user_'.$this->userId.'/files')) {
				mkdir('data/user_'.$this->userId.'/files/', 0744, true);
			}
		}
		
		$a = func_get_args(); 
		$i = func_num_args(); 
		
		if (method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f), $a);
		}
	}

	private function __construct1($pPlugin) {
		$this->plugin = $pPlugin;
		
		$basePath = dirname(dirname(__FILE__));
		$this->pluginFiles = $basePath . '/data/' . $pPlugin . '/';
		
		if(!is_dir($basePath . '/data/' . $pPlugin . '/') && !file_exists($basePath . '/data/' . $pPlugin) ) {
			mkdir($basePath . '/data/' . $pPlugin . '/', 0744, true);
		}
		
		
		if(!is_dir($this->userInfo.$pPlugin.'/') && !file_exists($this->userInfo.$pPlugin) ) {
			mkdir($this->userInfo.$pPlugin.'/', 0744, true);
		}
	}
	
	function getFolder($pPath, $type=FileManager::FILESYSTEM_PRIVATE) {
		$returnFolder = array();
		$returnFiles = array();
		
		if($type == FileManager::FILESYSTEM_PLUGIN_PUBLIC) {
			$folder = $this->pluginFiles . $pPath;
		} else if($type == FileManager::FILESYSTEM_PLUGIN_PRIVATE) {
			$folder = $this->userInfo . $this->plugin . '/' . $pPath;
		} else if($type == FileManager::FILESYSTEM_PRIVATE && !$this->secure) {
			$folder = $this->userFiles . $pPath;
		}
		
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
		$extension = strtoupper( FileManager::getExtension($pPath) );
		
		if(!empty($extension)) {
			if($extension == "MP3") {
				return "music";
			} else if($extension == "MP4") {
				return "video";
			} else if($extension == "JPEG" || $extension == "JPG" || $extension == "PNG" || $extension == "GIF") {
				return "image";
			} else if($extension == "TMPDL") {
				return "tmpdl";
			}
		}
		
		return "file";
	}
	
	function getFile($pPath) {
		$pPath = urldecode($pPath);
		$filetype = $this->getFileType($pPath);

		if(substr($pPath, 0, strlen(FileManager::FILESYSTEM_PLUGIN_DOWNLOAD)) === FileManager::FILESYSTEM_PLUGIN_DOWNLOAD) {
			$path = substr($pPath, strlen(FileManager::FILESYSTEM_PLUGIN_DOWNLOAD)+3);
			$path = explode('/', $path);
			
			$plugin = $path[0];
			unset($path[0]);
			
			$path = implode('/', $path);
			
			require_once(dirname(__FILE__) . '/PluginManager.php');
			
			$pluginManager = new PluginManager($plugin);
			if($pluginManager->isInstalled($plugin)) {
				$pluginManager->getView('download', $path);
			}
			
		} else {
			if(substr($pPath, 0, strlen(FileManager::FILESYSTEM_PLUGIN_PUBLIC)) === FileManager::FILESYSTEM_PLUGIN_PUBLIC) {
				$path = substr($pPath, strlen(FileManager::FILESYSTEM_PLUGIN_PUBLIC)+3);
				$path = dirname(dirname(__FILE__)) . '/data/' . $path;
			} else if(substr($pPath, 0, strlen(FileManager::FILESYSTEM_PLUGIN_PRIVATE)) === FileManager::FILESYSTEM_PLUGIN_PRIVATE) {
				$path = substr($pPath, strlen(FileManager::FILESYSTEM_PLUGIN_PRIVATE)+3);
				$path = $this->userFolder . '.userfiles/' . $path;
			} else {
				if($this->userFiles != null) {
					$path = $this->userFiles . $pPath;
				} else {
					die('noright');
				}
			}
			
			if(is_dir($path)) {
				ini_set("max_execution_time", 300);
				
				$uniquid = uniqid();
				$filename = $path . '_' . $uniquid . '.zip.tmpdl';
				
				shell_exec('cd ' . $path . ' && zip -r ' . $filename . ' . -x .\* "*/\.*"');
				
				header('Content-Type: application/octet-stream');
				header('Content-Transfer-Encoding: Binary'); 
				header('Content-disposition: attachment; filename="' . basename($path) . '.zip"'); 
				
				if(file_exists($filename)) {
					readfile($filename);
					unlink($filename);
				}
			}
			
			if(file_exists($path)) {
				if($filetype == "music") {
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
				} else if($filetype == "video") {
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

	function uploadFiles(&$file_post, $pPath = '', $type=FileManager::FILESYSTEM_PRIVATE) {
		$pPath = rtrim($pPath, "/") . '/';
		
		if($type == FileManager::FILESYSTEM_PLUGIN_PUBLIC) {
			$folder = $this->pluginFiles . $pPath;
		} else if($type == FileManager::FILESYSTEM_PLUGIN_PRIVATE) {
			$folder = $this->userInfo . $this->plugin . '/' . $pPath;
		} else if($type == FileManager::FILESYSTEM_PRIVATE && !$this->secure) {
			$folder = $this->userFiles . $pPath;
		}
		
		$fileArray = null;
		
		if (is_dir($folder)) {
			$files = FileManager::reArrayFiles($file_post);
			
			if(!empty($files)) {
				foreach($files as $file) {
					if (empty($file) || !empty($file['error'])) {
				        continue; // Skip file if any error found
				    }
				    
					$name = $file['name'];
				    if(!file_exists($folder.$name)) {
				    	move_uploaded_file($file["tmp_name"], $folder.$name);
						$fileArray[] = $name;
					} else {
						$uniquid = uniqid();
						$filename = FileManager::getFileName($name);
						$extension = FileManager::getExtension($name);
						move_uploaded_file($file["tmp_name"], $folder . $filename . ' - ' . $uniquid . '.' . $extension);
						$fileArray[] = $filename . ' - ' . $uniquid . '.' . $extension;
					}
				}
			}
		}
		
		return $fileArray;
	}
	
	function fileExists($pPathFile, $explicit=false, $type=FileManager::FILESYSTEM_PRIVATE) {
		if($type == FileManager::FILESYSTEM_PLUGIN_PUBLIC) {
			$path = $this->pluginFiles . $pPathFile;
		} else if($type == FileManager::FILESYSTEM_PLUGIN_PRIVATE) {
			$path = $this->userInfo . $this->plugin . '/' . $pPathFile;
		} else if($type == FileManager::FILESYSTEM_PRIVATE && !$this->secure) {
			$path = $this->userFiles . $pPathFile;
		}
		
		if(file_exists($path) || ( !$explicit && file_exists( substr($path, 0, -1) ) )) {
			return true;
		}
		
		return false;
	}
	
	function move($filenameOld, $filenameNew, $type=FileManager::FILESYSTEM_PRIVATE) {
		if($this->fileExists($filenameNew, TRUE, $type)) {
			$filenameNew = $filenameNew . ' - ' . uniqid();
		}
		
		if($type == FileManager::FILESYSTEM_PLUGIN_PUBLIC) {
			$fileOld = $this->pluginFiles . $filenameOld;
			$fileNew = $this->pluginFiles . $filenameNew;
		} else if($type == FileManager::FILESYSTEM_PLUGIN_PRIVATE) {
			$fileOld = $this->userInfo . $this->plugin . '/' . $filenameOld;
			$fileNew = $this->userInfo . $this->plugin . '/' . $filenameNew;
		} else if($type == FileManager::FILESYSTEM_PRIVATE && !$this->secure) {
			$fileOld = $this->userFiles . $filenameOld;
			$fileNew = $this->userFiles . $filenameNew;
		}
		
		if($this->fileExists($filenameOld, TRUE, $type)) {
			if(copy($fileOld, $fileNew)) {
				unlink($fileOld);
				return true;
			}
		}
		
		return false;
	}
	
	function delete($pPath, $type=FileManager::FILESYSTEM_PRIVATE) {
		if($type == FileManager::FILESYSTEM_PLUGIN_PUBLIC) {
			$path = $this->pluginFiles . $pPath;
		} else if($type == FileManager::FILESYSTEM_PLUGIN_PRIVATE) {
			$path = $this->userInfo . $this->plugin . '/' . $pPath;
		} else if($type == FileManager::FILESYSTEM_PRIVATE && !$this->secure) {
			$path = $this->userFiles . $pPath;
		}
		
		
		if(!is_dir($path)) {
			if($this->fileExists($pPath, true, $type)) {
				unlink($path);
				return true;
			} else if($this->fileExists(substr($pPath, 0, -1), true, $type)) {
				unlink( substr($path, 0, -1) );
				return true;
			}
		} else {
			FileManager::delTree($path);
		}
		
		return false;
	}
	
	public static function isVisible($path) {
		$name = FileManager::getBaseName($path);
		
		if(preg_match("/\/\.[^\/]/", '/' . $path)) {
			return false;
		}
		
		if(substr($name, 0, 1) == '.') {
			return false;
		}
		
		return true;
	}
	
	public static function delTree($dir) { // http://de2.php.net/manual/de/function.rmdir.php#107233
		 $files = array_diff(scandir($dir), array('.','..'));
		 
		 foreach ($files as $file) {
		 	(is_dir("$dir/$file")) ? FileManager::delTree("$dir/$file") : unlink("$dir/$file"); 
		 }
		 
		 return rmdir($dir); 
	} 
	
	static function getFileName($pPath) {
		return pathinfo($pPath, PATHINFO_FILENAME);
	}
	
	static function getExtension($pPath) {
		return pathinfo($pPath, PATHINFO_EXTENSION);
	}
	
	static function getBaseName($pPath) {
		return pathinfo($pPath, PATHINFO_BASENAME);
	}
	
	function getFolderFromPath($pPath) {
		$path = dirname($pPath);
		
		if(file_exists($this->userFiles . $path)) {
			return $path;
		}
		
		return "";
	}
	
	function isFolder($pPath, $type=FileManager::FILESYSTEM_PRIVATE) {
		if($type == FileManager::FILESYSTEM_PLUGIN_PUBLIC) {
			$path = $this->pluginFiles . $pPath;
		} else if($type == FileManager::FILESYSTEM_PLUGIN_PRIVATE) {
			$path = $this->userInfo . $this->plugin . '/' . $pPath;
		} else if($type == FileManager::FILESYSTEM_PRIVATE && !$this->secure) {
			$path = $this->userFiles . $pPath;
		}
		
		if(is_dir($path)) {
			return true;
		}
		
		return false;
	}
	
	function createFolder($pName, $pPath = '', $type=FileManager::FILESYSTEM_PRIVATE) {
		if($type == FileManager::FILESYSTEM_PLUGIN_PUBLIC) {
			$path = $this->pluginFiles . $pPath;
		} else if($type == FileManager::FILESYSTEM_PLUGIN_PRIVATE) {
			$path = $this->userInfo . $this->plugin . '/' . $pPath;
		} else if($type == FileManager::FILESYSTEM_PRIVATE && !$this->secure) {
			$path = $this->userFiles . $pPath;
		}
		
		if($this->isFolder($pPath . '/' . $pName) || $this->fileExists($pPath .'/' . $pName, true, $type)) {
			mkdir($path . '/' . $pName . uniqid(), 0744, true);
		} else {
			mkdir($path . '/' . $pName, 0744, true);
		}
	}
	
	function getBytesString($bytes) {
		if(is_numeric($bytes)) {
			$kb = $bytes/1000;
			$mb = $bytes/1000000;
			$gb = $bytes/1000000000;
			
			if(round($kb) != 0) {
				if(round($mb) != 0) {
					if(round($gb) != 0) {
						return round($gb, 2) . ' GB';
					}
					
					return round($mb, 2) . ' MB';
				}
				
				return round($kb, 2) . ' KB';
			}
			
			return $bytes . ' B';
		}
		
		return '0 MB';
	}
	
	static function reArrayFiles(&$file_post) {
	    $file_ary = array();
	    $file_count = count($file_post['name']);
	    $file_keys = array_keys($file_post);
	
	    for ($i=0; $i<$file_count; $i++) {
	        foreach ($file_keys as $key) {
	        	if(!empty($file_post[$key][$i])) {
	            	$file_ary[$i][$key] = $file_post[$key][$i];
				}
	        }
	    }
	
	    return $file_ary;
	}

	static function getImageHashByPath($pPath) {
		$basename = dirname(dirname(__FILE__)) . '/';
		
		if(substr($pPath, 0, strlen($basename)) !== $basename)
		$pPath = $basename . $pPath;
		
		$type = pathinfo($pPath, PATHINFO_EXTENSION);
		$data = file_get_contents($pPath);
		$base64 = 'data:image/' . $pPath . ';base64,' . base64_encode($data);
		
		return $base64;
	}
	
	function getImageHash($path, $type=FileManager::FILESYSTEM_PRIVATE) {
		if($type == FileManager::FILESYSTEM_PLUGIN_PUBLIC) {
			$file = $this->pluginFiles . $path;
		} else if($type == FileManager::FILESYSTEM_PLUGIN_PRIVATE) {
			$file = $this->userInfo . $this->plugin . '/' . $path;
		} else if($type == FileManager::FILESYSTEM_PRIVATE && !$this->secure) {
			$file = $this->userFiles . $path;
		}
		
		if($this->getFileType($file) == "image" && $this->fileExists($path, true, $type)) {
			return FileManager::getImageHashByPath($file);
		} else {
			return "";
		}
	}
	
	function resizeImage($file, $type, $newfile, $maxWidth=100, $maxHeight=100) {
		if($type == FileManager::FILESYSTEM_PLUGIN_PUBLIC) {
			$imagefile = $this->pluginFiles . $file;
			$newfile = $this->pluginFiles . $newfile;
		} else if($type == FileManager::FILESYSTEM_PLUGIN_PRIVATE) {
			$imagefile = $this->userInfo . $this->plugin . '/' . $file;
			$newfile = $this->userInfo . $this->plugin . '/' . $newfile;
		} else if($type == FileManager::FILESYSTEM_PRIVATE && !$this->secure) {
			$imagefile = $this->userFiles . $file;
			$newfile = $this->userFiles . $newfile;
		}
		
		$imagesize = getimagesize($imagefile);
		$imagewidth = $imagesize[0];
		$imageheight = $imagesize[1];
		$imagetype = $imagesize[2];
		switch ($imagetype) {
		    // Bedeutung von $imagetype:
		    // 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order), 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM
		    case 1: // GIF
		        $image = imagecreatefromgif($imagefile);
		        break;
		    case 2: // JPEG
		        $image = imagecreatefromjpeg($imagefile);
		        break;
		    case 3: // PNG
		        $image = imagecreatefrompng($imagefile);
		        break;
		    default:
		        die('Unsupported imageformat');
		}
		
		// Ausmaße kopieren, wir gehen zuerst davon aus, dass das Bild schon Thumbnailgröße hat
		$newWidth = $imagewidth;
		$newHeight = $imageheight;
		// Breite skalieren falls nötig
		if ($newWidth > $maxWidth)
		{
		    $factor = $maxWidth / $newWidth;
		    $newWidth *= $factor;
		    $newHeight *= $factor;
		}
		// Höhe skalieren, falls nötig
		if ($newHeight > $maxHeight)
		{
		    $factor = $maxHeight / $newHeight;
		    $newWidth *= $factor;
		    $newHeight *= $factor;
		}
		// Thumbnail erstellen
		$thumb = imagecreatetruecolor($newWidth, $newHeight);
		
		imagecopyresampled(
		    $thumb,
		    $image,
		    0, 0, 0, 0, // Startposition des Ausschnittes
		    $newWidth, $newHeight,
		    $imagewidth, $imageheight
		);
		
		header('Content-Type: image/png');
		imagepng($thumb, $newfile);
		// In Datei speichern
		// $thumbfile = 'thumbs/' . $imagefile;
		// imagepng($thumb, $thumbfile);
		imagedestroy($thumb);
	}
	
	function moveToUserPublic($privateFile, $publicFolder) {
		if(!$this->isFolder($privateFile, FileManager::FILESYSTEM_PLUGIN_PRIVATE) && $this->isFolder($publicFolder)) {
			
			$oldFile = $this->userInfo . $this->plugin . '/' . $privateFile;
			$newFile = $this->userFiles . $publicFolder . $this->getBaseName($privateFile);
			
			if(file_exists($newFile)) {
				$newFile = $this->userFiles . $publicFolder . $this->getBaseName($privateFile);
				
				$filename = FileManager::getFileName($privateFile);
				$extension = FileManager::getExtension($privateFile);
				$newFile = $this->userFiles . $publicFolder . $filename . ' - ' . uniqid() . '.' . $extension;
			}
			
			if(copy($oldFile, $newFile)) {
				unlink($oldFile);
			}
		}
	}
	
	function getAudioList() {
		$path = $this->userInfo . 'audio.json';
		
		if(file_exists($path)) {
			$handle = fopen($path, "a+");
			
			$string = fread($handle, filesize($path));
			fclose($handle);
			
			return json_decode($string);
		}
		
		return null;
	}
	
	function getVideoList() {
		$path = $this->userInfo . 'video.json';
		
		if(file_exists($path)) {
			$handle = fopen($path, "a+");
			
			$string = fread($handle, filesize($path));
			fclose($handle);
			
			return json_decode($string);
		}
		
		return null;
	}
	
	function getImageList() {
		$path = $this->userInfo . 'image.json';
		
		if(file_exists($path)) {
			$handle = fopen($path, "a+");
			
			$string = fread($handle, filesize($path));
			fclose($handle);
			
			return json_decode($string);
		}
		
		return null;
	}
	
	function forbidFilesystem() {
		$this->secure = true;
	}
	
	static function updateUserFileList($id=0) {
		global $loginManager;
		
		if(WEBSOCKET != 1 || empty($id) || !is_numeric($id)) {
			$id = $loginManager->getId();
		}
		
		$basedir = dirname(dirname(__FILE__)) . '/data/user_' . $id . '/files';
		
		$audio = null;
		$video = null;
		$images = null;
		
		$directory = new RecursiveDirectoryIterator($basedir);
		$objects = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
		foreach($objects as $name => $object){
			$extension = FileManager::getExtension($name);
			
			if(!FileManager::isVisible($object)) {
				continue;
			}
			
			if(in_array(strtoupper($extension), FileManager::EXTENSION_IMAGES)) {
				$images[] = str_replace($basedir, '', $name);
			} else if(in_array(strtoupper($extension), FileManager::EXTENSION_VIDEO)) {
				$video[] = str_replace($basedir, '', $name);
			} else if(in_array(strtoupper($extension), FileManager::EXTENSION_AUDIO)) {
				$audio[] = str_replace($basedir, '', $name);
			}
		}
		
		if(empty($images)) {
			$images = array();
		}
		
		if(empty($audio)) {
			$audio = array();
		}
		
		if(empty($video)) {
			$video = array();
		}
		
		$datei = fopen(dirname(dirname(__FILE__)) . '/data/user_' . $id . '/.userfiles/image.json',"w+");
		fwrite($datei, json_encode($images));
		fclose($datei);
		
		$datei = fopen(dirname(dirname(__FILE__)) . '/data/user_' . $id . '/.userfiles/video.json',"w+");
		fwrite($datei, json_encode($video));
		fclose($datei);
		
		$datei = fopen(dirname(dirname(__FILE__)) . '/data/user_' . $id . '/.userfiles/audio.json',"w+");
		fwrite($datei, json_encode($audio));
		fclose($datei);
	}
}

?>