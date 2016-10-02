function GuiPage() {
	GuiPage();
	this.root = null;
	this.padding = 10;
	this.paddingLeft = 10;
	
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

		window.jui.action.addAction('openPlugin', openPlugin);
		window.jui.action.addAction('openMedia', openMedia);

		window.jui.addOnBeforeParseListener(beforeParseListener);

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
	
	
	function setHeaders() {
		window.jui.setDefaultHeaders([
			{
				name: 'Authorization',
				value: 'bearer ' + window.token
			}
		]);
	}

	function parseHead (pJson) {
		console.log(pJson);

		if ( !window.jui.tools.empty(pJson['jwt']) ) {
			setStorage('token', pJson['jwt']);
			window.token = pJson['jwt'];
			setHeaders();
		}

		if (pJson['bgcolor'] != null) {
			$('body').css("background-color", pJson['bgcolor']);
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
	
	function insertRange(pJson) {
		if(pJson['name'] != null) {
			var el = jQuery('<input/>', {
				type: 'range',
				name: pJson['name']
			});
			
			if(pJson['min'] != null) {
				el.attr('min', pJson['min']);
			}
			
			if(pJson['max'] != null) {
				el.attr('max', pJson['max']);
			}
			
			if(pJson['value'] != null) {
				el.val(pJson['value']);
			}
			
			if(pJson['change'] != null) {
				el.change(function () {
					var response = new Object();
					response.plugin = getPlugin();
					response.action = pJson['change'];
					response.value = $( this ).val();
					
					socket.send(JSON.stringify(response));
				});
			}
			
			
			if(pJson['label'] != null) {
				var lab = jQuery('<label/>', {
					text: pJson['label']
				});
				
				jQuery('<br/>', {}).appendTo(lab);
				el.appendTo(lab);
				
				return lab;
			}
			
			return el;
		}
		
		return null;
	}
	
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
	}
	
	function insertButtonList(pJson) {
		if(pJson['value'] != null) {
			var value = pJson['value'];
			
			var div = jQuery('<div/>', { class: 'buttonlist' });
			
			for(var i = 0; i < value.length; i++) {
				var image   = value[i]['value'][0];
				var textVal = value[i]['value'][1];
				
				var tile = jQuery('<div/>', {
				});
				
				if(image == null || image == 'undefined') image = placeholder;
				var img = jQuery('<img/>', {
					src: image
				});
				img.appendTo(tile);
				
				
				var name = jQuery('<p/>', {
					text: textVal
				});
				name.appendTo(tile);
				
				
				if(value[i]['click'] != null && value[i]['click'] != null && value[i]['click'][i] != "") {
					tile.css('cursor','pointer');
					tile.click(new Function(value[i]['click']));
				} else {
					tile.css('cursor','default');
				}
				
				if(value[i]['longclick'] != null && value[i]['longclick'][i] != null && value[i]['longclick'][i] != "") {
					var __this = i;
					tile.get( 0 ).oncontextmenu = new Function(value[i]['longclick']);
					tile.on('contextmenu', function(e) {
						e.stopPropagation();
						e.preventDefault();
					});
				}
				
				tile.appendTo(div);
			}
			
			return div;
		}
		
		return null;
	}
	
	function insertEditor(pJson) {
		var editor = new Editor(pJson);
		
		/*
		if(pJson['label'] != null) {
			var lab = jQuery('<label/>', {
				text: pJson['label']
			});
			
			jQuery('<br/>', {}).appendTo(lab);
			el.appendTo(lab);
		}*/
		
		return editor.create();
	}
	
	this.getRoot = function() {
		return root;
	};
}

var Editor = (function(pJson) {
	var outer = jQuery('<div/>', {
		class: "editor"
	});
	
	var control = jQuery('<div/>', {
		class: "control"
	});
	
	var contentArea = jQuery('<div/>', {
		name: pJson['name'],
		class: "html"
	});
	
	
	var Editor = function(pJson) {
		createControls();
		createContentArea(pJson);
		
		return {
			create: function() {
				return outer.append(control).append(contentArea);
			}
		};
	};
	
	var createContentArea = function (pJson) {
		if(pJson['value'] != null) {
			var value = pJson['value'];
			value = value.replace(/<(.*)script(.*)>(.*)<(.*)\/(.*)script(.*)>/g, '&lt;$1script$2&gt;$3&lt;$4/$5script$6&gt;');
			contentArea.html(value);
		}
			
		if(pJson['focus'] != null && pJson['focus']) {
			contentArea.attr("autofocus", "autofocus");
		}
			
		contentArea.attr("contenteditable", "true");
	};
	
	var createControls = function () {
		var bold = jQuery('<button/>', {
			class: "bold",
			text: "b"
		}).click( function() {
			document.execCommand ('bold', false, null);
		});
		
		var italic = jQuery('<button/>', {
			class: "italic",
			text: "i"
		}).click( function() {
			document.execCommand ('italic', false, null);
		});
		
		var underlined = jQuery('<button/>', {
			class: "underlined",
			html: "<u>u</u>"
		}).click( function() {
			document.execCommand ('underline', false, null);
		});
		
		
		
		var left = jQuery('<button/>', {
			class: "underlined",
			html: "Links"
		}).click( function() {
			document.execCommand ('justifyLeft', false, null);
		});
		
		var center = jQuery('<button/>', {
			html: "Mittig"
		}).click( function() {
			document.execCommand ('justifyCenter', false, null);
		});
		
		var right = jQuery('<button/>', {
			html: "Rechts"
		}).click( function() {
			document.execCommand ('justifyRight', false, null);
		});
		
		var full = jQuery('<button/>', {
			html: "Blocksatz"
		}).click( function() {
			document.execCommand ('justifyFull', false, null);
		});
		
		
		var orderedList = jQuery('<button/>', {
			html: "Sortierte Liste"
		}).click( function() {
			document.execCommand ('insertOrderedList', false, null);
		});
		
		var unorderedList = jQuery('<button/>', {
			html: "Unsortierte Liste"
		}).click( function() {
			document.execCommand ('insertUnorderedList', false, null);
		});
		
		control.append(bold).append(italic).append(underlined);
		control.append(left).append(center).append(right).append(full);
		control.append(orderedList).append(unorderedList);
	};
	
	return Editor(pJson);
});


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



window.editor = (function(pJson) {
	var _this = window.editor;

	var outer = jQuery('<div/>', {
		class: "editor"
	});
	
	var control = jQuery('<div/>', {
		class: "control"
	});
	
	var contentArea = jQuery('<div/>', {
		name: pJson['name'],
		class: "html"
	});
	
	
	var Editor = function(pJson) {
		createControls();
		createContentArea(pJson);
		
		return {
			getDomElement: function() {
				return outer.append(control).append(contentArea).get(0);
			}
		};
	};
	
	var createContentArea = function (pJson) {
		if(pJson['value'] != null) {
			var value = pJson['value'];
			value = value.replace(/<(.*)script(.*)>(.*)<(.*)\/(.*)script(.*)>/g, '&lt;$1script$2&gt;$3&lt;$4/$5script$6&gt;');
			contentArea.html(value);
		}
			
		if(pJson['focus'] != null && pJson['focus']) {
			contentArea.attr("autofocus", "autofocus");
		}
			
		contentArea.attr("contenteditable", "true");
	};
	
	var createControls = function () {
		var bold = jQuery('<button/>', {
			class: "bold",
			text: "b"
		}).click( function() {
			document.execCommand ('bold', false, null);
		});
		
		var italic = jQuery('<button/>', {
			class: "italic",
			text: "i"
		}).click( function() {
			document.execCommand ('italic', false, null);
		});
		
		var underlined = jQuery('<button/>', {
			class: "underlined",
			html: "<u>u</u>"
		}).click( function() {
			document.execCommand ('underline', false, null);
		});
		
		
		
		var left = jQuery('<button/>', {
			class: "underlined",
			html: "Links"
		}).click( function() {
			document.execCommand ('justifyLeft', false, null);
		});
		
		var center = jQuery('<button/>', {
			html: "Mittig"
		}).click( function() {
			document.execCommand ('justifyCenter', false, null);
		});
		
		var right = jQuery('<button/>', {
			html: "Rechts"
		}).click( function() {
			document.execCommand ('justifyRight', false, null);
		});
		
		var full = jQuery('<button/>', {
			html: "Blocksatz"
		}).click( function() {
			document.execCommand ('justifyFull', false, null);
		});
		
		
		var orderedList = jQuery('<button/>', {
			html: "Sortierte Liste"
		}).click( function() {
			document.execCommand ('insertOrderedList', false, null);
		});
		
		var unorderedList = jQuery('<button/>', {
			html: "Unsortierte Liste"
		}).click( function() {
			document.execCommand ('insertUnorderedList', false, null);
		});
		
		control.append(bold).append(italic).append(underlined);
		control.append(left).append(center).append(right).append(full);
		control.append(orderedList).append(unorderedList);
	};
	
	return Editor(pJson);
});