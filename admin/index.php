<?php

session_start();

require_once dirname(dirname(__FILE__)) . '/config.php';

if(!$conf['root_enabled'] || empty($conf['root_password'])) {
	die("Root-Konto nicht aktiviert");
}

if(!empty($_POST['password']) && !empty($_POST['login']) && $_POST['password'] == $conf['root_password']) {
	$_SESSION['password'] = $_POST['password'];
}

if(!empty($_GET['action']) && $_GET['action'] == "logout") {
	unset($_SESSION['password']);
	header('Location: index.php');
}

?>

<html>
	<head>
		<style>
			body {
				background-color: #000000;
				font-family: Console, Lucidia, Monospace;
			}
			
			div {
				border: solid 3px #FFFFFF;
				padding: 10px;
				margin: 10px;
				
				text-align: center;
			}
			
			input, .button {
				display: inline-block;
				
				width: 300px;
				max-width: 100%;
				
				box-sizing: border-box;
				
				font-family: Console, Lucidia, Monospace;
				
				color: #FFFFFF;
				font-weight: bolder;
				
				padding: 5px;
				
				text-decoration: none;
			}
			
			input[type="text"], input[type="password"] {
				background-color: #000000;
				border: 1px solid #00FF00;
				margin: 5px;
			}
			
			input[type="submit"], .button {
				border: 6px outset #00FF00;
				background-color: #00FF00;
			}
		</style>
	</head>
	<body>

<?php

if(empty($_SESSION['password']) || $_SESSION['password'] != $conf['root_password']) {
?>

<div>
<form method="post">
	<input type="password" name="password" placeholder="Kennwort" required="required"><br /><br />
	<input type="submit" name="login" value="Anmelden">
</form>
</div>

<?php
} else {
?>
<div>
<form method="post">
	<input type="text" name="username" placeholder="Benutzername" required="required"><br />
	<input type="password" name="password1" placeholder="Kennwort" required="required"><br />
	<input type="password" name="password2" placeholder="Kennwort wiederholen" required="required"><br /><br />
	<input type="submit" name="create" value="Administrator erstellen">
</form>
</div>

<div>
	<a href="index.php?action=logout" class="button">Ausloggen</a>
</div>
<?php
}

?>

</body>
</html>