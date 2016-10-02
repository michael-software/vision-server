function GuiPage() {
	GuiPage();
	this.root = null;
	this.padding = 10;
	this.paddingLeft = 10;
	
	function GuiPage(pRoot) {
		if(pRoot == null) {
			this.root = $('#content');
		} else {
			this.root = pRoot;
		}
	}
	
	this.clean = function () {
		root.html('');
		root.css('padding', this.padding);
		root.css('padding-left', this.paddingLeft);
		root.css('margin-top', '0');
		$('body').css("background-color", "transparent");
	};
	
	this.parse = function (pJson, pElement) {
		if(pElement == null) {
			pElement = root;
		}
		
		if(pJson['head'] != null) {
			this.parseHead(pJson['head']);
		}
		
		if(pJson['data'] != null) {
			pJson = pJson['data'];
		}
		
		for(var i = 0; i < pJson.length; i++){
        	var el = this.insert(pJson[i]);
        	
        	if(el != null) {
        		if(pJson[i]['id'] != null) {
        			el.attr("id", "jui_" + pJson[i]['id']);
        		}
        		
				el.appendTo(pElement);
			}
        }
	};
	
	this.parseHead = function (pJson) {
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
	
	this.insert = function (pJson, allElements) {
		if(pJson['type'] != null) {
			if(pJson['height'] == null) {
				pJson['height'] = 'auto';
			}
			
			if(pJson['width'] == null) { /* TODO */
				pJson['width'] = 'auto';
			}
			
			if(pJson['name'] == null) { /* TODO */
				pJson['name'] = '';
			}
			
			if(pJson['value'] != null && typeof pJson['value'] === "string") { /* TODO */
				pJson['value'] = pJson['value'].replace(sep1, getCommand());
			}
			
			if(allElements == null) { /* TODO */
				allElements = true;
			}
			
			var el = this.parseSingleLineElements(pJson);
			
			if(allElements) {
				if(pJson['type'] == 'list') {
					el = insertList(pJson);
				} else if(pJson['type'] == 'table') {
					el = insertTable(pJson, this);
				} else if(pJson['type'] == 'frame') {
					el = insertFrame(pJson);
				} else if(pJson['type'] == 'warning') {
					el = insertWarning(pJson);
				} else if(pJson['type'] == 'widget') {
					el = insertWidget(pJson, this);
				} else if(pJson['type'] == 'buttonlist') {
					el = insertButtonList(pJson);
				} else if(pJson['type'] == 'spoiler') {
					el = insertSpoiler(pJson, this);
				} else if(pJson['type'] == 'range') {
					el = insertRange(pJson);
				} else if(pJson['type'] == 'editor') {
					el = insertEditor(pJson);
				}
			}
			
			if(el != null) {
				if(pJson['visible'] != null) {
					if(pJson['visible'] == 'hidden') {
						el.css("visibility", "hidden");
					} else if(pJson['visible'] == 'away') {
						el.css("display", "none");
					}
				}
				
                if (pJson['bgcolor'] != null) {
                    el.css("background-color", pJson['bgcolor']);
                }
			}
			
			
			
			return el;
		}
	};
	
	this.parseSingleLineElements = function(pJson) {
		var el = null;
		
		if(pJson['type'] == 'text') {
			el = insertText(pJson);
		} else if(pJson['type'] == 'heading') {
			el = insertHeading(pJson);
		} else if(pJson['type'] == 'headingSmall') {
			el = insertHeadingSmall(pJson);
		} else if(pJson['type'] == 'input') {
			el = insertInput(pJson);
		} else if(pJson['type'] == 'password') {
			el = insertPassword(pJson);
		} else if(pJson['type'] == 'textarea') {
			el = insertTextarea(pJson);
		} else if(pJson['type'] == 'submit') {
			el = insertSubmit(pJson, this, modUrl);
		} else if(pJson['type'] == 'button') {
			el = insertButton(pJson);
		} else if(pJson['type'] == 'checkbox') {
			el = insertCheckbox(pJson);
		} else if(pJson['type'] == 'nl') {
			el = insertNewLine(pJson);
		} else if(pJson['type'] == 'hline') {
			el = insertHorizontalLine(pJson);
		} else if(pJson['type'] == 'file') {
			el = insertFileInput(pJson);
		} else if(pJson['type'] == 'image') {
			el = insertImage(pJson);
		} else if(pJson['type'] == 'link') {
			el = insertLink(pJson);
		} else if(pJson['type'] == 'select') {
			el = insertSelect(pJson);
		} else if(pJson['type'] == 'color') {
			el = insertColor(pJson);
		}
		
		return el;
	};
	
	function insertText(pJson) {
		if(pJson['value'] != null) {
			var pText = pJson['value'];
			//pText = pText.replace("/&lt;br \/&gt;/g", "<br />").replace("/&lt;br\/&gt;/g", "<br />").replace("/&lt;br&gt;/g", "<br />").replace("/\n/g", "<br />");
            //pText = pText.replace("/<br \/> /g", "<br />").replace("/ <br \/> /g", "<br />");
			
			pText = pText.replace("/&lt;br \/&gt;/g", "<br />").replace("/&lt;br\/&gt;/g", "<br />").replace("/&lt;br&gt;/g", "<br />");
			pText = pText.replace(/(?:\r\n|\r|\n)/g, '<br />');
			pText = pText.replace("/<br \/> /g", "<br />").replace("/ <br \/>/g", "<br />");
			
			var el = jQuery('<div/>', {
				html: pText
			});
			
			if(pJson['align'] != null) {
				if(pJson['align'].toUpperCase() == 'RIGHT') {
					el.css('text-align','right');
				} else if(pJson['align'].toUpperCase() == 'CENTER') {
					el.css('text-align','center');
				} else {
					el.css('text-align','left');
				}
			}
			
			if(pJson['appearance'] != null) {
				if(pJson['appearance'].toUpperCase() == 'BOLD') {
					el.css('font-weight','bold');
				} else if(pJson['appearance'].toUpperCase() == 'ITALIC') {
					el.css('font-style','italic');
				} else if(pJson['appearance'].toUpperCase() == 'BOLDITALIC' || pJson['appearance'].toUpperCase() == 'ITALICBOLD') {
					el.css('font-weight','bold');
					el.css('font-style','italic');
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
			
			root.css('margin-top', '35px');
			
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
		
		if(pJson['accept'] != null) {
			if(pJson['accept'] == "numbers") {
				el.get(0).type = 'number';
			}
		}
		
		if(pJson['hint'] != null) {
			el.attr("placeholder", pJson['hint']);
		}
		
		if(pJson['focus'] != null && pJson['focus']) {
			el.attr("autofocus", "autofocus");
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
	
	function insertColor(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<input/>', {
				type: 'color',
				value: pJson['value'],
				name: pJson['name']
			});
		} else {
			var el = jQuery('<input/>', {
				type: 'color',
				name: pJson['name']
			});
		}
		
		if(pJson['focus'] != null && pJson['focus']) {
			el.attr("autofocus", "autofocus");
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
	
	function insertSelect(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<select/>', {
				type: 'text',
				name: pJson['name']
			});
			
			var valueArray = pJson['value'];
			
			for(var k = 0; k < valueArray.length; k++) {
				var valueString = valueArray[k];
				
				var opt = jQuery('<option/>', {
					value: valueString,
					text: valueString
				}).appendTo(el);
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
		}
		
		return null;
	}
	
	function insertPassword(pJson) {
		if(pJson['value'] != null) {
			if(pJson['value'] == sep1) {
				
				pJson['value'] = getCommand();
			}
			
			var el = jQuery('<input/>', {
				type: 'password',
				value: pJson['value'],
				name: pJson['name']
			});
		} else {
			var el = jQuery('<input/>', {
				type: 'password',
				name: pJson['name']
			});
		}
			
		if(pJson['hint'] != null) {
			el.attr("placeholder", pJson['hint']);
		}
		
		if(pJson['focus'] != null && pJson['focus']) {
			el.attr("autofocus", "autofocus");
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
		
		if(pJson['multiple'] != null) {
			el.attr('multiple','multiple');
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
		
		if(pJson['hint'] != null) {
			el.attr("placeholder", pJson['hint']);
		}
		
		if(pJson['focus'] != null && pJson['focus']) {
			el.attr("autofocus", "autofocus");
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
	
	function insertSubmit(pJson, gui, pUrl) {
		if(pJson['value'] != null) {
			var el = jQuery('<input/>', {
				value: pJson['value'],
				type:"button"
			}).click(function () {
				var formData = serialize(root);
				
				$.ajax({
					url: pUrl,
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
						} else {
							gui.clean();
        					gui.parse(response);
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
			
			addClick(el, pJson);
			
			addParameter(el, pJson);
			
			return el;
			//el.appendTo(root);
		}
		return null;
	}
	
	function insertLink(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<a/>', {
				text: pJson['value'],
				class: "link"
			});
			
			addClick(el, pJson);
			
			addParameter(el, pJson);
			
			return el;
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
	
	function insertTable(pJson, __this) {
		if(pJson['rows'] != null && pJson['rows'] != "") {
			var tab = jQuery('<table/>', {});
			
			for(var i = 0; i < pJson['rows'].length; i++) {
				var tr = jQuery('<tr/>', {});
				
				if(pJson['rows'][i] != null && pJson['rows'][i] != "")
				for(var j = 0; j < pJson['rows'][i].length; j++) {
					
					if( !Array.isArray(pJson['rows'][i][j]) ) {
						var td = jQuery('<td/>', {
							text: pJson['rows'][i][j]
						});
					} else {
						var td = jQuery('<td/>', {});
						
						var elements = pJson['rows'][i][j];
						
						for(var k = 0; k < elements.length; k++){
				        	var el = __this.insert(elements[k]);
				        	
				        	if(el != null) {
								el.appendTo(td);
							}
				        }
						//txt.appendTo(td);
					}
					
					td.css("vertical-align", "top").appendTo(tr);
				}
				
				if (pJson['click'] != null && pJson['click'][i] != null) {
                    tr.click(new Function(pJson['click'][i]));
                }
                
                if (pJson['longclick'] != null && pJson['longclick'][i] != null) {
                    tr.dblclick(new Function(pJson['longclick'][i]));
                }
				
				tr.appendTo(tab);
			}
			
			addParameter(tab, pJson);
			
			return tab;
			//tab.appendTo(root);
		}
		
		return null;
	}
	
	function insertWidget(pJson, __this) {
		if(pJson['value'] != null) {
			var div = jQuery('<div/>', { class: 'widget' });
			__this.parse(pJson['value'], div);
			
			return div;
		}
		
		return null;
	}
	
	function insertSpoiler(pJson, __this) {
		if(pJson['value'] != null) {
			var div = jQuery('<div/>', { class: 'spoiler' });
			
			var content = jQuery('<div/>', { class: 'spoiler' });
			__this.parse(pJson['value'], content);
			
			var button = jQuery('<input/>', {
				text: pJson['label'],
				type: 'button'
			}).click(function () {
				var status = content.css('display');
				
				if(status == 'block') {
					content.css('display', 'none');
					button.val(pJson['label'] + ' anzeigen');
				} else {
					content.css('display', 'block');
					button.val(pJson['label'] + ' ausblenden');
				}
			}).appendTo(div);
			
			content.appendTo(div); // Append it after Button
			
			
			if(pJson['default'] == null || pJson['default'] != 'SHOW') {
				content.css('display', 'none');
				button.val(pJson['label'] + ' anzeigen');
			} else {
				content.css('display', 'block');
				button.val(pJson['label'] + ' ausblenden');
			}
			
			return div;
		}
		
		return null;
	}
	
	function insertImage(pJson) {
		if(pJson['value'] != null) {
			var el = jQuery('<img/>', {
				src: pJson['value']
			});
			
			addClick(el, pJson);
			addParameter(el, pJson);
			
			return el;
			//el.appendTo(root);
		}
		
		return null;
	}
	
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
		
		if(pJson['visible'] != null && pJson['visible'] == 'invisible') {
			pEl.css('visibility', 'hidden');
		}
		
		if(pJson['margin'] != null) {
			pEl.css('margin', pJson['margin'] + 'px');
		}
		
		if(pJson['marginTop'] != null) {
			pEl.css('margin-top', pJson['marginTop'] + 'px');
		}
		
		if(pJson['marginLeft'] != null) {
			pEl.css('margin-left', pJson['marginLeft'] + 'px');
		}
		
		if(pJson['marginRight'] != null) {
			pEl.css('margin-right', pJson['marginRight'] + 'px');
		}
		
		if(pJson['marginBottom'] != null) {
			pEl.css('margin-bottom', pJson['marginBottom'] + 'px');
		}
	}
	
	function addClick(el, pJson) {
		if(pJson['click'] != null) {
			pJson['click'] = pJson['click'].replace(sep1, getCommand());
			el.click(new Function(pJson['click']));
		}
			
		if(pJson['longclick'] != null) {
			pJson['longclick'] = pJson['longclick'].replace(sep1, getCommand());
			el.dblclick(new Function(pJson['longclick']));
		}
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
