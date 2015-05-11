/**
 * Provides helper functions to work with DOM nodes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/DOM/Util
 */
define(function() {
	"use strict";
	
	var _matchesSelectorFunction = '';
	var _possibleFunctions = ['matches', 'webkitMatchesSelector', 'mozMatchesSelector', 'msMatchesSelector'];
	for (var i = 0; i < 4; i++) {
		if (Element.prototype.hasOwnProperty(_possibleFunctions[i])) {
			_matchesSelectorFunction = _possibleFunctions[i];
			break;
		}
	}
	
	var _idCounter = 0;
	
	/**
	 * @constructor
	 */
	function DOMUtil() {};
	DOMUtil.prototype = {
		/**
		 * Returns a unique element id.
		 * 
		 * @return	{string}	unique id
		 */
		getUniqueId: function() {
			var elementId;
			
			do {
				elementId = 'wcf' + _idCounter++;
			}
			while (document.getElementById(elementId) !== null);
			
			return elementId;
		},
		
		/**
		 * Returns the element's id. If there is no id set, a unique id will be
		 * created and assigned.
		 * 
		 * @param	{Element}	el	element
		 * @return	{string}	element id
		 */
		identify: function(el) {
			if (!el || !(el instanceof Element)) {
				return null;
			}
			
			var id = el.getAttribute('id');
			if (!id) {
				id = this.getUniqueId();
				el.setAttribute('id', id);
			}
			
			return id;
		},
		
		/**
		 * Returns true if element matches given CSS selector.
		 * 
		 * @param	{Element}	el		element
		 * @param	{string}	selector	CSS selector
		 * @return	{boolean}	true if element matches selector
		 */
		matches: function(el, selector) {
			return el[_matchesSelectorFunction](selector);
		},
		
		/**
		 * Returns the outer height of an element including margins.
		 * 
		 * @param	{Element}		el		element
		 * @param	{CSSStyleDeclaration=}	styles		result of window.getComputedStyle()
		 * @return	{integer}	outer height in px
		 */
		outerHeight: function(el, styles) {
			styles = styles || window.getComputedStyle(el);
			
			var height = el.offsetHeight;
			height += ~~styles.marginTop + ~~styles.marginBottom;
			
			return height;
		},
		
		/**
		 * Returns the outer width of an element including margins.
		 * 
		 * @param	{Element}		el		element
		 * @param	{CSSStyleDeclaration=}	styles		result of window.getComputedStyle()
		 * @return	{integer}	outer width in px
		 */
		outerWidth: function(el, styles) {
			styles = styles || window.getComputedStyle(el);
			
			var width = el.offsetWidth;
			width += ~~styles.marginLeft + ~~styles.marginRight;
			
			return width;
		},
		
		/**
		 * Returns the outer dimensions of an element including margins.
		 * 
		 * @param	{Element}		el		element
		 * @return	{{height: integer, width: integer}}	dimensions in px
		 */
		outerDimensions: function(el) {
			var styles = window.getComputedStyle(el);
			
			return {
				height: this.outerHeight(el, styles),
				width: this.outerWidth(el, styles)
			};
		},
		
		/**
		 * Returns the element's offset relative to the document's top left corner.
		 * 
		 * @param	{Element}	el	element
		 * @return	{{left: integer, top: integer}}		offset relative to top left corner
		 */
		offset: function(el) {
			var rect = el.getBoundingClientRect();
			
			return {
				top: rect.top + document.body.scrollTop,
				left: rect.left + document.body.scrollLeft
			};
		},
		
		/**
		 * Prepends an element to a parent element.
		 * 
		 * @param	{Element}	el		element to prepend
		 * @param	{Element}	parentEl	future containing element
		 */
		prepend: function(el, parentEl) {
			if (parentEl.childElementCount === 0) {
				parentEl.appendChild(el);
			}
			else {
				parentEl.insertBefore(el, parentEl.children[0]);
			}
		},
		
		/**
		 * Applies a list of CSS properties to an element.
		 * 
		 * @param	{Element}		el	element
		 * @param	{Object<string, mixed>}	styles	list of CSS styles
		 */
		setStyles: function(el, styles) {
			for (var property in styles) {
				if (styles.hasOwnProperty(property)) {
					el.style.setProperty(property, styles[property]);
				}
			}
		},
		
		/**
		 * Returns a style property value as integer.
		 * 
		 * The behavior of this method is undefined for properties that are not considered
		 * to have a "numeric" value, e.g. "background-image".
		 * 
		 * @param	{CSSStyleDeclaration}	styles		result of window.getComputedStyle()
		 * @param	{string}		propertyName	property name
		 * @return	{integer}	property value as integer
		 */
		styleAsInt: function(styles, propertyName) {
			var value = styles.getPropertyValue(propertyName);
			if (value === null) {
				return 0;
			}
			
			return parseInt(value);
		}
	};
	
	var domUtilObj = new DOMUtil();
	
	// expose methods on the window object for backwards-compatibility
	window.domUtilGetUniqueId = domUtilObj.getUniqueId.bind(domUtilObj);
	window.domUtilIdentify = domUtilObj.identify.bind(domUtilObj);
	
	return domUtilObj;
});
