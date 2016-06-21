/**
 * I18n interface for input and textarea fields.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Language/Input
 */
define(['Core', 'Dictionary', 'Language', 'ObjectMap', 'StringUtil', 'Dom/Traverse', 'Dom/Util', 'Ui/SimpleDropdown'], function(Core, Dictionary, Language, ObjectMap, StringUtil, DomTraverse, DomUtil, UiSimpleDropdown) {
	"use strict";
	
	var _elements = new Dictionary();
	var _didInit = false;
	var _forms = new ObjectMap();
	var _values = new Dictionary();
	
	var _callbackDropdownToggle = null;
	var _callbackSubmit = null;
	
	/**
	 * @exports	WoltLab/WCF/Language/Input
	 */
	var LanguageInput = {
		/**
		 * Initializes an input field.
		 * 
		 * @param	{string}			elementId		input element id
		 * @param	{object<int, string>}		values			preset values per language id
		 * @param	{object<int, string>}		availableLanguages	language names per language id
		 * @param	{boolean}			forceSelection		require i18n input
		 */
		init: function(elementId, values, availableLanguages, forceSelection) {
			if (_values.has(elementId)) {
				return;
			}
			
			var element = elById(elementId);
			if (element === null) {
				throw new Error("Expected a valid element id, cannot find '" + elementId + "'.");
			}
			
			this._setup();
			
			// unescape values
			var unescapedValues = new Dictionary();
			for (var key in values) {
				if (objOwns(values, key)) {
					unescapedValues.set(~~key, StringUtil.unescapeHTML(values[key]));
				}
			}
			
			_values.set(elementId, unescapedValues);
			
			this._initElement(elementId, element, unescapedValues, availableLanguages, forceSelection);
		},
		
		/**
		 * Caches common event listener callbacks.
		 */
		_setup: function() {
			if (_didInit) return;
			_didInit = true;
			
			_callbackDropdownToggle = this._dropdownToggle.bind(this);
			_callbackSubmit = this._submit.bind(this);
		},
		
		/**
		 * Sets up DOM and event listeners for an input field.
		 * 
		 * @param	{string}			elementId		input element id
		 * @param	{Element}			element			input or textarea element
		 * @param	{Dictionary}			values			preset values per language id
		 * @param	{object<int, string>}		availableLanguages	language names per language id
		 * @param	{boolean}			forceSelection		require i18n input
		 */
		_initElement: function(elementId, element, values, availableLanguages, forceSelection) {
			var container = element.parentNode;
			if (!container.classList.contains('inputAddon')) {
				container = elCreate('div');
				container.className = 'inputAddon' + (element.nodeName === 'TEXTAREA' ? ' inputAddonTextarea' : '');
				elData(container, 'input-id', elementId);
				
				element.parentNode.insertBefore(container, element);
				container.appendChild(element);
			}
			
			container.classList.add('dropdown');
			var button = elCreate('span');
			button.className = 'button dropdownToggle inputPrefix';
			
			var span = elCreate('span');
			span.textContent = Language.get('wcf.global.button.disabledI18n');
			
			button.appendChild(span);
			container.insertBefore(button, element);
			
			var dropdownMenu = elCreate('ul');
			dropdownMenu.className = 'dropdownMenu';
			DomUtil.insertAfter(dropdownMenu, button);
			
			var callbackClick = (function(event, isInit) {
				var languageId = ~~elData(event.currentTarget, 'language-id');
				
				var activeItem = DomTraverse.childByClass(dropdownMenu, 'active');
				if (activeItem !== null) activeItem.classList.remove('active');
				
				if (languageId) event.currentTarget.classList.add('active');
				
				this._select(elementId, languageId, isInit || false);
			}).bind(this);
			
			// build language dropdown
			for (var languageId in availableLanguages) {
				if (objOwns(availableLanguages, languageId)) {
					var listItem = elCreate('li');
					elData(listItem, 'language-id', languageId);
					
					span = elCreate('span');
					span.textContent = availableLanguages[languageId];
					
					listItem.appendChild(span);
					listItem.addEventListener(WCF_CLICK_EVENT, callbackClick);
					dropdownMenu.appendChild(listItem);
				}
			}
			
			if (forceSelection !== true) {
				var listItem = elCreate('li');
				listItem.className = 'dropdownDivider';
				dropdownMenu.appendChild(listItem);
				
				listItem = elCreate('li');
				elData(listItem, 'language-id', 0);
				span = elCreate('span');
				span.textContent = Language.get('wcf.global.button.disabledI18n');
				listItem.appendChild(span);
				listItem.addEventListener(WCF_CLICK_EVENT, callbackClick);
				dropdownMenu.appendChild(listItem);
			}
			
			var activeItem = null;
			if (forceSelection === true || values.size) {
				for (var i = 0, length = dropdownMenu.childElementCount; i < length; i++) {
					if (~~elData(dropdownMenu.children[i], 'language-id') === LANGUAGE_ID) {
						activeItem = dropdownMenu.children[i];
						break;
					}
				}
			}
			
			UiSimpleDropdown.init(button);
			UiSimpleDropdown.registerCallback(container.id, _callbackDropdownToggle);
			
			_elements.set(elementId, {
				buttonLabel: button.children[0],
				element: element,
				languageId: 0,
				isEnabled: true,
				forceSelection: forceSelection
			});
			
			// bind to submit event
			var submit = DomTraverse.parentByTag(element, 'FORM');
			if (submit !== null) {
				submit.addEventListener('submit', _callbackSubmit);
				
				var elementIds = _forms.get(submit);
				if (elementIds === undefined) {
					elementIds = [];
					_forms.set(submit, elementIds);
				}
				
				elementIds.push(elementId);
			}
			
			if (activeItem !== null) {
				callbackClick({ currentTarget: activeItem }, true);
			}
		},
		
		/**
		 * Selects a language or non-i18n from the dropdown list.
		 * 
		 * @param	{string}	elementId	input element id
		 * @param	{int}		languageId	language id or `0` to disable i18n
		 * @param	{boolean}	isInit		triggers pre-selection on init
		 */
		_select: function(elementId, languageId, isInit) {
			var data = _elements.get(elementId);
			
			var dropdownMenu = UiSimpleDropdown.getDropdownMenu(data.element.parentNode.id);
			var item, label = '';
			for (var i = 0, length = dropdownMenu.childElementCount; i < length; i++) {
				item = dropdownMenu.children[i];
				
				var itemLanguageId = elData(item, 'language-id');
				if (itemLanguageId.length && languageId === ~~itemLanguageId) {
					label = item.children[0].textContent;
				}
			}
			
			// save current value
			if (data.languageId !== languageId) {
				var values = _values.get(elementId);
				
				if (data.languageId) {
					values.set(data.languageId, data.element.value);
				}
				
				if (languageId === 0) {
					_values.set(elementId, new Dictionary());
				}
				else if (data.buttonLabel.classList.contains('active') || isInit === true) {
					data.element.value = (values.has(languageId)) ? values.get(languageId) : '';
				}
				
				// update label
				data.buttonLabel.textContent = label;
				data.buttonLabel.classList[(languageId ? 'add' : 'remove')]('active');
				
				data.languageId = languageId;
			}
			
			data.element.blur();
			data.element.focus();
		},
		
		/**
		 * Callback for dropdowns being opened, flags items with a missing value for one or more languages.
		 * 
		 * @param	{string}	containerId	dropdown container id
		 * @param	{string}	action		toggle action, can be `open` or `close`
		 */
		_dropdownToggle: function(containerId, action) {
			if (action !== 'open') {
				return;
			}
			
			var dropdownMenu = UiSimpleDropdown.getDropdownMenu(containerId);
			var elementId = elData(elById(containerId), 'input-id');
			var values = _values.get(elementId);
			
			var item, languageId;
			for (var i = 0, length = dropdownMenu.childElementCount; i < length; i++) {
				item = dropdownMenu.children[i];
				languageId = ~~elData(item, 'language-id');
				
				if (languageId) {
					item.classList[(values.has(languageId) || !values.size ? 'remove' : 'add')]('missingValue');
				}
			}
		},
		
		/**
		 * Inserts hidden fields for i18n input on submit.
		 * 
		 * @param	{object}	event		event object
		 */
		_submit: function(event) {
			var elementIds = _forms.get(event.currentTarget);
			
			var data, elementId, input, values;
			for (var i = 0, length = elementIds.length; i < length; i++) {
				elementId = elementIds[i];
				data = _elements.get(elementId);
				if (data.isEnabled) {
					values = _values.get(elementId);
					
					// update with current value
					if (data.languageId) {
						values.set(data.languageId, data.element.value);
					}
					
					if (values.size) {
						values.forEach(function(value, languageId) {
							input = elCreate('input');
							input.type = 'hidden';
							input.name = elementId + '_i18n[' + languageId + ']';
							input.value = value;
							
							event.currentTarget.appendChild(input);
						});
						
						// remove name attribute to enforce i18n values
						data.element.removeAttribute('name');
					}
				}
			}
		},
		
		/**
		 * Returns the values of an input field.
		 * 
		 * @param	{string}	elementId	input element id
		 * @return	{Dictionary}	values stored for the different languages
		 */
		getValues: function(elementId) {
			var element = _elements.get(elementId);
			if (element === undefined) {
				throw new Error("Expected a valid i18n input element, '" + elementId + "' is not i18n input field.");
			}
			
			var values = _values.get(elementId);
			
			// update with current value
			values.set(element.languageId, element.element.value);
			
			return values;
		},
		
		/**
		 * Sets the values of an input field.
		 * 
		 * @param	{string}	elementId	input element id
		 * @param	{Dictionary}	values		values for the different languages
		 */
		setValues: function(elementId, values) {
			var element = _elements.get(elementId);
			if (element === undefined) {
				throw new Error("Expected a valid i18n input element, '" + elementId + "' is not i18n input field.");
			}
			
			if (Core.isPlainObject(values)) {
				values = Dictionary.fromObject(values);
			}
			
			element.element.value = '';
			
			if (values.has(0)) {
				element.element.value = values.get(0);
				values['delete'](0);
			}
			
			_values.set(elementId, values);
			
			element.languageId = 0;
			this._select(elementId, LANGUAGE_ID, true);
		},
		
		/**
		 * Disables the i18n interface for an input field.
		 * 
		 * @param	{string}	elementId	input element id
		 */
		disable: function(elementId) {
			var element = _elements.get(elementId);
			if (element === undefined) {
				throw new Error("Expected a valid i18n input element, '" + elementId + "' is not i18n input field.");
			}
			
			if (!element.isEnabled) return;
			
			element.isEnabled = false;
			
			// hide language dropdown
			elHide(element.buttonLabel.parentNode);
			var dropdownContainer = element.buttonLabel.parentNode.parentNode;
			dropdownContainer.classList.remove('inputAddon');
			dropdownContainer.classList.remove('dropdown');
		},
		
		/**
		 * Enables the i18n interface for an input field.
		 * 
		 * @param	{string}	elementId	input element id
		 */
		enable: function(elementId) {
			var element = _elements.get(elementId);
			if (element === undefined) {
				throw new Error("Expected a valid i18n input element, '" + elementId + "' is not i18n input field.");
			}
			
			if (element.isEnabled) return;
			
			element.isEnabled = true;
			
			// show language dropdown
			elShow(element.buttonLabel.parentNode);
			var dropdownContainer = element.buttonLabel.parentNode.parentNode;
			dropdownContainer.classList.add('inputAddon');
			dropdownContainer.classList.add('dropdown');
		},
		
		/**
		 * Returns true if i18n input is enabled for an input field.
		 * 
		 * @param	{string}	elementId	input element id
		 * @return	{boolean}
		 */
		isEnabled: function(elementId) {
			var element = _elements.get(elementId);
			if (element === undefined) {
				throw new Error("Expected a valid i18n input element, '" + elementId + "' is not i18n input field.");
			}
			
			return element.isEnabled;
		},
		
		/**
		 * Returns true if the value of an i18n input field is valid.
		 * 
		 * If the element is disabled, true is returned.
		 * 
		 * @param	{string}	elementId		input element id
		 * @param	{boolean}	permitEmptyValue	if true, input may be empty for all languages
		 * @return	{boolean}	true if input is valid
		 */
		validate: function(elementId, permitEmptyValue) {
			var element = _elements.get(elementId);
			if (element === undefined) {
				throw new Error("Expected a valid i18n input element, '" + elementId + "' is not i18n input field.");
			}
			
			if (!element.isEnabled) return true;
			
			var values = _values.get(elementId);
			
			var dropdownMenu = UiSimpleDropdown.getDropdownMenu(element.element.parentNode.id);
			
			if (element.languageId) {
				values.set(element.languageId, element.element.value);
			}
			
			var item, languageId;
			var hasEmptyValue = false, hasNonEmptyValue = false;
			for (var i = 0, length = dropdownMenu.childElementCount; i < length; i++) {
				item = dropdownMenu.children[i];
				languageId = ~~elData(item, 'language-id');
				
				if (languageId) {
					if (!values.has(languageId) || values.get(languageId).length === 0) {
						// input has non-empty value for previously checked language
						if (hasNonEmptyValue) {
							return false;
						}
						
						hasEmptyValue = true;
					}
					else {
						// input has empty value for previously checked language
						if (hasEmptyValue) {
							return false;
						}
						
						hasNonEmptyValue = true;
					}
				}
			}
			
			if (hasEmptyValue && !permitEmptyValue) {
				return false;
			}
			
			return true;
		}
	};
	
	return LanguageInput;
});
