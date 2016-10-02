[
	{
		"type":"heading",
		"value":"Datei"
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
		"type":"button",
		"value":"Herunterladen",
		"click":"openMedia('file','%!#|params|#!%')"
	},
	{
		"type":"button",
		"value":"Datei löschen",
		"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','delete','%!#|params|#!%')"
	},{"type":"nl"},{"type":"nl"},
	{
		"type":"button",
		"value":"Zurück",
		"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','home','%!#|params|#!%')"
	}
]