/**
 * I18n interface for input and textarea fields.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Language/Input
 */
define(['Dictionary', 'Language', 'ObjectMap', 'StringUtil', 'DOM/Traverse', 'DOM/Util', 'UI/SimpleDropdown'], function(Dictionary, Language, ObjectMap, StringUtil, DOMTraverse, DOMUtil, UISimpleDropdown) {
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
		 * @param	{object<integer, string>}	values			preset values per language id
		 * @param	{object<integer, string>}	availableLanguages	language names per language id
		 * @param	{boolean}			forceSelection		require i18n input
		 */
		init: function(elementId, values, availableLanguages, forceSelection) {
			if (_values.has(elementId)) {
				return;
			}
			
			var element = document.getElementById(elementId);
			if (element === null) {
				throw new Error("Expected a valid element id, cannot find '" + elementId + "'.");
			}
			
			this._setup();
			
			// unescape values
			var unescapedValues = new Dictionary();
			for (var key in values) {
				if (values.hasOwnProperty(key)) {
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
		 * @param	{object<integer, string>}	availableLanguages	language names per language id
		 * @param	{boolean}			forceSelection		require i18n input
		 */
		_initElement: function(elementId, element, values, availableLanguages, forceSelection) {
			var container = element.parentNode;
			if (!container.classList.contains('inputAddon')) {
				container = document.createElement('div');
				container.className = 'inputAddon' + (element.nodeName === 'TEXTAREA' ? ' inputAddonTextarea' : '');
				container.setAttribute('data-input-id', elementId);
				
				element.parentNode.insertBefore(container, element);
				container.appendChild(element);
			}
			
			container.classList.add('dropdown');
			var button = document.createElement('span');
			button.className = 'button dropdownToggle inputPrefix';
			
			var span = document.createElement('span');
			span.textContent = Language.get('wcf.global.button.disabledI18n');
			
			button.appendChild(span);
			container.insertBefore(button, element);
			
			var dropdownMenu = document.createElement('ul');
			dropdownMenu.className = 'dropdownMenu';
			DOMUtil.insertAfter(dropdownMenu, button);
			
			var callbackClick = (function(event, isInit) {
				var languageId = ~~event.currentTarget.getAttribute('data-language-id');
				
				var activeItem = DOMTraverse.childByClass(dropdownMenu, 'active');
				if (activeItem !== null) activeItem.classList.remove('active');
				
				if (languageId) event.currentTarget.classList.add('active');
				
				this._select(elementId, languageId, event.currentTarget.children[0].textContent, isInit || false);
			}).bind(this);
			
			// build language dropdown
			for (var languageId in availableLanguages) {
				if (availableLanguages.hasOwnProperty(languageId)) {
					var listItem = document.createElement('li');
					listItem.setAttribute('data-language-id', languageId);
					
					span = document.createElement('span');
					span.textContent = availableLanguages[languageId];
					
					listItem.appendChild(span);
					listItem.addEventListener('click', callbackClick);
					dropdownMenu.appendChild(listItem);
				}
			}
			
			if (forceSelection !== true) {
				var listItem = document.createElement('li');
				listItem.className = 'dropdownDivider';
				listItem.setAttribute('data-language-id', 0);
				dropdownMenu.appendChild(listItem);
				
				listItem = document.createElement('li');
				span = document.createElement('span');
				span.textContent = Language.get('wcf.global.button.disabledI18n');
				listItem.appendChild(span);
				listItem.addEventListener('click', callbackClick);
				dropdownMenu.appendChild(listItem);
			}
			
			var activeItem = null;
			if (forceSelection === true || values.size) {
				for (var i = 0, length = dropdownMenu.childElementCount; i < length; i++) {
					if (~~dropdownMenu.children[i].getAttribute('data-language-id') === LANGUAGE_ID) {
						activeItem = dropdownMenu.children[i];
						break;
					}
				}
			}
			
			UISimpleDropdown.init(button);
			UISimpleDropdown.registerCallback(container.id, _callbackDropdownToggle);
			
			_elements.set(elementId, {
				buttonLabel: button.children[0],
				element: element,
				languageId: 0
			});
			
			// bind to submit event
			var submit = DOMTraverse.parentByTag(element, 'FORM');
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
		 * @param	{integer}	languageId	language id or `0` to disable i18n
		 * @param	{string}	label		new dropdown label for selection
		 * @param	{boolean}	isInit		triggers pre-selection on init
		 */
		_select: function(elementId, languageId, label, isInit) {
			var data = _elements.get(elementId);
			
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
			
			var dropdownMenu = UISimpleDropdown.getDropdownMenu(containerId);
			var elementId = document.getElementById(containerId).getAttribute('data-input-id');
			var values = _values.get(elementId);
			
			var item, languageId;
			for (var i = 0, length = dropdownMenu.childElementCount; i < length; i++) {
				item = dropdownMenu.children[i];
				languageId = ~~item.getAttribute('data-language-id');
				
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
				values = _values.get(elementId);
				
				// update with current value
				if (data.languageId) {
					values.set(data.languageId, data.element.value);
				}
				
				if (values.size) {
					values.forEach(function(value, languageId) {
						input = document.createElement('input');
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
	};
	
	return LanguageInput;
});
