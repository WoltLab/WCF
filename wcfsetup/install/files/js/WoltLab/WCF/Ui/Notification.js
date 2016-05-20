/**
 * Simple notification overlay.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Notification
 */
define(['Language'], function(Language) {
	"use strict";
	
	var _busy = false;
	var _callback = null;
	var _message = null;
	var _notificationElement = null;
	var _timeout = null;
	
	var _callbackHide = null;
	
	/**
	 * @exports	WoltLab/WCF/Ui/Notification
	 */
	var UiNotification = {
		/**
		 * Shows a notification.
		 * 
		 * @param	{string}	message		message
		 * @param	{function=}	callback	callback function to be executed once notification is being hidden
		 * @param	{string=}	cssClassName	alternate CSS class name, defaults to 'success'
		 */
		show: function(message, callback, cssClassName) {
			if (_busy) {
				return;
			}
			
			this._init();
			
			_callback = (typeof callback === 'function') ? callback : null;
			_message.className = cssClassName || 'success';
			_message.textContent = Language.get(message || 'wcf.global.success');
			
			_busy = true;
			
			_notificationElement.classList.add('active');
			
			_timeout = setTimeout(_callbackHide, 2000);
		},
		
		/**
		 * Initializes the UI elements.
		 */
		_init: function() {
			if (_notificationElement === null) {
				_callbackHide = this._hide.bind(this);
				
				_notificationElement = elCreate('div');
				_notificationElement.id = 'systemNotification';
				
				_message = elCreate('p');
				_message.addEventListener(WCF_CLICK_EVENT, _callbackHide);
				_notificationElement.appendChild(_message);
				
				document.body.appendChild(_notificationElement);
			}
		},
		
		/**
		 * Hides the notification and invokes the callback if provided.
		 */
		_hide: function() {
			clearTimeout(_timeout);
			
			_notificationElement.classList.remove('active');
			
			if (_callback !== null) {
				_callback();
			}
			
			_busy = false;
		}
	};
	
	return UiNotification;
});
