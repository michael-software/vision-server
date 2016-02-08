[
{
	"type":"heading",
	"value":<?php echo json_encode($loginManager->getUsername()); ?>
},
{
	"type":"button",
	"value":"Kennwort ändern",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','changepassword','')"
}
<?php



?>
]