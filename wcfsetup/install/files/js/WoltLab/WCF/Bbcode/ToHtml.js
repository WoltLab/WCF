/**
 * Converts a message containing BBCodes into HTML.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Bbcode/ToHtml
 */
define(['Core', 'EventHandler', 'Language', 'StringUtil', 'WoltLab/WCF/Bbcode/Parser'], function(Core, EventHandler, Language, StringUtil, BbcodeParser) {
	"use strict";
	
	var _bbcodes = null;
	var _options = {};
	var _removeNewlineAfter = [];
	var _removeNewlineBefore = [];
	
	/**
	 * Returns true if given value is a non-zero integer.
	 * 
	 * @param	{string}	value		target value
	 * @return	{boolean}	true if `value` is a non-zero integer
	 */
	function isNumber(value) {
		return value && value == ~~value;
	}
	
	/**
	 * Returns true if given value appears to be a filename, which means that it contains a dot
	 * or is neither numeric nor a known highlighter.
	 * 
	 * @param	{string}	value		target value
	 * @return	{boolean}	true if `value` appears to be a filename
	 */
	function isFilename(value) {
		return (value.indexOf('.') !== -1) || (!isNumber(value) && !isHighlighter(value));
	}
	
	/**
	 * Returns true if given value is a known highlighter.
	 * 
	 * @param	{string}	value		target value
	 * @return	{boolean}	true if `value` is a known highlighter
	 */
	function isHighlighter(value) {
		return objOwns(__REDACTOR_CODE_HIGHLIGHTERS, value);
	}
	
	/**
	 * @module	WoltLab/WCF/Bbcode/ToHtml
	 */
	var BbcodeToHtml = {
		/**
		 * Converts a message containing BBCodes to HTML.
		 * 
		 * @param	{string}	message		message containing BBCodes
		 * @return	{string}	HTML message
		 */
		convert: function(message, options) {
			_options = Core.extend({
				attachments: {
					images: {},
					thumbnailUrl: '',
					url: ''
				}
			}, options);
			
			this._convertSpecials(message);
			
			var stack = BbcodeParser.parse(message);
			
			if (stack.length) {
				this._initBBCodes();
			}
			
			EventHandler.fire('com.woltlab.wcf.bbcode.toHtml', 'beforeConvert', { stack: stack });
			
			var item, value;
			for (var i = 0, length = stack.length; i < length; i++) {
				item = stack[i];
				
				if (typeof item === 'object') {
					value = this._convert(stack, item, i);
					if (Array.isArray(value)) {
						stack[i] = (value[0] === null ? item.source : value[0]);
						stack[item.pair] = (value[1] === null ? stack[item.pair].source : value[1]);
					}
					else {
						stack[i] = value;
					}
				}
			}
			
			EventHandler.fire('com.woltlab.wcf.bbcode.toHtml', 'afterConvert', { stack: stack });
			
			message = stack.join('');
			
			message = message.replace(/\n/g, '<br>');
			
			return message;
		},
		
		/**
		 * Converts special characters to their entities.
		 * 
		 * @param	{string}	message		message containing BBCodes
		 * @return	{string}	message with replaced special characters
		 */
		_convertSpecials: function(message) {
			message = message.replace(/&/g, '&amp;');
			message = message.replace(/</g, '&lt;');
			message = message.replace(/>/g, '&gt;');
			
			return message;
		},
		
		/**
		 * Sets up converters applied to HTML elements.
		 */
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
				align: this._convertAlignment.bind(this),
				attach: this._convertAttachment.bind(this),
				color: this._convertColor.bind(this),
				code: this._convertCode.bind(this),
				email: this._convertEmail.bind(this),
				list: this._convertList.bind(this),
				quote: this._convertQuote.bind(this),
				size: this._convertSize.bind(this),
				url: this._convertUrl.bind(this),
				img: this._convertImage.bind(this)
			};
			
			_removeNewlineAfter = ['quote', 'table', 'td', 'tr'];
			_removeNewlineBefore = ['table', 'td', 'tr'];
			
			EventHandler.fire('com.woltlab.wcf.bbcode.toHtml', 'init', {
				bbcodes: _bbcodes,
				removeNewlineAfter: _removeNewlineAfter,
				removeNewlineBefore: _removeNewlineBefore
			});
		},
		
		/**
		 * Converts an item from the stack.
		 * 
		 * @param	{array<mixed>}		stack		linear list of BBCode tags and regular strings
		 * @param	{object}		item		current BBCode tag object
		 * @param	{int}			index		current stack index representing `item`
		 * @return	{(string|array)}	string if only the current item should be replaced or an array with
		 * 					the first item used for the opening tag and the second item for the closing tag
		 */
		_convert: function(stack, item, index) {
			var replace = _bbcodes[item.name], tmp;
			
			if (replace === undefined) {
				// treat as plain text
				return [null, null];
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
			this._convertSmilies(stack);
			
			if (typeof replace === 'string') {
				return ['<' + replace + '>', '</' + replace + '>'];
			}
			else {
				return replace(stack, item, index);
			}
		},
		
		/**
		 * Converts [align] into <div style="text-align: ...">.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 * @param	{object}	item	current BBCode tag object
		 * @param	{int}		index	current stack index representing `item`
		 * @returns	{array}		first item represents the opening tag, the second the closing one
		 */
		_convertAlignment: function(stack, item, index) {
			var align = (item.attributes.length) ? item.attributes[0] : '';
			if (['center', 'justify', 'left', 'right'].indexOf(align) === -1) {
				return [null, null];
			}
			
			return ['<div style="text-align: ' + align + '">', '</div>'];
		},
		
		/**
		 * Converts [attach] into an <img> or to plain text if attachment is a non-image.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 * @param	{object}	item	current BBCode tag object
		 * @param	{int}		index	current stack index representing `item`
		 * @returns	{array}		first item represents the opening tag, the second the closing one
		 */
		_convertAttachment: function(stack, item, index) {
			var attachmentId = 0, attributes = item.attributes, length = attributes.length;
			if (!_options.attachments.url) {
				length = 0;
			}
			else if (length > 0) {
				attachmentId = ~~attributes[0];
				if (!objOwns(_options.attachments.images, attachmentId)) {
					length = 0;
				}
			}
			
			if (length === 0) {
				return [null, null];
			}
			
			var maxHeight = ~~_options.attachments.images[attachmentId].height;
			var maxWidth = ~~_options.attachments.images[attachmentId].width;
			var styles = ['max-height: ' + maxHeight + 'px', 'max-width: ' + maxWidth + 'px'];
			
			if (length > 1) {
				if (item.attributes[1] === 'left' || attributes[1] === 'right') {
					styles.push('float: ' + attributes[1]);
					styles.push('margin: ' + (attributes[1] === 'left' ? '0 15px 7px 0' : '0 0 7px 15px'));
				}
			}
			
			var width, baseUrl = _options.attachments.thumbnailUrl;
			if (length > 2) {
				width = ~~attributes[2] || 0;
				if (width) {
					if (width > maxWidth) width = maxWidth;
					
					styles.push('width: ' + width + 'px');
					baseUrl = _options.attachments.url;
				}
			}
			
			return [
				'<img src="' + baseUrl.replace(/987654321/, attachmentId) + '" class="redactorEmbeddedAttachment redactorDisableResize" data-attachment-id="' + attachmentId + '"' + (styles.length ? ' style="' + styles.join(';') + '"' : '') + '>',
				''
			];
		},
		
		/**
		 * Converts [code] to <div class="codeBox">.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 * @param	{object}	item	current BBCode tag object
		 * @param	{int}		index	current stack index representing `item`
		 * @returns	{array}		first item represents the opening tag, the second the closing one
		 */
		_convertCode: function(stack, item, index) {
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
			
			return [
				'<div class="codeBox container" contenteditable="false" data-highlighter="' + highlighter + '" data-filename="' + (filename ? StringUtil.escapeHTML(filename) : '') + '">'
					+ '<div>'
					+ '<div>'
						+ '<h3>' + __REDACTOR_CODE_HIGHLIGHTERS[highlighter] + (filename ? ': ' + StringUtil.escapeHTML(filename) : '') + '</h3>'
					+ '</div>'
					+ '<ol start="' + (lineNumber > 1 ? lineNumber : 1) + '">',
				'</ol></div></div>'
			];
		},
		
		/**
		 * Converts [color] to <span style="color: ...">.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 * @param	{object}	item	current BBCode tag object
		 * @param	{int}		index	current stack index representing `item`
		 * @returns	{array}		first item represents the opening tag, the second the closing one
		 */
		_convertColor: function(stack, item, index) {
			if (!item.attributes.length || !item.attributes[0].match(/^[a-z0-9#]+$/i)) {
				return [null, null];
			}
			
			return ['<span style="color: ' + StringUtil.escapeHTML(item.attributes[0]) + '">', '</span>'];
		},
		
		/**
		 * Converts [email] to <a href="mailto: ...">.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 * @param	{object}	item	current BBCode tag object
		 * @param	{int}		index	current stack index representing `item`
		 * @returns	{array}		first item represents the opening tag, the second the closing one
		 */
		_convertEmail: function(stack, item, index) {
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
					return [null, null];
				}
			}
			
			return ['<a href="mailto:' + StringUtil.escapeHTML(email) + '">', '</a>'];
		},
		
		/**
		 * Converts [img] to <img>.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 * @param	{object}	item	current BBCode tag object
		 * @param	{int}		index	current stack index representing `item`
		 * @returns	{array}		first item represents the opening tag, the second the closing one
		 */
		_convertImage: function(stack, item, index) {
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
			
			return ['<img src="' + StringUtil.escapeHTML(source) + '"' + (styles.length ? ' style="' + styles.join(';') + '"' : '') + '>', ''];
		},
		
		/**
		 * Converts [list] to <ol> or <ul>.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 * @param	{object}	item	current BBCode tag object
		 * @param	{int}		index	current stack index representing `item`
		 * @returns	{array}		first item represents the opening tag, the second the closing one
		 */
		_convertList: function(stack, item, index) {
			var type = (item.attributes.length) ? item.attributes[0] : '';
			
			// replace list items
			for (var i = index + 1; i < item.pair; i++) {
				if (typeof stack[i] === 'string') {
					stack[i] = stack[i].replace(/\[\*\]/g, '<li>');
				}
			}
			
			if (type == '1' || type === 'decimal') {
				return ['<ol>', '</ol>'];
			}
			
			if (type.length && type.match(/^(?:none|circle|square|disc|decimal|lower-roman|upper-roman|decimal-leading-zero|lower-greek|lower-latin|upper-latin|armenian|georgian)$/)) {
				return ['<ul style="list-style-type: ' + type + '">', '</ul>'];
			}
			
			return ['<ul>', '</ul>'];
		},
		
		/**
		 * Converts [quote] to <blockquote>.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 * @param	{object}	item	current BBCode tag object
		 * @param	{int}		index	current stack index representing `item`
		 * @returns	{array}		first item represents the opening tag, the second the closing one
		 */
		_convertQuote: function(stack, item, index) {
			var author = '', link = '';
			if (item.attributes.length > 1) {
				author = item.attributes[0];
				link = item.attributes[1];
			}
			else if (item.attributes.length === 1) {
				author = item.attributes[0];
			}
			
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
			
			return [
				'<blockquote class="quoteBox container containerPadding quoteBoxSimple" cite="' + StringUtil.escapeHTML(link) + '" data-author="' + StringUtil.escapeHTML(author) + '">'
					+ '<header contenteditable="false">'
						+ '<h3>'
							+ header
						+ '</h3>'
						+ '<a class="redactorQuoteEdit"></a>'
					+ '</header>'
					+ '<div>\u200b',
				'</div></blockquote>'
			];
		},
		
		/**
		 * Converts smiley codes into <img>.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 */
		_convertSmilies: function(stack) {
			var altValue, item, regexp;
			for (var i = 0, length = stack.length; i < length; i++) {
				item = stack[i];
				
				if (typeof item === 'string') {
					for (var smileyCode in __REDACTOR_SMILIES) {
						if (objOwns(__REDACTOR_SMILIES, smileyCode)) {
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
		
		/**
		 * Converts [size] to <span style="font-size: ...">.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 * @param	{object}	item	current BBCode tag object
		 * @param	{int}		index	current stack index representing `item`
		 * @returns	{array}		first item represents the opening tag, the second the closing one
		 */
		_convertSize: function(stack, item, index) {
			if (!item.attributes.length || ~~item.attributes[0] === 0) {
				return [null, null];
			}
			
			return ['<span style="font-size: ' + ~~item.attributes[0] + 'pt">', '</span>'];
		},
		
		/**
		 * Converts [url] to <a>.
		 * 
		 * @param	{array<mixed>}	stack	linear list of BBCode tags and regular strings
		 * @param	{object}	item	current BBCode tag object
		 * @param	{int}		index	current stack index representing `item`
		 * @returns	{array}		first item represents the opening tag, the second the closing one
		 */
		_convertUrl: function(stack, item, index) {
			// ignore url bbcode without arguments
			if (!item.attributes.length) {
				return [null, null];
			}
			
			return ['<a href="' + StringUtil.escapeHTML(item.attributes[0]) + '">', '</a>'];
		}
	};
	
	return BbcodeToHtml;
});
