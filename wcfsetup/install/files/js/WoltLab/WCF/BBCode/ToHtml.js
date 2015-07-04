define(['EventHandler', 'Language', 'StringUtil', 'WoltLab/WCF/BBCode/Parser'], function(EventHandler, Language, StringUtil, BBCodeParser) {
	"use strict";
	
	var _bbcodes = null;
	var _removeNewlineAfter = [];
	var _removeNewlineBefore = [];
	
	function isNumber(value) { return value && value == ~~value; }
	function isFilename(value) { return (value.indexOf('.') !== -1) || (!isNumber(value) && !isHighlighter(value)); }
	function isHighlighter(value) { return __REDACTOR_CODE_HIGHLIGHTERS.hasOwnProperty(value); }
	
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
				tt: 'kbd',
				
				// callback replacement
				color: this._replaceColor.bind(this),
				code: this._replaceCode.bind(this),
				email: this._replaceEmail.bind(this),
				list: this._replaceList.bind(this),
				quote: this._replaceQuote.bind(this),
				url: this._replaceUrl.bind(this),
				img: this._replaceImage.bind(this)
			};
			
			_removeNewlineAfter = ['quote', 'table', 'td', 'tr'];
			_removeNewlineBefore = ['table', 'td', 'tr'];
			
			EventHandler.fire('com.woltlab.wcf.bbcode.toHtml', 'init', {
				bbcodes: _bbcodes,
				removeNewlineAfter: _removeNewlineAfter,
				removeNewlineBefore: _removeNewlineBefore
			});
		},
		
		_replace: function(stack, item, index) {
			var replace = _bbcodes[item.name], tmp;
			
			if (replace === undefined) {
				// treat as plain text
				console.debug(item);
				console.debug(stack);
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
			
			// replace smilies
			this._replaceSmilies(stack);
			
			if (typeof replace === 'string') {
				stack[item.pair] = '</' + replace + '>';
				
				return '<' + replace + '>';
			}
			else {
				return replace(stack, item, index);
			}
		},
		
		_replaceCode: function(stack, item, index) {
			var attributes = item.attributes, filename = '', highlighter = 'auto', lineNumber = 0;
			
			// parse arguments
			switch (attributes.length) {
				case 1:
					if (isNumber(attributes[0])) {
						lineNumber = ~~attributes[0];
					}
					else if (isFilename(attributes[0])) {
						filename = attributes[0];
					}
					else if (isHighlighter(attributes[0])) {
						highlighter = attributes[0];
					}
					break;
				case 2:
					if (isNumber(attributes[0])) {
						lineNumber = ~~attributes[0];
						
						if (isHighlighter(attributes[1])) {
							highlighter = attributes[1];
						}
						else if (isFilename(attributes[1])) {
							filename = attributes[1];
						}
					}
					else {
						if (isHighlighter(attributes[0])) highlighter = attributes[0];
						if (isFilename(attributes[1])) filename = attributes[1];
					}
					break;
				case 3:
					if (isHighlighter(attributes[0])) highlighter = attributes[0];
					if (isNumber(attributes[1])) lineNumber = ~~attributes[1];
					if (isFilename(attributes[2])) filename = attributes[2];
					break;
			}
			
			// transform content
			var before = true, content, line, empty = -1;
			for (var i = index + 1; i < item.pair; i++) {
				line = stack[i];
				
				if (line.trim() === '') {
					if (before) {
						stack[i] = '';
						continue;
					}
					else if (empty === -1) {
						empty = i;
					}
				}
				else {
					before = false;
					empty = -1;
				}
				
				content = line.split('\n');
				for (var j = 0, innerLength = content.length; j < innerLength; j++) {
					content[j] = '<li>' + (content[j] ? StringUtil.escapeHTML(content[j]) : '\u200b') + '</li>';
				}
				
				stack[i] = content.join('');
			}
			
			if (!before && empty !== -1) {
				for (var i = item.pair - 1; i >= empty; i--) {
					stack[i] = '';
				}
			}
			
			stack[item.pair] = '</ol></div></div>';
			
			return '<div class="codeBox container" contenteditable="false" data-highlighter="' + highlighter + '" data-filename="' + (filename ? StringUtil.escapeHTML(filename) : '') + '">'
					+ '<div>'
					+ '<div>'
						+ '<h3>' + __REDACTOR_CODE_HIGHLIGHTERS[highlighter] + (filename ? ': ' + StringUtil.escapeHTML(filename) : '') + '</h3>'
					+ '</div>'
					+ '<ol start="' + (lineNumber > 1 ? lineNumber : 1) + '">';
		},
		
		_replaceColor: function(stack, item, index) {
			if (!item.attributes.length || !item.attributes[0].match(/^[a-z0-9#]+$/i)) {
				stack[item.pair] = '';
				
				return '';
			}
			
			stack[item.pair] = '</span>';
			
			return '<span style="color: ' + StringUtil.escapeHTML(item.attributes[0]) + '">';
		},
		
		_replaceEmail: function(stack, item, index) {
			var email = '';
			if (item.attributes.length) {
				email = item.attributes[0];
			}
			else {
				var element;
				for (var i = index + 1; i < item.pair; i++) {
					element = stack[i];
					
					if (typeof element === 'object') {
						email = '';
						break;
					}
					else {
						email += element;
					}
				}
				
				// no attribute present and element is empty, handle as plain text
				if (email.trim() === '') {
					stack[item.pair] = stack[item.pair].source;
					
					return item.source;
				}
			}
			
			stack[item.pair] = '</a>';
			
			return '<a href="mailto:' + StringUtil.escapeHTML(email) + '">';
		},
		
		_replaceImage: function(stack, item, index) {
			stack[item.pair] = '';
			
			var float = 'none', source = '', width = 0;
			
			switch (item.attributes.length) {
				case 0:
					if (index + 1 < item.pair && typeof stack[index + 1] === 'string') {
						source = stack[index + 1];
						stack[index + 1] = '';
					}
					else {
						// [img] without attributes and content, discard
						return '';
					}
				break;
				
				case 1:
					source = item.attributes[0];
				break;
				
				case 2:
					source = item.attributes[0];
					float = item.attributes[1];
				break;
				
				case 3:
					source = item.attributes[0];
					float = item.attributes[1];
					width = ~~item.attributes[2];
				break;
			}
			
			if (float !== 'left' && float !== 'right') float = 'none';
			
			var styles = [];
			if (width > 0) {
				styles.push('width: ' + width + 'px');
			}
			
			if (float !== 'none') {
				styles.push('float: ' + float);
				styles.push('margin: ' + (float === 'left' ? '0 15px 7px 0' : '0 0 7px 15px'));
			}
			
			return '<img src="' + StringUtil.escapeHTML(source) + '"' + (styles.length ? ' style="' + styles.join(';') + '"' : '') + '>';
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
				header += Language.get('wcf.bbcode.quote.title.javascript', { quoteAuthor: author.replace(/\\'/g, "'") });
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
		
		_replaceSmilies: function(stack) {
			var altValue, item, regexp;
			for (var i = 0, length = stack.length; i < length; i++) {
				item = stack[i];
				
				if (typeof item === 'string') {
					for (var smileyCode in __REDACTOR_SMILIES) {
						if (__REDACTOR_SMILIES.hasOwnProperty(smileyCode)) {
							altValue = smileyCode.replace(/</g, '&lt;').replace(/>/g, '&gt;');
							regexp = new RegExp('(\\s|^)' + StringUtil.escapeRegExp(smileyCode) + '(?=\\s|$)', 'gi');
							item = item.replace(regexp, '$1<img src="' + __REDACTOR_SMILIES[smileyCode] + '" class="smiley" alt="' + altValue + '">');
						}
					}
					
					stack[i] = item;
				}
				else if (__REDACTOR_SOURCE_BBCODES.indexOf(item.name) !== -1) {
					// skip processing content
					i = item.pair;
				}
			}
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
