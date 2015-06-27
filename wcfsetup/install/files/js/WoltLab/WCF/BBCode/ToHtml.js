define(['WoltLab/WCF/BBCode/Parser'], function(BBCodeParser) {
	"use strict";
	
	var _bbcodes = null;
	
	var BBCodeToHtml = {
		convert: function(message) {
			this._convertSpecials(message);
			
			var stack = BBCodeParser.parse(message);
			
			if (stack.length) {
				this._initBBCodes();
			}
			
			var item;
			for (var i = 0, length = stack.length; i < length; i++) {
				item = stack[i];
				
				if (typeof item === 'object') {
					stack[i] = this._replace(stack, item, i);
				}
			}
			
			message = stack.join('');
			
			message = message.replace(/\n/g, '<br>');
			
			return message;
		},
		
		_convertSpecials: function(message) {
			message = message.replace(/&/g, '&amp;');
			message = message.replace(/</g, '&lt;');
			message = message.replace(/>/g, '&gt;');
			
			return message;
		},
		
		_initBBCodes: function() {
			if (_bbcodes !== null) {
				return;
			}
			
			_bbcodes = {
				// simple replacements
				b: 'strong',
				i: 'em',
				u: 'u',
				s: 'del',
				sub: 'sub',
				sup: 'sup',
				
				// callback replacement
				color: this._replaceColor.bind(this),
				list: this._replaceList.bind(this),
				url: this._replaceUrl.bind(this)
			};
		},
		
		_replace: function(stack, item, index) {
			var pair = stack[item.pair], replace = _bbcodes[item.name];
			
			if (replace === undefined) {
				// treat as plain text
				stack[item.pair] = pair.source;
				
				return item.source;
			}
			else if (typeof replace === 'string') {
				stack[item.pair] = '</' + replace + '>';
				
				return '<' + replace + '>';
			}
			else {
				return replace(stack, item, pair, index);
			}
		},
		
		_replaceColor: function(stack, item, pair) {
			if (item.attributes === undefined || !item.attributes.length || !item.attributes[0].match(/^[a-z0-9#]+$/i)) {
				stack[item.pair] = '';
				
				return '';
			}
			
			stack[item.pair] = '</span>';
			
			return '<span style="color: ' + item.attributes[0] + '">';
		},
		
		_replaceList: function(stack, item, pair, index) {
			var type = (item.attributes === undefined || !items.attributes.length) ? '' : item.attributes[0].trim();
			
			// replace list items
			for (var i = index + 1; i < item.pair; i++) {
				if (typeof stack[i] === 'string') {
					stack[i] = stack[i].replace(/\[\*\]/g, '<li>');
				}
			}
			
			if (type == '1' || type === 'decimal') {
				stack[item.pair] = '</ol>';
				
				return '<ol>';
			}
			
			stack[item.pair] = '</ul>';
			if (type.length && type.match(/^(?:none|circle|square|disc|decimal|lower-roman|upper-roman|decimal-leading-zero|lower-greek|lower-latin|upper-latin|armenian|georgian)$/)) {
				return '<ul style="list-style-type: ' + type + '">';
			}
			
			return '<ul>';
		},
		
		_replaceUrl: function(stack, item, pair) {
			// ignore url bbcode without arguments
			if (item.attributes === undefined || !item.attributes.length) {
				stack[item.pair] = '';
				
				return '';
			}
			
			stack[item.pair] = '</a>';
			
			return '<a href="' + item.attributes[0] + '">';
		}
	};
	
	return BBCodeToHtml;
});
