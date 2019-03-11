/**
 * Provides data of the active user.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Controller/Captcha
 */
define(['Dictionary'], function(Dictionary) {
	"use strict";
	
	var _captchas = new Dictionary();
	
	/**
	 * @exports	WoltLabSuite/Core/Controller/Captcha
	 */
	return {
		/**
		 * Registers a captcha with the given identifier and callback used to get captcha data.
		 * 
		 * @param	{string}	captchaId	captcha identifier
		 * @param	{function}	callback	callback to get captcha data
		 */
		add: function(captchaId, callback) {
			if (_captchas.has(captchaId)) {
				throw new Error("Captcha with id '" + captchaId + "' is already registered.");
			}
			
			if (typeof callback !== 'function') {
				throw new TypeError("Expected a valid callback for parameter 'callback'.");
			}
			
			_captchas.set(captchaId, callback);
		},
		
		/**
		 * Deletes the captcha with the given identifier.
		 * 
		 * @param	{string}	captchaId	identifier of the captcha to be deleted
		 */
		'delete': function(captchaId) {
			if (!_captchas.has(captchaId)) {
				throw new Error("Unknown captcha with id '" + captchaId + "'.");
			}
			
			_captchas.delete(captchaId)();
		},
		
		/**
		 * Returns true if a captcha with the given identifier exists.
		 * 
		 * @param	{string}	captchaId	captcha identifier
		 * @return	{boolean}
		 */
		has: function(captchaId) {
			return _captchas.has(captchaId);
		},
		
		/**
		 * Returns the data of the captcha with the given identifier.
		 * 
		 * @param	{string}	captchaId	captcha identifier
		 * @return	{Object}	captcha data
		 */
		getData: function(captchaId) {
			if (!_captchas.has(captchaId)) {
				throw new Error("Unknown captcha with id '" + captchaId + "'.");
			}
			
			return _captchas.get(captchaId)();
		}
	};
});
