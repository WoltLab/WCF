/**
 * Collection of global short hand functions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
(function(window, document) {
	/**
	 * Shorthand function to retrieve or set an attribute.
	 * 
	 * @param	{Element}	element		target element
	 * @param	{string}	attribute	attribute name
	 * @param	{?=}            value		attribute value, omit if attribute should be read
	 * @return	{(string|undefined)}		attribute value, empty string if attribute is not set or undefined if `value` was omitted
	 */
	window.elAttr = function(element, attribute, value) {
		if (value === undefined) {
			return element.getAttribute(attribute) || '';
		}
		
		element.setAttribute(attribute, value);
	};
	
	/**
	 * Shorthand function to retrieve a boolean attribute.
	 * 
	 * @param	{Element}	element		target element
	 * @param	{string}	attribute	attribute name
	 * @return	{boolean}	true if value is either `1` or `true`
	 */
	window.elAttrBool = function(element, attribute) {
		var value = elAttr(element, attribute);
		
		return (value === "1" || value === "true");
	};
	
	/**
	 * Shorthand function to find elements by class name.
	 * 
	 * @param	{string}	className	CSS class name
	 * @param	{Element=}	context		target element, assuming `document` if omitted
	 * @return	{NodeList}	matching elements
	 */
	window.elByClass = function(className, context) {
		return (context || document).getElementsByClassName(className);
	};
	
	/**
	 * Shorthand function to retrieve an element by id.
	 * 
	 * @param	{string}	id	element id
	 * @return	{(Element|null)}	matching element or null if not found
	 */
	window.elById = function(id) {
		return document.getElementById(id);
	};
	
	/**
	 * Shorthand function to find an element by CSS selector.
	 * 
	 * @param	{string}	selector	CSS selector
	 * @param	{Element=}	context		target element, assuming `document` if omitted
	 * @return	{(Element|null)}		matching element or null if no match
	 */
	window.elBySel = function(selector, context) {
		return (context || document).querySelector(selector);
	};
	
	/**
	 * Shorthand function to find elements by CSS selector.
	 * 
	 * @param	{string}	selector	CSS selector
	 * @param	{Element=}	context		target element, assuming `document` if omitted
	 * @param       {function=}     callback        callback function pased to forEach()
	 * @return	{NodeList}	matching elements
	 */
	window.elBySelAll = function(selector, context, callback) {
		var nodeList = (context || document).querySelectorAll(selector);
		if (typeof callback === 'function') {
			Array.prototype.forEach.call(nodeList, callback);
		}
		
		return nodeList;
	};
	
	/**
	 * Shorthand function to find elements by tag name.
	 * 
	 * @param	{string}	tagName		element tag name
	 * @param	{Element=}	context		target element, assuming `document` if omitted
	 * @return	{NodeList}	matching elements
	 */
	window.elByTag = function(tagName, context) {
		return (context || document).getElementsByTagName(tagName);
	};
	
	/**
	 * Shorthand function to create a DOM element.
	 * 
	 * @param	{string}	tagName		element tag name
	 * @return	{Element}	new DOM element
	 */
	window.elCreate = function(tagName) {
		return document.createElement(tagName);
	};
	
	/**
	 * Shorthand function to retrieve or set a 'data-' attribute.
	 * 
	 * @param	{Element}	element		target element
	 * @param	{string}	attribute	attribute name
	 * @param	{?=}            value		attribute value, omit if attribute should be read
	 * @return	{(string|undefined)}		attribute value, empty string if attribute is not set or undefined if `value` was omitted
	 */
	window.elData = function(element, attribute, value) {
		attribute = 'data-' + attribute;
		
		if (value === undefined) {
			return element.getAttribute(attribute) || '';
		}
		
		element.setAttribute(attribute, value);
	};
	
	/**
	 * Shorthand function to retrieve a boolean 'data-' attribute.
	 * 
	 * @param	{Element}	element		target element
	 * @param	{string}	attribute	attribute name
	 * @return	{boolean}	true if value is either `1` or `true`
	 */
	window.elDataBool = function(element, attribute) {
		var value = elData(element, attribute);
		
		return (value === "1" || value === "true");
	}; 
	
	/**
	 * Shorthand function to hide an element by setting its 'display' value to 'none'.
	 * 
	 * @param	{Element}	element		DOM element
	 */
	window.elHide = function(element) {
		element.style.setProperty('display', 'none', '');
	};
	
	/**
	 * Shorthand function to remove an element.
	 * 
	 * @param	{Node}	        element		DOM node
	 */
	window.elRemove = function(element) {
		element.parentNode.removeChild(element);
	};
	
	/**
	 * Shorthand function to show an element previously hidden by using `elHide()`.
	 * 
	 * @param	{Element}	element		DOM element
	 */
	window.elShow = function(element) {
		element.style.removeProperty('display');
	};
	
	/**
	 * Shorthand function to iterative over an array-like object, arguments passed are the value and the index second.
	 * 
	 * Do not use this function if a simple `for()` is enough or `list` is a plain object.
	 * 
	 * @param	{object}	list		array-like object
	 * @param	{function}	callback	callback function
	 */
	window.forEach = function(list, callback) {
		for (var i = 0, length = list.length; i < length; i++) {
			callback(list[i], i);
		}
	};
	
	/**
	 * Shorthand function to check if an object has a property while ignoring the chain.
	 * 
	 * @param	{object}	obj		target object
	 * @param	{string}	property	property name
	 * @return	{boolean}	false if property does not exist or belongs to the chain
	 */
	window.objOwns = function(obj, property) {
		return obj.hasOwnProperty(property);
	};
	
	/* assigns a global constant defining the proper 'click' event depending on the browser,
	   enforcing 'touchstart' on mobile devices for a better UX. We're using defineProperty()
	   here because at the time of writing Safari does not support 'const'. Thanks Safari.
	 */
	var clickEvent = ('touchstart' in document.documentElement || 'ontouchstart' in window || navigator.MaxTouchPoints > 0 || navigator.msMaxTouchPoints > 0) ? 'touchstart' : 'click';
	Object.defineProperty(window, 'WCF_CLICK_EVENT', {
		value: clickEvent
	});
})(window, document);
