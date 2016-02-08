<?php

class SearchManager {
	function __construct() {
	}
	
	function getArray($pSearch) {
		$array;
		$pluginManager = new PluginManager();
		$pluginArray = json_decode($pluginManager->getPlugins());
		
		$pluginArray = $pluginManager->getPluginTags();
		foreach($pluginArray as $plugin) {
			$name = $plugin['name'];
			
			if(!empty($plugin['icon'])) {
				$icon = $plugin['icon'];
			} else {
				$icon = 'images/th-small.png';
			}
			
			$id   = $plugin['id'];
			
			if(stristr($name,$pSearch) !== false) {
				$array[] = Array("title"=>$name,"icon"=>$icon,"click"=>"openPlugin('$id','','')");
			}
			
			if(!empty($plugin['tags'])) {
				$tags = $plugin['tags'];
				
				foreach($tags as $tag) {
					if(is_array($tag) && count($tag) == 2 && count($tag[1]) == 2) {
						if(stristr($tag[0],$pSearch) !== false) {
							$description = $name.' - '.$tag[0];
							$array[] = Array("title"=>$description,"icon"=>$icon,"click"=>"openPlugin('$id','" . $tag[1][0] . "','" . $tag[1][1] . "')");
						}
					} else {
						if(stristr($tag,$pSearch) !== false) {
							$description = $name.' - '.$tag;
							$array[] = Array("title"=>$description,"icon"=>$icon,"click"=>"openPlugin('$id','','')");
						}
					}
				}
			}
		}
		
		if(!empty($array)) {
			return $array;
		}
		
		return null;
	}
}

?>