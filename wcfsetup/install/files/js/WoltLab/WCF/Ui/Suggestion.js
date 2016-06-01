/**
 * Flexible UI element featuring both a list of items and an input field with suggestion support.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Suggestion
 */
define(['Ajax', 'Core', 'Ui/SimpleDropdown'], function(Ajax, Core, UiSimpleDropdown) {
	"use strict";
	
	/**
	 * @constructor
	 * @param	{string}		elementId	input element id
	 * @param	{object<mixed>}		options		option list
	 */
	function UiSuggestion(elementId, options) { this.init(elementId, options); };
	UiSuggestion.prototype = {
		/**
		 * Initializes a new suggestion input.
		 * 
		 * @param	{string}		element id	input element id
		 * @param	{object<mixed>}		options		option list
		 */
		init: function(elementId, options) {
			this._dropdownMenu = null;
			this._value = '';
			
			this._element = elById(elementId);
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
			
			this._element.addEventListener(WCF_CLICK_EVENT, function(event) { event.stopPropagation(); });
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
			if (this._dropdownMenu === null || !UiSimpleDropdown.isOpen(this._element.id)) {
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
				UiSimpleDropdown.close(this._element.id);
				
				this._select(active);
			}
			else if (event.keyCode === 27) {
				if (UiSimpleDropdown.isOpen(this._element.id)) {
					UiSimpleDropdown.close(this._element.id);
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
			
			this._options.callbackSelect(this._element.id, { objectId: elData(item.children[0], 'object-id'), value: item.textContent });
			
			if (isEvent) {
				this._element.focus();
			}
		},
		
		/**
		 * Performs a search for the input value unless it is below the threshold.
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
					UiSimpleDropdown.close(this._element.id);
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
				this._dropdownMenu = elCreate('div');
				this._dropdownMenu.className = 'dropdownMenu';
				
				UiSimpleDropdown.initFragment(this._element, this._dropdownMenu);
			}
			else {
				this._dropdownMenu.innerHTML = '';
			}
			
			if (data.returnValues.length) {
				var anchor, item, listItem;
				for (var i = 0, length = data.returnValues.length; i < length; i++) {
					item = data.returnValues[i];
					
					anchor = elCreate('a');
					anchor.textContent = item.label;
					elData(anchor, 'object-id', item.objectID);
					anchor.addEventListener(WCF_CLICK_EVENT, this._select.bind(this));
					
					listItem = elCreate('li');
					if (i === 0) listItem.className = 'active';
					listItem.appendChild(anchor);
					
					this._dropdownMenu.appendChild(listItem);
				}
				
				UiSimpleDropdown.open(this._element.id);
			}
			else {
				UiSimpleDropdown.close(this._element.id);
			}
		}
	};
	
	return UiSuggestion;
});
