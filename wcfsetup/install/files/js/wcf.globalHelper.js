/**
 * Collection of global short hand functions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
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
	 * @param       {function=}     callback        callback function passed to forEach()
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
	 * Returns the closest element (parent for text nodes), optionally matching
	 * the provided selector.
	 * 
	 * @param       {Node}          node            start node
	 * @param       {string=}       selector        optional CSS selector
	 * @return      {Element}       closest matching element
	 */
	window.elClosest = function (node, selector) {
		if (!(node instanceof Node)) {
			throw new TypeError('Provided element is not a Node.');
		}
		
		// retrieve the parent element for text nodes
		if (node.nodeType === Node.TEXT_NODE) {
			node = node.parentNode;
			
			// text node had no parent
			if (node === null) return null;
		}
		
		if (typeof selector !== 'string') selector = '';
		
		if (selector.length === 0) return node;
		
		return node.closest(selector);
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
	 * Shorthand function to check if given element is hidden by setting its 'display'
	 * value to 'none'.
	 *
	 * @param	{Element}	element		DOM element
	 * @return	{boolean}
	 */
	window.elIsHidden = function(element) {
		return element.style.getPropertyValue('display') === 'none';
	}
	
	/**
	 * Displays or removes an error message below the provided element.
	 * 
	 * @param       {Element}       element         DOM element
	 * @param       {string?}       errorMessage    error message; `false`, `null` and `undefined` are treated as an empty string
	 * @param       {boolean?}      isHtml          defaults to false, causes `errorMessage` to be treated as text only
	 * @return      {?Element}      the inner error element or null if it was removed
	 */
	window.elInnerError = function (element, errorMessage, isHtml) {
		var parent = element.parentNode;
		if (parent === null) {
			throw new Error('Only elements that have a parent element or document are valid.');
		}
		
		if (typeof errorMessage !== 'string') {
			if (errorMessage === undefined || errorMessage === null || errorMessage === false) {
				errorMessage = '';
			}
			else {
				throw new TypeError('The error message must be a string; `false`, `null` or `undefined` can be used as a substitute for an empty string.');
			}
		}
		
		var innerError = element.nextElementSibling;
		if (innerError === null || innerError.nodeName !== 'SMALL' || !innerError.classList.contains('innerError')) {
			if (errorMessage === '') {
				innerError = null;
			}
			else {
				innerError = elCreate('small');
				innerError.className = 'innerError';
				parent.insertBefore(innerError, element.nextSibling);
			}
		}
		
		if (errorMessage === '') {
			if (innerError !== null) {
				parent.removeChild(innerError);
				innerError = null;
			}
		}
		else {
			innerError[(isHtml ? 'innerHTML' : 'textContent')] = errorMessage;
		}
		
		return innerError;
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
	 * Toggles visibility of an element using the display style.
	 * 
	 * @param       {Element}       element         DOM element
	 */
	window.elToggle = function (element) {
		if (element.style.getPropertyValue('display') === 'none') {
			elShow(element);
		}
		else {
			elHide(element);
		}
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
		value: 'click' //clickEvent
	});
	
	/* Overwrites any history states after 'initial' with 'skip' on initial page load.
	   This is done, as the necessary DOM of other history states may not exist any more.
	   On forward navigation these 'skip' states are automatically skipped, otherwise the
	   user might have to press the forward button several times.
	   Note: A 'skip' state cannot be hit in the 'popstate' event when navigation backwards,
	         because the history already is left of all the 'skip' states for the current page.
	   Note 2: Setting the URL component of `history.replaceState()` to an empty string will
	           cause the Internet Explorer to discard the path and query string from the
	           address bar.
	 */
	(function() {
		var stateDepth = 0;
		function check() {
			if (window.history.state && window.history.state.name && window.history.state.name !== 'initial') {
				window.history.replaceState({
					name: 'skip',
					depth: ++stateDepth
				}, '');
				window.history.back();
				
				// window.history does not update in this iteration of the event loop
				setTimeout(check, 1);
			}
			else {
				window.history.replaceState({name: 'initial'}, '');
			}
		}
		check();
		
		window.addEventListener('popstate', function(event) {
			if (event.state && event.state.name && event.state.name === 'skip') {
				window.history.go(event.state.depth);
			}
		});
	})();
	
	/**
	 * Provides a hashCode() method for strings, similar to Java's String.hashCode().
	 *
	 * @see	http://werxltd.com/wp/2010/05/13/javascript-implementation-of-javas-string-hashcode-method/
	 */
	window.String.prototype.hashCode = function() {
		var $char;
		var $hash = 0;
		
		if (this.length) {
			for (var $i = 0, $length = this.length; $i < $length; $i++) {
				$char = this.charCodeAt($i);
				$hash = (($hash << 5) - $hash) + $char;
				$hash = $hash & $hash; // convert to 32bit integer
			}
		}
		
		return $hash;
	};
})(window, document);
