/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Redactor/Metacode
 */
define(['Dom/Util'], function(DomUtil) {
	"use strict";
	
	/**
	 * @exports     WoltLab/WCF/Ui/Redactor/Metacode
	 */
	return {
		/**
		 * Converts `<woltlab-metacode>` into the bbcode representation.
		 * 
		 * @param       {Element}       element         textarea element
		 */
		convert: function(element) {
			var div = elCreate('div');
			div.innerHTML = element.textContent;
			
			var attributes, metacode, metacodes = elByTag('woltlab-metacode', div), name, tagClose, tagOpen;
			while (metacodes.length) {
				metacode = metacodes[0];
				name = elData(metacode, 'name');
				attributes = elData(metacode, 'attributes');
				
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
			
			element.textContent = div.innerHTML;
		},
		
		/**
		 * Returns a text node representing the opening bbcode tag.
		 * 
		 * @param       {string}        name            bbcode tag
		 * @param       {string}        attributes      base64- and JSON-encoded attributes
		 * @returns     {Text}          text node containing the opening bbcode tag
		 * @protected
		 */
		_getOpeningTag: function(name, attributes) {
			try {
				attributes = JSON.parse(atob(attributes));
			}
			catch (e) { /* invalid base64 data or invalid json */ }
			
			if (!Array.isArray(attributes)) {
				attributes = [];
			}
			
			var buffer = '[' + name;
			if (attributes.length) {
				buffer += '=' + attributes.join(',');
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
		}
	};
});
