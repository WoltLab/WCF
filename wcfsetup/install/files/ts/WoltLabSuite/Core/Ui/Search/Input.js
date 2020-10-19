/**
 * Provides suggestions using an input field, designed to work with `wcf\data\ISearchAction`.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Search/Input
 */
define(['Ajax', 'Core', 'EventKey', 'Dom/Util', 'Ui/SimpleDropdown'], function(Ajax, Core, EventKey, DomUtil, UiSimpleDropdown) {
	"use strict";
	
	/**
	 * @param       {Element}       element         target input[type="text"]
	 * @param       {Object}        options         search options and settings
	 * @constructor
	 */
	function UiSearchInput(element, options) { this.init(element, options); }
	UiSearchInput.prototype = {
		/**
		 * Initializes the search input field.
		 * 
		 * @param       {Element}       element         target input[type="text"]
		 * @param       {Object}        options         search options and settings
		 */
		init: function(element, options) {
			this._element = element;
			if (!(this._element instanceof Element)) {
				throw new TypeError("Expected a valid DOM element.");
			}
			else if (this._element.nodeName !== 'INPUT' || (this._element.type !== 'search' && this._element.type !== 'text')) {
				throw new Error('Expected an input[type="text"].');
			}
			
			this._activeItem = null;
			this._dropdownContainerId = '';
			this._lastValue = '';
			this._list = null;
			this._request = null;
			this._timerDelay = null;
			
			this._options = Core.extend({
				ajax: {
					actionName: 'getSearchResultList',
					className: '',
					interfaceName: 'wcf\\data\\ISearchAction'
				},
				autoFocus: true,
				callbackDropdownInit: null,
				callbackSelect: null,
				delay: 500,
				excludedSearchValues: [],
				minLength: 3,
				noResultPlaceholder: '',
				preventSubmit: false
			}, options);
			
			// disable auto-complete as it collides with the suggestion dropdown
			elAttr(this._element, 'autocomplete', 'off');
			
			this._element.addEventListener('keydown', this._keydown.bind(this));
			this._element.addEventListener('keyup', this._keyup.bind(this));
		},
		
		/**
		 * Adds an excluded search value.
		 * 
		 * @param       {string}        value   excluded value
		 */
		addExcludedSearchValues: function (value) {
			if (this._options.excludedSearchValues.indexOf(value) === -1) {
				this._options.excludedSearchValues.push(value);
			}
		},
		
		/**
		 * Removes a value from the excluded search values.
		 * 
		 * @param       {string}        value   excluded value
		 */
		removeExcludedSearchValues: function (value) {
			var index = this._options.excludedSearchValues.indexOf(value);
			if (index !== -1) {
				this._options.excludedSearchValues.splice(index, 1);
			}
		},
		
		/**
		 * Handles the 'keydown' event.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_keydown: function(event) {
			if ((this._activeItem !== null && UiSimpleDropdown.isOpen(this._dropdownContainerId)) || this._options.preventSubmit) {
				if (EventKey.Enter(event)) {
					event.preventDefault();
				}
			}
			
			if (EventKey.ArrowUp(event) || EventKey.ArrowDown(event) || EventKey.Escape(event)) {
				event.preventDefault();
			}
		},
		
		/**
		 * Handles the 'keyup' event, provides keyboard navigation and executes search queries.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_keyup: function(event) {
			// handle dropdown keyboard navigation
			if (this._activeItem !== null || !this._options.autoFocus) {
				if (UiSimpleDropdown.isOpen(this._dropdownContainerId)) {
					if (EventKey.ArrowUp(event)) {
						event.preventDefault();
						
						return this._keyboardPreviousItem();
					}
					else if (EventKey.ArrowDown(event)) {
						event.preventDefault();
						
						return this._keyboardNextItem();
					}
					else if (EventKey.Enter(event)) {
						event.preventDefault();
						
						return this._keyboardSelectItem();
					}
				}
				else {
					this._activeItem = null;
				}
			}
			
			// close list on escape
			if (EventKey.Escape(event)) {
				UiSimpleDropdown.close(this._dropdownContainerId);
				
				return;
			}
			
			var value = this._element.value.trim();
			if (this._lastValue === value) {
				// value did not change, e.g. previously it was "Test" and now it is "Test ",
				// but the trailing whitespace has been ignored
				return;
			}
			
			this._lastValue = value;
			
			if (value.length < this._options.minLength) {
				if (this._dropdownContainerId) {
					UiSimpleDropdown.close(this._dropdownContainerId);
					this._activeItem = null;
				}
				
				// value below threshold
				return;
			}
			
			if (this._options.delay) {
				if (this._timerDelay !== null) {
					window.clearTimeout(this._timerDelay);
				}
				
				this._timerDelay = window.setTimeout((function() {
					this._search(value);
				}).bind(this), this._options.delay);
			}
			else {
				this._search(value);
			}
		},
		
		/**
		 * Queries the server with the provided search string.
		 * 
		 * @param       {string}        value   search string
		 * @protected
		 */
		_search: function(value) {
			if (this._request) {
				this._request.abortPrevious();
			}
			
			this._request = Ajax.api(this, this._getParameters(value));
		},
		
		/**
		 * Returns additional AJAX parameters.
		 * 
		 * @param       {string}        value   search string
		 * @return      {Object}        additional AJAX parameters
		 * @protected
		 */
		_getParameters: function(value) {
			return {
				parameters: {
					data: {
						excludedSearchValues: this._options.excludedSearchValues,
						searchString: value
					}
				}
			};
		},
		
		/**
		 * Selects the next dropdown item.
		 * 
		 * @protected
		 */
		_keyboardNextItem: function() {
			var nextItem;
			
			if (this._activeItem !== null) {
				this._activeItem.classList.remove('active');
				
				if (this._activeItem.nextElementSibling) {
					nextItem = this._activeItem.nextElementSibling;
				}
			}
			
			this._activeItem = nextItem || this._list.children[0];
			this._activeItem.classList.add('active');
		},
		
		/**
		 * Selects the previous dropdown item.
		 * 
		 * @protected
		 */
		_keyboardPreviousItem: function() {
			var nextItem;
			
			if (this._activeItem !== null) {
				this._activeItem.classList.remove('active');
				
				if (this._activeItem.previousElementSibling) {
					nextItem = this._activeItem.previousElementSibling;
				}
			}
			
			this._activeItem = nextItem || this._list.children[this._list.childElementCount - 1];
			this._activeItem.classList.add('active');
		},
		
		/**
		 * Selects the active item from the dropdown.
		 * 
		 * @protected
		 */
		_keyboardSelectItem: function() {
			this._selectItem(this._activeItem);
		},
		
		/**
		 * Selects an item from the dropdown by clicking it.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_clickSelectItem: function(event) {
			this._selectItem(event.currentTarget);
		},
		
		/**
		 * Selects an item.
		 * 
		 * @param       {Element}       item    selected item
		 * @protected
		 */
		_selectItem: function(item) {
			if (this._options.callbackSelect && this._options.callbackSelect(item) === false) {
				this._element.value = '';
			}
			else {
				this._element.value = elData(item, 'label');
			}
			
			this._activeItem = null;
			UiSimpleDropdown.close(this._dropdownContainerId);
		},
		
		/**
		 * Handles successful AJAX requests.
		 * 
		 * @param       {Object}        data    response data
		 * @protected
		 */
		_ajaxSuccess: function(data) {
			var createdList = false;
			if (this._list === null) {
				this._list = elCreate('ul');
				this._list.className = 'dropdownMenu';
				
				createdList = true;
				
				if (typeof this._options.callbackDropdownInit === 'function') {
					this._options.callbackDropdownInit(this._list);
				}
			}
			else {
				// reset current list
				this._list.innerHTML = '';
			}
			
			if (typeof data.returnValues === 'object') {
				var callbackClick = this._clickSelectItem.bind(this), listItem;
				
				for (var key in data.returnValues) {
					if (data.returnValues.hasOwnProperty(key)) {
						listItem = this._createListItem(data.returnValues[key]);
						
						listItem.addEventListener(WCF_CLICK_EVENT, callbackClick);
						this._list.appendChild(listItem);
					}
				}
			}
			
			if (createdList) {
				DomUtil.insertAfter(this._list, this._element);
				UiSimpleDropdown.initFragment(this._element.parentNode, this._list);
				
				this._dropdownContainerId = DomUtil.identify(this._element.parentNode);
			}
			
			if (this._dropdownContainerId) {
				this._activeItem = null;
				
				if (!this._list.childElementCount && this._handleEmptyResult() === false) {
					UiSimpleDropdown.close(this._dropdownContainerId);
				}
				else {
					UiSimpleDropdown.open(this._dropdownContainerId, true);
					
					// mark first item as active
					if (this._options.autoFocus && this._list.childElementCount && ~~elData(this._list.children[0], 'object-id')) {
						this._activeItem = this._list.children[0];
						this._activeItem.classList.add('active');
					}
				}
			}
		},
		
		/**
		 * Handles an empty result set, return a boolean false to hide the dropdown.
		 * 
		 * @return      {boolean}      false to close the dropdown
		 * @protected
		 */
		_handleEmptyResult: function() {
			if (!this._options.noResultPlaceholder) {
				return false;
			}
			
			var listItem = elCreate('li');
			listItem.className = 'dropdownText';
			
			var span = elCreate('span');
			span.textContent = this._options.noResultPlaceholder;
			listItem.appendChild(span);
			
			this._list.appendChild(listItem);
			
			return true;
		},
		
		/**
		 * Creates an list item from response data.
		 * 
		 * @param       {Object}        item    response data
		 * @return      {Element}       list item
		 * @protected
		 */
		_createListItem: function(item) {
			var listItem = elCreate('li');
			elData(listItem, 'object-id', item.objectID);
			elData(listItem, 'label', item.label);
			
			var span = elCreate('span');
			span.textContent = item.label;
			listItem.appendChild(span);
			
			return listItem;
		},
		
		_ajaxSetup: function() {
			return {
				data: this._options.ajax
			};
		}
	};
	
	return UiSearchInput;
});
