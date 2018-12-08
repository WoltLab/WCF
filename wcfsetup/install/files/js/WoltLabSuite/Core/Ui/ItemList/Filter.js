/**
 * Provides a filter input for checkbox lists.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/ItemList/Filter
 */
define(['Core', 'EventKey', 'Language', 'List', 'StringUtil', 'Dom/Util', 'Ui/SimpleDropdown'], function (Core, EventKey, Language, List, StringUtil, DomUtil, UiSimpleDropdown) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_buildItems: function() {},
			_prepareItem: function() {},
			_keyup: function() {},
			_toggleVisibility: function () {},
			_setupVisibilityFilter: function () {},
			_setVisibility: function () {}
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
				callbackPrepareItem: undefined,
				enableVisibilityFilter: true
			}, options);
			
			var element = elById(elementId);
			if (element === null) {
				throw new Error("Expected a valid element id, '" + elementId + "' does not match anything.");
			}
			else if (!element.classList.contains('scrollableCheckboxList') && typeof this._options.callbackPrepareItem !== 'function') {
				throw new Error("Filter only works with elements with the CSS class 'scrollableCheckboxList'.");
			}
			
			elData(element, 'filter', 'showAll');
			
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
			
			if (this._options.enableVisibilityFilter) {
				var visibilityButton = elCreate('a');
				visibilityButton.href = '#';
				visibilityButton.className = 'button inputSuffix jsTooltip';
				visibilityButton.title = Language.get('wcf.global.filter.button.visibility');
				visibilityButton.innerHTML = '<span class="icon icon16 fa-eye"></span>';
				visibilityButton.addEventListener(WCF_CLICK_EVENT, this._toggleVisibility.bind(this));
				inputAddon.appendChild(visibilityButton);
			}
			
			container.appendChild(inputAddon);
			
			this._container = container;
			this._dropdown = null;
			this._dropdownId = '';
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
			
			elInnerError(this._container, (hasVisibleItems) ? false : Language.get('wcf.global.filter.error.noMatches'));
		},
		
		/**
		 * Toggles the visibility mode for marked items.
		 *
		 * @param       {Event}         event
		 * @protected
		 */
		_toggleVisibility: function (event) {
			event.preventDefault();
			event.stopPropagation();
			
			var button = event.currentTarget;
			if (this._dropdown === null) {
				var dropdown = elCreate('ul');
				dropdown.className = 'dropdownMenu';
				
				['activeOnly', 'highlightActive', 'showAll'].forEach((function (type) {
					var link = elCreate('a');
					elData(link, 'type', type);
					link.href = '#';
					link.textContent = Language.get('wcf.global.filter.visibility.' + type);
					link.addEventListener(WCF_CLICK_EVENT, this._setVisibility.bind(this));
					
					var li = elCreate('li');
					li.appendChild(link);
					
					if (type === 'showAll') {
						li.className = 'active';
						
						var divider = elCreate('li');
						divider.className = 'dropdownDivider';
						dropdown.appendChild(divider);
					}
					
					dropdown.appendChild(li);
				}).bind(this));
				
				UiSimpleDropdown.initFragment(button, dropdown);
				
				// add `active` classes required for the visibility filter
				this._setupVisibilityFilter();
				
				this._dropdown = dropdown;
				this._dropdownId = button.id;
			}
			
			UiSimpleDropdown.toggleDropdown(button.id, button);
		},
		
		/**
		 * Set-ups the visibility filter by assigning an active class to the
		 * list items that hold the checkboxes and observing the checkboxes
		 * for any changes.
		 *
		 * This process involves quite a few DOM changes and new event listeners,
		 * therefore we'll delay this until the filter has been accessed for
		 * the first time, because none of these changes matter before that.
		 *
		 * @protected
		 */
		_setupVisibilityFilter: function () {
			var nextSibling = this._element.nextSibling;
			var parent = this._element.parentNode;
			var scrollTop = this._element.scrollTop;
			
			// mass-editing of DOM elements is slow while they're part of the document 
			var fragment = document.createDocumentFragment();
			fragment.appendChild(this._element);
			
			elBySelAll('li', this._element, function(li) {
				var checkbox = elBySel('input[type="checkbox"]', li);
				if (checkbox.checked) li.classList.add('active');
				
				checkbox.addEventListener('change', function() {
					li.classList[(checkbox.checked ? 'add' : 'remove')]('active');
				});
			});
			
			// re-insert the modified DOM
			parent.insertBefore(this._element, nextSibling);
			this._element.scrollTop = scrollTop;
		},
		
		/**
		 * Sets the visibility of marked items.
		 *
		 * @param       {Event}         event
		 * @protected
		 */
		_setVisibility: function (event) {
			event.preventDefault();
			
			var link = event.currentTarget;
			var type = elData(link, 'type');
			
			UiSimpleDropdown.close(this._dropdownId);
			
			if (elData(this._element, 'filter') === type) {
				// filter did not change
				return;
			}
			
			elData(this._element, 'filter', type);
			
			elBySel('.active', this._dropdown).classList.remove('active');
			link.parentNode.classList.add('active');
			
			var button = elById(this._dropdownId);
			button.classList[(type === 'showAll' ? 'remove' : 'add')]('active');
			
			var icon = elBySel('.icon', button);
			icon.classList[(type === 'showAll' ? 'add' : 'remove')]('fa-eye');
			icon.classList[(type === 'showAll' ? 'remove' : 'add')]('fa-eye-slash');
		}
	};
	
	return UiItemListFilter;
});
