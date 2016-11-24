<?php

$jUI->add(new JUI\Heading("Lizenzen"));

$list = new JUI\ListView();

$list->addItem("Webdav - Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/) - three-clause BSD", new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'webdav' ));
$list->addItem("PHP-Websockets Server - Copyright (c) 2012, Adam Alexander - own license (modified three-clause BSD)", new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'websockets' ));
$list->addItem("Websocket client - (c) Paragi Aps 2013, Simon Riget - own license", new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'websocketclient1' ));
$list->addItem("TouchImageView (android) - (c) 2012, Michael Ortiz - own license", new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'touchimageview' ));

$jUI->add($list);
?>
