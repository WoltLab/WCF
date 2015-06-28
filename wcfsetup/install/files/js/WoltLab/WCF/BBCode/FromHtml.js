define(['DOM/Traverse'], function(DOMTraverse) {
	"use strict";
	
	var _converter = [];
	var _inlineConverter = {};
	
	var BBCodeFromHtml = {
		convert: function(message) {
			if (message.length) this._setup();
			
			var container = document.createElement('div');
			container.innerHTML = message;
			
			// convert line breaks
			var elements = container.getElementsByTagName('P');
			while (elements.length) elements[0].outerHTML = elements[0].innerHTML;
			
			var elements = container.getElementsByTagName('BR');
			while (elements.length) elements[0].outerHTML = "\n";
			
			for (var i = 0, length = _converter.length; i < length; i++) {
				this._convert(container, _converter[i]);
			}
			
			message = this._convertSpecials(container.innerHTML);
			
			return message;
		},
		
		_convertSpecials: function(message) {
			message = message.replace(/&amp;/g, '&');
			message = message.replace(/&lt;/g, '<');
			message = message.replace(/&gt;/g, '>');
			
			return message;
		},
		
		_setup: function() {
			if (_converter.length) {
				return;
			}
			
			_converter = [
				// simple replacement
				{ tagName: 'STRONG', bbcode: 'b' },
				{ tagName: 'DEL', bbcode: 's' },
				{ tagName: 'EM', bbcode: 'i' },
				{ tagName: 'SUB', bbcode: 'sub' },
				{ tagName: 'SUP', bbcode: 'sup' },
				{ tagName: 'U', bbcode: 'u' },
				
				// callback replacement
				{ tagName: 'A', callback: this._convertUrl.bind(this) },
				{ tagName: 'LI', callback: this._convertListItem.bind(this) },
				{ tagName: 'OL', callback: this._convertList.bind(this) },
				{ tagName: 'TABLE', callback: this._convertTable.bind(this) },
				{ tagName: 'UL', callback: this._convertList.bind(this) },
				
				// convert these last
				{ tagName: 'SPAN', callback: this._convertSpan.bind(this) },
				{ tagName: 'DIV', callback: this._convertDiv.bind(this) }
			];
			
			_inlineConverter = {
				span: [
					{ style: 'color', callback: this._convertInlineColor.bind(this) },
					{ style: 'font-size', callback: this._convertInlineFontSize.bind(this) },
					{ style: 'font-family', callback: this._convertInlineFontFamily.bind(this) }
				],
				div: [
					{ style: 'text-align', callback: this._convertInlineTextAlign.bind(this) }
				]
			};
		},
		
		_convert: function(container, converter) {
			if (typeof converter === 'function') {
				converter(container);
				return;
			}
			
			var element, elements = container.getElementsByTagName(converter.tagName);
			while (elements.length) {
				element = elements[0];
				
				if (converter.bbcode) {
					element.outerHTML = '[' + converter.bbcode + ']' + element.innerHTML + '[/' + converter.bbcode + ']';
				}
				else {
					converter.callback(element);
				}
			}
		},
		
		_convertList: function(element) {
			var open;
			
			if (element.nodeName === 'OL') {
				open = '[list=1]';
			}
			else {
				var type = element.style.getPropertyValue('list-style-type') || '';
				if (type === '') {
					open = '[list]';
				}
				else {
					open = '[list=' + (type === 'lower-latin' ? 'a' : type) + ']';
				}
			}
			
			element.outerHTML = open + element.innerHTML + '[/list]';
		},
		
		_convertListItem: function(element) {
			if (element.parentNode.nodeName !== 'UL' && element.parentNode.nodeName !== 'OL') {
				element.outerHTML = element.innerHTML;
			}
			else {
				element.outerHTML = '[*]' + element.innerHTML;
			}
		},
		
		_convertSpan: function(element) {
			if (element.style.length || element.className) {
				var converter, value;
				for (var i = 0, length = _inlineConverter.span.length; i < length; i++) {
					converter = _inlineConverter.span[i];
					
					if (converter.style) {
						value = element.style.getPropertyValue(converter.style) || '';
						if (value) {
							converter.callback(element, value);
						}
					}
					else {
						if (element.classList.contains(converter.className)) {
							converter.callback(element);
						}
					}
				}
			}
			
			element.outerHTML = element.innerHTML;
		},
		
		_convertDiv: function(element) {
			if (element.style.length) {
				var converter, value;
				for (var i = 0, length = _inlineConverter.div.length; i < length; i++) {
					converter = _inlineConverter.div[i];
					
					value = element.style.getPropertyValue(converter.style) || '';
					if (value) {
						converter.callback(element, value);
					}
				}
			}
			
			element.outerHTML = element.innerHTML;
		},
		
		_convertInlineColor: function(element, value) {
			if (value.match(/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/i)) {
				var r = RegExp.$1;
				var g = RegExp.$2;
				var b = RegExp.$3;
				
				var chars = '0123456789ABCDEF';
				value = '#' + (chars.charAt((r - r % 16) / 16) + '' + chars.charAt(r % 16)) + '' + (chars.charAt((g - g % 16) / 16) + '' + chars.charAt(g % 16)) + '' + (chars.charAt((b - b % 16) / 16) + '' + chars.charAt(b % 16));
			}
			
			element.innerHTML = '[color=' + value + ']' + element.innerHTML + '[/color]';
		},
		
		_convertInlineFontSize: function(element, value) {
			if (value.match(/^(\d+)pt$/)) {
				value = RegExp.$1;
			}
			else if (value.match(/^(\d+)(px|em|rem|%)$/)) {
				value = window.getComputedStyle(value).fontSize.replace(/^(\d+).*$/, '$1');
				value = Math.round(value);
			}
			else {
				// unknown or unsupported value, ignore
				value = '';
			}
			
			if (value) {
				// min size is 8 and maximum is 36
				value = Math.min(Math.max(value, 8), 36);
				
				element.innerHTML = '[size=' + value + ']' + element.innerHTML + '[/size]';
			}
		},
		
		_convertInlineFontFamily: function(element, value) {
			element.innerHTML = '[font=' + value.replace(/'/g, '') + ']' + element.innerHTML + '[/font]';
		},
		
		_convertInlineTextAlign: function(element, value) {
			if (value === 'left' || value === 'right' || value === 'justify') {
				element.innerHTML = '[align=' + value + ']' + innerHTML + '[/align]';
			}
		},
		
		_convertTable: function(element) {
			var elements = element.getElementsByTagName('TD');
			while (elements.length) {
				elements[0].outerHTML = '[td]' + elements[0].innerHTML + '[/td]\n';
			}
			
			elements = element.getElementsByTagName('TR');
			while (elements.length) {
				elements[0].outerHTML = '\n[tr]\n' + elements[0].innerHTML + '[/tr]';
			}
			
			var tbody = DOMTraverse.childByTag(element, 'TBODY');
			var innerHtml = (tbody === null) ? element.innerHTML : tbody.innerHTML;
			element.outerHTML = '\n[table]' + innerHtml + '\n[/table]\n';
		},
		
		_convertUrl: function(element) {
			var content = element.textContent.trim(), href = element.href.trim();
			
			if (href === '' || content === '') {
				// empty href or content
				element.outerHTML = element.innerHTML;
				return;
			}
			
			if (href.indexOf('mailto:') === 0) {
				element.outerHTML = '[email=' + href.substr(6) + ']' + element.innerHTML + '[/email]';
			}
			else if (href === content) {
				element.outerHTML = '[url]' + href + '[/url]';
			}
			else {
				element.outerHTML = "[url='" + href + "']" + element.innerHTML + "[/url]";
			}
		}
	};
	
	return BBCodeFromHtml;
});
