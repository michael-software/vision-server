<?php

if(!empty($pluginManager->getCommand(0)) && $pluginManager->getCommand(0) == "music") {
	foreach($pluginManager->fileManager->getAudioList() as $audio) {
		$filename = $pluginManager->fileManager->getFileName($audio);
		$output['audio'][] = array("name"=>$filename,"file"=>$audio);
	}
} else if(!empty($pluginManager->getCommand(0)) && $pluginManager->getCommand(0) == "video") {
	foreach($pluginManager->fileManager->getVideoList() as $video) {
		$filename = $pluginManager->fileManager->getFileName($video);
		$output['video'][] = array("name"=>$filename,"file"=>$video);
	}
} else if(!empty($pluginManager->getCommand(0)) && $pluginManager->getCommand(0) == "image") {
	if(!empty($pluginManager->getCommand(1)) && $pluginManager->getCommand(1) == "all") {
		foreach($pluginManager->fileManager->getImageList() as $image) {
			$filename = $pluginManager->fileManager->getFileName($image);
			$output['image'][] = array("name"=>$filename,"file"=>$image);
		}
	}
} else {
	$audio = $pluginManager->fileManager->getAudioList();
	$video = $pluginManager->fileManager->getVideoList();
	$image = $pluginManager->fileManager->getImageList();
	
	$output['audio'] = $audio;
	$output['video'] = $video;
	$output['image'] = $image;
}
?>