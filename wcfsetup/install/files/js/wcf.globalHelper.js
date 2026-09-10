/**
 * Collection of global short hand functions.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
(function (window, document) {
	/**
	 * Shorthand function to retrieve or set an attribute.
	 *
	 * @param	{Element}	element		target element
	 * @param	{string}	attribute	attribute name
	 * @param	{?=}            value		attribute value, omit if attribute should be read
	 * @return	{(string|undefined)}		attribute value, empty string if attribute is not set or undefined if `value` was omitted
	 *
	 * @deprecated 5.3 Use `Element.getAttribute()` or `Element.setAttribute()` instead.
	 */
	window.elAttr = function (element, attribute, value) {
		if (value === undefined) {
			return element.getAttribute(attribute) || "";
		}

		element.setAttribute(attribute, value);
	};

	/**
	 * Shorthand function to retrieve a boolean attribute.
	 *
	 * @param	{Element}	element		target element
	 * @param	{string}	attribute	attribute name
	 * @return	{boolean}	true if value is either `1` or `true`
	 *
	 * @deprecated 5.3 Use `Element.getAttribute()` instead.
	 */
	window.elAttrBool = function (element, attribute) {
		var value = elAttr(element, attribute);

		return value === "1" || value === "true";
	};

	/**
	 * Shorthand function to find elements by class name.
	 *
	 * @param	{string}	className	CSS class name
	 * @param	{Element=}	context		target element, assuming `document` if omitted
	 * @return	{NodeList}	matching elements
	 *
	 * @deprecated 5.3 Use `Element.getElementsByClassName()` instead.
	 */
	window.elByClass = function (className, context) {
		return (context || document).getElementsByClassName(className);
	};

	/**
	 * Shorthand function to retrieve an element by id.
	 *
	 * @param	{string}	id	element id
	 * @return	{(Element|null)}	matching element or null if not found
	 *
	 * @deprecated 5.3 Use `document.getElementById()` instead.
	 */
	window.elById = function (id) {
		return document.getElementById(id);
	};

	/**
	 * Shorthand function to find an element by CSS selector.
	 *
	 * @param	{string}	selector	CSS selector
	 * @param	{Element=}	context		target element, assuming `document` if omitted
	 * @return	{(Element|null)}		matching element or null if no match
	 *
	 * @deprecated 5.3 Use `Element.querySelector()` instead.
	 */
	window.elBySel = function (selector, context) {
		return (context || document).querySelector(selector);
	};

	/**
	 * Shorthand function to find elements by CSS selector.
	 *
	 * @param	{string}	selector	CSS selector
	 * @param	{Element=}	context		target element, assuming `document` if omitted
	 * @param       {function=}     callback        callback function passed to forEach()
	 * @return	{NodeList}	matching elements
	 *
	 * @deprecated 5.3 Use `Element.querySelectorAll(…).forEach(…)` instead.
	 */
	window.elBySelAll = function (selector, context, callback) {
		var nodeList = (context || document).querySelectorAll(selector);
		if (typeof callback === "function") {
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
	 *
	 * @deprecated 5.3 Use `Element.getElementsByTagName()` instead.
	 */
	window.elByTag = function (tagName, context) {
		return (context || document).getElementsByTagName(tagName);
	};

	/**
	 * Shorthand function to create a DOM element.
	 *
	 * @param	{string}	tagName		element tag name
	 * @return	{Element}	new DOM element
	 *
	 * @deprecated 5.3 Use `document.createElement()` instead.
	 */
	window.elCreate = function (tagName) {
		return document.createElement(tagName);
	};

	/**
	 * Returns the closest element (parent for text nodes), optionally matching
	 * the provided selector.
	 *
	 * @param       {Node}          node            start node
	 * @param       {string=}       selector        optional CSS selector
	 * @return      {Element}       closest matching element
	 *
	 * @deprecated 5.3 Use `Element.closest()` instead.
	 */
	window.elClosest = function (node, selector) {
		if (!(node instanceof Node)) {
			throw new TypeError("Provided element is not a Node.");
		}

		// retrieve the parent element for text nodes
		if (node.nodeType === Node.TEXT_NODE) {
			node = node.parentNode;

			// text node had no parent
			if (node === null) return null;
		}

		if (typeof selector !== "string") selector = "";

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
	 *
	 * @deprecated 5.3 Use `Element.dataset` instead.
	 */
	window.elData = function (element, attribute, value) {
		attribute = "data-" + attribute;

		if (value === undefined) {
			return element.getAttribute(attribute) || "";
		}

		element.setAttribute(attribute, value);
	};

	/**
	 * Shorthand function to retrieve a boolean 'data-' attribute.
	 *
	 * @param	{Element}	element		target element
	 * @param	{string}	attribute	attribute name
	 * @return	{boolean}	true if value is either `1` or `true`
	 *
	 * @deprecated 5.3 Use `Element.dataset` instead.
	 */
	window.elDataBool = function (element, attribute) {
		var value = elData(element, attribute);

		return value === "1" || value === "true";
	};

	/**
	 * Shorthand function to hide an element by setting its 'display' value to 'none'.
	 *
	 * @param	{Element}	element		DOM element
	 *
	 * @deprecated 5.3 Set the `Element.hidden` property instead.
	 */
	window.elHide = function (element) {
		element.style.setProperty("display", "none", "");
	};

	/**
	 * Shorthand function to check if given element is hidden by setting its 'display'
	 * value to 'none'.
	 *
	 * @param	{Element}	element		DOM element
	 * @return	{boolean}
	 *
	 * @deprecated 5.3 Check against the `Element.hidden` property instead.
	 */
	window.elIsHidden = function (element) {
		return element.style.getPropertyValue("display") === "none";
	};

	/**
	 * Displays or removes an error message below the provided element.
	 *
	 * @param       {Element}       element         DOM element
	 * @param       {string?}       errorMessage    error message; `false`, `null` and `undefined` are treated as an empty string
	 * @param       {boolean?}      isHtml          defaults to false, causes `errorMessage` to be treated as text only
	 * @return      {?Element}      the inner error element or null if it was removed
	 *
	 * @deprecated 5.3 Use `WoltLabSuite/Core/Dom/Util.innerError()` instead.
	 */
	window.elInnerError = function (element, errorMessage, isHtml) {
		var parent = element.parentNode;
		if (parent === null) {
			throw new Error("Only elements that have a parent element or document are valid.");
		}

		if (typeof errorMessage !== "string") {
			if (errorMessage === undefined || errorMessage === null || errorMessage === false) {
				errorMessage = "";
			} else {
				throw new TypeError(
					"The error message must be a string; `false`, `null` or `undefined` can be used as a substitute for an empty string.",
				);
			}
		}

		var insertTarget = parent;
		var referenceElement = element;
		if (insertTarget.classList.contains("inputAddon")) {
			insertTarget = parent.parentElement;
			referenceElement = parent;
		}

		var innerError = referenceElement.nextElementSibling;
		if (
			innerError === null ||
			innerError.nodeName !== "SMALL" ||
			!innerError.classList.contains("innerError")
		) {
			if (errorMessage === "") {
				innerError = null;
			} else {
				innerError = elCreate("small");
				innerError.className = "innerError";
				insertTarget.insertBefore(innerError, referenceElement.nextSibling);
			}
		}

		if (errorMessage === "") {
			if (innerError !== null) {
				parent.removeChild(innerError);
				innerError = null;
			}
		} else {
			innerError[isHtml ? "innerHTML" : "textContent"] = errorMessage;
		}

		return innerError;
	};

	/**
	 * Shorthand function to remove an element.
	 *
	 * @param	{Node}	        element		DOM node
	 *
	 * @deprecated 5.3 Use `Element.remove()` instead.
	 */
	window.elRemove = function (element) {
		element.parentNode.removeChild(element);
	};

	/**
	 * Shorthand function to show an element previously hidden by using `elHide()`.
	 *
	 * @param	{Element}	element		DOM element
	 *
	 * @deprecated 5.3 Use `Element.hidden` instead.
	 */
	window.elShow = function (element) {
		element.style.removeProperty("display");
	};

	/**
	 * Toggles visibility of an element using the display style.
	 *
	 * @param       {Element}       element         DOM element
	 *
	 * @deprecated 5.3 Use `Element.hidden` instead.
	 */
	window.elToggle = function (element) {
		if (element.style.getPropertyValue("display") === "none") {
			elShow(element);
		} else {
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
	 *
	 * @deprecated 5.3 Use the native methods for iterables.
	 */
	window.forEach = function (list, callback) {
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
	 *
	 * @deprecated 5.3 Use `Object.hasOwn()` instead.
	 */
	window.objOwns = function (obj, property) {
		return obj.hasOwnProperty(property);
	};

	/**
	 * Returns a function, that, as long as it continues to be invoked, will not
	 * be triggered. The function will be called after it stops being called for
	 * N milliseconds. If `immediate` is passed, trigger the function on the
	 * leading edge, instead of the trailing.
	 *
	 * @param {function} func
	 * @param {number} wait
	 * @param {boolean} immediate
	 * @return function
	 * @see https://davidwalsh.name/javascript-debounce-function
	 *
	 * @deprecated 5.3 Use `WoltLabSuite/Core/Core.debounce()` instead.
	 */
	window.debounce = function (func, wait, immediate) {
		var timeout;

		return function () {
			var context = this;
			var args = arguments;

			clearTimeout(timeout);

			timeout = setTimeout(function () {
				timeout = null;

				if (!immediate) {
					func.apply(context, args);
				}
			}, wait);

			if (immediate && !timeout) {
				func.apply(context, args);
			}
		};
	};

	/** @deprecated 5.4 Use `click` directly. */
	Object.defineProperty(window, "WCF_CLICK_EVENT", {
		value: "click",
	});

	/**
	 * Provides a hashCode() method for strings, similar to Java's String.hashCode().
	 *
	 * @see	http://werxltd.com/wp/2010/05/13/javascript-implementation-of-javas-string-hashcode-method/
	 */
	window.String.prototype.hashCode = function () {
		var $char;
		var $hash = 0;

		if (this.length) {
			for (var $i = 0, $length = this.length; $i < $length; $i++) {
				$char = this.charCodeAt($i);
				$hash = ($hash << 5) - $hash + $char;
				$hash = $hash & $hash; // convert to 32bit integer
			}
		}

		return $hash;
	};

	Object.defineProperty(window, "SECURITY_TOKEN", {
		configurable: false,
		get() {
			// This implementation effectively is a copy of WoltLabSuite/Core/Core#getXsrfToken, but
			// we can't use this here for compatibility reasons. Use of the global SECURITY_TOKEN
			// property is deprecated anyway.

			const cookies = document.cookie.split(";").map((c) => c.trim());
			const xsrfToken = cookies.find((c) => c.startsWith("XSRF-TOKEN="));

			if (xsrfToken === undefined) {
				return "COOKIE_NOT_FOUND";
			}

			const [_key, value] = xsrfToken.split(/=/, 2);

			return decodeURIComponent(value.trim());
		},
	});
})(window, document);
