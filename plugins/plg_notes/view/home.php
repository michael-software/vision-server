<?php
	$notesArray = $pluginManager->databaseManager->getValues();
	$notes = null;
	$noteIds = null;
	
	foreach($notesArray as $note) {
		$notes[] = $note['name'];
		
		if(empty($note['password'])) {
			$noteIds[] = "openPlugin('plg_notes', 'notes','" . $note['id'] . "')";
		} else {
			$noteIds[] = "openPlugin('plg_notes', 'password','" . $note['id'] . "')";
		}
	}
?>

[
	{
		"type":"heading",
		"value":"Notizen"
	},
	{
		"type":"list",
		"value":<?php echo json_encode($notes); ?>,
		"click":<?php echo json_encode($noteIds); ?>,
		"longclick":<?php echo json_encode($noteIds); ?>
	},
	{
		"type":"button",
		"value":"Neue Notiz erstellen",
		"click":"openPlugin('plg_notes','addnote','')"
	}
]