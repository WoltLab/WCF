/**
 * Provides a filter input for checkbox lists.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/ItemList/Filter
 */
define(['Core', 'EventKey', 'Language', 'List', 'StringUtil', 'Dom/Util'], function (Core, EventKey, Language, List, StringUtil, DomUtil) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_buildItems: function() {},
			_prepareItem: function() {},
			_keyup: function() {}
		};
		return Fake;
	}
	
	/**
	 * Creates a new filter input.
	 * 
	 * @param       {string}        elementId       list element id
	 * @param       {Object=}       options         options
	 * @constructor
	 */
	function UiItemListFilter(elementId, options) { this.init(elementId, options); }
	UiItemListFilter.prototype = {
		/**
		 * Creates a new filter input.
		 * 
		 * @param       {string}        elementId       list element id
		 * @param       {Object=}       options         options
		 */
		init: function(elementId, options) {
			this._value = '';
			
			this._options = Core.extend({
				callbackPrepareItem: undefined
			}, options);
			
			var element = elById(elementId);
			if (element === null) {
				throw new Error("Expected a valid element id, '" + elementId + "' does not match anything.");
			}
			else if (!element.classList.contains('scrollableCheckboxList') && typeof this._options.callbackPrepareItem !== 'function') {
				throw new Error("Filter only works with elements with the CSS class 'scrollableCheckboxList'.");
			}
			
			var container = elCreate('div');
			container.className = 'itemListFilter';
			
			element.parentNode.insertBefore(container, element);
			container.appendChild(element);
			
			var inputAddon = elCreate('div');
			inputAddon.className = 'inputAddon';
			
			var input = elCreate('input');
			input.className = 'long';
			input.type = 'text';
			input.placeholder = Language.get('wcf.global.filter.placeholder');
			input.addEventListener('keydown', function (event) {
				if (EventKey.Enter(event)) {
					event.preventDefault();
				}
			});
			input.addEventListener('keyup', this._keyup.bind(this));
			
			var clearButton = elCreate('a');
			clearButton.href = '#';
			clearButton.className = 'button inputSuffix jsTooltip';
			clearButton.title = Language.get('wcf.global.filter.button.clear');
			clearButton.innerHTML = '<span class="icon icon16 fa-times"></span>';
			clearButton.addEventListener('click', (function(event) {
				event.preventDefault();
				
				this.reset();
			}).bind(this));
			
			inputAddon.appendChild(input);
			inputAddon.appendChild(clearButton);
			
			container.appendChild(inputAddon);
			
			this._container = container;
			this._element = element;
			this._input = input;
			this._items = null;
			this._fragment = null;
		},
		
		/**
		 * Resets the filter.
		 */
		reset: function () {
			this._input.value = '';
			this._keyup();
		},
		
		/**
		 * Builds the item list and rebuilds the items' DOM for easier manipulation.
		 * 
		 * @protected
		 */
		_buildItems: function() {
			this._items = new List();
			
			var callback = (typeof this._options.callbackPrepareItem === 'function') ? this._options.callbackPrepareItem : this._prepareItem.bind(this);
			for (var i = 0, length = this._element.childElementCount; i < length; i++) {
				this._items.add(callback(this._element.children[i]));
			}
		},
		
		/**
		 * Processes an item and returns the meta data.
		 * 
		 * @param       {Element}       item    current item
		 * @return      {{item: *, span: Element, text: string}}
		 * @protected
		 */
		_prepareItem: function(item) {
			var label = item.children[0];
			var text = label.textContent.trim();
			
			var checkbox = label.children[0];
			while (checkbox.nextSibling) {
				label.removeChild(checkbox.nextSibling);
			}
			
			label.appendChild(document.createTextNode(' '));
			
			var span = elCreate('span');
			span.textContent = text;
			label.appendChild(span);
			
			return {
				item: item,
				span: span,
				text: text
			};
		},
		
		/**
		 * Rebuilds the list on keyup, uses case-insensitive matching.
		 * 
		 * @protected
		 */
		_keyup: function() {
			var value = this._input.value.trim();
			if (this._value === value) {
				return;
			}
			
			if (this._fragment === null) {
				this._fragment = document.createDocumentFragment();
				
				// set fixed height to avoid layout jumps
				this._element.style.setProperty('height', this._element.offsetHeight + 'px', '');
			}
			
			// move list into fragment before editing items, increases performance
			// by avoiding the browser to perform repaint/layout over and over again
			this._fragment.appendChild(this._element);
			
			if (this._items === null) {
				this._buildItems();
			}
			
			var regexp = new RegExp('(' + StringUtil.escapeRegExp(value) + ')', 'i');
			var hasVisibleItems = (value === '');
			this._items.forEach(function (item) {
				if (value === '') {
					item.span.textContent = item.text;
					
					elShow(item.item);
				}
				else {
					if (regexp.test(item.text)) {
						item.span.innerHTML = item.text.replace(regexp, '<u>$1</u>');
						
						elShow(item.item);
						hasVisibleItems = true;
					}
					else {
						elHide(item.item);
					}
				}
			});
			
			this._container.insertBefore(this._fragment.firstChild, this._container.firstChild);
			this._value = value;
			
			var innerError = this._container.nextElementSibling;
			if (innerError && !innerError.classList.contains('innerError')) innerError = null;
			
			if (hasVisibleItems) {
				if (innerError) {
					elRemove(innerError);
				}
			}
			else {
				if (!innerError) {
					innerError = elCreate('small');
					innerError.className = 'innerError';
					innerError.textContent = Language.get('wcf.global.filter.error.noMatches');
					DomUtil.insertAfter(innerError, this._container);
				}
			}
		}
	};
	
	return UiItemListFilter;
});
