<?php
$command = $pluginManager->getCommand(0);

if($pluginManager->isInstalled('plg_arkviewer')) {
	require_once $pluginManager->getController('plg_arkviewer', 'ArkViewer');
}

if($command == "restart") {
	exec('sudo /var/www/sh/restart.sh');
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
} else if ($command == "shutdown") {
	exec('sudo /var/www/sh/shutdown.sh');
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
} else if ($command == "update") {
	exec('sudo /var/www/sh/serverupdate.sh');
	die('{"redirect":["' . $pluginManager->getPluginName() . '", "home", ""]}');
}
?>

[

<?php if($loginManager->isAllowed(LoginManager::STOP_SERVER)) { ?>

{
	"type":"heading",
	"value":"Servereinstellungen"
},
{
	"type":"button",
	"value":"Server neustarten",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','home','restart')"
},
{
	"type":"button",
	"value":"Server herunterfahren",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','home','shutdown')"
},
{
	"type":"button",
	"value":"Server updaten",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','home','update')"
},
{"type":"nl"},{"type":"nl"},

<?php } ?>

{
	"type":"heading",
	"value":"Gameserver"
},
{
	"type":"headingSmall",
	"value":"TeamSpeak"
},
{
	"type":"table",
	"rows":[["Status:",
<?php
	$status = exec('sudo /var/www/sh/ts3status.sh');

	if($status == "Server is running") {
		echo '{"value":"Server online",';
		echo '"color":"#00FF00"}';
	} else {
		echo '{"value":"Server offline",';
		echo '"color":"#FF0000"}';
	}
?>
]]
},

<?php if($loginManager->isAllowed(LoginManager::STOP_SERVER)) { ?>

{
	"type":"button",
	"value":"TeamSpeak 3 starten",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','start','teamspeak')"
},
{
	"type":"button",
	"value":"TeamSpeak 3 stoppen",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','stop','teamspeak')"
},
<?php
if($status == "Server is running" && $pluginManager->isInstalled('plg_ts3viewer')) {
	echo '{"type":"button","value":"TeamSpeak 3 Viewer","click":"openPlugin(\'plg_ts3viewer\',\'\',\'\')"},';
	echo '{"type":"button","value":"TeamSpeak 3 Server betreten","click":"openUrl(\'ts3server://' . $pluginManager->getIp() . '\')"},';
}

}
?>
{ "type":"nl" },{ "type":"nl" },
{
	"type":"headingSmall",
	"value":"CS:GO"
},
{
	"type":"table",
	"rows":[["Status:",
<?php
	ob_start();
	passthru('sudo /var/www/sh/csgostatus.sh');
	$status = ob_get_clean();
	
	if(strpos($status, "csgo") === false) {
		echo '{"value":"Server offline",';
		echo '"color":"#FF0000"}';
	} else {
		echo '{"value":"Server online",';
		echo '"color":"#00FF00"}';
	}
?>
]] },

<?php if($loginManager->isAllowed(LoginManager::STOP_SERVER)) { ?>

{
	"type":"button",
	"value":"CS:GO Server starten",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','start','csgo')"
},
{
	"type":"button",
	"value":"CS:GO Server stoppen",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','stop','csgo')"
},
{
	"type":"button",
	"value":"CS:GO Server updaten",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','update','csgo')"
},

<?php } ?>

{ "type":"nl" },{ "type":"nl" },
{
	"type":"headingSmall",
	"value":"ARK:SE"
},
{
	"type":"table",
	"rows":[["Status:",
<?php
	ob_start();
	passthru('sudo /var/www/sh/csgostatus.sh');
	$status = ob_get_clean();
	
	if(strpos($status, "arkse") === false) {
		echo '{"value":"Server offline",';
		echo '"color":"#FF0000"}';
	} else {
		echo '{"value":"Server online",';
		echo '"color":"#00FF00"}';
	}
?>
]] },

<?php if($loginManager->isAllowed(LoginManager::STOP_SERVER)) { ?>

{
	"type":"button",
	"value":"ARK:SurvivalEvolved Server starten",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','start','arkse')"
},
{
	"type":"button",
	"value":"ARK:SurvivalEvolved Server stoppen",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','stop','arkse')"
}
<?php
if(strpos($status, "arkse") === false) {
} else {
	if($pluginManager->isInstalled('plg_arkviewer')) {
		echo ',{"type":"button","value":"Ark:SE Viewer","click":"openPlugin(\'plg_arkviewer\',\'\',\'\')"}';
	}
}

} else {
	echo '{"type":"nl"}';
}
?>
,{"type":"nl"},{"type":"nl"}

<?php
if($loginManager->isAllowed(LoginManager::MODIFY_USERS)) { 

$users = $pluginManager->getUserManager()->getUserList();

$userArray;
$userArrayClick;

foreach($users as $user) {
	$userArray[] = $user['username'];
	$userArrayClick[] = "openPlugin('" . $pluginManager->getPluginName() . "','user','" . $user['id'] . "')";
}

?>
,{
	"type":"heading",
	"value":"Benutzer"
},{
	"type":"button",
	"value":"Benutzer erstellen",
	"click":"openPlugin('<?php echo $pluginManager->getPluginName(); ?>','user','create')"
},{
	"type":"list",
	"value":<?php echo json_encode($userArray); ?>,
	"click":<?php echo json_encode($userArrayClick); ?>
}
<?php } ?>
]