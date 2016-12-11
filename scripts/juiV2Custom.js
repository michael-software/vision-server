function GuiPage() {
	GuiPage();
	this.root = null;
	this.padding = 10;
	this.paddingLeft = 10;

	var _tools = window.jui.tools;
	var _body = document.querySelector('body');
	var _juiHead = null;
	
	function GuiPage(pRoot) {
		if(pRoot == null) {
			this.root = document.querySelector('#content');
		} else {
			this.root = pRoot;
		}

        window.jui.init(this.root);
	    window.jui.clean();

		window.jui.setHeadCallback(parseHead);

		window.jui.registerCustomElement('buttonlist', window.buttonlist,'bl');
		window.jui.registerCustomElement('editor', window.editor,'ed');
		window.jui.registerCustomElement('autoinput', window.autoinput,'ai');

		window.jui.action.addAction('openPlugin', openPlugin);
		window.jui.action.addAction('openMedia', openMedia);
		window.jui.action.addAction('openGallery', openGallery);
		window.jui.action.addAction('sendAsync', sendAction);

		window.jui.addOnBeforeParseListener(beforeParseListener);

		window.jui.setSubmitCallback(function(formData, name, element) {
			if(element.classList.contains('editor') && element.querySelector('.html') != null) {
				if(!_tools.empty(element.querySelector('.html').innerHTML)) {
					formData.append(name, element.querySelector('.html').innerHTML);
				}
			}
		});

		setHeaders();
	}
	
	this.clean = function () {/*
		root.html('');
		root.css('padding', this.padding);
		root.css('padding-left', this.paddingLeft);
		root.css('margin-top', '0');
		$('body').css("background-color", "transparent");*/
	};
	
	this.parse = function (pJson, pElement) {
		window.jui.parse(pJson, this.root, true);
	};

	this.requestParse = function (url, callback) {
		window.jui.requestParse(url, null, null, callback);
	}

	this.getJuiHeader = function() {
		return _juiHead;
	}
	
	
	function setHeaders() {
		window.jui.setDefaultHeaders([
			{
				name: 'Authorization',
				value: 'bearer ' + window.token
			}
		]);
	}

	function parseHead (pJson) {
		_juiHead = pJson;

		if ( !window.jui.tools.empty(pJson['jwt']) ) {
			setStorage('token', pJson['jwt']);
			window.token = pJson['jwt'];
			setHeaders();
		}

		if (pJson['bgcolor'] != null) {
			_body.style.backgroundColor = pJson['bgcolor'];
		}
		
		if (pJson['share'] != null && pJson['share']['name'] != null) { /* TODO */
			var name = pJson['share']['name'];
			
			if(pJson['share']['view'] != null) {
				var page = pJson['share']['view'];
			} else {
				var page = 'share';
			}
			
			if(pJson['share']['command'] != null) {
				var command = pJson['share']['command'];
			} else {
				var command = '';
			}
			
			showShareButton(name, page, command, false);
		}
	};
	
	/*
	function serialize(element) {
		var fd = new FormData();   
		
		element.find("textarea").each(function( index ) {
			fd.append($(this).attr('name'), $(this).val());
		});
		
		element.find('input').each(function( index ) {
			if($(this).attr('type') == "text" || $(this).attr('type') == "password" || $(this).attr('type') == "number" || $(this).attr('type') == "range" || $(this).attr('type') == "color") {
				fd.append($(this).attr('name'), $(this).val());
			}
			
			if($(this).attr('type') == "checkbox") {
				if($(this).prop('checked')) {
					fd.append($(this).attr('name'), '1');
				}
			}
			
			if($(this).attr('type') == "file") {
				for(var i = 0; i < $(this)[0].files.length; i++) {
					fd.append($(this).attr('name')+'[]', $(this)[0].files[i] );
				}
			}
		});
		
		element.find("select").each(function( index ) {
			fd.append($(this).attr('name'), $(this).val());
		});
		
		element.find(".editor .html").each(function( index ) {
			fd.append($(this).attr('name'), $(this).html());
		});
		
		return fd;
	}*/
}


window.buttonlist = (function (jsonObject) {
    var value = '';
    var properties = [];

    var click = '';
    var longclick = '';

    var _this = window.buttonlist;
    var _tools = window.jui.tools;

	var placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    var parse = function (jsonObject) {
        if (!_tools.empty(jsonObject['value'])) {
            _this.setValue(jsonObject['value']);

            if(!_tools.empty(jsonObject['click'])) {
                _this.setClick(jsonObject['click']);
            }

            if(!_tools.empty(jsonObject['longclick'])) {
                _this.setLongClick(jsonObject['longclick']);
            }

            properties = jsonObject;
        }

        return _this;
    };

    _this.setValue = function (pValue) {
        value = pValue;
    };

    _this.setClick = function (pAction) {
        click = pAction;
    };

    _this.setLongClick = function (pAction) {
        longclick = pAction;
    };

    _this.getDomElement = function () {
        if(!_tools.empty(value)) {
			var div = document.createElement('div');
				div.className = 'buttonlist';
			
			for(var i = 0; i < value.length; i++) {
				var image   = value[i]['value'][0];
				var textVal = value[i]['value'][1];
				
				var tile = document.createElement('div');
				
				if(_tools.empty(image)) image = placeholder;

				var img = document.createElement('img');
				img.src = image;
				tile.appendChild(img);
				
				
				var name = document.createElement('p');
				name.appendChild( document.createTextNode(textVal) );
				tile.appendChild(name);
				
				
				if( !_tools.empty(value[i]['click']) ) {
					tile.style.cursor = 'pointer';
					tile.addEventListener('click', window.jui.action.caller(value[i]['click']), false);
				} else {
					tile.style.cursor = 'default';
				}
				
				if(value[i]['longclick'] != null && value[i]['longclick'][i] != null && value[i]['longclick'][i] != "") {
					tile.addEventListener('contextmenu', function(e) {
						window.jui.action.call(value[i]['longclick']);

						e.stopPropagation();
						e.preventDefault();
					}, false);
				}
				
				div.appendChild(tile);
			}

			window.jui.views.view.addProperties(div, properties);

			return div;
		}
    };

    return parse(jsonObject);
});


window.externalEditor = function(domElement) {
	var messageQueue = [];
    var _this = this;
    var loaded = false;
	var _iframe = null;
	var autosync = false;
	var value = '';

	this.init = function(domElement) {
        _iframe = document.createElement('iframe');
        _iframe.src = document.location.origin + '/editor/index.html';
        _iframe.style.height = '500px';
        _iframe.style.width = '100%';
        _iframe.style.border = 'none';
        _iframe.style.outline = 'none';

		if(domElement && {}.toString.call(domElement.appendChild) === '[object Function]') {
            domElement.appendChild(_iframe);
        }

        _iframe.addEventListener('load', function() {
			loaded = true;

			for(var i = 0, x = messageQueue.length; i < x; i++) {
                _this.postMessage(messageQueue[i]);
			}
		}, false);

		window.addEventListener("message", function (event) {
			if (event.origin === "http://example.com:8080") // TODO
				return;

			try {
				var data = JSON.parse(event.data);

				switch(data.action) {
					case 'save':
						value = data.value || '';
						//console.log(data.value);
						break;
					case 'setHeight':
						console.log(data.value);
						_iframe.style.height = data.value + 'px';
						//console.log(data.value);
						break;
					default:
						console.warn('action not found');
						break;
				}
			} catch(ex) {
				console.warn('Error while parsing message', ex);
			}
		});
	};

	this.postMessage = function(data) {
		if(loaded) {
			if(_iframe) {
				_iframe.contentWindow.postMessage(JSON.stringify(data), window.location.origin);
			}
		} else {
			messageQueue.push(data);
		}
	};

	this.disableFiles = function() {
		this.postMessage({
			action: 'updateConfig',
			value: {
				menu: {
					file: false
				}
			}
		});
	};

	this.getContent = function(callback) {
		if(autosync) {
			return value;
		} else {

		}
	};

	this.setContent = function(data, type) {
		this.postMessage({
			action: 'load',
			value: data,
			mime: type
		});

		value = data;
	};

	this.enableAutosync = function(data, type) {
		this.postMessage({
			action: 'enableAutosync',
			value: true
		});

		autosync = true;
	};

	this.enableAutoresize = function() {
		this.postMessage({
			action: 'autoResize',
			value: true
		});
	};

    this.init(domElement);
};


window.editor = (function(pJson) {
	var _this = window.editor;

	var outer = document.createElement('div');
		outer.className = 'editor';
	
	var control = document.createElement('div');
		control.className = 'control';

	var contentArea = document.createElement('div');
		contentArea.name = pJson['name'];
		contentArea.className = 'html';


    var container = document.createElement('div');
		var editor = new window.externalEditor(container);
			editor.disableFiles();
			editor.setContent('<h1>Test</h1>Hallo', 'text/html');
			editor.enableAutosync();
			editor.enableAutoresize();
	
	var Editor = function(pJson) {
		//createControls();
		editor.setContent(pJson['value'] || '', 'text/html');
		//createContentArea(pJson);
		
		return {
			getDomElement: function() {
				var element = container;
					//element.appendChild(control);
					//element.appendChild(contentArea);

				window.jui.views.view.addProperties(element, pJson);

				element.style.height = 'auto';

				window.jui.registerSubmitCallback(pJson['name'], function() {

					return editor.getContent();
				});

				return window.jui.views.view.addInputProperties(element, pJson);
			}
		};
	};
	
	var createContentArea = function (pJson) {
		if(pJson['value'] != null) {
			var value = pJson['value'];
			value = value.replace(/<(.*)script(.*)>(.*)<(.*)\/(.*)script(.*)>/g, '&lt;$1script$2&gt;$3&lt;$4/$5script$6&gt;');
			contentArea.innerHTML = value;
		}
			
		if(pJson['focus'] != null && pJson['focus']) {
			contentArea.setAttribute("autofocus", "autofocus");
		}
			
		contentArea.setAttribute("contenteditable", "true");
	};
	
	var createControls = function () {
		var bold = document.createElement('button');
			bold.className = 'bold';
			bold.innerHTML = 'b';
			bold.addEventListener('click', function() {
				document.execCommand ('bold', false, null);
			}, false);
		control.appendChild(bold);
		
		var italic = document.createElement('button');
			italic.className = 'italic';
			italic.innerHTML = 'i';
			italic.addEventListener('click', function() {
				document.execCommand ('italic', false, null);
			}, false);
		control.appendChild(italic);
		
		var underlined = document.createElement('button');
			underlined.className = 'underlined';
			underlined.innerHTML = '<u>u</u>';
			underlined.addEventListener('click', function() {
				document.execCommand ('underline', false, null);
			}, false);
		control.appendChild(underlined);




		var left = document.createElement('button');
			left.innerHTML = 'Links';
			left.addEventListener('click', function() {
				document.execCommand ('justifyLeft', false, null);
			}, false);
		control.appendChild(left);

		var center = document.createElement('button');
			center.innerHTML = 'Mittig';
			center.addEventListener('click', function() {
				document.execCommand ('justifyCenter', false, null);
			}, false);
		control.appendChild(center);

		var right = document.createElement('button');
			right.innerHTML = 'Rechts';
			right.addEventListener('click', function() {
				document.execCommand ('justifyRight', false, null);
			}, false);
		control.appendChild(right);
		
		var full = document.createElement('button');
			full.innerHTML = 'Blocksatz';
			full.addEventListener('click', function() {
				document.execCommand ('justifyFull', false, null);
			}, false);
		control.appendChild(full);

		


		var orderedList = document.createElement('button');
			orderedList.innerHTML = 'Sortierte Liste';
			orderedList.addEventListener('click', function() {
				document.execCommand ('insertOrderedList', false, null);
			}, false);
		control.appendChild(orderedList);

		var unorderedList = document.createElement('button');
			unorderedList.innerHTML = 'Unsortierte Liste';
			unorderedList.addEventListener('click', function() {
				document.execCommand ('insertUnorderedList', false, null);
			}, false);
		control.appendChild(unorderedList);
	};
	
	return Editor(pJson);
});