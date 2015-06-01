/**
 * Provides the confirmation dialog overlay.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/Confirmation
 */
define(['Core', 'Language', 'UI/Dialog'], function(Core, Language, UIDialog) {
	"use strict";
	
	var _active = false;
	var _confirmButton = null;
	var _content = null;
	var _options = {};
	var _text = null;
	
	/**
	 * Confirmation dialog overlay.
	 * 
	 * @exports	WoltLab/WCF/UI/Confirmation
	 */
	var UIConfirmation = {
		/**
		 * Shows the confirmation dialog.
		 * 
		 * Possible options:
		 *  - cancel: callback if user cancels the dialog
		 *  - confirm: callback if user confirm the dialog
		 *  - legacyCallback: WCF 2.0/2.1 compatible callback with string parameter
		 *  - message: displayed confirmation message
		 *  - parameters: list of parameters passed to the callback on confirm
		 *  - template: optional HTML string to be inserted below the `message`
		 * 
		 * @param	{object<string, *>}	options		confirmation options
		 */
		show: function(options) {
			if (_active) {
				return;
			}
			
			_options = Core.extend({
				cancel: null,
				confirm: null,
				legacyCallback: null,
				message: '',
				parameters: {},
				template: ''
			}, options);
			
			_options.message = (typeof _options.message === 'string') ? _options.message.trim() : '';
			if (!_options.message.length) {
				throw new Error("Expected a non-empty string for option 'message'.");
			}
			
			if (typeof _options.confirm !== 'function' && typeof _options.legacyCallback !== 'function') {
				throw new TypeError("Expected a valid callback for option 'confirm'.");
			}
			
			if (_content === null) {
				this._createDialog();
			}
			
			_content.innerHTML = (typeof options.template === 'string') ? options.template.trim() : '';
			_text.textContent = _options.message;
			
			_active = true;
			
			UIDialog.open('wcfSystemConfirmation', null, {
				onClose: this._onClose.bind(this),
				onShow: this._onShow.bind(this),
				title: Language.get('wcf.global.confirmation.title')
			});
		},
		
		/**
		 * Returns content container element.
		 * 
		 * @return	{Element}	content container element
		 */
		getContentElement: function() {
			return _content;
		},
		
		/**
		 * Creates the dialog DOM elements.
		 */
		_createDialog: function() {
			var dialog = document.createElement('div');
			dialog.setAttribute('id', 'wcfSystemConfirmation');
			dialog.classList.add('systemConfirmation');
			
			_text = document.createElement('p');
			dialog.appendChild(_text);
			
			_content = document.createElement('div');
			_content.setAttribute('id', 'wcfSystemConfirmationContent');
			dialog.appendChild(_content);
			
			var formSubmit = document.createElement('div');
			formSubmit.classList.add('formSubmit');
			dialog.appendChild(formSubmit);
			
			_confirmButton = document.createElement('button');
			_confirmButton.classList.add('buttonPrimary');
			_confirmButton.textContent = Language.get('wcf.global.confirmation.confirm');
			_confirmButton.addEventListener('click', this._confirm.bind(this));
			formSubmit.appendChild(_confirmButton);
			
			var cancelButton = document.createElement('button');
			cancelButton.textContent = Language.get('wcf.global.confirmation.cancel');
			cancelButton.addEventListener('click', function() { UIDialog.close('wcfSystemConfirmation'); });
			formSubmit.appendChild(cancelButton);
			
			document.body.appendChild(dialog);
		},
		
		/**
		 * Invoked if the user confirms the dialog.
		 */
		_confirm: function() {
			if (typeof _options.legacyCallback === 'function') {
				_options.legacyCallback('confirm', _options.parameters);
			}
			else {
				_options.confirm(_options.parameters);
			}
			
			_active = false;
			UIDialog.close('wcfSystemConfirmation');
		},
		
		/**
		 * Invoked on dialog close or if user cancels the dialog.
		 */
		_onClose: function() {
			if (_active) {
				_confirmButton.blur();
				_active = false;
				
				if (typeof _options.legacyCallback === 'function') {
					_options.legacyCallback('cancel', _options.parameters);
				}
				else if (typeof _options.cancel === 'function') {
					_options.cancel(_options.parameters);
				}
			}
		},
		
		/**
		 * Sets the focus on the confirm button on dialog open for proper keyboard support.
		 */
		_onShow: function() {
			_confirmButton.blur();
			_confirmButton.focus();
		}
	};
	
	return UIConfirmation;
});
