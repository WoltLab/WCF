/**
 * Provides helper functions to traverse the DOM.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Dom/Traverse
 */
define([], function() {
	"use strict";
	
	/** @const */ var NONE = 0;
	/** @const */ var SELECTOR = 1;
	/** @const */ var CLASS_NAME = 2;
	/** @const */ var TAG_NAME = 3;
	
	var _probe = [
		function(el, none) { return true; },
		function(el, selector) { return el.matches(selector); },
		function(el, className) { return el.classList.contains(className); },
		function(el, tagName) { return el.nodeName === tagName; }
	];
	
	var _children = function(el, type, value) {
		if (!(el instanceof Element)) {
			throw new TypeError("Expected a valid element as first argument.");
		}
		
		var children = [];
		
		for (var i = 0; i < el.childElementCount; i++) {
			if (_probe[type](el.children[i], value)) {
				children.push(el.children[i]);
			}
		}
		
		return children;
	};
	
	var _parent = function(el, type, value, untilElement) {
		if (!(el instanceof Element)) {
			throw new TypeError("Expected a valid element as first argument.");
		}
		
		el = el.parentNode;
		
		while (el instanceof Element) {
			if (el === untilElement) {
				return null;
			}
			
			if (_probe[type](el, value)) {
				return el;
			}
			
			el = el.parentNode;
		}
		
		return null;
	};
	
	var _sibling = function(el, siblingType, type, value) {
		if (!(el instanceof Element)) {
			throw new TypeError("Expected a valid element as first argument.");
		}
		
		if (el instanceof Element) {
			if (el[siblingType] !== null && _probe[type](el[siblingType], value)) {
				return el[siblingType];
			}
		}
		
		return null;
	};
	
	/**
	 * @exports	WoltLabSuite/Core/Dom/Traverse
	 */
	return {
		/**
		 * Examines child elements and returns the first child matching the given selector.
		 * 
		 * @param	{Element}		el		element
		 * @param	{string}		selector	CSS selector to match child elements against
		 * @return	{(Element|null)}	null if there is no child node matching the selector
		 */
		childBySel: function(el, selector) {
			return _children(el, SELECTOR, selector)[0] || null;
		},
		
		/**
		 * Examines child elements and returns the first child that has the given CSS class set.
		 * 
		 * @param	{Element}		el		element
		 * @param	{string}		className	CSS class name
		 * @return	{(Element|null)}	null if there is no child node with given CSS class
		 */
		childByClass: function(el, className) {
			return _children(el, CLASS_NAME, className)[0] || null;
		},
		
		/**
		 * Examines child elements and returns the first child which equals the given tag.
		 * 
		 * @param	{Element}		el		element
		 * @param	{string}		tagName		element tag name
		 * @return	{(Element|null)}	null if there is no child node which equals given tag
		 */
		childByTag: function(el, tagName) {
			return _children(el, TAG_NAME, tagName)[0] || null;
		},
		
		/**
		 * Examines child elements and returns all children matching the given selector.
		 * 
		 * @param	{Element}		el		element
		 * @param	{string}		selector	CSS selector to match child elements against
		 * @return	{array<Element>}	list of children matching the selector
		 */
		childrenBySel: function(el, selector) {
			return _children(el, SELECTOR, selector);
		},
		
		/**
		 * Examines child elements and returns all children that have the given CSS class set.
		 * 
		 * @param	{Element}		el		element
		 * @param	{string}		className	CSS class name
		 * @return	{array<Element>}	list of children with the given class
		 */
		childrenByClass: function(el, className) {
			return _children(el, CLASS_NAME, className);
		},
		
		/**
		 * Examines child elements and returns all children which equal the given tag.
		 * 
		 * @param	{Element}		el		element
		 * @param	{string}		tagName		element tag name
		 * @return	{array<Element>}	list of children equaling the tag name
		 */
		childrenByTag: function(el, tagName) {
			return _children(el, TAG_NAME, tagName);
		},
		
		/**
		 * Examines parent nodes and returns the first parent that matches the given selector.
		 * 
		 * @param	{Element}	el		child element
		 * @param	{string}	selector	CSS selector to match parent nodes against
		 * @param	{Element=}	untilElement	stop when reaching this element
		 * @return	{(Element|null)}	null if no parent node matched the selector
		 */
		parentBySel: function(el, selector, untilElement) {
			return _parent(el, SELECTOR, selector, untilElement);
		},
		
		/**
		 * Examines parent nodes and returns the first parent that has the given CSS class set.
		 * 
		 * @param	{Element}	el		child element
		 * @param	{string}	className	CSS class name
		 * @param	{Element=}	untilElement	stop when reaching this element
		 * @return	{(Element|null)}	null if there is no parent node with given class
		 */
		parentByClass: function(el, className, untilElement) {
			return _parent(el, CLASS_NAME, className, untilElement);
		},
		
		/**
		 * Examines parent nodes and returns the first parent which equals the given tag.
		 * 
		 * @param	{Element}	el		child element
		 * @param	{string}	tagName		element tag name
		 * @param	{Element=}	untilElement	stop when reaching this element
		 * @return	{(Element|null)}	null if there is no parent node of given tag type
		 */
		parentByTag: function(el, tagName, untilElement) {
			return _parent(el, TAG_NAME, tagName, untilElement);
		},
		
		/**
		 * Returns the next element sibling.
		 * 
		 * @param	{Element}	el		element
		 * @return	{(Element|null)}	null if there is no next sibling element
		 */
		next: function(el) {
			return _sibling(el, 'nextElementSibling', NONE, null);
		},
		
		/**
		 * Returns the next element sibling that matches the given selector.
		 * 
		 * @param	{Element}	el		element
		 * @param	{string}	selector	CSS selector to match parent nodes against
		 * @return	{(Element|null)}	null if there is no next sibling element or it does not match the selector
		 */
		nextBySel: function(el, selector) {
			return _sibling(el, 'nextElementSibling', SELECTOR, selector);
		},
		
		/**
		 * Returns the next element sibling with given CSS class.
		 * 
		 * @param	{Element}	el		element
		 * @param	{string}	className	CSS class name
		 * @return	{(Element|null)}	null if there is no next sibling element or it does not have the class set
		 */
		nextByClass: function(el, className) {
			return _sibling(el, 'nextElementSibling', CLASS_NAME, className);
		},
		
		/**
		 * Returns the next element sibling with given CSS class.
		 * 
		 * @param	{Element}	el		element
		 * @param	{string}	tagName         element tag name
		 * @return	{(Element|null)}	null if there is no next sibling element or it does not have the class set
		 */
		nextByTag: function(el, tagName) {
			return _sibling(el, 'nextElementSibling', TAG_NAME, tagName);
		},
		
		/**
		 * Returns the previous element sibling.
		 * 
		 * @param	{Element}	el		element
		 * @return	{(Element|null)}	null if there is no previous sibling element
		 */
		prev: function(el) {
			return _sibling(el, 'previousElementSibling', NONE, null);
		},
		
		/**
		 * Returns the previous element sibling that matches the given selector.
		 * 
		 * @param	{Element}	el		element
		 * @param	{string}	selector	CSS selector to match parent nodes against
		 * @return	{(Element|null)}	null if there is no previous sibling element or it does not match the selector
		 */
		prevBySel: function(el, selector) {
			return _sibling(el, 'previousElementSibling', SELECTOR, selector);
		},
		
		/**
		 * Returns the previous element sibling with given CSS class.
		 * 
		 * @param	{Element}	el		element
		 * @param	{string}	className	CSS class name
		 * @return	{(Element|null)}	null if there is no previous sibling element or it does not have the class set
		 */
		prevByClass: function(el, className) {
			return _sibling(el, 'previousElementSibling', CLASS_NAME, className);
		},
		
		/**
		 * Returns the previous element sibling with given CSS class.
		 * 
		 * @param	{Element}	el		element
		 * @param	{string}	tagName         element tag name
		 * @return	{(Element|null)}	null if there is no previous sibling element or it does not have the class set
		 */
		prevByTag: function(el, tagName) {
			return _sibling(el, 'previousElementSibling', TAG_NAME, tagName);
		}
	};
});
