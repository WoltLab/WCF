/**
 * Converts a message containing HTML tags into BBCodes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Bbcode/FromHtml
 */
define(['EventHandler', 'StringUtil', 'Dom/Traverse'], function(EventHandler, StringUtil, DomTraverse) {
	"use strict";
	
	var _converter = [];
	var _inlineConverter = {};
	var _sourceConverter = [];
	
	/**
	 * Returns true if a whitespace should be inserted before or after the smiley.
	 * 
	 * @param	{Element}	element		image element
	 * @param	{boolean}	before		evaluate previous node
	 * @return	{boolean}	true if a whitespace should be inserted
	 */
	function addSmileyPadding(element, before) {
		var target = element[(before ? 'previousSibling' : 'nextSibling')];
		if (target === null || target.nodeType !== Node.TEXT_NODE || !/\s$/.test(target.textContent)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @module	WoltLab/WCF/Bbcode/FromHtml
	 */
	var BbcodeFromHtml = {
		/**
		 * Converts a message containing HTML elements into BBCodes.
		 * 
		 * @param	{string}	message		message containing HTML elements
		 * @return	{string}	message containing BBCodes
		 */
		convert: function(message) {
			if (message.length) this._setup();
			
			var container = document.createElement('div');
			container.innerHTML = message;
			
			// convert line breaks
			var elements = container.getElementsByTagName('P');
			while (elements.length) elements[0].outerHTML = elements[0].innerHTML;
			
			elements = container.getElementsByTagName('BR');
			while (elements.length) elements[0].outerHTML = "\n";
			
			// prevent conversion taking place inside source bbcodes
			var sourceElements = this._preserveSourceElements(container);
			
			EventHandler.fire('com.woltlab.wcf.bbcode.fromHtml', 'beforeConvert', { container: container });
			
			for (var i = 0, length = _converter.length; i < length; i++) {
				this._convert(container, _converter[i]);
			}
			
			EventHandler.fire('com.woltlab.wcf.bbcode.fromHtml', 'afterConvert', { container: container });
			
			this._restoreSourceElements(container, sourceElements);
			
			// remove remaining HTML elements
			elements = container.getElementsByTagName('*');
			while (elements.length) elements[0].outerHTML = elements[0].innerHTML;
			
			message = this._convertSpecials(container.innerHTML);
			
			return message;
		},
		
		/**
		 * Replaces HTML elements mapping to source BBCodes to avoid
		 * them being handled by other converters.
		 * 
		 * @param	{Element}	container	container element
		 * @return	{array<object>}	list of source elements and their placeholder
		 */
		_preserveSourceElements: function(container) {
			var elements, sourceElements = [], tmp;
			
			for (var i = 0, length = _sourceConverter.length; i < length; i++) {
				elements = container.querySelectorAll(_sourceConverter[i].selector);
				
				tmp = [];
				for (var j = 0, innerLength = elements.length; j < innerLength; j++) {
					this._preserveSourceElement(elements[j], tmp);
				}
				
				sourceElements.push(tmp);
			}
			
			return sourceElements;
		},
		
		/**
		 * Replaces an element with a placeholder.
		 * 
		 * @param	{Element}	element		target element
		 * @param	{array<object>}	list of removed elements and their placeholders
		 */
		_preserveSourceElement: function(element, sourceElements) {
			var placeholder = document.createElement('var');
			placeholder.setAttribute('data-source', 'wcf');
			element.parentNode.insertBefore(placeholder, element);
			
			var fragment = document.createDocumentFragment();
			fragment.appendChild(element);
			
			sourceElements.push({
				fragment: fragment,
				placeholder: placeholder
			});
		},
		
		/**
		 * Reinserts source elements for parsing.
		 * 
		 * @param	{Element}	container	container element
		 * @param	{array<object>}	sourceElements	list of removed elements and their placeholders
		 */
		_restoreSourceElements: function(container, sourceElements) {
			var element, elements, placeholder;
			for (var i = 0, length = sourceElements.length; i < length; i++) {
				elements = sourceElements[i];
				
				if (elements.length === 0) {
					continue;
				}
				
				for (var j = 0, innerLength = elements.length; j < innerLength; j++) {
					element = elements[j];
					placeholder = element.placeholder;
					
					placeholder.parentNode.insertBefore(element.fragment, placeholder);
					
					_sourceConverter[i].callback(placeholder.previousElementSibling);
					
					placeholder.parentNode.removeChild(placeholder);
				}
			}
		},
		
		/**
		 * Converts special entities.
		 * 
		 * @param	{string}	message		HTML message
		 * @return	{string}	HTML message
		 */
		_convertSpecials: function(message) {
			message = message.replace(/&amp;/g, '&');
			message = message.replace(/&lt;/g, '<');
			message = message.replace(/&gt;/g, '>');
			
			return message;
		},
		
		/**
		 * Sets up converters applied to elements in linear order.
		 */
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
				{ tagName: 'KBD', bbcode: 'tt' },
				
				// callback replacement
				{ tagName: 'A', callback: this._convertUrl.bind(this) },
				{ tagName: 'IMG', callback: this._convertImage.bind(this) },
				{ tagName: 'LI', callback: this._convertListItem.bind(this) },
				{ tagName: 'OL', callback: this._convertList.bind(this) },
				{ tagName: 'TABLE', callback: this._convertTable.bind(this) },
				{ tagName: 'UL', callback: this._convertList.bind(this) },
				{ tagName: 'BLOCKQUOTE', callback: this._convertBlockquote.bind(this) },
				
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
			
			_sourceConverter = [
				{ selector: 'div.codeBox', callback: this._convertSourceCodeBox.bind(this) }
			];
			
			EventHandler.fire('com.woltlab.wcf.bbcode.fromHtml', 'init', {
				converter: _converter,
				inlineConverter: _inlineConverter,
				sourceConverter: _sourceConverter
			});
		},
		
		/**
		 * Converts an element into a raw string.
		 * 
		 * @param	{Element}	container	container element
		 * @param	{object}	converter	converter object
		 */
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
		
		/**
		 * Converts <blockquote> into [quote].
		 * 
		 * @param	{Element}	element		target element
		 */
		_convertBlockquote: function(element) {
			var author = element.getAttribute('data-author') || '';
			var link = element.getAttribute('cite') || '';
			
			var open = '[quote]';
			if (author) {
				author = StringUtil.escapeHTML(author).replace(/(\\)?'/g, function(match, isEscaped) { return isEscaped ? match : "\\'"; });
				if (link) {
					open = "[quote='" + author + "','" + StringUtil.escapeHTML(link) + "']";
				}
				else {
					open = "[quote='" + author + "']";
				}
			}
			
			var header = DomTraverse.childByTag(element, 'HEADER');
			if (header !== null) element.removeChild(header);
			
			var divs = DomTraverse.childrenByTag(element, 'DIV');
			for (var i = 0, length = divs.length; i < length; i++) {
				divs[i].outerHTML = divs[i].innerHTML + '\n';
			}
			
			element.outerHTML = open + element.innerHTML.replace(/^\n*/, '').replace(/\n*$/, '') + '[/quote]\n';
		},
		
		/**
		 * Converts <img> into smilies, [attach] or [img].
		 * 
		 * @param	{Element}	element		target element
		 */
		_convertImage: function(element) {
			if (element.classList.contains('smiley')) {
				// smiley
				element.outerHTML = (addSmileyPadding(element, true) ? ' ' : '') + element.getAttribute('alt') + (addSmileyPadding(element, false) ? ' ' : '');
				return;
			}
			
			var float = element.style.getPropertyValue('float') || 'none';
			var width = element.style.getPropertyValue('width');
			width = (typeof width === 'string') ? ~~width.replace(/px$/, '') : 0;
			
			if (element.classList.contains('redactorEmbeddedAttachment')) {
				var attachmentId = element.getAttribute('data-attachment-id');
				
				if (width > 0) {
					element.outerHTML = "[attach=" + attachmentId + "," + float + "," + width + "][/attach]";
				}
				else if (float !== 'none') {
					element.outerHTML = "[attach=" + attachmentId + "," + float + "][/attach]";
				}
				else {
					element.outerHTML = "[attach=" + attachmentId + "][/attach]";
				}
			}
			else {
				// regular image
				var source = element.src.trim();
				
				if (width > 0) {
					element.outerHTML = "[img='" + source + "'," + float + "," + width + "][/img]";
				}
				else if (float !== 'none') {
					element.outerHTML = "[img='" + source + "'," + float + "][/img]";
				}
				else {
					element.outerHTML = "[img]" + source + "[/img]";
				}
			}
		},
		
		/**
		 * Converts <ol> and <ul> into [list].
		 * 
		 * @param	{Element}	element		target element
		 */
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
		
		/**
		 * Converts <li> into [*] unless it is not encapsulated in <ol> or <ul>.
		 * 
		 * @param	{Element}	element		target element
		 */
		_convertListItem: function(element) {
			if (element.parentNode.nodeName !== 'UL' && element.parentNode.nodeName !== 'OL') {
				element.outerHTML = element.innerHTML;
			}
			else {
				element.outerHTML = '[*]' + element.innerHTML;
			}
		},
		
		/**
		 * Converts <span> into a series of BBCodes including [color], [font] and [size].
		 * 
		 * @param	{Element}	element		target element
		 */
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
		
		/**
		 * Converts <div> into a series of BBCodes including [align].
		 * 
		 * @param	{Element}	element		target element
		 */
		_convertDiv: function(element) {
			if (element.className.length || element.style.length) {
				var converter, value;
				for (var i = 0, length = _inlineConverter.div.length; i < length; i++) {
					converter = _inlineConverter.div[i];
					
					if (converter.className && element.classList.contains(converter.className)) {
						converter.callback(element);
					}
					else if (converter.style) {
						value = element.style.getPropertyValue(converter.style) || '';
						if (value) {
							converter.callback(element, value);
						}
					}
				}
			}
			
			element.outerHTML = element.innerHTML;
		},
		
		/**
		 * Converts the CSS style `color` into [color].
		 * 
		 * @param	{Element}	element		target element
		 */
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
		
		/**
		 * Converts the CSS style `font-size` into [size].
		 * 
		 * @param	{Element}	element		target element
		 */
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
		
		/**
		 * Converts the CSS style `font-family` into [font].
		 * 
		 * @param	{Element}	element		target element
		 */
		_convertInlineFontFamily: function(element, value) {
			element.innerHTML = '[font=' + value.replace(/'/g, '') + ']' + element.innerHTML + '[/font]';
		},
		
		/**
		 * Converts the CSS style `text-align` into [align].
		 * 
		 * @param	{Element}	element		target element
		 */
		_convertInlineTextAlign: function(element, value) {
			if (['center', 'justify', 'left', 'right'].indexOf(value) !== -1) {
				element.innerHTML = '[align=' + value + ']' + element.innerHTML + '[/align]';
			}
		},
		
		/**
		 * Converts tables and their children into BBCodes.
		 * 
		 * @param	{Element}	element		target element
		 */
		_convertTable: function(element) {
			var elements = element.getElementsByTagName('TD');
			while (elements.length) {
				elements[0].outerHTML = '[td]' + elements[0].innerHTML + '[/td]\n';
			}
			
			elements = element.getElementsByTagName('TR');
			while (elements.length) {
				elements[0].outerHTML = '\n[tr]\n' + elements[0].innerHTML + '[/tr]';
			}
			
			var tbody = DomTraverse.childByTag(element, 'TBODY');
			var innerHtml = (tbody === null) ? element.innerHTML : tbody.innerHTML;
			element.outerHTML = '\n[table]' + innerHtml + '\n[/table]\n';
		},
		
		/**
		 * Converts <a> into [email] or [url].
		 * 
		 * @param	{Element}	element		target element
		 */
		_convertUrl: function(element) {
			var content = element.textContent.trim(), href = element.href.trim(), tagName = 'url';
			
			if (href === '' || content === '') {
				// empty href or content
				element.outerHTML = element.innerHTML;
				return;
			}
			
			if (href.indexOf('mailto:') === 0) {
				href = href.substr(7);
				tagName = 'email';
			}
			
			if (href === content) {
				element.outerHTML = '[' + tagName + ']' + href + '[/' + tagName + ']';
			}
			else {
				element.outerHTML = "[" + tagName + "='" + href + "']" + element.innerHTML + "[/" + tagName + "]";
			}
		},
		
		/**
		 * Converts <div class="codeBox"> into [code].
		 * 
		 * @param	{Element}	element		target element
		 */
		_convertSourceCodeBox: function(element) {
			var filename = element.getAttribute('data-filename').trim() || '';
			var highlighter = element.getAttribute('data-highlighter') || '';
			window.dtdesign = element;
			var list = DomTraverse.childByTag(element.children[0], 'OL');
			var lineNumber = ~~list.getAttribute('start') || 1;
			
			var content = '';
			for (var i = 0, length = list.childElementCount; i < length; i++) {
				if (content) content += "\n";
				content += list.children[i].textContent;
			}
			
			var open = "[code='" + highlighter + "'," + lineNumber + ",'" + filename + "']";
			
			element.outerHTML = open + content + '[/code]';
		}
	};
	
	return BbcodeFromHtml;
});
