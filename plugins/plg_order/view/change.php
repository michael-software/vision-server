<?php

$jUI->add(new JUI\Text("Drücken sie auf eine Element aus der ersten Liste um dieses aus dem Menü zu entfernen. Drücken sie auf ein Element aus der zweiten Liste um dieses dem Menü hinzuzufügen."));

$jUI->add(new JUI\Heading("Menüleiste"));

$menuList = new JUI\ListView();
$mainPlugins = $pluginManager->getMainPluginsArray();

if(!empty($mainPlugins) && is_array($mainPlugins))
foreach($mainPlugins as $mainPlugin) {
	$id = $mainPlugin['id'];
	$name = $mainPlugin['name'];
	
	$menuList->addItem($name, new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'remove', $id));
}

$jUI->add($menuList);

$jUI->add(new JUI\Heading("Alle Plugins"));

$pluginList = new JUI\ListView();
$plugins = $pluginManager->getPlugins(FALSE);

if(!empty($plugins) && is_array($plugins))
foreach($plugins as $plugin) {
	$id = $plugin['id'];
	$name = $plugin['name'];
	
	if( !$pluginManager->isMainPlugin($id) ) {
		$pluginList->addItem($name, new JUI\Click(JUI\Click::openPlugin, $pluginManager, 'add', $id));
	}
}

$jUI->add($pluginList);
?>