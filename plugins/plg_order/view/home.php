<?php

$jUI->setFlyover();
$heading = new JUI\Heading("Plugins");
$jUI->add($heading);

$list = new JUI\ListView();
$plugins = $pluginManager->getPluginTags();

$pluginArray = null;

if(!empty($plugins)) 
foreach ($plugins as $plugin) {
	$name = $plugin['name'];
	$id   = $plugin['id'];
	
	if($id == $pluginManager->getPluginName() || $id == 'plg_user') {
		continue;
	}
	
	if(!$loginManager->isAllowed('use_' . $id)) {
		continue;
	}
	
	if(empty($pluginArray[$name])) {
		$pluginArray[$name] = array("id"=>$id, "name"=>$name);
	} else {
		$pluginArray[$name . '_' . count($pluginArray)] = array("id"=>$id, "name"=>$name);
	}
}

ksort($pluginArray, SORT_STRING); /* TODO */

if(!empty($pluginArray))
foreach($pluginArray as $plugin) {
	$name = $plugin['name'];
	$id   = $plugin['id'];
	
	$list->addItem($name, new JUI\Click(JUI\Click::openPlugin, $id));
}

$jUI->add($list);

$jUI->hline();
$jUI->add(new JUI\Button("Seitenleiste bearbeiten", new JUI\Click(JUI\Click::openPlugin, $pluginManager, "change")));

?>