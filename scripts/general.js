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

$(document).ready(function () {
	proofMobile();
	
	$('body').on('dragstart', function () {
		return false;
	});
	
	window.setTimeout(function() {
		$('#info-box').slideUp();
	}, 5000);
	
	if(!isShared) {
		$('#search-overlay').click(function() {
			closeSearch();
		});
		
		$('#search-overlay-box').click(function(e) {
			e.stopPropagation();
		});
		
		$('#search-box').keyup(function () {
			searchString = $('#search-box').val();
			if(searchString != lastSearch) {
				lastSearch = searchString;
				clearTimeout(searchTimeout);
				searchTimeout = setTimeout(search, 1000);
			}
		});
		
		$("html").on("dragover", function(event) {
			event.preventDefault();
			event.stopPropagation();
			dragOverBox = false;
			$(this).addClass('dragging');
			blur();
		});
		
		$("#uploadzone").on("dragover", function(event) {
			event.preventDefault();
			event.stopPropagation();
			dragOverBox = false;
			clearTimeout(dragTimeout);
			$("html").addClass('dragging');
		});
		
		$("#uploadzone").on("dragleave", function(event) {
			event.preventDefault();
			event.stopPropagation();
			
			if(!dragOverBox)
				dragTimeout = setTimeout(function () { $("html").removeClass('dragging'); unBlur(); }, 100);
		});
		
		$("#uploadzone-box").on("dragover", function(event) { // only is there to prevent display none when drag over box
			event.preventDefault();
			event.stopPropagation();
			
			dragOverBox = true;
			
			clearTimeout(dragTimeout);
			$("html").addClass('dragging');
		});
		
		$("#uploadzone-box").on("dragleave", function(event) { // only is there to prevent display none when drag over box
			event.preventDefault();
			event.stopPropagation();
			
			dragOverBox = false;
			
			clearTimeout(dragTimeout);
			$("html").addClass('dragging');
		});
		
		$('#uploadzone').get( 0 ).addEventListener('drop', handleDropEvent, false);
		
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
    
    $("html").removeClass('dragging');
    
    return false;
}

function openMimeSelect(mime) {
	var mimeType = getMimeFromExtension(mime);
	var root = $('#mime-select');
	root.html('');
	
	if(mimeTypes[mimeType] != null) {
		var array = [];
		var arrayClick = [];
		
		for(var i = 0; i < mimeTypes[mimeType].length; i++){
        	var pluginId = mimeTypes[mimeType][i];
        	
        	
        	array[array.length] = plugins[pluginId]['name'];
        	arrayClick[arrayClick.length] = "openPlugin('webinterface', 'share', '" + pluginId + "')";
        }
        
        var listView = [];
        listView['type'] = 'list';
        listView['value'] = array;
        listView['click'] = arrayClick;
        
        var el = gui.insert(listView);
        if(el != null) {
			el.appendTo(root);
		}
	}
	
	$("#mime-select").css('display', 'block');
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

		$('#menu').html('');
		
		var usernameText = getStorage('username');
			
		var link = jQuery('<a/>', {
			text: usernameText
		});
		
		jQuery('<li/>', {
			id: 'menu-username',
			class: 'menu-tile no-icon'
		}).append(link).on("click", new Function('openPlugin(\'plg_user\',\'\',\'\')')).appendTo('#menu');
		
		var mainPlugins = getStorage("mainplugins");
		
		for(var i = 0; i < obj.length; i++) {
			var id   = obj[i]['id'];
			var name = obj[i]['name'];
			
			var icon = obj[i]['icon'];
			var iconColor = obj[i]['icon-color'];
			
			var visible = obj[i]['visible'];
			var mimes = obj[i]['mime'];
			var shareable = obj[i]['shareable'];
			
			var img1 = jQuery('<img/>', {
				class: 'menu-icon',
				src: icon
			});
			
			var img2 = jQuery('<img/>', {
				class: 'menu-icon-color',
				src: iconColor
			});
			
			var link = jQuery('<a/>', {
				text: name
			});
			
			if((visible == null || visible != 'no') && ((mainPlugins != null && mainPlugins.indexOf(id) !== -1) || id == 'plg_order')) {
				var li = jQuery('<li/>', {
					class: 'menu-tile'
				}).append(img1).append(img2).append(link).on("click", new Function('openPlugin(\'' + id + '\')')).appendTo('#menu');
				
				if(icon == null && iconColor == null) {
					li.addClass('no-icon');
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
	var heading = jQuery('<div/>', {
		text: "Lade Daten vom Server"
	});
	
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
	    $("#mime-select").css('display', 'none');
	    
	    $.ajax({
			url: "ajax.php?plugin=" + pPage + "&page=receiver&get=view",
			data: formData,
			processData: false,
			contentType: false,
			type: 'POST',
			success: function(data) {
				parseResponse(data);
			}
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
	pJson = $.parseJSON(pJson);
	
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
	$('#jui_' + pId).toggle();
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
	}
}

function openImage(pUrl) {
	var heading = jQuery('<img/>', {
		src: 'ajax.php?action=getFile&file='+encodeURIComponent(pUrl)
	}).css('max-width', '100%').css('max-height', '100%');
	
	overlay.setOverlayContent(heading);
	overlay.show();
}

function downloadFile(pUrl) {
	if(downloadElement == null) {
		downloadElement = jQuery('<iframe/>', {
		    id: 'download'
		}).css('display', 'none').appendTo("body");
	}
	
	downloadElement.prop('src', 'ajax.php?action=getFile&file='+encodeURIComponent(pUrl) );
}

function openMusic(pUrl) {
	if (audioElement == null || !audioElement.length) {
		audioElement = jQuery('<audio/>', {
		    id: 'audio'
		}).appendTo("body");
		
		audioOverlay = jQuery('<div/>', {
		    id: 'music-overlay'
		});
		
		var name = decodeURI(pUrl);
		name = name.replace(/^.*(\\|\/|\:)/, '/');
		name = name.substring(name.lastIndexOf('/')+1, name.lastIndexOf('.'));
		
		audioOverlayNameOuter = jQuery('<div/>', {
		    id: 'music-name-outer'
		}).appendTo(audioOverlay);
		
		audioOverlayName = jQuery('<div/>', {
		    id: 'music-name',
		    text: name,
		    class: 'marquee'
		}).appendTo(audioOverlayNameOuter);
		
		audioOverlayTrack = jQuery('<input/>', {
		    type: 'range',
		    id: 'music-track',
		    min: 0,
		    max: 100,
		    value: 0
		}).appendTo(audioOverlay);
		
		audioOverlayTrack.mouseup(function(event) {
			if(event.which == 1) {
				audioElement.get( 0 ).currentTime = audioOverlayTrack.get( 0 ).value;
				audioElement.get( 0 ).ontimeupdate = musicTimeUpdate;
			}
		});
		
		audioOverlayTrack.mousedown(function(event) {
			if(event.which == 1)
			audioElement.get( 0 ).ontimeupdate = function () {};
		});
		
		audioOverlayTime = jQuery('<div/>', {
		    id: 'music-time'
		}).appendTo(audioOverlay);
		
		
		audioOverlayPlay = jQuery('<img/>', {
		    id: 'music-play',
		    src: 'images/media-pause.png'
		}).click(function () { toggleMusic(); }).appendTo(audioOverlay);
		
		
		audioOverlayVolume = jQuery('<input/>', {
			id: 'music-volume',
		    type: 'range',
		    min: 0,
		    max: 100,
		    value: 50
		}).appendTo(audioOverlay);
		
		audioOverlayVolume.get( 0 ).onmousemove = function () {
			audioElement.get( 0 ).volume = audioOverlayVolume.get( 0 ).value / 100;
		};
		
		audioOverlayVolume.get( 0 ).onkeydown = function () {
			audioElement.get( 0 ).volume = audioOverlayVolume.get( 0 ).value / 100;
		};
		
		audioElement.get( 0 ).ondurationchange = function () {
			var duration = audioElement.get( 0 ).duration;
			audioOverlayTrack.get( 0 ).max = duration;
		};
		
		audioElement.get( 0 ).ontimeupdate = musicTimeUpdate;
		
		audioElement.get( 0 ).onended = function () {
			audioOverlay.slideUp( "slow", function() {
				audioElement.remove();
				audioOverlay.remove();
				
				audioElement = null;
				audioOverlay = null;
				audioOverlayTrack = null;
				audioOverlayPlay = null;
				audioOverlayVolume = null;
			});
			
			if(!isMobile) {
				$('body').css('margin-bottom','0px');
			} else {
				$('body').css('margin-bottom','80px');
			}
		};
		
		audioOverlay.css('display','none');
		
		audioOverlay.appendTo("body");
		audioOverlay.slideDown( "slow", function() {
			
		});
	}
	
	var name = pUrl.replace(/^.*(\\|\/|\:)/, '/');
		name = name.substring(name.lastIndexOf('/')+1, name.lastIndexOf('.'));
	
	$('#music-name').html(name);
	
	audioElement.get( 0 ).src = 'ajax.php?action=getFile&file='+encodeURIComponent(pUrl);
	audioElement.get( 0 ).play();
	audioElement.get( 0 ).volume = 0.5;
	
	resizeAudio();
}

function resizeAudio() {
	if (audioElement != null && audioElement.length && !audioElement.get(0).ended) {
		var textWidth = audioOverlayName.textWidth();
		
		if(textWidth > audioOverlayNameOuter.innerWidth()) {
			audioOverlayName.addClass('marquee');
			audioOverlayName.css('margin-left', '-25%');
			audioOverlayName.css('text-align', 'left');
			
			audioOverlayName.width( textWidth );
		} else {
			audioOverlayName.removeClass('marquee');
			audioOverlayName.css('margin-left', '0');
			audioOverlayName.css('text-align', 'center');
			
			audioOverlayName.width( audioOverlayNameOuter.innerWidth() );
		}
		
		if(!isMobile) {
			$('body').css('margin-bottom','50px');
		} else {
			$('body').css('margin-bottom','130px');
		}
	} else {
		if(!isMobile) {
			$('body').css('margin-bottom','0px');
		} else {
			$('body').css('margin-bottom','80px');
		}
	}
}

function musicTimeUpdate() {
	var currentTime = audioElement.get( 0 ).currentTime;
	var duration    = audioElement.get( 0 ).duration;
	audioOverlayTrack.get( 0 ).value = currentTime;
	
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
	
	audioOverlayTime.html(currentTime+'/'+time);
}

function openUrl(pUrl) {
	window.open(pUrl,'_blank');
}

function toggleMusic() {
	if(audioElement.get( 0 ).paused) {
		audioElement.get( 0 ).play();
		audioOverlayPlay.attr('src','images/media-pause.png');
	} else {
		audioElement.get( 0 ).pause();
		audioOverlayPlay.attr('src','images/media-play.png');
	}
}

function openVideo(pUrl) {
	if (videoElement == null || !videoElement.length) {
		videoElement = jQuery('<video/>', {
		    id: 'video'
		}).attr('controls',true);
	}
	
	overlay.setOverlayContent(videoElement);
	overlay.show();
	
	videoElement.get( 0 ).src = 'ajax.php?action=getFile&file='+encodeURIComponent(pUrl);
	videoElement.get( 0 ).play();
}

function Overlay() { // Class to manage the overlay
	var overlayElement;
	var overlayElementInner;
	Overlay();
	
	function Overlay() {
		if ((overlayElement == null || !overlayElement.length) && !$('#overlay').length) {
			overlayElement = jQuery('<div/>', {
			    id: 'overlay'
			}).appendTo("body").click(function () {
				hide();
			});
			
			overlayElementInner = jQuery('<div/>', {
			    id: 'overlay-inner'
			}).appendTo(overlayElement).click(function (e) {
				e.stopPropagation();
			});
			
			hide();
		} else {
			overlayElement = $('#overlay');
			overlayElementInner = $('#overlay-inner');
		}
	}
	
	this.setOverlayContent = function (pElement) {
		overlayElementInner.html('');
		overlayElementInner.append(pElement);
	};
	
	this.show = show;
	function show () {
		overlayElement.css('display', 'block');
	};
	
	this.hide = hide;
	function hide() {
		overlayElement.css('display', 'none');
		overlayElementInner.html('');
	}
}

function loadPlugin(pName, pPage, pCommand) {
	if(pPage == null) {
		pPage = '';
	}
	
	if(pCommand == null) {
		pCommand = '';
	}
	
	urlString = 'api/plugin.php?plugin=' + pName + '&page=' + pPage + '&cmd=' + pCommand + '&get=view';
	if (getStorage('share') != null && getStorage('share') != "") {
		urlString += '&share=' + getStorage('share');
	}
	
	if(!jQuery.inArray( pName, shareableId )) {
		showShareButton(pName, pPage, pCommand);
	} else {
		hideShareButton();
	}
	
	if(pName != 'share') {
		showLoadingData();

		if(gui == null) {
			gui = new GuiPage();
		}

		console.log('gui', gui);

		gui.requestParse(urlString, function(data, status) {
			var obj = JSON.parse(data);

			console.log(data);

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

	var flyover = $('#flyover');
	hideLoadingData();
			
	if(result == null || result == "") {
		result = JSON.parse('[{"type":"heading","value":"Keine Antwort vom Server erhalten."}]');
	}
	
	/*try {
		var obj = $.parseJSON(result);
	} catch(e) {
		var obj = $.parseJSON('[{"type":"heading","value":"Die Antwort des Servers war fehlerhaft:"},{"type":"text","value":' + JSON.stringify(result) + '}]');
	}*/

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
		flyover.html('');
		window.jui.parse(obj.value, flyover.get(0), true);
		
		$('#overlay-invisible').css('display', 'block');
		$('#overlay-invisible').click(function() {
			$('#flyover').fadeOut();
			$('#overlay-invisible').css('display', 'none');
		});
		
		flyover.fadeIn();
		
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
	
	flyover.fadeOut();
	$('#overlay-invisible').css('display', 'none');
	
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
	
	console.log(string);
	
	gui.clean();
	gui.parse($.parseJSON(string));
}

/* Tools */

function openSearch() {
	if(!isShared) {
		$('#search-overlay').fadeIn("slow", function() {});
		$('#search-box').val('');
		$('#search-box').focus();
		blur();
	}
}

function blur() {
	$('#content').addClass('blur');
	$('#loader').addClass('blur');
	$('#flyover').addClass('blur');
}

function closeSearch() {
	$('#search-overlay').fadeOut("slow", function() {});
	unBlur();
}

function unBlur() {
	$('#content').removeClass('blur');
	$('#loader').removeClass('blur');
	$('#flyover').removeClass('blur');
}

function search() {
	$('#search-results').html('');
	
	if(lastSearch != '') {
		$.ajax({ url:"api/search.php?query=" + encodeURIComponent(lastSearch) }).done(function( data ) {
			if(data != '') {
				var obj = $.parseJSON( data );
				
				for(var i = 0; i < obj.length; i++) {
					addSearchResult(obj[i]['title'], obj[i]['icon'], obj[i]['click']);
				}
			}
		});
	}
}

function addSearchResult(pTitle, pIcon, pFunction) {
	var el = jQuery('<div/>', {
		class: 'search-result'
	}).click(function() { new Function(pFunction).call(); closeSearch(); });
	
	var icon = jQuery('<img/>', {
		src: pIcon,
		class: 'search-result-icon'
	}).appendTo(el);
	
	var title = jQuery('<div/>', {
		text: pTitle,
		class: 'search-result-title'
	}).appendTo( el );
	
	var clear = jQuery('<div/>', {
		style:'clear:both;'
	}).appendTo( el );
	
	el.appendTo( $('#search-results') );
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
		$('#share-button').fadeIn().unbind().click(function () {
			var urlStr = "ajax.php?action=share&plugin=" + pName + "&page=" + pView + "&cmd=" + pParameter;
			
			$.ajax({ url: urlStr }).done(function( data ) {
				parseShareData(data);
			});
		});
	} else {
		$('#share-button').fadeIn().unbind().click(function () {
			openPlugin(pName, pView, pParameter);
			hideShareButton();
		});
	}
	
	$('#content').css('margin-bottom', '68px');
}

function hideShareButton() {
	$('#share-button').fadeOut();
	$('#content').css('margin-bottom', '8px');
}

function parseShareData(data) {
	if(data != null && data != '')
		var json = $.parseJSON(data);
	
	if(json != null && json.url != null && json.url != '') {
		$('#share-dialog-input').val(json.url);
		
		var root = jQuery('<div/>', {
			width: '400px',
			height: '300px',
			class: 'overlay-white'
		});
		
		jQuery('<h1/>', {
			text: 'Über Link freigeben'
		}).appendTo(root);
		
		jQuery('<input/>', {
			value: json.url,
			width: '100%'
		}).click(function () {
			$(this).select();
		}).css('text-align', 'center').appendTo(root);
		
		jQuery('<div />', {
			text: 'Dieser Link ist ab sofort gültig. Sollten sie ihn doch nicht gebrauchen, so empfehlen wir ihnen diesen zu löschen.'
		}).css('color', '#FF0000').appendTo(root);
		
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

/* Thanks to http://jsfiddle.net/philfreo/MqM76/ */
$.fn.textWidth = function(text, font) {
	if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
	$.fn.textWidth.fakeEl.text(text || this.val() || this.text()).css('font', font || this.css('font'));
	
	return $.fn.textWidth.fakeEl.width();
};