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
	 * @param	{mixed=}	value		attribute value, omit if attribute should be read
	 * @return	{(string|undefined)}		attribute value, empty string if attribute is not set or undefined if `value` was omitted
	 */
	window.elAttr = function(element, attribute, value) {
		if (value === undefined) {
			return element.getAttribute(attribute) || '';
		}
		
		element.setAttribute(attribute, value);
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
	 * @return	{NodeList}	matching elements
	 */
	window.elBySelAll = function(selector, context) {
		return (context || document).querySelectorAll(selector);
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
	 * Shorthand function to check if an object has a property while ignoring the chain.
	 * 
	 * @param	{object}	obj		target object
	 * @param	{string}	property	property name
	 * @return	{boolean}	false if property does not exist or belongs to the chain
	 */
	window.objOwns = function(obj, property) {
		return obj.hasOwnProperty(property);
	};
})(window, document);
