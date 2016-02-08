<?php
	if(!empty($_POST['passwordOld']) && !empty($_POST['passwordNew1']) && !empty($_POST['passwordNew2']) && $_POST['passwordNew1'] == $_POST['passwordNew2']) {
		$oldPassword = $_POST['passwordOld'];
		$newPassword = $_POST['passwordNew1'];
		
		if($loginManager->changePassword($oldPassword, $newPassword)) {
			die('{"redirect":["' . $pluginManager->getPluginName() . '","home",""]}');
		}
	}
?>

[
{
	"type":"heading",
	"value":"Kennwort ändern"
},
{
	"type":"input",
	"name":"passwordOld",
	"label":"Altes Kennwort: "
},
{ "type":"nl" }, { "type":"nl" },
{
	"type":"input",
	"name":"passwordNew1",
	"label":"Neues Kennwort: "
},
{ "type":"nl" },
{
	"type":"input",
	"name":"passwordNew2",
	"label":"Neues Kennwort wiederholen: "
},
{ "type":"nl" },{ "type":"nl" },
{
	"type":"submit",
	"value":"Ändern"
}
]