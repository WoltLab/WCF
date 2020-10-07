/**
 * Flexible UI element featuring both a list of items and an input field with suggestion support.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/ItemList
 */
define(['Core', 'Dictionary', 'Language', 'Dom/Traverse', 'EventKey', 'WoltLabSuite/Core/Ui/Suggestion', 'Ui/SimpleDropdown'], function(Core, Dictionary, Language, DomTraverse, EventKey, UiSuggestion, UiSimpleDropdown) {
	"use strict";
	
	var _activeId = '';
	var _data = new Dictionary();
	var _didInit = false;
	
	var _callbackKeyDown = null;
	var _callbackKeyPress = null;
	var _callbackKeyUp = null;
	var _callbackPaste = null;
	var _callbackRemoveItem = null;
	var _callbackBlur = null;
	
	/**
	 * @exports	WoltLabSuite/Core/Ui/ItemList
	 */
	return {
		/**
		 * Initializes an item list.
		 * 
		 * The `values` argument must be empty or contain a list of strings or object, e.g.
		 * `['foo', 'bar']` or `[{ objectId: 1337, value: 'baz'}, {...}]`
		 * 
		 * @param	{string}	elementId	input element id
		 * @param	{Array}		values		list of existing values
		 * @param	{Object}	options		option list
		 */
		init: function(elementId, values, options) {
			var element = elById(elementId);
			if (element === null) {
				throw new Error("Expected a valid element id, '" + elementId + "' is invalid.");
			}
			
			// remove data from previous instance
			if (_data.has(elementId)) {
				var tmp = _data.get(elementId);
				
				for (var key in tmp) {
					if (tmp.hasOwnProperty(key)) {
						var el = tmp[key];
						if (el instanceof Element && el.parentNode) {
							elRemove(el);
						}
					}
				}
				
				UiSimpleDropdown.destroy(elementId);
				_data.delete(elementId);
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
				// Callback for the custom shadow synchronization.
				callbackSyncShadow: null,
				// Callback to set values during the setup.
				callbackSetupValues: null,
				// value may contain the placeholder `{$objectId}`
				submitFieldName: ''
			}, options);
			
			var form = DomTraverse.parentByTag(element, 'FORM');
			if (form !== null) {
				if (options.isCSV === false) {
					if (!options.submitFieldName.length && typeof options.callbackSubmit !== 'function') {
						throw new Error("Expected a valid function for option 'callbackSubmit', a non-empty value for option 'submitFieldName' or enabling the option 'submitFieldCSV'.");
					}
					
					form.addEventListener('submit', (function() {
						if (this._acceptsNewItems(elementId)) {
							var value = _data.get(elementId).element.value.trim();
							if (value.length) {
								this._addItem(elementId, { objectId: 0, value: value });
							}
						}
						
						var values = this.getValues(elementId);
						if (options.submitFieldName.length) {
							var input;
							for (var i = 0, length = values.length; i < length; i++) {
								input = elCreate('input');
								input.type = 'hidden';
								input.name = options.submitFieldName.replace('{$objectId}', values[i].objectId);
								input.value = values[i].value;
								
								form.appendChild(input);
							}
						}
						else {
							options.callbackSubmit(form, values);
						}
					}).bind(this));
				}
				else {
					form.addEventListener('submit', function() {
						if (this._acceptsNewItems(elementId)) {
							var value = _data.get(elementId).element.value.trim();
							if (value.length) {
								this._addItem(elementId, {objectId: 0, value: value});
							}
						}
					}.bind(this));
				}
			}
			
			this._setup();
			
			var data = this._createUI(element, options);
			//noinspection JSUnresolvedVariable
			var suggestion = new UiSuggestion(elementId, {
				ajax: options.ajax,
				callbackSelect: this._addItem.bind(this),
				excludedSearchValues: options.excludedSearchValues
			});
			
			_data.set(elementId, {
				dropdownMenu: null,
				element: data.element,
				limitReached: data.limitReached,
				list: data.list,
				listItem: data.element.parentNode,
				options: options,
				shadow: data.shadow,
				suggestion: suggestion
			});
			
			if (options.callbackSetupValues) {
				values = options.callbackSetupValues();
			}
			else {
				values = (data.values.length) ? data.values : values;
			}
			
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
		 * @param	{string}	elementId	input element id
		 * @return	{Array}		list of objects containing object id and value
		 */
		getValues: function(elementId) {
			if (!_data.has(elementId)) {
				throw new Error("Element id '" + elementId + "' is unknown.");
			}
			
			var data = _data.get(elementId);
			var values = [];
			elBySelAll('.item > span', data.list, function(span) {
				values.push({
					objectId: ~~elData(span, 'object-id'),
					value: span.textContent.trim(),
					type: elData(span, 'type')
				});
			});
			
			return values;
		},
		
		/**
		 * Sets the list of current values.
		 * 
		 * @param	{string}	elementId	input element id
		 * @param	{Array}		values		list of objects containing object id and value
		 */
		setValues: function(elementId, values) {
			if (!_data.has(elementId)) {
				throw new Error("Element id '" + elementId + "' is unknown.");
			}
			
			var data = _data.get(elementId);
			
			// remove all existing items first
			var i, length;
			var items = DomTraverse.childrenByClass(data.list, 'item');
			for (i = 0, length = items.length; i < length; i++) {
				this._removeItem(null, items[i], true);
			}
			
			// add new items
			for (i = 0, length = values.length; i < length; i++) {
				this._addItem(elementId, values[i]);
			}
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
			_callbackPaste = this._paste.bind(this);
			_callbackRemoveItem = this._removeItem.bind(this);
			_callbackBlur = this._blur.bind(this);
		},
		
		/**
		 * Creates the DOM structure for target element. If `element` is a `<textarea>`
		 * it will be automatically replaced with an `<input>` element.
		 * 
		 * @param	{Element}	element		input element
		 * @param	{Object}	options		option list
		 */
		_createUI: function(element, options) {
			var list = elCreate('ol');
			list.className = 'inputItemList' + (element.disabled ? ' disabled' : '');
			elData(list, 'element-id', element.id);
			list.addEventListener(WCF_CLICK_EVENT, function(event) {
				if (event.target === list) {
					//noinspection JSUnresolvedFunction
					element.focus();
				}
			});
			
			var listItem = elCreate('li');
			listItem.className = 'input';
			list.appendChild(listItem);
			
			element.addEventListener('keydown', _callbackKeyDown);
			element.addEventListener('keypress', _callbackKeyPress);
			element.addEventListener('keyup', _callbackKeyUp);
			element.addEventListener('paste', _callbackPaste);
			var hasFocus = element === document.activeElement;
			if (hasFocus) {
				//noinspection JSUnresolvedFunction
				element.blur();
			}
			element.addEventListener('blur', _callbackBlur);
			element.parentNode.insertBefore(list, element);
			listItem.appendChild(element);
			if (hasFocus) {
				window.setTimeout(function() {
					//noinspection JSUnresolvedFunction
					element.focus();
				}, 1);
			}
			
			if (options.maxLength !== -1) {
				elAttr(element, 'maxLength', options.maxLength);
			}
			
			var limitReached = elCreate('span');
			limitReached.className = 'inputItemListLimitReached';
			limitReached.textContent = Language.get('wcf.global.form.input.maxItems');
			elHide(limitReached);
			listItem.appendChild(limitReached);
			
			var shadow = null, values = [];
			if (options.isCSV) {
				shadow = elCreate('input');
				shadow.className = 'itemListInputShadow';
				shadow.type = 'hidden';
				//noinspection JSUnresolvedVariable
				shadow.name = element.name;
				element.removeAttribute('name');
				
				list.parentNode.insertBefore(shadow, list);
				
				//noinspection JSUnresolvedVariable
				var value, tmp = element.value.split(',');
				for (var i = 0, length = tmp.length; i < length; i++) {
					value = tmp[i].trim();
					if (value.length) {
						values.push(value);
					}
				}
				
				if (element.nodeName === 'TEXTAREA') {
					var inputElement = elCreate('input');
					inputElement.type = 'text';
					element.parentNode.insertBefore(inputElement, element);
					inputElement.id = element.id;
					
					elRemove(element);
					element = inputElement;
				}
			}
			
			return {
				element: element,
				limitReached: limitReached,
				list: list,
				shadow: shadow,
				values: values
			};
		},
		
		/**
		 * Returns true if the input accepts new items.
		 * 
		 * @param       {string}        elementId       input element id
		 * @return      {boolean}       true if at least one more item can be added
		 * @protected
		 */
		_acceptsNewItems: function (elementId) {
			var data = _data.get(elementId);
			if (data.options.maxItems === -1) {
				return true;
			}
			
			return (data.list.childElementCount - 1 < data.options.maxItems);
		},
		
		/**
		 * Enforces the maximum number of items.
		 * 
		 * @param	{string}	elementId	input element id
		 */
		_handleLimit: function(elementId) {
			var data = _data.get(elementId);
			if (this._acceptsNewItems(elementId)) {
				elShow(data.element);
				elHide(data.limitReached);
			}
			else {
				elHide(data.element);
				elShow(data.limitReached);
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
		 * @param	{Event}         event		event object
		 */
		_keyPress: function(event) {
			if (EventKey.Enter(event) || EventKey.Comma(event)) {
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
		 * Splits comma-separated values being pasted into the input field.
		 * 
		 * @param       {Event}         event
		 * @protected
		 */
		_paste: function (event) {
			var text = '';
			if (typeof window.clipboardData === 'object') {
				// IE11
				text = window.clipboardData.getData('Text');
			}
			else {
				text = event.clipboardData.getData('text/plain');
			}
			
			var element = event.currentTarget;
			var elementId = element.id;
			var maxLength = ~~elAttr(element, 'maxLength');
			
			text.split(/,/).forEach((function(item) {
				item = item.trim();
				if (maxLength && item.length > maxLength) {
					// truncating items provides a better UX than throwing an error or silently discarding it
					item = item.substr(0, maxLength);
				}
				
				if (item.length > 0 && this._acceptsNewItems(elementId)) {
					this._addItem(elementId, {objectId: 0, value: item});
				}
			}).bind(this));
			
			event.preventDefault();
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
		 * @param	{object}	value		item value
		 */
		_addItem: function(elementId, value) {
			var data = _data.get(elementId);
			
			var listItem = elCreate('li');
			listItem.className = 'item';
			
			var content = elCreate('span');
			content.className = 'content';
			elData(content, 'object-id', value.objectId);
			if (value.type) elData(content, 'type', value.type);
			content.textContent = value.value;
			listItem.appendChild(content);
			
			if (!data.element.disabled) {
				var button = elCreate('a');
				button.className = 'icon icon16 fa-times';
				button.addEventListener(WCF_CLICK_EVENT, _callbackRemoveItem);
				listItem.appendChild(button);
			}
			
			data.list.insertBefore(listItem, data.listItem);
			data.suggestion.addExcludedValue(value.value);
			data.element.value = '';
			
			if (!data.element.disabled) {
				this._handleLimit(elementId);
			}
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
		 * @param	{Element?}	item		list item
		 * @param	{boolean?}	noFocus		input element will not be focused if true
		 */
		_removeItem: function(event, item, noFocus) {
			item = (event === null) ? item : event.currentTarget.parentNode;
			
			var parent = item.parentNode;
			//noinspection JSCheckFunctionSignatures
			var elementId = elData(parent, 'element-id');
			var data = _data.get(elementId);
			
			data.suggestion.removeExcludedValue(item.children[0].textContent);
			parent.removeChild(item);
			if (!noFocus) data.element.focus();
			
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
			if (typeof data.options.callbackSyncShadow === 'function') {
				return data.options.callbackSyncShadow(data);
			}
			
			var value = '', values = this.getValues(data.element.id);
			for (var i = 0, length = values.length; i < length; i++) {
				value += (value.length ? ',' : '') + values[i].value;
			}
			
			data.shadow.value = value;
			
			return values;
		},
		
		/**
		 * Handles the blur event.
		 *
		 * @param	{object}	event		event object
		 */
		_blur: function(event) {
			var input = event.currentTarget;
			var data = _data.get(input.id);
			if (data.options.restricted) {
				// restricted item lists only allow results from the dropdown to be picked
				return;
			}
			
			var value = input.value.trim();
			if (value.length) {
				if (!data.suggestion || !data.suggestion.isActive()) {
					this._addItem(input.id, { objectId: 0, value: value });
				}
			}
		}
	};
});
