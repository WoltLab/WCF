/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Redactor/Metacode
 */
define(['EventHandler', 'Dom/Util'], function(EventHandler, DomUtil) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			convert: function() {},
			convertFromHtml: function() {},
			_getOpeningTag: function() {},
			_getClosingTag: function() {},
			_getFirstParagraph: function() {},
			_getLastParagraph: function() {},
			_parseAttributes: function() {}
		};
		return Fake;
	}
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/Redactor/Metacode
	 */
	return {
		/**
		 * Converts `<woltlab-metacode>` into the bbcode representation.
		 * 
		 * @param       {Element}       element         textarea element
		 */
		convert: function(element) {
			element.textContent = this.convertFromHtml(element.textContent);
		},
		
		convertFromHtml: function (editorId, html) {
			var div = elCreate('div');
			div.innerHTML = html;
			
			var attributes, data, metacode, metacodes = elByTag('woltlab-metacode', div), name, tagClose, tagOpen;
			while (metacodes.length) {
				metacode = metacodes[0];
				name = elData(metacode, 'name');
				attributes = this._parseAttributes(elData(metacode, 'attributes'));
				
				data = {
					attributes: attributes,
					cancel: false,
					metacode: metacode
				};
				
				EventHandler.fire('com.woltlab.wcf.redactor2', 'metacode_' + name + '_' + editorId, data);
				if (data.cancel === true) {
					continue;
				}
				
				tagOpen = this._getOpeningTag(name, attributes);
				tagClose = this._getClosingTag(name);
				
				if (metacode.parentNode === div) {
					DomUtil.prepend(tagOpen, this._getFirstParagraph(metacode));
					this._getLastParagraph(metacode).appendChild(tagClose);
				}
				else {
					DomUtil.prepend(tagOpen, metacode);
					metacode.appendChild(tagClose);
				}
				
				DomUtil.unwrapChildNodes(metacode);
			}
			
			// convert `<kbd>…</kbd>` to `[tt]…[/tt]`
			var inlineCode, inlineCodes = elByTag('kbd', div);
			while (inlineCodes.length) {
				inlineCode = inlineCodes[0];
				
				inlineCode.insertBefore(document.createTextNode('[tt]'), inlineCode.firstChild);
				inlineCode.appendChild(document.createTextNode('[/tt]'));
				
				DomUtil.unwrapChildNodes(inlineCode);
			}
			
			return div.innerHTML;
		},
		
		/**
		 * Returns a text node representing the opening bbcode tag.
		 * 
		 * @param       {string}        name            bbcode tag
		 * @param       {Array}         attributes      list of attributes
		 * @returns     {Text}          text node containing the opening bbcode tag
		 * @protected
		 */
		_getOpeningTag: function(name, attributes) {
			var buffer = '[' + name;
			if (attributes.length) {
				buffer += '=';
				
				for (var i = 0, length = attributes.length; i < length; i++) {
					if (i > 0) buffer += ",";
					buffer += "'" + attributes[i] + "'";
				}
			}
			
			return document.createTextNode(buffer + ']');
		},
		
		/**
		 * Returns a text node representing the closing bbcode tag.
		 * 
		 * @param       {string}        name            bbcode tag
		 * @returns     {Text}          text node containing the closing bbcode tag
		 * @protected
		 */
		_getClosingTag: function(name) {
			return document.createTextNode('[/' + name + ']');
		},
		
		/**
		 * Returns the first paragraph of provided element. If there are no children or
		 * the first child is not a paragraph, a new paragraph is created and inserted
		 * as first child.
		 * 
		 * @param       {Element}       element         metacode element
		 * @returns     {Element}       paragraph that is the first child of provided element
		 * @protected
		 */
		_getFirstParagraph: function (element) {
			var firstChild, paragraph;
			
			if (element.childElementCount === 0) {
				paragraph = elCreate('p');
				element.appendChild(paragraph);
			}
			else {
				firstChild = element.children[0];
				
				if (firstChild.nodeName === 'P') {
					paragraph = firstChild;
				}
				else {
					paragraph = elCreate('p');
					element.insertBefore(paragraph, firstChild);
				}
			}
			
			return paragraph;
		},
		
		/**
		 * Returns the last paragraph of provided element. If there are no children or
		 * the last child is not a paragraph, a new paragraph is created and inserted
		 * as last child.
		 * 
		 * @param       {Element}       element         metacode element
		 * @returns     {Element}       paragraph that is the last child of provided element
		 * @protected
		 */
		_getLastParagraph: function (element) {
			var count = element.childElementCount, lastChild, paragraph;
			
			if (count === 0) {
				paragraph = elCreate('p');
				element.appendChild(paragraph);
			}
			else {
				lastChild = element.children[count - 1];
				
				if (lastChild.nodeName === 'P') {
					paragraph = lastChild;
				}
				else {
					paragraph = elCreate('p');
					element.appendChild(paragraph);
				}
			}
			
			return paragraph;
		},
		
		/**
		 * Parses the attributes string.
		 * 
		 * @param       {string}        attributes      base64- and JSON-encoded attributes
		 * @return      {Array}         list of parsed attributes
		 * @protected
		 */
		_parseAttributes: function(attributes) {
			try {
				attributes = JSON.parse(atob(attributes));
			}
			catch (e) { /* invalid base64 data or invalid json */ }
			
			if (!Array.isArray(attributes)) {
				return [];
			}
			
			var attribute, parsedAttributes = [];
			for (var i = 0, length = attributes.length; i < length; i++) {
				attribute = attributes[i];
				
				if (typeof attribute === 'string') {
					attribute = attribute.replace(/^'(.*)'$/, '$1');
				}
				
				parsedAttributes.push(attribute);
			}
			
			return parsedAttributes;
		}
	};
});
