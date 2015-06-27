define([], function() {
	"use strict";
	
	var _converter = [];
	var _inlineConverter = [];
	
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
				{ tagName: 'UL', callback: this._convertList.bind(this) },
				{ tagName: 'SPAN', callback: this._convertSpan.bind(this) }
			];
			
			_inlineConverter = [
				{ style: 'color', callback: this._convertInlineColor.bind(this) },
				{ style: 'font-size', callback: this._convertInlineFontSize.bind(this) },
				{ style: 'font-family', callback: this._convertInlineFontFamily.bind(this) }
			];
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
				for (var i = 0, length = _inlineConverters.length; i < length; i++) {
					converter = _inlineConverters[i];
					
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
		
		_convertInlineColor: function(element, color) {
			if (color.match(/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/i)) {
				var r = RegExp.$1;
				var g = RegExp.$2;
				var b = RegExp.$3;
				
				var chars = '0123456789ABCDEF';
				color = '#' + (chars.charAt((r - r % 16) / 16) + '' + chars.charAt(r % 16)) + '' + (chars.charAt((g - g % 16) / 16) + '' + chars.charAt(g % 16)) + '' + (chars.charAt((b - b % 16) / 16) + '' + chars.charAt(b % 16));
			}
			
			element.innerHTML = '[color=' + color + ']' + element.innerHTML + '[/color]';
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
