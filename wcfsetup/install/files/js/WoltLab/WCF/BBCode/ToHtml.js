define(['Language', 'StringUtil', 'WoltLab/WCF/BBCode/Parser'], function(Language, StringUtil, BBCodeParser) {
	"use strict";
	
	var _bbcodes = null;
	var _removeNewlineAfter = [];
	var _removeNewlineBefore = [];
	
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
				table: 'table',
				td: 'td',
				tr: 'tr',
				
				// callback replacement
				color: this._replaceColor.bind(this),
				list: this._replaceList.bind(this),
				quote: this._replaceQuote.bind(this),
				url: this._replaceUrl.bind(this)
			};
			
			_removeNewlineAfter = ['quote', 'table', 'td', 'tr'];
			_removeNewlineBefore = ['table', 'td', 'tr'];
		},
		
		_replace: function(stack, item, index) {
			var replace = _bbcodes[item.name], tmp;
			
			if (replace === undefined) {
				// treat as plain text
				stack[item.pair] = stack[item.pair].source;
				
				return item.source;
			}
			
			if (_removeNewlineAfter.indexOf(item.name) !== -1) {
				tmp = stack[index + 1];
				if (typeof tmp === 'string') {
					stack[index + 1] = tmp.replace(/^\n/, '');
				}
				
				if (stack.length > item.pair + 1) {
					tmp = stack[item.pair + 1];
					if (typeof tmp === 'string') {
						stack[item.pair + 1] = tmp.replace(/^\n/, '');
					}
				}
			}
			
			if (_removeNewlineBefore.indexOf(item.name) !== -1) {
				if (index - 1 >= 0) {
					tmp = stack[index - 1];
					if (typeof tmp === 'string') {
						stack[index - 1] = tmp.replace(/\n$/, '');
					}
				}
				
				tmp = stack[item.pair - 1];
				if (typeof tmp === 'string') {
					stack[item.pair - 1] = tmp.replace(/\n$/, '');
				}
			}
			
			if (typeof replace === 'string') {
				stack[item.pair] = '</' + replace + '>';
				
				return '<' + replace + '>';
			}
			else {
				return replace(stack, item, index);
			}
		},
		
		_replaceColor: function(stack, item, index) {
			if (!item.attributes.length || !item.attributes[0].match(/^[a-z0-9#]+$/i)) {
				stack[item.pair] = '';
				
				return '';
			}
			
			stack[item.pair] = '</span>';
			
			return '<span style="color: ' + StringUtil.escapeHTML(item.attributes[0]) + '">';
		},
		
		_replaceList: function(stack, item, index) {
			var type = (items.attributes.length) ? item.attributes[0] : '';
			
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
		
		_replaceQuote: function(stack, item, index) {
			var author = '', link = '';
			if (item.attributes.length > 1) {
				author = item.attributes[0];
				link = item.attributes[1];
			}
			else if (item.attributes.length === 1) {
				author = item.attributes[0];
			}
			
			stack[item.pair] = '</div></blockquote>';
			
			// get rid of the trailing newline for quote content
			for (var i = item.pair - 1; i > index; i--) {
				if (typeof stack[i] === 'string') {
					stack[i] = stack[i].replace(/\n$/, '');
					break;
				}
			}
			
			var header = '';
			if (author) {
				if (link) header = '<a href="' + StringUtil.escapeHTML(link) + '" tabindex="-1">';
				header += Language.get('wcf.bbcode.quote.title.javascript', { quoteAuthor: author });
				if (link) header += '</a>';
			}
			else {
				header = '<small>' + Language.get('wcf.bbcode.quote.title.clickToSet') + '</small>';
			}
			
			return '<blockquote class="quoteBox container containerPadding quoteBoxSimple" cite="' + StringUtil.escapeHTML(link) + '" data-author="' + StringUtil.escapeHTML(author) + '">'
					+ '<header contenteditable="false">'
						+ '<h3>'
							+ header
						+ '</h3>'
						+ '<a class="redactorQuoteEdit"></a>'
					+ '</header>'
					+ '<div>\u200b';
		},
		
		_replaceUrl: function(stack, item, index) {
			// ignore url bbcode without arguments
			if (!item.attributes.length) {
				stack[item.pair] = '';
				
				return '';
			}
			
			stack[item.pair] = '</a>';
			
			return '<a href="' + StringUtil.escapeHTML(item.attributes[0]) + '">';
		}
	};
	
	return BBCodeToHtml;
});
