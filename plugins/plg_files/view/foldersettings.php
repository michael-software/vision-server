[
	{
		"type":"heading",
		"value":"Ordner"
	},
	{
		"type":"headingSmall",
		"value":"Speicherort:"
	},
	{
		"type":"text",
		"value":"%!#|params|#!%"
	},
	{
		"type":"input",
		"value":"%!#|params|#!%",
		"visible":"away",
		"name":"file"
	},
	{ "type":"nl" }, { "type":"nl" },
	{
		"type":"text",
		"color":"#FF0000",
		"value":"Bitte beachten sie, dass beim Löschen eines Ordners auch sämtlicher Inhalt gelöscht wird !"
	},
	{
		"type":"button",
		"value":"Ordner löschen",
		"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','delete','%!#|params|#!%')"
	},{"type":"nl"},
	{
		"type":"button",
		"value":"Zu Zip Datei",
		"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','zip','%!#|params|#!%')"
	},{
		"type":"button",
		"value":"Als ZIP-Datei herunterladen",
		"click":"openMedia('file','%!#|params|#!%')"
	},{"type":"nl"},{"type":"nl"},
	{
		"type":"button",
		"value":"Zurück",
		"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','home','%!#|params|#!%')"
	}
]