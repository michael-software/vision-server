<?php
if ( (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ) && !empty($_POST['authtoken'])) {
    $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirect_url");
    exit();
}
?>

<!-- <html manifest="vision.manifest"> -->
<html manifest="cache.manifest">
	<head>
		<link rel="icon" href="images/vision_icon.png" type="image/x-icon" />
		
		<link rel="manifest" href="/manifest.json" />
		<meta name="theme-color" content="#FF0000" />
		
		<title>Vision</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
		<style>
		noscript {
			position: fixed;
			
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			
			z-index: 10;
			
			background-color: #FFFFFF;
			
			padding: 50px;
			
			text-align: justify;
		}
		
		noscript .right {
			text-align: right;
		}
		
		#menu, #flyover, #overlay-invisible {
			display: none;
		}
		
		#loader.loaded #loading-status {
			display: none;
		}
		
		#login input {
			font-family: Arial;
			font-size: 20px;
		}
		
		#loading-status a, #loader.loaded #menu a {
			font-family: "Courier New", Courier, monospace !important;
		}
		
		body {
			margin: 0;
			padding: 0;
			font-family: Arial, Helvetica, sans-serif;
		}
		
		#info-box {
			display: none;
			background-color: #0000FF;
			
			line-height: 25px;
			text-align: center;
			color: #FF0000;
			
			padding: 5px;
			
			width: 100%;
		}
		
		@media only screen and (min-width: 600px){
			body {
				margin-left: 126px;
			}
			
			#loader
			{
				position: fixed;
				top: 0px;
				left: 0px;
				right: 0px;
				bottom: 0px;
				
				width: 100%;
				height: 100%;
				
				z-index: 9;
				
				margin: 0;
				padding: 0;
				
				padding-top: 10px;
				
				background-color: #FBFBFB;
				
				cursor: default;
			}
			
			#loader.loaded #menu {
				position: fixed;
				
				top: 5px;
				left: 0;
				bottom: 70px;
				
				overflow: hidden;
				overflow-y: auto;
				
				display: block;
				
				width: 125px;
				
				border: none;
				
				font-family: "Courier New", Courier, monospace !important;
				font-weight: bold;
				
				text-align: center;
				
				list-style-type: none;
				margin: 0;
				padding: 0;
				
				-webkit-animation: fadeIn 1s ease;
				-moz-animation: fadeIn 1s ease;
				animation: fadeIn 1s ease;
			}
			
			#loader.loaded #infinity {
				position: fixed;
				
				top: 100%;
				bottom: 50px;
				left: 10px;
				
				width: 106px;
				height: 50px;
				
				margin: 0;
				margin-top: -60px;
				
				-webkit-animation: slideIconMenu 1s ease;
				-moz-animation: slideIconMenu 1s ease;
				animation: slideIconMenu 1s ease;
			}
			
			#loader.loaded #infinity:before, #loader.loaded #infinity:after {
				width: 30px;
				height: 30px;
				border: 10px solid #FF0000;
				
				-webkit-animation: scaleIconMenu 1s ease;
				-moz-animation: scaleIconMenu 1s ease;
				animation: scaleIconMenu 1s ease;
			}
			
			#loading-status {
				position: fixed;
				
				top: 50%;
				left: 50%;
				
				width: 500px;
				height: 200px;
				
				margin-top: 200px;
				margin-left: -250px;
				
				font-family: "Courier New", Courier, monospace !important;
				font-weight: bolder;
				font-size: 50px;
				
				text-align: center;
				color: #000000;
			}
			
			#login {
				display: table-cell;
				position: fixed;
				top: 50%;
				left: 50%;
				margin-top: -157px;
				margin-left: -332px;
				width: 650px;
				height: 300px;
				
				padding: 7px;
				
				background-color: #F8F8F8;
				
				font-family: Arial;
				font-size: 20px;
				text-align: center;
				
				box-shadow: 0px 0px 5px #AAAAAA;
				
				z-index: 10;
			}
			
			#infinity {
				position: fixed;
				
				top: 50%;
				left: 50%;
				
				width: 642px; /*212*/
				height: 300px; /*100*/
				
				margin-top: -150px;
				margin-left: -321px;
				
				z-index: 3;
			}
	
			#infinity:before,#infinity:after {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				width: 180px;
				height: 180px;
				border: 60px solid #FFFFFF;
				-moz-border-radius: 100% 100% 0 100%;
				border-radius: 100% 100% 0 100%;
				-webkit-transform: rotate(-45deg);
				-moz-transform: rotate(-45deg);
				-ms-transform: rotate(-45deg);
				-o-transform: rotate(-45deg);
				transform: rotate(-45deg);
				
				-webkit-animation: pulse 4s linear infinite;
				-moz-animation: pulse 4s linear infinite;
				animation: pulse 4s linear infinite;
			}
	
			#infinity:after {
				left: auto;
				right: 0;
				-moz-border-radius: 100% 100% 100% 0; 
				border-radius: 100% 100% 100% 0;
				-webkit-transform: rotate(45deg);
				-moz-transform: rotate(45deg);
				-ms-transform: rotate(45deg);
				-o-transform: rotate(45deg);
				transform: rotate(45deg);
			}
			
			@-moz-keyframes pulse {
				0% { border: 60px solid #FF0000; }
				50% { border: 60px solid #000000; }
				100% { border: 60px solid #FF0000; }
			}
			@-webkit-keyframes pulse { 
				0% { border: 60px solid #FF0000; }
				50% { border: 60px solid #000000; }
				100% { border: 60px solid #FF0000; }
			}
			@keyframes pulse { 
				0% { border: 60px solid #FF0000; }
				50% { border: 60px solid #000000; }
				100% { border: 60px solid #FF0000; }
			}
			
			
			@-moz-keyframes slideIconMenu {
				0% { top: 50%; margin-top: -150px; margin-left: -321px; left: 50%; width: 642px; height: 300px; }
				100% { top: 100%; margin-top: -60px; margin-left: 0; left: 10px; width: 106px; height: 50px; }
			}
			@-webkit-keyframes slideIconMenu { 
				0% { top: 50%; margin-top: -150px; margin-left: -321px; left: 50%; width: 642px; height: 300px; }
				100% { top: 100%; margin-top: -60px; margin-left: 0; left: 10px; width: 106px; height: 50px; }
			}
			@keyframes slideIconMenu { 
				0% { top: 50%; margin-top: -150px; margin-left: -321px; left: 50%; width: 642px; height: 300px; }
				100% { top: 100%; margin-top: -60px; margin-left: 0; left: 10px; width: 106px; height: 50px; }
			}
			
			
			@-moz-keyframes fadeIn {
				0% { opacity: 0; }
				100% { opacity: 1; }
			}
			@-webkit-keyframes fadeIn { 
				0% { opacity: 0; }
				100% { opacity: 1; }
			}
			@keyframes fadeIn { 
				0% { opacity: 0; }
				100% { opacity: 1; }
			}
		}
		
		@media only screen and (max-width:600px){
			body {
				margin-bottom: 80px;
			}
			
			#login {
				display: block;
				position: fixed;
				top: 5px;
				left: 5px;
				bottom: 75px;
				right: 5px;
				
				width: auto;
				height: auto;
				
				overflow-x: scroll;
				
				background-color: #F8F8F8;
				
				font-family: Arial;
				font-size: 20px;
				text-align: center;
				
				box-shadow: 0px 0px 5px #AAAAAA;
				
				z-index: 10;
			}
			
			#loader.loaded {
				position: fixed;
				top: initial;
				left: 0px;
				bottom: 0px;
				right: 0px;
				
				height: 75px;
				width: 100%;
				
				overflow: hidden;
			}
			
			#loader.loaded #menu {
				display: initial;
				
				position: fixed;
				left: 0px;
				bottom: 0px;
				right: 0px;
				
				max-height: 75px;
				width: 100%;
				list-style-type: none;
				margin: 0;
				padding: 0;
				
				white-space: nowrap;
				overflow-x: auto;
				overflow-y: hidden;
				
				z-index: 4;
			}
			
			#loader {
				position: fixed;
				top: 0px;
				left: 0px;
				right: 0px;
				bottom: 0px;
				
				width: 100%;
				height: 100%;
				
				z-index: 9;
				
				margin: 0;
				padding: 0;
				
				background-color: #FBFBFB;
			}
			
			#loading-status {
				position: fixed;
				
				top: 50%;
				
				width: 100%;
				
				-webkit-transform: translateY(-50%);
				-ms-transform: translateY(-50%);
				transform: translateY(-50%);
				
				font-family: "Courier New", Courier, monospace !important;
				font-weight: bolder;
				font-size: 50px;
				
				text-align: center;
				color: #000000;
			}
			
			#loader.loaded #menu li { display: inline-block; }
			
			#infinity-wrapper {
				position: fixed;
				
				right: -70px;
				bottom: 0;
				
				width: 300px; /*212*/
				height: 75px; /*100*/
				
				overflow: hidden;
				z-index: 3;
			}
			
			::-webkit-scrollbar {
				display: none; 
			}
			
			#infinity {
				position: fixed;
				
				bottom: 7px;
				left: 50%;
				
				width: 128px;
				height: 60px;
				
				margin-left: -64px;
				overflow: hidden;
				
				z-index: 3;
			}
			
			#loader.loaded #infinity {
				position: relative;
				width: 128px;
				height: 60px;
				
				margin-left: -64px;
			}
			
			#loader.loaded #infinity {
				bottom: -22px;
				
				width: 256px; /*212*/
				height: 120px; /*100*/
				
				margin-left: -128px;
				
				opacity: 0.3;
			}
	
			#infinity:before,#infinity:after {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				width: 36px;
				height: 36px;
				border: 12px solid #FF0000;
				-moz-border-radius: 100% 100% 0 100%;
				border-radius: 100% 100% 0 100%;
				-webkit-transform: rotate(-45deg);
				-moz-transform: rotate(-45deg);
				-ms-transform: rotate(-45deg);
				-o-transform: rotate(-45deg);
				transform: rotate(-45deg);
			}
			
			#loader.loaded #infinity:before, #loader.loaded #infinity:after {
				width: 72px;
				height: 72px;
				border: 24px solid #FF0000;
			}
	
			#infinity:after {
				left: auto;
				right: 0;
				-moz-border-radius: 100% 100% 100% 0; 
				border-radius: 100% 100% 100% 0;
				-webkit-transform: rotate(45deg);
				-moz-transform: rotate(45deg);
				-ms-transform: rotate(45deg);
				-o-transform: rotate(45deg);
				transform: rotate(45deg);
			}
		}
		</style>

		<script>
			var socket;
			var isShared = false;
			var openPluginArray = null;
			
			function getHash()
			{
				var hash = location.hash; // get the hash
				
				if(hash.indexOf('#') == 0) // when a hash(#) is in front of the real hash
				{
					hash = hash.substr(1, hash.length); // delete the hash(#) in front of the hash
				}
				
				return hash;
			}
			
			//addElementHead('scripts/jquery-2.1.4.min.js', 'JS', function() {
				addElementHead('scripts/output.js', 'JS', function() {
					var hash = getHash();
					hash = hash.split('/');
					
					if(hash[0] == 'share') {
						document.querySelector("#login").style.display = 'none';
						loginShare(hash[1]);
					} else if (getStorage('share') != null && getStorage('share') != "") {
						document.querySelector("#login").style.display = 'none';
						loginShare(getStorage('share'));
					} else {
						loginUser();
					}
				});
			//});
			
			function isNormalInteger(str) {
			    var n = ~~Number(str);
			    return String(n) === str && n >= 0;
			}
			
			function loginShare(shareCode) {
				var formData = new FormData();
				formData.append('share', shareCode);

				window.jui.tools.requestSite("ajax.php?action=login", formData, null, function (data) {
					parseShareLogin(data);
				});
			}
			
			function parseShareLogin(data) {
				var json = JSON.parse(data);
					
				if(json.status != null && json.status != "" && json.status == 'login') {
					isShared = true;
					
					setStorage("share",json.share);
					location.hash = json.hash;
					
					loadJui();
				} else if(json.status != null && json.status != "" && json.status == 'notloggedin') {
					loginUser();
					location.hash = '';
				}
			}
			
			function loginUser() {
				var token = getStorage("token");
				
				if(token != null && token != "") {
					document.querySelector("#login").style.display = 'none';
					loginWithToken(token);
				} else {
					openLogin();
				}
				
				//loadJs();
			}
			
			function addElementHead(pSrc, pScript, pFunction) {
				var head = document.getElementsByTagName("head")[0] || document.documentElement;
				
				if(pScript != null && pScript == 'JS') {
					var script = document.createElement("script");
					script.src = pSrc;
					
					var done = false;
				
					script.onload = script.onreadystatechange = function() {
						if (!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
							pFunction();
							// Handle memory leak in IE
							script.onload = script.onreadystatechange = null;
							if ( head && script.parentNode ) {
								head.removeChild( script );
							}
						}
					};
					
					head.insertBefore(script, head.firstChild);
				} else if(pScript != null && pScript == 'CSS') {
					var link = document.createElement("link");
					link.type = "text/css";
					link.rel = "stylesheet";
					link.href = pSrc;
					
					link.onload = link.onreadystatechange = function() {
						if (!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
							pFunction();
						}
					};
					
					document.getElementsByTagName("head")[0].appendChild(link);
				}
			}
			
			function openLogin() {
				document.querySelector('#loader').classList.remove('loaded')

				document.querySelector("#login").style.display = 'block';
				changeLoadingStatus('Anmeldung');
				
				var frm = document.querySelector("#login form");
				
				frm.addEventListener('submit', function (ev) {
					ev.preventDefault();

					var formData = new FormData();
						formData.append('username', document.querySelector('#username-login').value);
						formData.append('password', document.querySelector('#password-login').value);

					window.jui.tools.requestSite('api/login.php?action=login', formData, null, function (data) {
						parseLogin(data);
					});
				}, false);
			}
			
			function parseLogin(data) {
				if(data != "failure 0") {
					var json = JSON.parse(data);
					
					if(json.token != null && json.token != "" && json.username != null && json.username != "") {
						setStorage("token", json.token);
						setStorage("username", json.username);
						loginWithToken();
						document.querySelector("#login").style.display = 'none';
					}
				} else {
					alert("Fehler");
				}
			}
			
			function loginWithToken() {
				var authToken = getStorage("token");
				
				window.jui.tools.requestSite("api/login.php", null, [
					{
						name: 'Authorization',
						value: 'bearer ' + authToken
					}
				], function( data, status ) {
					if (status == 401) {
						cleanLogin();
						openLogin();
					} else {
						parseLoginToken(data);
					}
				});
			}
			
			function parseLoginToken(data) {
				var json = JSON.parse(data);
				
				if(json.status != null && json.status != "") {
					if(json.status == "login") {
						if(json.username != null && json.username != "") {
							setStorage("username", json.username);
							loadJui();
						}
						
						if(json.mainplugins != null && json.mainplugins != "") {
							setStorage("mainplugins", json.mainplugins);
						}
						
						if(json.fgcolor != null && json.fgcolor != "") {
							setStorage("fgcolor", json.fgcolor);
						}
						
						if(json.bgcolor != null && json.bgcolor != "") {
							setStorage("bgcolor", json.bgcolor);
						}
						
						if(json.wsport != null && json.wsport != "" && json.host != null && json.host != "") {
							initWs(json.host, json.wsport);
							//alert(json.wsport + ':' + json.host);
						}
						
						if(json.seamless != null) {
							openPluginArray = [];
							openPluginArray['name']    = json.seamless.name;
							openPluginArray['page']    = json.seamless.page;
							openPluginArray['command'] = json.seamless.command;
						}

						window.token = getStorage('token');
					} else if(json.status == 401) {
						cleanLogin();
						openLogin();
					}
				}
			}
			
			function initWs(ip, port) {
				var host = 'ws://' + ip + ':' + port; // SET THIS TO YOUR SERVER
				
				try {
					socket = new WebSocket(host);
					console.log('WebSocket - status '+socket.readyState);
					
					socket.onopen = function(msg) {
						console.log("Welcome - status "+this.readyState);
						
						if(getStorage('token') != null) {
							socket.send('login ' + getStorage('token') + ' desktop');
						}
					};
					
					socket.onmessage = function(msg) {
						if(msg.data != 'login ok' && msg.data != 'double login') {
							console.log(msg.data);
							
							var notify = JSON.parse(msg.data);
							
							if(notify.type == null || notify.type == "notification") {
								if(notify.action != null) {
									var action = notify.action;
								} else {
									var action = '';
								}
								
								if(notify.icon != null) {
									var icon = notify.icon;
								} else {
									var icon = 'images/vision_icon.png';
								}
								
								notifyUser(notify.title, notify.text, icon, action);
							} else if(notify.type == "action" && notify.action != null) {
								new Function(notify.action)();
							}
						} else if(msg.data == 'double login') {
							var infobox = document.getElementById('info-box');
							infobox.innerHTML = '<b>Doppelter Login</b><br /><i>Sie sind in diesem Browser zwei mal angemeldet. Bitte schließen sie den anderen Tab, sofern sie ihn nicht mehr benötigen.</i>';
							infobox.style.display = 'block';
						}
						//console.log("Received: "+msg.data);
					};
					
					socket.onerror = function(msg) {
						setTimeout(function(){ checkWs(ip, port); }, 5000);
					};
					
					socket.onclose = function(msg) {
						console.log("Disconnected - status "+this.readyState);
						setTimeout(function(){ checkWs(ip, port); }, 5000);
					};
				}
				catch(ex){
					console.log(ex);
				}
			}
			
			function checkWs(ip, port) {
				if(!socket || socket.readyState == 3) initWs(ip, port);
			}
			
			function notifyUser(title, text, image, click) {
				var options = {
					body: text,
					icon: image
				};
				
				if(click == null || click == '') {
					click = "openPlugin('', '', '')";
				}
				
				if (!("Notification" in window)) {
					//alert("This browser does not support desktop notification");
				}
				else if (Notification.permission === "granted") {
					var notification = new Notification(title, options);
					notification.onclick = new Function(click);
				}
				else if (Notification.permission !== 'denied') {
					Notification.requestPermission(function (permission) {
						if (permission === "granted") {
							var notification = new Notification(title, options);
							
							notification.onclick = new Function(click);
						}
					});
				}
			}
			
			function getStorage(pKey) {
				if (localStorage) {
					return localStorage.getItem(pKey);
				} else {
					var key = pKey + "=";
					var ca = document.cookie.split(';');
					for(var i = 0; i < ca.length; i++) {
						var c = ca[i];
						while (c.charAt(0)==' ') {
							c = c.substring(1);
						}
						
						if (c.indexOf(key) == 0)
							return c.substring(key.length,c.length);
					}
				}
				
				return "";
			}
			
			function setStorage(pKey, pValue) {
				if (localStorage) {
					localStorage.setItem(pKey, pValue);
				} else {
					document.cookie= pKey +"=" + pValue + "; expires=Thu, 18 Dec 2019 12:00:00 UTC";
				}
			}
			
			function cleanLogin() {
				setStorage("token","");
				setStorage("username","");
			}
			
			function changeLoadingStatus(pString) {
				document.getElementById('loading-status').innerHTML = pString;
			}

			function loadJui() {
				//changeLoadingStatus('Lade Grafische Benutzeroberfläche');
				
				//addElementHead('scripts/output.js', 'JS', function() { loadJs(); });

				loadJs();
			}
			
			function loadJs() {
				changeLoadingStatus('Lade Funktionen');
				
				addElementHead('scripts/general.js', 'JS', function() { loadJui2(); });
			}
			
			function loadJsShared() {
				changeLoadingStatus('Lade Funktionen');
				
				addElementHead('scripts/shared.js', 'JS', function() { loadSharedMain(); });
			}
			
			function loadJui2() {
				changeLoadingStatus('Lade Grafische Benutzeroberfläche');
				
				addElementHead('scripts/juiV2Custom.js', 'JS', function() { loadCss(); });
			}
			
			function loadCss() {
				changeLoadingStatus('Lade Design');
				
				addElementHead('styles/minified.php?fgcolor='
					+ getStorage('fgcolor').replace(/#/g, '%23')
					+ '&bgcolor=' + getStorage('bgcolor').replace(/#/g, '%23'), 'CSS', function() { loadVision(); });
			}
			
			function loadCssDesktop() { /* TODO */
				changeLoadingStatus('Lade Design');
				
				addElementHead('styles/desktop.css', 'CSS', function() { loadCssMobile(); });
			}
			
			function loadCssMobile() {
				changeLoadingStatus('Lade Design');
				
				addElementHead('styles/mobile.css', 'CSS', function() { loadVision(); });
			}
			
			function loadVision() {
				changeLoadingStatus('Lade Vision');
				
				if(!isShared) {
					if(openPluginArray != null) {
						openPlugin(openPluginArray['name'], openPluginArray['page'], openPluginArray['command']);
					}
					
					loadMenu();
				} else {
					loadJsShared();
				}
			}
		</script>
	</head>
	<body>
		<div id="loader">
			<ul id="menu">
				<li class="menu-tile">Notizen</li>
			</ul>
			
			<div id="infinity-wrapper">
				<div id="infinity"></div>
			</div>
			<div id="loading-status">Lade jQuery</div>
		</div>
		
		<div id="login">
			<br /><br />
			<form action="api/login.php?action=login" method="post">
				<label for="username-login">Benutzername</label><br/><input type="text" name="username" id="username-login" placeholder="Benutzername"><br /><br />
				<label for="password-login">Kennwort</label><br/><input type="password" name="password" id="password-login" placeholder="Kennwort"><br /><br /><br />
				<input type="submit" value="Anmelden">
			</form>
		</div>
		
		<div id="info-box">
        </div>
		
		<div id="content">
			Test
		</div>
		
		<div id="search-overlay">
			<div id="search-overlay-box">
				<input id="search-box" type="search" placeholder="Suchen">
				<div id="search-results"></div>
			</div>
		</div>
		
		<div id="flyover">
		</div>
		
		<div id="overlay-invisible">
		</div>
		
		<div id="uploadzone">
            <div id="uploadzone-box">
            	Dateien hierher ziehen
            </div>
        </div>
        
        <div id="mime-select">
            Dateien hierher ziehen
        </div>
        
        <div id="share-button">
        </div>
        
        <noscript>
        	<div class="right"><img src="images/vision_icon.png"></div>
        	<h1>In ihrem Browser wurde Javascript deaktiviert.</h1><hr />
        	Leider können wir ihnen aus diesem Grund unsere Webapp nicht zur Verfügung stellen.<br />
        	Diese Anwendung benutzt JavaScript nur um die Kommunikation mit dem Server herzustellen (AJAX und Websockets)
        	und die Anmeldedaten des Benutzers sowie evtl. bei Interesse Dateien auf dem Computer zu speichern.<br />
        	Wir würden sie daher bitten JavaScript zumindest auf dieser Seite zu aktivieren. <br/><br />
        	Hier finden Sie eine <a href="http://www.enable-javascript.com/de/" target="_blank">Anleitung wie Sie JavaScript in Ihrem Browser aktivieren</a>.
        </noscript>
	</body>
</html>