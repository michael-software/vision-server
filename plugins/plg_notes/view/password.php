<?php
if(!empty($_POST['notePassword']) && !empty($_POST['noteId']))
die('{"redirect":["plg_notes", "notes", "' . $_POST['noteId'] . '/' . $_POST['notePassword'] . '"]}');
?>

[
	{
		"type":"heading",
		"value":"Anmeldung für Notiz"
	},
	{
		"type":"input",
		"value":"%!#|params|#!%",
		"name":"noteId",
		"visible":"away"
	},
	{
		"type":"input",
		"name":"notePassword",
		"label":"Kennwort"
	},
	{ "type":"nl" },{ "type":"nl" },
	{
		"type":"submit",
		"url":["plg_notes","notes","%!#|params|#!%"],
		"value":"Speichern"
	}
]
