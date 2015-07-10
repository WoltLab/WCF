/**
 * Flexible UI element featuring both a list of items and an input field with suggestion support.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/Suggestion
 */
define(['Ajax', 'Core', 'UI/SimpleDropdown'], function(Ajax, Core, UISimpleDropdown) {
	"use strict";
	
	/**
	 * @constructor
	 * @param	{string}		elementId	input element id
	 * @param	{object<mixed>}		options		option list
	 */
	function UISuggestion(elementId, options) { this.init(elementId, options); };
	UISuggestion.prototype = {
		/**
		 * Initializes a new suggestion input.
		 * 
		 * @param	{string}		element id	input element id
		 * @param	{object<mixed>}		options		option list
		 */
		init: function(elementId, options) {
			this._dropdownMenu = null;
			this._value = '';
			
			this._element = document.getElementById(elementId);
			if (this._element === null) {
				throw new Error("Expected a valid element id.");
			}
			
			this._options = Core.extend({
				ajax: {
					actionName: 'getSearchResultList',
					className: '',
					interfaceName: 'wcf\\data\\ISearchAction',
					parameters: {
						data: {}
					}
				},
				
				// will be executed once a value from the dropdown has been selected
				callbackSelect: null,
				// list of excluded search values
				excludedSearchValues: [],
				// minimum number of characters required to trigger a search request
				treshold: 3
			}, options);
			
			if (typeof this._options.callbackSelect !== 'function') {
				throw new Error("Expected a valid callback for option 'callbackSelect'.");
			}
			
			this._element.addEventListener('click', function(event) { event.stopPropagation(); });
			this._element.addEventListener('keydown', this._keyDown.bind(this));
			this._element.addEventListener('keyup', this._keyUp.bind(this));
		},
		
		/**
		 * Adds an excluded search value.
		 * 
		 * @param	{string}	value		excluded value
		 */
		addExcludedValue: function(value) {
			if (this._options.excludedSearchValues.indexOf(value) === -1) {
				this._options.excludedSearchValues.push(value);
			}
		},
		
		/**
		 * Removes an excluded search value.
		 * 
		 * @param	{string}	value		excluded value
		 */
		removeExcludedValue: function(value) {
			var index = this._options.excludedSearchValues.indexOf(value);
			if (index !== -1) {
				this._options.excludedSearchValues.splice(index, 1);
			}
		},
		
		/**
		 * Handles the keyboard navigation for interaction with the suggestion list.
		 * 
		 * @param	{object}	event		event object
		 */
		_keyDown: function(event) {
			if (this._dropdownMenu === null || !UISimpleDropdown.isOpen(this._element.id)) {
				return true;
			}
			
			if (event.keyCode !== 13 && event.keyCode !== 27 && event.keyCode !== 38 && event.keyCode !== 40) {
				return true;
			}
			
			var active, i = 0, length = this._dropdownMenu.childElementCount;
			while (i < length) {
				active = this._dropdownMenu.children[i];
				if (active.classList.contains('active')) {
					break;
				}
				
				i++;
			}
			
			if (event.keyCode === 13) {
				// Enter
				UISimpleDropdown.close(this._element.id);
				
				this._select(active);
			}
			else if (event.keyCode === 27) {
				if (UISimpleDropdown.isOpen(this._element.id)) {
					UISimpleDropdown.close(this._element.id);
				}
				else {
					// let the event pass through
					return true;
				}
			}
			else {
				var index = 0;
				
				if (event.keyCode === 38) {
					// ArrowUp
					index = ((i === 0) ? length : i) - 1;
				}
				else if (event.keyCode === 40) {
					// ArrowDown
					index = i + 1;
					if (index === length) index = 0;
				}
				
				if (index !== i) {
					active.classList.remove('active');
					this._dropdownMenu.children[index].classList.add('active');
				}
			}
			
			event.preventDefault();
			return false;
		},
		
		/**
		 * Selects an item from the list.
		 * 
		 * @param	{(Element|Event)}	item	list item or event object
		 */
		_select: function(item) {
			var isEvent = (item instanceof Event);
			if (isEvent) {
				item = item.currentTarget.parentNode;
			}
			
			this._options.callbackSelect(this._element.id, { objectId: item.children[0].getAttribute('data-object-id'), value: item.textContent });
			
			if (isEvent) {
				this._element.focus();
			}
		},
		
		/**
		 * Performs a search for the input value unless it is below the treshold.
		 * 
		 * @param	{object}		event		event object
		 */
		_keyUp: function(event) {
			var value = event.currentTarget.value.trim();
			
			if (this._value === value) {
				return;
			}
			else if (value.length < this._options.treshold) {
				if (this._dropdownMenu !== null) {
					UISimpleDropdown.close(this._element.id);
				}
				
				this._value = value;
				
				return;
			}
			
			this._value = value;
			
			Ajax.api(this, {
				parameters: {
					data: {
						excludedSearchValues: this._options.excludedSearchValues,
						searchString: value
					}
				}
			});
		},
		
		_ajaxSetup: function() {
			return {
				data: this._options.ajax
			};
		},
		
		/**
		 * Handles successful Ajax requests.
		 * 
		 * @param	{object}	data		response values
		 */
		_ajaxSuccess: function(data) {
			if (this._dropdownMenu === null) {
				this._dropdownMenu = document.createElement('div');
				this._dropdownMenu.className = 'dropdownMenu';
				
				UISimpleDropdown.initFragment(this._element, this._dropdownMenu);
			}
			else {
				this._dropdownMenu.innerHTML = '';
			}
			
			if (data.returnValues.length) {
				var anchor, item, listItem;
				for (var i = 0, length = data.returnValues.length; i < length; i++) {
					item = data.returnValues[i];
					
					anchor = document.createElement('a');
					anchor.textContent = item.label;
					anchor.setAttribute('data-object-id', item.objectID);
					anchor.addEventListener('click', this._select.bind(this));
					
					listItem = document.createElement('li');
					if (i === 0) listItem.className = 'active';
					listItem.appendChild(anchor);
					
					this._dropdownMenu.appendChild(listItem);
				}
				
				UISimpleDropdown.open(this._element.id);
			}
			else {
				UISimpleDropdown.close(this._element.id);
			}
		}
	};
	
	return UISuggestion;
});
