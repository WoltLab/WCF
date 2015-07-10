/**
 * Flexible UI element featuring both a list of items and an input field with suggestion support.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/ItemList
 */
define(['Core', 'Dictionary', 'Language', 'DOM/Traverse', 'WoltLab/WCF/UI/Suggestion'], function(Core, Dictionary, Language, DOMTraverse, UISuggestion) {
	"use strict";
	
	var _activeId = '';
	var _data = new Dictionary();
	var _didInit = false;
	
	var _callbackKeyDown = null;
	var _callbackKeyPress = null;
	var _callbackKeyUp = null;
	var _callbackRemoveItem = null;
	
	/**
	 * @exports	WoltLab/WCF/UI/ItemList
	 */
	var UIItemList = {
		/**
		 * Initializes an item list.
		 * 
		 * The `values` argument must be empty or contain a list of strings or object, e.g.
		 * `['foo', 'bar']` or `[{ objectId: 1337, value: 'baz'}, {...}]`
		 * 
		 * @param	{string}		elementId	input element id
		 * @param	{array<mixed>}		values		list of existing values
		 * @param	{object<string>}	options		option list
		 */
		init: function(elementId, values, options) {
			var element = document.getElementById(elementId);
			if (element === null) {
				throw new Error("Expected a valid element id.");
			}
			
			options = Core.extend({
				// search parameters for suggestions
				ajax: {
					actionName: 'getSearchResultList',
					className: '',
					data: {}
				},
				
				// list of excluded string values, e.g. `['ignore', 'these strings', 'when', 'searching']`
				excludedSearchValues: [],
				// maximum number of items this list may contain, `-1` for infinite
				maxItems: -1,
				// maximum length of an item value, `-1` for infinite
				maxLength: -1,
				// disallow custom values, only values offered by the suggestion dropdown are accepted
				restricted: false,
				
				// initial value will be interpreted as comma separated value and submitted as such
				isCSV: false,
				
				// will be invoked whenever the items change, receives the element id first and list of values second
				callbackChange: null,
				// callback once the form is about to be submitted
				callbackSubmit: null,
				// value may contain the placeholder `{$objectId}`
				submitFieldName: ''
			}, options);
			
			var form = DOMTraverse.parentByTag(element, 'FORM');
			if (form !== null) {
				if (options.isCSV === false) {
					if (!options.submitFieldName.length && typeof options.callbackSubmit !== 'function') {
						throw new Error("Expected a valid function for option 'callbackSubmit', a non-empty value for option 'submitFieldName' or enabling the option 'submitFieldCSV'.");
					}
					
					form.addEventListener('submit', (function() {
						var values = this.getValues(elementId);
						if (options.submitFieldName.length) {
							var input;
							for (var i = 0, length = values.length; i < length; i++) {
								input = document.createElement('input');
								input.type = 'hidden';
								input.name = options.submitFieldName.replace(/{$objectId}/, values[i].objectId);
								input.value = values[i].value;
								
								form.appendChild(input);
							}
						}
						else {
							options.callbackSubmit(form, values);
						}
					}).bind(this));
				}
			}
			
			this._setup();
			
			var data = this._createUI(element, options, values);
			var suggestion = new UISuggestion(elementId, {
				ajax: options.ajax,
				callbackSelect: this._addItem.bind(this),
				excludedSearchValues: options.excludedSearchValues
			});
			
			_data.set(elementId, {
				dropdownMenu: null,
				element: data.element,
				list: data.list,
				listItem: data.element.parentNode,
				options: options,
				shadow: data.shadow,
				suggestion: suggestion
			});
			
			values = (data.values.length) ? data.values : values;
			if (Array.isArray(values)) {
				var value;
				for (var i = 0, length = values.length; i < length; i++) {
					value = values[i];
					if (typeof value === 'string') {
						value = { objectId: 0, value: value };
					}
					
					this._addItem(elementId, value);
				}
			}
		},
		
		/**
		 * Returns the list of current values.
		 * 
		 * @param	{string}		element id	input element id
		 * @return	{array<object>}		list of objects containing object id and value
		 */
		getValues: function(elementId) {
			if (!_data.has(elementId)) {
				throw new Error("Element id '" + elementId + "' is unknown.");
			}
			
			var data = _data.get(elementId);
			var items = DOMTraverse.childrenByClass(data.list, 'item');
			var values = [], value, item;
			for (var i = 0, length = items.length; i < length; i++) {
				item = items[i];
				value = {
					objectId: item.getAttribute('data-object-id'),
					value: DOMTraverse.childByTag(item, 'SPAN').textContent
				};
				
				values.push(value);
			}
			
			return values;
		},
		
		/**
		 * Binds static event listeners.
		 */
		_setup: function() {
			if (_didInit) {
				return;
			}
			
			_didInit = true;
			
			_callbackKeyDown = this._keyDown.bind(this);
			_callbackKeyPress = this._keyPress.bind(this);
			_callbackKeyUp = this._keyUp.bind(this);
			_callbackRemoveItem = this._removeItem.bind(this);
		},
		
		/**
		 * Creates the DOM structure for target element. If `element` is a `<textarea>`
		 * it will be automatically replaced with an `<input>` element.
		 * 
		 * @param	{Element}		element		input element
		 * @param	{object<string>}	options		option list
		 */
		_createUI: function(element, options) {
			var list = document.createElement('ol');
			list.className = 'inputItemList';
			list.setAttribute('data-element-id', element.id);
			list.addEventListener('click', function(event) {
				if (event.target === list) element.focus();
			});
			
			var listItem = document.createElement('li');
			listItem.className = 'input';
			list.appendChild(listItem);
			
			element.addEventListener('keydown', _callbackKeyDown);
			element.addEventListener('keypress', _callbackKeyPress);
			element.addEventListener('keyup', _callbackKeyUp);
			
			element.parentNode.insertBefore(list, element);
			listItem.appendChild(element);
			
			if (options.maxLength !== -1) {
				element.setAttribute('maxLength', options.maxLength);
			}
			
			var shadow = null, values = [];
			if (options.isCSV) {
				shadow = document.createElement('input');
				shadow.className = 'itemListInputShadow';
				shadow.type = 'hidden';
				shadow.name = element.name;
				element.removeAttribute('name');
				
				list.parentNode.insertBefore(shadow, list);
				
				if (element.nodeName === 'TEXTAREA') {
					var value, tmp = element.value.split(',');
					for (var i = 0, length = tmp.length; i < length; i++) {
						value = tmp[i].trim();
						if (value.length) {
							values.push(value);
						}
					}
					
					var inputElement = document.createElement('input');
					element.parentNode.insertBefore(inputElement, element);
					inputElement.id = element.id;
					
					element.parentNode.removeChild(element);
					element = inputElement;
				}
			}
			
			return {
				element: element,
				list: list,
				shadow: shadow,
				values: values
			};
		},
		
		/**
		 * Enforces the maximum number of items.
		 * 
		 * @param	{string}	elementId	input element id
		 */
		_handleLimit: function(elementId) {
			var data = _data.get(elementId);
			if (data.options.maxItems === -1) {
				return;
			}
			
			if (data.list.childElementCount - 1 < data.options.maxItems) {
				if (data.element.disabled) {
					data.element.disabled = false;
					data.element.removeAttribute('placeholder');
				}
			}
			else if (!data.element.disabled) {
				data.element.disabled = true;
				data.element.setAttribute('placeholder', Language.get('wcf.global.form.input.maxItems'));
			}
		},
		
		/**
		 * Sets the active item list id and handles keyboard access to remove an existing item.
		 * 
		 * @param	{object}	event		event object
		 */
		_keyDown: function(event) {
			var input = event.currentTarget;
			var lastItem = input.parentNode.previousElementSibling;
			
			_activeId = input.id;
			
			if (event.keyCode === 8) {
				// 8 = [BACKSPACE]
				if (input.value.length === 0) {
					if (lastItem !== null) {
						if (lastItem.classList.contains('active')) {
							this._removeItem(null, lastItem);
						}
						else {
							lastItem.classList.add('active');
						}
					}
				}
			}
			else if (event.keyCode === 27) {
				// 27 = [ESC]
				if (lastItem !== null && lastItem.classList.contains('active')) {
					lastItem.classList.remove('active');
				}
			}
		},
		
		/**
		 * Handles the `[ENTER]` and `[,]` key to add an item to the list unless it is restricted.
		 * 
		 * @param	{object}	event		event object
		 */
		_keyPress: function(event) {
			// 13 = [ENTER], 44 = [,]
			if (event.charCode === 13 || event.charCode === 44) {
				event.preventDefault();
				
				if (_data.get(event.currentTarget.id).options.restricted) {
					// restricted item lists only allow results from the dropdown to be picked
					return;
				}
				
				var value = event.currentTarget.value.trim();
				if (value.length) {
					this._addItem(event.currentTarget.id, { objectId: 0, value: value });
				}
			}
		},
		
		/**
		 * Handles the keyup event to unmark an item for deletion.
		 * 
		 * @param	{object}	event		event object
		 */
		_keyUp: function(event) {
			var input = event.currentTarget;
			
			if (input.value.length > 0) {
				var lastItem = input.parentNode.previousElementSibling;
				if (lastItem !== null) {
					lastItem.classList.remove('active');
				}
			}
		},
		
		/**
		 * Adds an item to the list.
		 * 
		 * @param	{string}	elementId	input element id
		 * @param	{string}	value		item value
		 */
		_addItem: function(elementId, value) {
			var data = _data.get(elementId);
			
			var listItem = document.createElement('li');
			listItem.className = 'item';
			
			var content = document.createElement('span');
			content.className = 'content';
			content.setAttribute('data-object-id', value.objectId);
			content.textContent = value.value;
			
			var button = document.createElement('a');
			button.className = 'icon icon16 fa-times';
			button.addEventListener('click', _callbackRemoveItem);
			listItem.appendChild(content);
			listItem.appendChild(button);
			
			data.list.insertBefore(listItem, data.listItem);
			data.suggestion.addExcludedValue(value.value);
			data.element.value = '';
			
			this._handleLimit(elementId);
			var values = this._syncShadow(data);
			
			if (typeof data.options.callbackChange === 'function') {
				if (values === null) values = this.getValues(elementId);
				data.options.callbackChange(elementId, values);
			}
		},
		
		/**
		 * Removes an item from the list.
		 * 
		 * @param	{?object}	event		event object
		 * @param	{Element=}	item		list item
		 */
		_removeItem: function(event, item) {
			item = (event === null) ? item : event.currentTarget.parentNode;
			
			var parent = item.parentNode;
			var elementId = parent.getAttribute('data-element-id');
			var data = _data.get(elementId);
			
			data.suggestion.removeExcludedValue(item.children[0].textContent);
			parent.removeChild(item);
			data.element.focus();
			
			this._handleLimit(elementId);
			var values = this._syncShadow(data);
			
			if (typeof data.options.callbackChange === 'function') {
				if (values === null) values = this.getValues(elementId);
				data.options.callbackChange(elementId, values);
			}
		},
		
		/**
		 * Synchronizes the shadow input field with the current list item values.
		 * 
		 * @param	{object}	data		element data
		 */
		_syncShadow: function(data) {
			if (!data.options.isCSV) return null;
			
			var value = '', values = this.getValues(data.element.id);
			for (var i = 0, length = values.length; i < length; i++) {
				value += (value.length ? ',' : '') + values[i].value;
			}
			
			data.shadow.value = value;
			
			return values;
		}
	};
	
	return UIItemList;
});
