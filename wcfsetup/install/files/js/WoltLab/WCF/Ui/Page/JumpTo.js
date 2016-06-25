/**
 * Utility class to provide a 'Jump To' overlay. 
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Page/JumpTo
 */
define(['Language', 'ObjectMap', 'Ui/Dialog'], function(Language, ObjectMap, UiDialog) {
	"use strict";
	
	var _activeElement = null;
	var _buttonSubmit = null;
	var _description = null;
	var _elements = new ObjectMap();
	var _input = null;
	
	/**
	 * @exports	WoltLab/WCF/Ui/Page/JumpTo
	 */
	var UiPageJumpTo = {
		/**
		 * Initializes a 'Jump To' element.
		 * 
		 * @param	{Element}	element		trigger element
		 * @param	{function}	callback	callback function, receives the page number as first argument
		 */
		init: function(element, callback) {
			callback = callback || null;
			if (callback === null) {
				var redirectUrl = elData(element, 'link');
				if (redirectUrl) {
					callback = function(pageNo) {
						window.location = redirectUrl.replace(/pageNo=%d/, 'pageNo=' + pageNo);
					};
				}
				else {
					callback = function() {};
				}
				
			}
			else if (typeof callback !== 'function') {
				throw new TypeError("Expected a valid function for parameter 'callback'.");
			}
			
			if (!_elements.has(element)) {
				elBySelAll('.jumpTo', element, (function(jumpTo) {
					jumpTo.addEventListener(WCF_CLICK_EVENT, this._click.bind(this, element));
					_elements.set(element, { callback: callback });
				}).bind(this));
			}
		},
		
		/**
		 * Handles clicks on the trigger element.
		 * 
		 * @param	{Element}	element		trigger element
		 * @param	{object}	event		event object
		 */
		_click: function(element, event) {
			_activeElement = element;
			
			if (typeof event === 'object') {
				event.preventDefault();
			}
			
			UiDialog.open(this);
			
			var pages = elData(element, 'pages');
			_input.value = pages;
			_input.setAttribute('max', pages);
			
			_description.textContent = Language.get('wcf.global.page.jumpTo.description').replace(/#pages#/, pages);
		},
		
		/**
		 * Handles changes to the page number input field.
		 * 
		 * @param	{object}	event		event object
		 */
		_keyUp: function(event) {
			if (event.which === 13 && _buttonSubmit.disabled === false) {
				this._submit();
				return;
			}
			
			var pageNo = ~~_input.value;
			if (pageNo < 1 || pageNo > ~~elAttr(_input, 'max')) {
				_buttonSubmit.disabled = true;
			}
			else {
				_buttonSubmit.disabled = false;
			}
		},
		
		/**
		 * Invokes the callback with the chosen page number as first argument.
		 * 
		 * @param	{object}	event		event object
		 */
		_submit: function(event) {
			_elements.get(_activeElement).callback(~~_input.value);
			
			UiDialog.close(this);
		},
		
		_dialogSetup: function() {
			var source = '<dl>'
					+ '<dt><label for="jsPaginationPageNo">' + Language.get('wcf.global.page.jumpTo') + '</label></dt>'
					+ '<dd>'
						+ '<input type="number" id="jsPaginationPageNo" value="1" min="1" max="1" class="tiny">'
						+ '<small></small>'
					+ '</dd>'
				+ '</dl>'
				+ '<div class="formSubmit">'
					+ '<button class="buttonPrimary">' + Language.get('wcf.global.button.submit') + '</button>'
				+ '</div>';
			
			return {
				id: 'paginationOverlay',
				options: {
					onSetup: (function(content) {
						_input = elByTag('input', content)[0];
						_input.addEventListener('keyup', this._keyUp.bind(this));
						
						_description = elByTag('small', content)[0];
						
						_buttonSubmit = elByTag('button', content)[0];
						_buttonSubmit.addEventListener(WCF_CLICK_EVENT, this._submit.bind(this));
					}).bind(this),
					title: Language.get('wcf.global.page.pagination')
				},
				source: source
			};
		}
	};
	
	return UiPageJumpTo;
});