var modUrl = '';
var audioElement = null;
var audioOverlay = null, audioOverlayPlay = null, audioOverlayTrack = null, audioOverlayVolume = null, audioOverlayTime = null;
var downloadElement = null;
var videoElement = null;
var overlay = new Overlay();

var sep1 = '%!#|params|#!%';
var lastSearch = '';
var searchTimeout;

window.onhashchange = function () {
	hashChanged(getHash());
};

$(document).ready(function () {
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
});

document.addEventListener("keydown", function(e) {
  if ((e.keyCode == 83 || e.keyCode == 70) && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
    e.preventDefault();
    openSearch();
  }
}, false);

function loadMenu() {
	$.ajax({
		url: 'ajax.php?show=plugins',
		success: function(result){
        	var obj = $.parseJSON(result);
        	$('#menu').html('');
        	
        	var usernameText = getStorage('username');
				
			var link = jQuery('<a/>', {
				text: usernameText
			});
			
			jQuery('<div/>', {
				id: 'menu-username',
				class: 'menu-tile'
			}).append(link).on("click", new Function('openPlugin(\'plg_user\',\'\',\'\')')).appendTo('#menu');
        	
        	for(var i = 0; i < obj.length; i++) {
        		var id   = obj[i]['id'];
        		var name = obj[i]['name'];
        		
        		var icon = obj[i]['icon'];
        		var iconColor = obj[i]['icon-color'];
        		
        		var visible = obj[i]['visible'];
        		
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
        		
        		if(visible == null || visible != 'no') {
					jQuery('<div/>', {
						class: 'menu-tile'
					}).append(img1).append(img2).append(link).on("click", new Function('openPlugin(\'' + id + '\')')).appendTo('#menu');
				}
			}
			
			var hash = getHash();
			if(hash != '') {
				hashChanged(hash, true);
			} else {
				hideLoadingScreen();
			}
    	}
    });
}

function hideLoadingScreen() {
	if(document.getElementById('loader').className != null && document.getElementById('loader').className != 'loaded') {
		document.getElementById('loader').className = 'loaded';
	}
}

function openPlugin(pName, pView, pPage) {
	if((pView == null || pView == '') && (pPage == null || pPage == '')) {
		var newHash = pName;
	} else if(pView != null && pPage == null) {
		var newHash = pName+'/'+pView;
	} else if(pView != null && pPage != null) {
		var newHash = pName+'/'+pView+'/'+pPage;
	}
	
	
	if(newHash == getHash()) {
		hashChanged(newHash);
	} else {
		location.hash = newHash;
	}
}

function openMedia(pType, pUrl) {
	if(pType == "music") {
		openMusic(pUrl);
	} else if(pType == "video") {
		openVideo(pUrl);
	} else if(pType == "file") {
		downloadFile(pUrl);
	}
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
		
		audioOverlayTrack = jQuery('<div/>', {
		    id: 'music-name',
		    text: pUrl
		}).appendTo(audioOverlay);
		
		audioOverlayTrack = jQuery('<input/>', {
		    type: 'range',
		    id: 'music-track',
		    min: 0,
		    max: 100,
		    value: 0,
		    width: 500
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
		
		$('body').css('margin-bottom','50px');
		
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
			
			$('body').css('margin-bottom','0px');
		};
		
		audioOverlay.css('display','none');
		
		audioOverlay.appendTo("body");
		audioOverlay.slideDown( "slow", function() {
			
		});
	}
	
	audioElement.get( 0 ).src = 'ajax.php?action=getFile&file='+encodeURIComponent(pUrl);
	audioElement.get( 0 ).play();
	audioElement.get( 0 ).volume = 0.5;
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
	
	$.ajax({
		url: 'ajax.php?plugin=' + pName + '&page=' + pPage + '&cmd=' + pCommand + '&get=view',
		success: function(result){
			var gui = new GuiPage();
				gui.clean();
			
			if(result == null || result == "") {
				result = '[{"type":"heading","value":"Keine Antwort vom Server erhalten."}]';
			}
			
			try {
        		var obj = $.parseJSON(result);
        	} catch(e) {
				var obj = $.parseJSON('[{"type":"heading","value":"Die Antwort des Servers war fehlerhaft:"},{"type":"text","value":' + JSON.stringify(result) + '}]');
			}
        	
        	modUrl = 'ajax.php?plugin=' + pName + '&page=' + pPage + '&cmd=' + pCommand + '&get=view';
        	
        	if(obj.redirect != null) {
        		var red1 = obj.redirect[0];
        		var red2 = obj.redirect[1];
        		var red3 = obj.redirect[2];
        		
        		openPlugin(red1, red2, red3);
        		return true;
        	}
        	
        	for(var i = 0; i < obj.length; i++){
        		gui.insert(obj[i]);
        	}
        	
        	hideLoadingScreen();
    	}
    });
}

function hashChanged(pHash) {
	var hash = pHash.split('/');
	
	if(hash.length < 3)
	{
		currentApp = hash[0];
		loadPlugin(hash[0], hash[1]);
	} else {
		var plugin = hash[0];
		var view = hash[1];
		
		hash.splice(0,2);
		
		var urlParam = hash.join('/');
		
		loadPlugin(plugin, view, urlParam);
	}
}

/* Tools */

function getHash()
{
	var hash = location.hash; // get the hash
	
	if(hash.indexOf('#') == 0) // when a hash(#) is in front of the real hash
	{
		hash = hash.substr(1, hash.length); // delete the hash(#) in front of the hash
	}
	
	return hash;
}

function openSearch() {
	$('#search-box').val('');
	$('#search-overlay').fadeIn("slow", function() {});
	$('#search-box').focus();
	$('#content').addClass('blur');
	$('#loader').addClass('blur');
}

function closeSearch() {
	$('#search-overlay').fadeOut("slow", function() {});
	$('#content').removeClass('blur');
	$('#loader').removeClass('blur');
}

function search() {
	$('#search-results').html('');
	
	if(lastSearch != '') {
		$.post("ajax.php?action=search",
		{
			search: lastSearch
		}).done(function( data ) {
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

function isLast(pString, pChar) {
	if(pString.substring(pString.length - 1) == pChar) {
		return true;
	}
	
	return false;
}

function removeLast(pString) {
	return pString.substring(0, pString.length - 1);
}

function GuiPage() {
	GuiPage();
	this.root = null;
	this.padding = 8;
	this.paddingLeft = 16;
	
	function GuiPage() {
		root = $('#content');
	}
	
	this.clean = function () {
		root.html('');
		root.css('padding', this.padding);
		root.css('padding-left', this.paddingLeft);
	};
	
	this.insert = function (pJson) {
		if(pJson['type'] != null) {
			if(pJson['height'] == null) {
				pJson['height'] = 'auto';
			}
			
			if(pJson['width'] == null) {
				pJson['width'] = 'auto';
			}
			
			if(pJson['name'] == null) {
				pJson['name'] = '';
			}
			
			if(pJson['value'] != null && !Array.isArray(pJson['value'])) {
				pJson['value'] = pJson['value'].replace(sep1, getCommand());
			}
			
			if(pJson['type'] == 'text') {
				var el = insertText(pJson);
			} else if(pJson['type'] == 'heading') {
				var el = insertHeading(pJson);
			} else if(pJson['type'] == 'headingSmall') {
				var el = insertHeadingSmall(pJson);
			} else if(pJson['type'] == 'list') {
				var el = insertList(pJson);
			} else if(pJson['type'] == 'input') {
				var el = insertInput(pJson);
			} else if(pJson['type'] == 'textarea') {
				var el = insertTextarea(pJson);
			} else if(pJson['type'] == 'submit') {
				var el = insertSubmit(pJson);
			} else if(pJson['type'] == 'button') {
				var el = insertButton(pJson);
			} else if(pJson['type'] == 'checkbox') {
				var el = insertCheckbox(pJson);
			} else if(pJson['type'] == 'table') {
				var el = insertTable(pJson);
			} else if(pJson['type'] == 'nl') {
				var el = insertNewLine(pJson);
			} else if(pJson['type'] == 'hline') {
				var el = insertHorizontalLine(pJson);
			} else if(pJson['type'] == 'frame') {
				var el = insertFrame(pJson);
			} else if(pJson['type'] == 'file') {
				var el = insertFileInput(pJson);
			} else if(pJson['type'] == 'warning') {
				var el = insertWarning(pJson);
			}
			
			if(pJson['visible'] != null) {
				if(pJson['visible'] == 'hidden') {
					el.css("visibility", "hidden");
				} else if(pJson['visible'] == 'away') {
					el.css("display", "none");
				}
			}
			
			if(el != null) {
				el.appendTo(root);
			}
		}
	};
	
	function insertText(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<div/>', {
				text: pJson['value']
			});
			
			if(pJson['align'] != null) {
				if(pJson['align'] == 'right') {
					el.css('text-align','right');
				} else if(pJson['align'] == 'center') {
					el.css('text-align','center');
				} else {
					el.css('text-align','left');
				}
			}
			
			addParameter(el, pJson);
			
			return el;
			//el.appendTo(root);
		}
		return null;
	}
	
	function insertNewLine(pJson) {
		var nl = jQuery('<br/>', {});
		
		//nl.appendTo(root);
		return nl;
	}
	
	function insertHorizontalLine(pJson) {
		var hl = jQuery('<hr/>', {});
		
		//nl.appendTo(root);
		return hl;
	}
	
	function insertFrame(pJson) {
		if(pJson['src'] != null || pJson['url'] != null) {
			if(pJson['src'] != null) {
				var pSrc = pJson['src'];
			} else {
				var pSrc = pJson['url'];
			}
			
			var el = jQuery('<iframe/>', {
				src:pSrc
			}).css('border','none');
			
			if(pJson['width'] != null && pJson['width'] == "100%" && pJson['height'] != null && pJson['height'] == "100%") {
				root.css('padding', '0');
			}
			
			addParameter(el, pJson);
			return el;
		}
		
		return null;
	}
	
	function insertHeading(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<h2/>', {
				text: pJson['value']
			});
			
			addParameter(el, pJson);
			
			return el;
			//el.appendTo(root);
		}
		return null;
	}
	
	function insertWarning(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<div/>', {
				class: 'warning',
				text: pJson['value']
			});
			
			return el;
			//el.appendTo(root);
		}
		return null;
	}
	
	function insertHeadingSmall(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<h3/>', {
				text: pJson['value']
			});
			
			addParameter(el, pJson);
			
			return el;
			//el.appendTo(root);
		}
		return null;
	}
	
	function insertInput(pJson) {
		if(pJson['value'] != null) {
			if(pJson['value'] == sep1) {
				
				pJson['value'] = getCommand();
			}
			
			var el = jQuery('<input/>', {
				type: 'text',
				value: pJson['value'],
				name: pJson['name']
			});
		} else {
			var el = jQuery('<input/>', {
				type: 'text',
				name: pJson['name']
			});
		}
			
		addParameter(el, pJson);
		
		if(pJson['label'] != null) {
			var lab = jQuery('<label/>', {
				text: pJson['label']
			});
			el.appendTo(lab);
			
			return lab;
			//lab.appendTo(root);
		} else {
			return el;
			//el.appendTo(root);
		}
		
		return null;
	}
	
	function insertCheckbox(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<input/>', {
				type: 'checkbox',
				name: pJson['name']
			});
		} else {
			var el = jQuery('<input/>', {
				type: 'checkbox',
				name: pJson['name']
			});
		}
		
		if(pJson['checked'] != null && pJson['checked'] == "checked") {
			el.prop('checked', true);
		}
		
		addParameter(el, pJson);
		
		if(pJson['label'] != null) {
			var lab = jQuery('<label/>', {});
			
			var labText = jQuery('<p/>', {
				text: pJson['label']
			}).css('display','inline');
			
			
			el.appendTo(lab);
			labText.appendTo(lab);
			
			return lab;
		} else {
			return el;
		}
		
		return null;
	}
	
	function insertFileInput(pJson) {
		if(pJson['value'] != null) {		
			var el = jQuery('<input/>', {
				value: pJson['value'],
				type: 'file',
				name: pJson['name']
			});
		} else {
			var el = jQuery('<input/>', {
				name: pJson['name'],
				type: 'file'
			});
		}
			
		addParameter(el, pJson);
		
		if(pJson['label'] != null) {
			var lab = jQuery('<label/>', {
				text: pJson['label']
			});
			el.appendTo(lab);
			
			return lab;
			//lab.appendTo(root);
		} else {
			return el;
			//el.appendTo(root);
		}
		
		return null;
	}
	
	function insertTextarea(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<textarea/>', {
				text: pJson['value'],
				name: pJson['name']
			});
		} else {
			var el = jQuery('<textarea/>', {
				name: pJson['name']
			});
		}
		
		addParameter(el, pJson);
		
		if(pJson['label'] != null) {
			var lab = jQuery('<label/>', {
				text: pJson['label']
			});
			
			jQuery('<br/>', {}).appendTo(lab);
			el.appendTo(lab);
			
			return lab;
			//lab.appendTo(root);
		} else {
			return el;
			//el.appendTo(root);
		}
		
		return null;
	}
	
	function insertSubmit(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<input/>', {
				value: pJson['value'],
				type:"button"
			}).click(function () {
				var formData = serialize(root);
				
				$.ajax({
					url: modUrl,
					data: formData,
					processData: false,
					contentType: false,
					type: 'POST',
					success: function(data) {
						var response = jQuery.parseJSON(data);
						
						if(response.redirect != null) {
			        		var red1 = response.redirect[0];
			        		var red2 = response.redirect[1];
			        		var red3 = response.redirect[2];
			        		
			        		openPlugin(red1, red2, red3);
			        		return true;
			        	}
						
						if(response.code == "success") {
							alert("Success!");
						} else if(response.code == "failure") {
							alert(response.err);
						}
					}
				});
				
			});
			
			addParameter(el, pJson);
			
			return el;
			//el.appendTo(root);
		}
		return null;
	}
	
	function insertButton(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<input/>', {
				value: pJson['value'],
				type:"button"
			});
			
			if(pJson['click'] != null) {
				pJson['click'] = pJson['click'].replace(sep1, getCommand());
				el.click(new Function(pJson['click']));
			}
			
			addParameter(el, pJson);
			
			return el;
			//el.appendTo(root);
		}
		return null;
	}
	
	function insertList(pJson) {
		if(pJson['value'] != null) {
			var ul = jQuery('<ul/>', {
				class:'list'
			});
			
			for(var i = 0; i < pJson['value'].length; i++) {
				var li = jQuery('<li/>', {
					text: pJson['value'][i]
				});
				
				if(pJson['click'] != null && pJson['click'][i] != null && pJson['click'][i] != "") {
					li.css('cursor','pointer');
					li.click(new Function(pJson['click'][i]));
				} else {
					li.css('cursor','default');
				}
				
				if(pJson['longclick'] != null && pJson['longclick'][i] != null && pJson['longclick'][i] != "") {
					var __this = i;
					li.get( 0 ).oncontextmenu = new Function(pJson['longclick'][i]);
					li.on('contextmenu', function(e) {
						e.stopPropagation();
						e.preventDefault();
					});
				}
				
				li.appendTo(ul);
			}
			
			addParameter(ul, pJson);
			
			return ul;
			//ul.appendTo(root);
		}
		
		return null;
	}
	
	function insertTable(pJson) {
		if(pJson['rows'] != null && pJson['rows'] != "") {
			var tab = jQuery('<table/>', {});
			
			for(var i = 0; i < pJson['rows'].length; i++) {
				var tr = jQuery('<tr/>', {});
				
				if(pJson['rows'][i] != null && pJson['rows'][i] != "")
				for(var j = 0; j < pJson['rows'][i].length; j++) {
					
					if(pJson['rows'][i][j]['value'] == null) {
						var td = jQuery('<td/>', {
							text: pJson['rows'][i][j]
						});
					} else {
						var td = jQuery('<td/>', {});
						
						var txt = insertText(pJson['rows'][i][j]);
						txt.appendTo(td);
					}
					
					td.appendTo(tr);
				}
				
				tr.appendTo(tab);
			}
			
			addParameter(tab, pJson);
			
			return tab;
			//tab.appendTo(root);
		}
		
		return null;
	}
	
	function addParameter(pEl, pJson) {
		if(pJson['width'] != null && pJson['width'] != '') {
			pEl.css('width', pJson['width']);
		}
		
		if(pJson['height'] != null && pJson['height'] != '') {
			pEl.css('height', pJson['height']);
		}
		
		if(pJson['color'] != null && pJson['color'] != '') {
			pEl.css('color', pJson['color']);
		}
		
		if(pJson['visible'] != null && pJson['visible'] == 'away') {
			pEl.css('display', 'none');
		}
	}
	
	function serialize(element) {
		var fd = new FormData();   
		
		element.find("textarea").each(function( index ) {
			fd.append($(this).attr('name'), $(this).val());
		});
		
		element.find('input').each(function( index ) {
			if($(this).attr('type') == "text" || $(this).attr('type') == "password") {
				fd.append($(this).attr('name'), $(this).val());
			}
			
			if($(this).attr('type') == "checkbox") {
				if($(this).prop('checked')) {
					fd.append($(this).attr('name'), '1');
				}
			}
		});
		
		return fd;
	}
}
