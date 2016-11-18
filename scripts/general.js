var modUrl = '';
var audioElement = null;
var audioOverlay = null, audioOverlayPlay = null, audioOverlayTrack = null, audioOverlayVolume = null, audioOverlayTime = null, audioOverlayName = null, audioOverlayNameOuter = null;
var downloadElement = null;
var videoElement = null;
var overlay = new Overlay();
var plugins = [];
var mimeTypes = [];

var sep1 = '%!#|params|#!%';
var lastSearch = '';
var searchTimeout;

var _body = document.querySelector('body');
var flyover, overlayInvisible = document.querySelector('#overlay-invisible');
var searchOverlay, searchBox;

var dragOverBox = false;
var dragTimeout;

var gui = null;
var shareableId = [];

var isMobile = false;

window.placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

window.onhashchange = function () {
	hashChanged(getHash());
};

window.onresize = function () {
	resizeAudio();
	proofMobile();
};

function proofMobile() {
	var width = window.innerWidth;
	var height = window.innerHeight;
	
	if(width < 600) {
		isMobile = true;
	} else {
		isMobile = false;
	}
}

window.ready(function () {
	searchBox = document.querySelector('#search-box');

	proofMobile();
	
	document.querySelector('body').addEventListener('dragstart', function () {
		return false;
	});
	
	window.setTimeout(function() {
		document.querySelector('#info-box').style.display = 'none';
	}, 5000);
	
	if(!isShared) {
		document.querySelector('#search-overlay').addEventListener('click', function() {
			closeSearch();
		});
		
		document.querySelector('#search-overlay-box').addEventListener('click', function(e) {
			e.stopPropagation();
		});
		
		searchBox.addEventListener('keyup', function () {
			searchString = searchBox.value;

			if(searchString != lastSearch) {
				lastSearch = searchString;
				clearTimeout(searchTimeout);
				searchTimeout = setTimeout(search, 1000);
			}
		});
		
		document.querySelector('html').addEventListener('dragover', function(event) {
			event.preventDefault();
			event.stopPropagation();
			dragOverBox = false;
			document.querySelector('html').classList.add('dragging');
			blur();
		});
		
		document.querySelector("#uploadzone").addEventListener('dragover', function(event) {
			event.preventDefault();
			event.stopPropagation();
			dragOverBox = false;
			clearTimeout(dragTimeout);
			document.querySelector('html').classList.add('dragging');
		});
		
		document.querySelector("#uploadzone").addEventListener('dragleave', function(event) {
			event.preventDefault();
			event.stopPropagation();
			
			if(!dragOverBox)
				dragTimeout = setTimeout(function () {
					document.querySelector('html').classList.remove('dragging');
					unBlur();
				}, 100);
		});
		
		document.querySelector('#uploadzone-box').addEventListener('dragover', function(event) { // only is there to prevent display none when drag over box
			event.preventDefault();
			event.stopPropagation();
			
			dragOverBox = true;
			
			clearTimeout(dragTimeout);
			document.querySelector('html').classList.add('dragging');
		});
		
		document.querySelector('#uploadzone-box').addEventListener('dragleave', function(event) { // only is there to prevent display none when drag over box
			event.preventDefault();
			event.stopPropagation();
			
			dragOverBox = false;
			
			clearTimeout(dragTimeout);
			document.querySelector('html').classList.add('dragging');
		});
		
		document.querySelector('#uploadzone').addEventListener('drop', handleDropEvent, false);
		
		document.addEventListener("keydown", function(e) {
		  if ((e.keyCode == 83 || e.keyCode == 70) && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
		    e.preventDefault();
		    openSearch();
		  } else if ((e.keyCode == 68 || e.keyCode == 72) && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
		    e.preventDefault();
		    location.hash = '';
		  }
		}, false);
	}
});

/* FileUploads */
var filelist = [];  // Ein Array, das alle hochzuladenden Files enthält
var totalSize = 0; // Enthält die Gesamtgröße aller hochzuladenden Dateien
var totalProgress = 0; // Enthält den aktuellen Gesamtfortschritt
var currentUpload = null; // Enthält die Datei, die aktuell hochgeladen wird
 
function handleDropEvent(event)
{
    event.stopPropagation();
    event.preventDefault();
 
    // event.dataTransfer.files enthält eine Liste aller gedroppten Dateien
    for (var i = 0; i < event.dataTransfer.files.length; i++) {
        filelist.push(event.dataTransfer.files[i]);  // Hinzufügen der Datei zur Uploadqueue
        totalSize += event.dataTransfer.files[i].size;  // Hinzufügen der Dateigröße zur Gesamtgröße
    }
    
    var extension = getFileExtension(filelist[0]['name']);
    openMimeSelect(extension);
    
    document.querySelector('html').classList.remove('dragging');
    
    return false;
}

function openMimeSelect(mime) {
	var mimeType = getMimeFromExtension(mime);
	var root = document.querySelector('#mime-select');
	root.innerHTML = '';
	
	if(mimeTypes[mimeType] != null) {
		var array = [];
		var arrayClick = [];
		
		for(var i = 0; i < mimeTypes[mimeType].length; i++){
        	var pluginId = mimeTypes[mimeType][i];
        	
        	
        	array[array.length] = plugins[pluginId]['name'];
        	arrayClick[arrayClick.length] = "openPlugin('webinterface', 'share', '" + pluginId + "')";
        }
        
        var listView = {};
        listView['type'] = 'list';
        listView['value'] = array;
        listView['click'] = arrayClick;
        
        var el = window.jui.parse({
			data: [listView]
		}, true, true);

        if(el != null) {
			root.appendChild(el);
		}
	}
	
	document.querySelector("#mime-select").style.display = 'block';
}

function getFileExtension(filename) {
	return filename.split('.').pop();
}

function getMimeFromExtension(extension) {
	extension = extension.toUpperCase();
	
	switch(extension) {
	    case 'PNG':
	        return 'image/*';
	        break;
	    case 'JPG':
	        return 'image/*';
	        break;
	    default:
	        return 'file/*';
	}
}

function loadMenu() {
	window.jui.tools.requestSite('api/plugins.php', null, [
		{
			name: 'Authorization',
			value: "bearer " + window.token
		}
	], function(data, status) {
		var obj = JSON.parse(data);

		if(!window.jui.tools.empty(obj.head) && !window.jui.tools.empty(obj.head.status) && obj.head.status == 401) {
			cleanLogin();
			openLogin();
			return;
		}

		document.querySelector('#menu').innerHTML = '';
		
		var usernameText = getStorage('username');

		var uLink = document.createElement('a');
			uLink.innerHTML = usernameText;
		
		
		var li = document.createElement('li');
			li.id = 'menu-username';
			li.className = 'menu-tile no-icon';
			li.appendChild(uLink);
			li.addEventListener('click', new Function('openPlugin(\'plg_user\',\'\',\'\')'), false);
		document.querySelector('#menu').appendChild(li);
		
		var mainPlugins = getStorage("mainplugins");
		
		for(var i = 0; i < obj.length; i++) {
			var id   = obj[i]['id'];
			var name = obj[i]['name'];
			
			var icon = obj[i]['icon'] || '';
			var iconColor = obj[i]['icon-color'] || '';
			
			var visible = obj[i]['visible'];
			var mimes = obj[i]['mime'];
			var shareable = obj[i]['shareable'];
			
			var img1 = document.createElement('img');
				img1.className = 'menu-icon';
				img1.src = icon;

			var img2 = document.createElement('img');
				img2.className = 'menu-icon-color';
				img2.src = iconColor;
			
			var link = document.createElement('a');
				link.innerHTML = name;
			
			if((visible == null || visible != 'no') && ((mainPlugins != null && mainPlugins.indexOf(id) !== -1) || id == 'plg_order')) {
				var li = document.createElement('li');
					li.className = 'menu-tile';
					li.appendChild(img1);
					li.appendChild(img2);
					li.appendChild(link);
					li.addEventListener('click', new Function('openPlugin(\'' + id + '\')'), false);
				document.querySelector('#menu').appendChild(li);
				
				if(icon == null && iconColor == null) {
					li.classList.add('no-icon');
				}
			}
			
			if(mimes != null) {
				for(var j = 0; j < mimes.length; j++) {
					var mime = mimes[j];
					
					var size = 0;
					if(mimeTypes[mime] != null) {
						size = mimeTypes[mime].length;
					} else {
						mimeTypes[mime] = [];
					}
					
					mimeTypes[mime][size] = id;
				}
			}
			
			if(shareable != null && shareable) {
				shareableId[shareableId.length] = id;
			}
			
			plugins[id] = [];
			plugins[id]['name'] = name;
			plugins[id]['icon'] = icon;
			if((visible == null || visible != 'no') && ((mainPlugins != null && mainPlugins.indexOf(id) !== -1) || id == 'plg_order')) {
				plugins[id]['visible'] = false;
			}
		}
		
		var hash = getHash();
		if(hash != '') {
			hashChanged(hash, true);
		} else {
			hashChanged('', true);
		}
	});
}

function showLoadingData() {
	var heading = document.createElement('div');
		heading.innerHTML = 'Lade Daten vom Server';
	
	overlay.setOverlayContent(heading);
	overlay.show();
}

function hideLoadingData() {
	overlay.hide();
}

function hideLoadingScreen() {
	if(document.getElementById('loader').className != null && document.getElementById('loader').className != 'loaded') {
		document.getElementById('loader').className = 'loaded';
	}
}

function openPlugin(pName, pView, pPage, noHistory) {
	if((pView == null || pView == '') && (pPage == null || pPage == '')) {
		var newHash = pName;
	} else if(pView != null && pPage == null) {
		var newHash = pName+'/'+pView;
	} else if(pView != null && pPage != null) {
		var newHash = pName+'/'+pView+'/'+pPage;
	}
	
	if(pName == 'webinterface' && pView == 'share' && pPage != null) {
	    var formData = new FormData();    // Anlegen eines FormData Objekts zum Versenden unserer Datei
	    formData.append('data[]', filelist[0]);  // Anhängen der Datei an das Objekt
	    
	    unBlur();
	    document.querySelector("#mime-select").style.display = 'none';

		window.jui.tools.requestSite("ajax.php?plugin=" + pPage + "&page=receiver&get=view", formData, null, function( data, status ) {
			window.jui.parse(data);
		});
	} else if(newHash == getHash()) {
		hashChanged(newHash);
	} else {
		if(noHistory != null && noHistory) {
			window.location.replace('#' + newHash);
		} else {
			location.hash = newHash;
		}
	}
}

function addViews(pJson) {
	pJson = window.jui.tools.parseJSON(pJson);
	
	for(var i = 0; i < pJson.length; i++){
        var el = gui.insert(pJson[i]);
        
        if(el != null) {
        	if(pJson[i]['id'] != null) {
        		el.attr("id", "jui_" + pJson[i]['id']);
        	}
        	
			el.appendTo(gui.getRoot());
		}
	}
}

function toggleView(pId) {
	if(document.querySelector('#jui_' + pId).style.display == 'none') {
		document.querySelector('#jui_' + pId).style.display = 'block';
	} else {
		document.querySelector('#jui_' + pId).style.display = 'none';
	}
}

function openMedia(pType, pUrl) {
	console.log(pType);

	if(pType == "music") {
		openMusic(pUrl);
	} else if(pType == "video") {
		openVideo(pUrl);
	} else if(pType == "file") {
		downloadFile(pUrl);
	} else if(pType == "image") {
		openImage(pUrl);
	} else {
		downloadFile(pUrl);
	}
}

function openImage(pUrl) {

	var imageBox = document.createElement('div');
	imageBox.className = 'image-box';

		var image = document.createElement('img');
		image.src = 'api/file.php?file='+encodeURIComponent(pUrl)+'&jwt=' + encodeURIComponent(window.token);
		image.style.maxWidth = '100%';
		image.style.maxHeight = '100%';

	imageBox.appendChild(image);
	
	overlay.setOverlayContent(imageBox);
	overlay.show();
}

function downloadFile(pUrl) {
	if(downloadElement == null) {
		downloadElement = document.createElement('iframe');
		downloadElement.id = 'download';
		downloadElement.style.display = 'none';
		_body.appendChild(downloadElement);
	}
	
	downloadElement.setAttribute('src', 'api/file.php?file='+encodeURIComponent(pUrl)+'&jwt=' + encodeURIComponent(window.token) );
}

function openMusic(pUrl) {
	if (audioElement == null) {
		audioElement = document.createElement('audio');
		audioElement.id = 'audio';
		_body.appendChild(audioElement);
		
		audioOverlay = document.createElement('div');
		audioOverlay.id = 'music-overlay';
		
		var name = decodeURI(pUrl);
		name = name.replace(/^.*(\\|\/|\:)/, '/');
		name = name.substring(name.lastIndexOf('/')+1, name.lastIndexOf('.'));
		
		audioOverlayNameOuter = document.createElement('div');
		audioOverlayNameOuter.id = 'music-name-outer';
		audioOverlay.appendChild(audioOverlayNameOuter);


		audioOverlayName = document.createElement('div');
		audioOverlayName.id = 'music-name';
		audioOverlayName.innerHTML = name;
		audioOverlayName.class = 'marquee';
		audioOverlayNameOuter.appendChild(audioOverlayName);

		
		audioOverlayTrack = document.createElement('input');
		audioOverlayTrack.type = 'range';
		audioOverlayTrack.id = 'music-track';
		audioOverlayTrack.min = 0;
		audioOverlayTrack.max = 100;
		audioOverlayTrack.value = 0;
		audioOverlay.appendChild(audioOverlayTrack);

		
		audioOverlayTrack.onmouseup = function(event) {
			if(event.which == 1) {
				audioElement.currentTime = audioOverlayTrack.value;
				audioElement.ontimeupdate = musicTimeUpdate;
			}
		};
		
		audioOverlayTrack.onmousedown = function(event) {
			if(event.which == 1)
			audioElement.ontimeupdate = function () {};
		};

		
		audioOverlayTime = document.createElement('div');
		audioOverlayTime.id = 'music-time';
		audioOverlay.appendChild(audioOverlayTime);
		

		audioOverlayPlay = document.createElement('img');
		audioOverlayPlay.id = 'music-play';
		audioOverlayPlay.src = 'images/media-pause.png';
		audioOverlayPlay.addEventListener('click', toggleMusic, false);
		audioOverlay.appendChild(audioOverlayPlay);
		
		
		audioOverlayVolume = document.createElement('input');
		audioOverlayVolume.id = 'music-volume';
		audioOverlayVolume.type = 'range';
		audioOverlayVolume.min = 0;
		audioOverlayVolume.max = 100;
		audioOverlayVolume.value = 50;
		audioOverlay.appendChild(audioOverlayVolume);
		
		audioOverlayVolume.onmousemove = function () {
			audioElement.volume = audioOverlayVolume.value / 100;
		};
		
		audioOverlayVolume.onkeydown = function () {
			audioElement.volume = audioOverlayVolume.value / 100;
		};
		
		audioElement.ondurationchange = function () {
			var duration = audioElement.duration;
			audioOverlayTrack.max = duration;
		};
		
		audioElement.ontimeupdate = musicTimeUpdate;
		
		audioElement.onended = function () {
			//audioOverlay.style.display = 'none';

			audioElement.parentNode.removeChild(audioElement);
			audioOverlay.parentNode.removeChild(audioOverlay);

			audioElement = null;
			audioOverlay = null;
			audioOverlayTrack = null;
			audioOverlayPlay = null;
			audioOverlayVolume = null;
			
			if(!isMobile) {
				_body.style.marginBottom = '0px';
			} else {
				_body.style.marginBottom = '80px';
			}
		};
		
		audioOverlay.style.display = 'block';
		
		_body.appendChild(audioOverlay);
	}
	
	var name = pUrl.replace(/^.*(\\|\/|\:)/, '/');
		name = name.substring(name.lastIndexOf('/')+1, name.lastIndexOf('.'));
	
	document.querySelector('#music-name').innerHTML = name;
	
	audioElement.src = 'api/file.php?file='+encodeURIComponent(pUrl)+'&jwt=' + encodeURIComponent(window.token);
	audioElement.play();
	audioElement.volume = 0.5;
	
	resizeAudio();
}

function resizeAudio() {
	if (audioElement != null && !audioElement.ended) {
		var textWidth = window.jui.tools.getTextWidth(audioOverlayName, undefined, undefined, '28px', 'bold');

		if(textWidth > audioOverlayNameOuter.getBoundingClientRect().width) {
			

			audioOverlayName.classList.add('marquee');
			audioOverlayName.style.marginLeft = '-25%';
			audioOverlayName.style.textAlign = 'left';
			
			audioOverlayName.style.width = textWidth + 'px';
		} else {
			audioOverlayName.classList.remove('marquee');
			audioOverlayName.style.marginLeft = '0';
			audioOverlayName.style.textAlign = 'center';
			
			audioOverlayName.style.width = audioOverlayNameOuter.getBoundingClientRect().width + 'px';
		}
		
		if(!isMobile) {
			_body.style.marginBottom = '50px';
		} else {
			_body.style.marginBottom = '130px';
		}
	} else {
		if(!isMobile) {
			_body.style.marginBottom = '0px';
		} else {
			_body.style.marginBottom = '80px';
		}
	}
}

function musicTimeUpdate() {
	var currentTime = audioElement.currentTime;
	var duration    = audioElement.duration;
	audioOverlayTrack.value = currentTime;
	
	var currentSecs = (currentTime+'').split(".")[0];
	var currentMins = (currentSecs/60 + '').split(".")[0];
	currentSecs = currentSecs - currentMins*60;
	
	if(currentSecs < 10) {
		var currentTime = currentMins + ':0' + currentSecs;
	} else  {
		var currentTime = currentMins + ':' + currentSecs;
	}
	
	
	var secs = (duration+'').split(".")[0];
	var mins = (secs/60 + '').split(".")[0];
	secs = secs - mins*60;
	
	if(secs < 10) {
		var time = mins + ':0' + secs;
	} else  {
		var time = mins + ':' + secs;
	}
	
	audioOverlayTime.innerHTML = currentTime+'/'+time;
}

function openUrl(pUrl) {
	window.open(pUrl,'_blank');
}

function toggleMusic() {
	if(audioElement.paused) {
		audioElement.play();
		audioOverlayPlay.src = 'images/media-pause.png';
	} else {
		audioElement.pause();
		audioOverlayPlay.src = 'images/media-play.png';
	}
}

function openVideo(pUrl) {
	if (videoElement == null) {
		videoElement = document.createElement('video');
		videoElement.id = 'video';
		videoElement.controls = true;
	}
	
	overlay.setOverlayContent(videoElement);
	overlay.show();
	
	videoElement.src = 'api/file.php?file='+encodeURIComponent(pUrl)+'&jwt=' + encodeURIComponent(window.token);
	videoElement.play();
}

function Overlay() { // Class to manage the overlay
	var overlayElement;
	var overlayElementInner;
	Overlay();
	
	function Overlay() {
		if (overlayElement == null && document.querySelector('#overlay') == null) {
			overlayElement = document.createElement('div');
				overlayElement.id = 'overlay';
				overlayElement.addEventListener('click', hide, false);
			document.querySelector('body').appendChild(overlayElement);
			

			overlayElementInner = document.createElement('div');
				overlayElementInner.id = 'overlay-inner';
				overlayElementInner.addEventListener('click', function(e) {
					e.stopPropagation();
				}, false);
			overlayElement.appendChild(overlayElementInner);
			
			hide();
		} else {
			overlayElement = document.querySelector('#overlay');
			overlayElementInner = document.querySelector('#overlay-inner');
		}
	}
	
	this.setOverlayContent = function (pElement) {
		overlayElementInner.innerHTML = '';
		overlayElementInner.appendChild(pElement);
	};
	
	this.show = show;
	function show () {
		overlayElement.style.display = 'block';
	};
	
	this.hide = hide;
	function hide() {
		overlayElement.style.display = 'none';
		overlayElementInner.innerHTML = '';
	}
}

function loadPlugin(pName, pPage, pCommand) {
	if(pPage == null) {
		pPage = '';
	}
	
	if(pCommand == null) {
		pCommand = '';
	}
	
	urlString = 'api/plugin.php?plugin=' + pName + '&page=' + pPage + '&cmd=' + pCommand;
	if (getStorage('share') != null && getStorage('share') != "") {
		urlString += '&share=' + getStorage('share');
	}
	
	if(window.jui.tools.inArray( pName, shareableId )) {
		showShareButton(pName, pPage, pCommand);
	} else {
		hideShareButton();
	}
	
	if(pName != 'share') {
		showLoadingData();

		if(gui == null) {
			gui = new GuiPage();
		}


		gui.requestParse(urlString, function(data, status) {
			var obj = JSON.parse(data);

			if(!window.jui.tools.empty(obj.head) && !window.jui.tools.empty(obj.head.status) && obj.head.status == 401) {
				cleanLogin();
				openLogin();
				return;
			}
			/*modUrl = urlString;

			hideLoadingScreen();
			hideLoadingData();

			var flyover = $('#flyover');
			flyover.fadeOut();
			$('#overlay-invisible').css('display', 'none');*/
		});

		/*
		$.ajax({
			url: urlString,
			success: function(result){
				modUrl = urlString;
				parseResponse(result);
	    	}
	    });*/
	}
}

function beforeParseListener(result, parentElement) {
	if(!window.jui.tools.empty(parentElement)) {
		return true;
	}

	flyover = document.querySelector('#flyover');
	hideLoadingData();
			
	if(result == null || result == "") {
		result = JSON.parse('[{"type":"heading","value":"Keine Antwort vom Server erhalten."}]');
	}


	obj = result;
	
	if(obj.status != null && obj.status == "needrelogin") { // Handles missing $_SESSION on server-side
		location.reload(true);
	}
	
	if(obj.redirect != null) { // handles redirect requests
		var red1 = obj.redirect[0];
		var red2 = obj.redirect[1];
		var red3 = obj.redirect[2];
		
		openPlugin(red1, red2, red3, true);
		hideLoadingScreen();
		
		return false;
	}
	
	if(obj.type != null && obj.type == "flyover") { // handles the flyover element
		flyover.innerHTML = '';
		window.jui.parse(obj.value, flyover, true);
		
		overlayInvisible.style.display = 'block';
		overlayInvisible.onclick = function() {
			flyover.style.display = 'none';
			overlayInvisible.style.display = 'none';
		};
		
		flyover.style.display = 'block';
		
		hideLoadingScreen();
		return false;
	}
	
	if(obj.action != null) { // handles logout request from the server
		if(obj.action == 'logout') {
			cleanLogin();
			document.location.href = '';
		}

		return false;
	}
	
	flyover.style.display = 'none';
	overlayInvisible.style.display = 'none';
	
	hideLoadingScreen();
	hideLoadingData();

	return true;
}

function hashChanged(pHash) {
	var hash = pHash.split('/');
	
	var pluginId = 'Home';
	
	if(pHash == '') {
		openHome();
		
		hideLoadingScreen();
	} else if(hash.length < 3) {
		currentApp = hash[0];
		loadPlugin(hash[0], hash[1]);
		pluginId = hash[0];
	} else {
		var plugin = hash[0];
		var view = hash[1];
		
		hash.splice(0,2);
		
		var urlParam = hash.join('/');
		
		loadPlugin(plugin, view, urlParam);
		pluginId = plugin;
	}
	
	if(pluginId != null) {
		var title = '';
		
		if(plugins[pluginId] != null && plugins[pluginId]['name'] != null) {
			title = plugins[pluginId]['name'];
		} else {
			title = pluginId;
		}
		
		document.title = "Vision - " + title;
	}
}

function openHome() {
	var gui = new GuiPage();
	
	var string = '[';
	string += '{"type":"heading","value":"Guten Tag ' + getStorage('username') + '"},';
	string += '{"type":"buttonlist","value":[';
	for (var id in plugins) {
		var plugin = plugins[id];
		
		if(plugin['visible'] == null || plugin['visible']) {
			continue;
		}
		
		string += '{"value":["' + plugin['icon'] + '","' + plugin['name'] + '"], "click":"openPlugin(\'' + id + '\',\'\',\'\')"},';
	}
	string = string.substr(0, string.length-1);
	string += ']}]';
	
	gui.clean();
	gui.parse(window.jui.tools.parseJuiJSON(string));
}

/* Tools */

function openSearch() {
	if(!isShared) {
		searchOverlay = document.querySelector('#search-overlay');
		searchOverlay.style.display = 'block';

		searchBox.value = '';
		searchBox.focus();
		blur();
	}
}

function blur() {
	document.querySelector('#content').classList.add('blur');
	document.querySelector('#loader').classList.add('blur');
	flyover.classList.add('blur');
}

function closeSearch() {
	searchOverlay.style.display = 'none';
	unBlur();
}

function unBlur() {
	document.querySelector('#content').classList.remove('blur');
	document.querySelector('#loader').classList.remove('blur');
	flyover.classList.remove('blur');
}

function search() {
	document.querySelector('#search-results').innerHTML = '';
	
	if(lastSearch != '') {
		window.jui.tools.requestSite("api/search.php?query=" + encodeURIComponent(lastSearch), null, null, function( data, status ) {
			if(data != '') {
				var obj = window.jui.tools.parseJSON( data );
				
				for(var i = 0; i < obj.length; i++) {
					addSearchResult(obj[i]['title'], obj[i]['icon'], obj[i]['click']);
				}
			}
		});
	}
}

function addSearchResult(pTitle, pIcon, pFunction) {
	var el = document.createElement('div');
		el.className = 'search-result';
		el.addEventListener('click', function() {
			new Function(pFunction).call();
			closeSearch();
		}, false);
	

	var icon = document.createElement('img');
		icon.src = pIcon;
		icon.className = 'search-result-icon';
	el.appendChild(icon);


	var title = document.createElement('div');
		title.className = 'search-result-title';
		title.innerHTML = pTitle;
	el.appendChild(title);
	

	var clear = document.createElement('div')
		clear.style.clear = 'both';
	el.appendChild(clear);
	
	document.querySelector('#search-results').appendChild(el);
}

function getCommand() {
	var hash = getHash();
	hash = hash.split('/');
				
	if(hash.length >= 3) {
		hash.splice(0,2);
		
		var urlParam = hash.join('/');
		
		if(isLast(urlParam, '/')) {
			urlParam = removeLast(urlParam);
		}
		
		return urlParam;
	} else {
		return "";
	}
}

function getPlugin() {
	var hash = getHash();
	hash = hash.split('/');
				
	if(hash.length >= 1) {
		hash.splice(1,hash.length-1);
		
		var urlParam = hash.join('/');
		
		if(isLast(urlParam, '/')) {
			urlParam = removeLast(urlParam);
		}
		
		return urlParam;
	} else {
		return "";
	}
}

function showShareButton(pName, pView, pParameter, handle) {
	if(handle == null) {
		handle = true;
	}

	if(handle) {
		document.querySelector('#share-button').onclick = function() {
			var urlStr = "ajax.php?action=share&plugin=" + pName + "&page=" + pView + "&cmd=" + pParameter;
			
			window.jui.tools.requestSite(urlStr, null, null, function( data, status ) {
				parseShareData(data);
			});
		};
	} else {
		document.querySelector('#share-button').onclick = function () {
			openPlugin(pName, pView, pParameter);
			hideShareButton();
		};
	}

	document.querySelector('#share-button').style.display = 'block';
	
	document.querySelector('#content').style.marginBottom = '68px';
}

function hideShareButton() {
	document.querySelector('#share-button').style.display = 'none';

	document.querySelector('#content').style.marginBottom = '8px';
}

function parseShareData(data) {
	if(data != null && data != '')
		var json = window.jui.tools.parseJSON(data);
	
	if(json != null && json.url != null && json.url != '') {
		var root = document.createElement('div');
		root.style.width = '400px';
		root.style.height = '300px';
		root.className = 'overlay-white';

		var headline = document.createElement('h1');
		headline.innerHTML = 'Über Link freigeben';
		root.appendChild(headline);

		var input = document.createElement('input');
		input.value = json.url;
		input.style.width = '100%';
		input.style.textAlign = 'center';
		input.addEventListener('click', function() {
			this.select();
		}, false);
		root.appendChild(input);

		var warning = document.createElement('div');
		warning.innerHTML = 'Dieser Link ist ab sofort gültig. Sollten sie ihn doch nicht gebrauchen, so empfehlen wir ihnen diesen zu löschen.';
		warning.style.color = '#FF0000';
		root.appendChild(warning);
		
		overlay.setOverlayContent(root);
		overlay.show();
	}
}

/* Tools */
function isLast(pString, pChar) {
	if(pString.substring(pString.length - 1) == pChar) {
		return true;
	}
	
	return false;
}

function removeLast(pString) {
	return pString.substring(0, pString.length - 1);
}