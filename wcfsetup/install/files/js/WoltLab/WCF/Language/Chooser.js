/**
 * Dropdown language chooser.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Language/Chooser
 */
define(['Dictionary', 'Language', 'Dom/Traverse', 'Dom/Util', 'ObjectMap', 'Ui/SimpleDropdown'], function(Dictionary, Language, DomTraverse, DomUtil, ObjectMap, UiSimpleDropdown) {
	"use strict";
	
	var _choosers = new Dictionary();
	var _didInit = false;
	var _forms = new ObjectMap();
	
	var _callbackSubmit = null;
	
	/**
	 * @exports	WoltLab/WCF/Language/Chooser
	 */
	return {
		/**
		 * Initializes a language chooser.
		 * 
		 * @param       {string}                                containerId             input element conainer id
		 * @param       {string}                                chooserId               input element id
		 * @param       {int}                                   languageId              selected language id
		 * @param       {object<int, object<string, string>>}   languages               data of available languages
		 * @param       {function}                              callback                function called after a language is selected
		 * @param       {boolean}                               allowEmptyValue         true if no language may be selected
		 */
		init: function(containerId, chooserId, languageId, languages, callback, allowEmptyValue) {
			if (_choosers.has(chooserId)) {
				return;
			}
			
			var container = elById(containerId);
			if (container === null) {
				throw new Error("Expected a valid container id, cannot find '" + chooserId + "'.");
			}
			
			var element = elById(chooserId);
			if (element === null) {
				element = elCreate('input');
				elAttr(element, 'type', 'hidden');
				elAttr(element, 'id', chooserId);
				elAttr(element, 'name', chooserId);
				elAttr(element, 'value', languageId);
				
				container.appendChild(element);
			}
			
			this._initElement(chooserId, element, languageId, languages, callback, allowEmptyValue);
		},
		
		/**
		 * Caches common event listener callbacks.
		 */
		_setup: function() {
			if (_didInit) return;
			_didInit = true;
			
			_callbackSubmit = this._submit.bind(this);
		},
		
		/**
		 * Sets up DOM and event listeners for a language chooser.
		 *
		 * @param       {string}                                chooserId               chooser id
		 * @param       {Element}                               element                 chooser element
		 * @param       {int}                                   languageId              selected language id
		 * @param       {object<int, object<string, string>>}   languages               data of available languages
		 * @param       {function}                              callback                callback function invoked on selection change
		 * @param       {boolean}                               allowEmptyValue         true if no language may be selected
		 */
		_initElement: function(chooserId, element, languageId, languages, callback, allowEmptyValue) {
			var container;
			
			if (element.parentNode.nodeName === 'DD') {
				container = elCreate('div');
				container.className = 'dropdown';
				element.parentNode.insertBefore(container, element);
			}
			else {
				container = element.parentNode;
				container.classList.add('dropdown');
			}
			
			elHide(element);
			
			var dropdownToggle = elCreate('a');
			dropdownToggle.className = 'dropdownToggle boxFlag box24 inputPrefix' + (element.parentNode.nodeName === 'DD' ? ' button' : '');
			container.appendChild(dropdownToggle);
			
			var dropdownMenu = elCreate('ul');
			dropdownMenu.className = 'dropdownMenu';
			container.appendChild(dropdownMenu);
			
			var callbackClick = (function(event) {
				var languageId = ~~elData(event.currentTarget, 'language-id');
				
				var activeItem = DomTraverse.childByClass(dropdownMenu, 'active');
				if (activeItem !== null) activeItem.classList.remove('active');
				
				if (languageId) event.currentTarget.classList.add('active');
				
				this._select(chooserId, languageId, event.currentTarget);
			}).bind(this);
			
			// add language dropdown items
			var a, div, h3, img, listItem;
			for (var availableLanguageId in languages) {
				if (objOwns(languages, availableLanguageId)) {
					var language = languages[availableLanguageId];
					
					listItem = elCreate('li');
					listItem.className = 'boxFlag';
					listItem.addEventListener('click', callbackClick);
					elData(listItem, 'language-id', availableLanguageId);
					dropdownMenu.appendChild(listItem);
					
					a = elCreate('a');
					a.className = 'box24';
					listItem.appendChild(a);
					
					div = elCreate('div');
					//div.className = 'framed';
					a.appendChild(div);
					
					img = elCreate('img');
					elAttr(img, 'src', language.iconPath);
					elAttr(img, 'alt', '');
					img.className = 'iconFlag';
					div.appendChild(img);
					
					div = elCreate('div');
					a.appendChild(div);
					
					h3 = elCreate('h3');
					h3.textContent = language.languageName;
					div.appendChild(h3);
					
					if (availableLanguageId == languageId) {
						dropdownToggle.innerHTML = listItem.firstChild.innerHTML;
					}
				}
			}
			
			// add dropdown item for "no selection"
			if (allowEmptyValue) {
				listItem = elCreate('li');
				listItem.className = 'dropdownDivider';
				dropdownMenu.appendChild(listItem);
				
				listItem = elCreate('li');
				elData(listItem, 'language-id', availableLanguageId);
				listItem.addEventListener('click', callbackClick);
				dropdownMenu.appendChild(listItem);
				
				a = elCreate('a');
				a.textContent = Language.get('wcf.global.language.noSelection');
				listItem.appendChild(a);
				
				if (languageId === 0) {
					dropdownToggle.innerHTML = listItem.firstChild.innerHTML;
				}
				
				listItem.addEventListener('click', callbackClick)
			}
			else if (languageId === 0) {
				dropdownToggle.innerHTML = null;
				
				div = elCreate('div');
				dropdownToggle.appendChild(div);
				
				var span = elCreate('span');
				span.className = 'icon icon24 fa-question';
				div.appendChild(span);
				
				div = elCreate('div');
				dropdownToggle.appendChild(div);
				
				h3 = elCreate('h3');
				h3.textContent = Language.get('wcf.global.language.noSelection');
				div.appendChild(h3);
			}
			
			UiSimpleDropdown.init(dropdownToggle);
			
			_choosers.set(chooserId, {
				callback: callback,
				dropdownMenu: dropdownMenu,
				dropdownToggle: dropdownToggle,
				element: element
			});
			
			// bind to submit event
			var form = DomTraverse.parentByTag(element, 'FORM');
			if (form !== null) {
				form.addEventListener('submit', _callbackSubmit);
				
				var chooserIds = _forms.get(form);
				if (chooserIds === undefined) {
					chooserIds = [];
					_forms.set(form, chooserIds);
				}
				
				chooserIds.push(chooserId);
			}
		},
		
		/**
		 * Selects a language from the dropdown list.
		 * 
		 * @param	{string}	chooserId	input element id
		 * @param	{int}	        languageId	language id or `0` to disable i18n
		 * @param	{Element=}	listItem	selected list item
		 */
		_select: function(chooserId, languageId, listItem) {
			var chooser = _choosers.get(chooserId);
			
			if (listItem === undefined) {
				var listItems = chooser.dropdownMenu.childNodes;
				for (var i = 0, length = listItems.length; i < length; i++) {
					var _listItem = listItems[i];
					if (~~elData(_listItem, 'language-id') === languageId) {
						listItem = _listItem;
						break;
					}
				}
				
				if (listItem === undefined) {
					throw new Error("Cannot select unknown language id '" + languageId + "'");
				}
			}
			
			chooser.element.value = languageId;
			
			chooser.dropdownToggle.innerHTML = listItem.firstChild.innerHTML;
			
			_choosers.set(chooserId, chooser);
			
			// execute callback
			if (typeof chooser.callback === 'function') {
				chooser.callback(listItem);
			}
		},
		
		/**
		 * Inserts hidden fields for the language chooser value on submit.
		 *
		 * @param	{object}	event		event object
		 */
		_submit: function(event) {
			var elementIds = _forms.get(event.currentTarget);
			
			var input;
			for (var i = 0, length = elementIds.length; i < length; i++) {
				input = elCreate('input');
				input.type = 'hidden';
				input.name = elementIds[i];
				input.value = this.getLanguageId(elementIds[i]);
				
				event.currentTarget.appendChild(input);
			}
		},
		
		/**
		 * Returns the chooser for an input field.
		 * 
		 * @param	{string}	chooserId	input element id
		 * @return	{Dictionary}	data of the chooser
		 */
		getChooser: function(chooserId) {
			var chooser = _choosers.get(chooserId);
			if (chooser === undefined) {
				throw new Error("Expected a valid language chooser input element, '" + chooserId + "' is not i18n input field.");
			}
			
			return chooser;
		},
		
		/**
		 * Returns the selected language for a certain chooser.
		 * 
		 * @param	{string}	chooserId	input element id
		 * @return	{int}	        choosen language id
		 */
		getLanguageId: function(chooserId) {
			return ~~this.getChooser(chooserId).element.value;
		},
		
		/**
		 * Sets the language for a certain chooser.
		 * 
		 * @param	{string}	chooserId	input element id
		 * @param	{int}	        languageId	language id to be set
		 */
		setLanguageId: function(chooserId, languageId) {
			if (_choosers.get(chooserId) === undefined) {
				throw new Error("Expected a valid  input element, '" + chooserId + "' is not i18n input field.");
			}
			
			this._select(chooserId, languageId);
		}
	};
});
