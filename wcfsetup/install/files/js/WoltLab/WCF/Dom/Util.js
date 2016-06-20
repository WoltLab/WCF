/**
 * Provides helper functions to work with DOM nodes.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Dom/Util
 */
define(['Environment', 'StringUtil'], function(Environment, StringUtil) {
	"use strict";
	
	function _isBoundaryNode(element, ancestor, position) {
		if (!ancestor.contains(element)) {
			throw new Error("Ancestor element does not contain target element.");
		}
		
		var node, whichSibling = position + 'Sibling';
		while (element !== null && element !== ancestor) {
			if (element[position + 'ElementSibling'] !== null) {
				return false;
			}
			else if (element[whichSibling]) {
				node = element[whichSibling];
				while (node) {
					if (node.textContent.trim() !== '') {
						return false;
					}
					
					node = node[whichSibling];
				}
			}
			
			element = element.parentNode;
		}
		
		return true;
	}
	
	var _idCounter = 0;
	
	/**
	 * @exports	WoltLab/WCF/Dom/Util
	 */
	var DomUtil = {
		/**
		 * Returns a DocumentFragment containing the provided HTML string as DOM nodes.
		 * 
		 * @param	{string}	html	HTML string
		 * @return	{DocumentFragment}	fragment containing DOM nodes
		 */
		createFragmentFromHtml: function(html) {
			var tmp = elCreate('div');
			tmp.innerHTML = html;
			
			var fragment = document.createDocumentFragment();
			while (tmp.childNodes.length) {
				fragment.appendChild(tmp.childNodes[0]);
			}
			
			return fragment;
		},
		
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
			while (elById(elementId) !== null);
			
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
			if (!(el instanceof Element)) {
				throw new TypeError("Expected a valid DOM element as argument.");
			}
			
			var id = elAttr(el, 'id');
			if (!id) {
				id = this.getUniqueId();
				elAttr(el, 'id', id);
			}
			
			return id;
		},
		
		/**
		 * Returns the outer height of an element including margins.
		 * 
		 * @param	{Element}		el		element
		 * @param	{CSSStyleDeclaration=}	styles		result of window.getComputedStyle()
		 * @return	{int}	                outer height in px
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
		 * @return	{int}	outer width in px
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
		 * @param	{Element}       el	        element
		 * @return	{{height: int, width: int}}     dimensions in px
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
		 * @param	{Element}	el	        element
		 * @return	{{left: int, top: int}}         offset relative to top left corner
		 */
		offset: function(el) {
			var rect = el.getBoundingClientRect();
			
			return {
				top: rect.top + window.scrollY,
				left: rect.left + window.scrollX
			};
		},
		
		/**
		 * Prepends an element to a parent element.
		 * 
		 * @param	{Element}	el		element to prepend
		 * @param	{Element}	parentEl	future containing element
		 */
		prepend: function(el, parentEl) {
			if (parentEl.childNodes.length === 0) {
				parentEl.appendChild(el);
			}
			else {
				parentEl.insertBefore(el, parentEl.childNodes[0]);
			}
		},
		
		/**
		 * Inserts an element after an existing element.
		 * 
		 * @param	{Element}	newEl		element to insert
		 * @param	{Element}	el		reference element
		 */
		insertAfter: function(newEl, el) {
			if (el.nextElementSibling !== null) {
				el.parentNode.insertBefore(newEl, el.nextElementSibling);
			}
			else {
				el.parentNode.appendChild(newEl);
			}
		},
		
		/**
		 * Applies a list of CSS properties to an element.
		 * 
		 * @param	{Element}		el	element
		 * @param	{Object<string, *>}	styles	list of CSS styles
		 */
		setStyles: function(el, styles) {
			var important = false;
			for (var property in styles) {
				if (objOwns(styles, property)) {
					if (/ !important$/.test(styles[property])) {
						important = true;
						
						styles[property] = styles[property].replace(/ !important$/, '');
					}
					else {
						important = false;
					}
					
					// for a set style property with priority = important, Safari is not able to
					// overwrite it with a property != important; removing the property first
					// solves the issue
					if (Environment.browser() === 'safari' && el.style.getPropertyPriority(property) === 'important' && !important) {
						el.style.removeProperty(property);
					}
					
					el.style.setProperty(property, styles[property], (important ? 'important' : ''));
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
		 * @return	{int}	                property value as integer
		 */
		styleAsInt: function(styles, propertyName) {
			var value = styles.getPropertyValue(propertyName);
			if (value === null) {
				return 0;
			}
			
			return parseInt(value);
		},
		
		/**
		 * Sets the inner HTML of given element and reinjects <script> elements to be properly executed.
		 * 
		 * @see		http://www.w3.org/TR/2008/WD-html5-20080610/dom.html#innerhtml0
		 * @param	{Element}	element		target element
		 * @param	{string}	innerHtml	HTML string
		 */
		setInnerHtml: function(element, innerHtml) {
			element.innerHTML = innerHtml;
			
			var newScript, script, scripts = elBySelAll('script', element);
			for (var i = 0, length = scripts.length; i < length; i++) {
				script = scripts[i];
				newScript = elCreate('script');
				if (script.src) {
					newScript.src = script.src;
				}
				else {
					newScript.textContent = script.textContent;
				}
				
				element.appendChild(newScript);
				elRemove(script);
			}
		},
		
		/**
		 * 
		 * @param html
		 * @param {Element} referenceElement
		 * @param insertMethod
		 */
		insertHtml: function(html, referenceElement, insertMethod) {
			var element = elCreate('div');
			this.setInnerHtml(element, html);
			
			if (insertMethod === 'append' || insertMethod === 'after') {
				while (element.childNodes.length) {
					if (insertMethod === 'append') {
						referenceElement.appendChild(element.childNodes[0]);
					}
					else {
						this.insertAfter(element.childNodes[0], referenceElement);
					}
				}
			}
			else if (insertMethod === 'prepend' || insertMethod === 'before') {
				for (var i = element.childNodes.length - 1; i >= 0; i--) {
					if (insertMethod === 'prepend') {
						this.prepend(element.childNodes[i], referenceElement);
					}
					else {
						referenceElement.parentNode.insertBefore(element.childNodes[i], referenceElement);
					}
				}
			}
			else {
				throw new Error("Unknown insert method '" + insertMethod + "'.");
			}
		},
		
		/**
		 * Returns true if `element` contains the `child` element.
		 * 
		 * @param	{Element}	element		container element
		 * @param	{Element}	child		child element
		 * @returns	{boolean}	true if `child` is a (in-)direct child of `element`
		 */
		contains: function(element, child) {
			while (child !== null) {
				child = child.parentNode;
				
				if (element === child) {
					return true;
				}
			}
			
			return false;
		},
		
		/**
		 * Retrieves all data attributes from target element, optionally allowing for
		 * a custom prefix that serves two purposes: First it will restrict the results
		 * for items starting with it and second it will remove that prefix.
		 * 
		 * @param       {Element}       element         target element
		 * @param       {string=}       prefix          attribute prefix
		 * @param       {boolean=}      camcelCaseName  transform attribute names into camel case using dashes as separators
		 * @param       {boolean=}      idToUpperCase   transform '-id' into 'ID'
		 * @returns     {object<string, string>}        list of data attributes
		 */
		getDataAttributes: function(element, prefix, camcelCaseName, idToUpperCase) {
			prefix = prefix || '';
			if (!/^data-/.test(prefix)) prefix = 'data-' + prefix;
			camcelCaseName = (camcelCaseName === true);
			idToUpperCase = (idToUpperCase === true);
			
			var attribute, attributes = {}, name, tmp;
			for (var i = 0, length = element.attributes.length; i < length; i++) {
				attribute = element.attributes[i];
				
				if (attribute.name.indexOf(prefix) === 0) {
					name = attribute.name.replace(new RegExp('^' + prefix), '');
					if (camcelCaseName) {
						tmp = name.split('-');
						name = '';
						for (var j = 0, innerLength = tmp.length; j < innerLength; j++) {
							if (name.length) {
								if (idToUpperCase && tmp[j] === 'id') {
									tmp[j] = 'ID';
								}
								else {
									tmp[j] = StringUtil.ucfirst(tmp[j]);
								}
							}
							
							name += tmp[j];
						}
					}
					
					attributes[name] = attribute.value;
				}
			}
			
			return attributes;
		},
		
		/**
		 * Unwraps contained nodes by moving them out of `element` while
		 * preserving their previous order. Target element will be removed
		 * at the end of the operation.
		 * 
		 * @param       {Element}       element         target element
		 */
		unwrapChildNodes: function(element) {
			var parent = element.parentNode;
			while (element.childNodes.length) {
				parent.insertBefore(element.childNodes[0], element);
			}
			
			elRemove(element);
		},
		
		/**
		 * Replaces an element by moving all child nodes into the new element
		 * while preserving their previous order. The old element will be removed
		 * at the end of the operation.
		 * 
		 * @param       {Element}       oldElement      old element
		 * @param       {Element}       newElement      old element
		 */
		replaceElement: function(oldElement, newElement) {
			while (oldElement.childNodes.length) {
				newElement.appendChild(oldElement.childNodes[0]);
			}
			
			oldElement.parentNode.insertBefore(newElement, oldElement);
			elRemove(oldElement);
		},
		
		/**
		 * Returns true if given element is the most left node of the ancestor, that is
		 * a node without any content nor elements before it or its parent nodes.
		 * 
		 * @param       {Element}       element         target element
		 * @param       {Element}       ancestor        ancestor element, must contain the target element
		 * @returns     {boolean}       true if target element is the most left node
		 */
		isAtNodeStart: function(element, ancestor) {
			return _isBoundaryNode(element, ancestor, 'previous');
		},
		
		/**
		 * Returns true if given element is the most right node of the ancestor, that is
		 * a node without any content nor elements after it or its parent nodes.
		 * 
		 * @param       {Element}       element         target element
		 * @param       {Element}       ancestor        ancestor element, must contain the target element
		 * @returns     {boolean}       true if target element is the most right node
		 */
		isAtNodeEnd: function(element, ancestor) {
			return _isBoundaryNode(element, ancestor, 'next');
		}
	};
	
	// expose on window object for backward compatibility
	window.bc_wcfDomUtil = DomUtil;
	
	return DomUtil;
});
