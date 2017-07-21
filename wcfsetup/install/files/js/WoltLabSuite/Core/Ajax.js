/**
 * Handles AJAX requests.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ajax
 */
define(['AjaxRequest', 'Core', 'ObjectMap'], function(AjaxRequest, Core, ObjectMap) {
	"use strict";
	
	var _requests = new ObjectMap();
	
	/**
	 * @exports	WoltLabSuite/Core/Ajax
	 */
	return {
		/**
		 * Shorthand function to perform a request against the WCF-API with overrides
		 * for success and failure callbacks.
		 * 
		 * @param	{object}		callbackObject	callback object
		 * @param	{object<string, *>=}	data		request data
		 * @param	{function=}		success		success callback
		 * @param	{function=}		failure		failure callback
		 * @return	{AjaxRequest}
		 */
		api: function(callbackObject, data, success, failure) {
			// Fetch AjaxRequest, as it cannot be provided because of a circular dependency
			if (AjaxRequest === undefined) AjaxRequest = require('AjaxRequest');
			
			if (typeof data !== 'object') data = {};
			
			var request = _requests.get(callbackObject);
			if (request === undefined) {
				if (typeof callbackObject._ajaxSetup !== 'function') {
					throw new TypeError("Callback object must implement at least _ajaxSetup().");
				}
				
				var options = callbackObject._ajaxSetup();
				
				options.pinData = true;
				options.callbackObject = callbackObject;
				
				if (!options.url) {
					options.url = 'index.php?ajax-proxy/&t=' + SECURITY_TOKEN;
					options.withCredentials = true;
				}
				
				request = new AjaxRequest(options);
				
				_requests.set(callbackObject, request);
			}
			
			var oldSuccess = null;
			var oldFailure = null;
			
			if (typeof success === 'function') {
				oldSuccess = request.getOption('success');
				request.setOption('success', success);
			}
			if (typeof failure === 'function') {
				oldFailure = request.getOption('failure');
				request.setOption('failure', failure);
			}
			
			request.setData(data);
			request.sendRequest();
			
			// restore callbacks
			if (oldSuccess !== null) request.setOption('success', oldSuccess);
			if (oldFailure !== null) request.setOption('failure', oldFailure);
			
			return request;
		},
		
		/**
		 * Shorthand function to perform a single request against the WCF-API.
		 * 
		 * Please use `Ajax.api` if you're about to repeatedly send requests because this
		 * method will spawn an new and rather expensive `AjaxRequest` with each call.
		 *  
		 * @param	{object<string, *>}	options		request options
		 */
		apiOnce: function(options) {
			// Fetch AjaxRequest, as it cannot be provided because of a circular dependency
			if (AjaxRequest === undefined) AjaxRequest = require('AjaxRequest');
			
			options.pinData = false;
			options.callbackObject = null;
			if (!options.url) {
				options.url = 'index.php?ajax-proxy/&t=' + SECURITY_TOKEN;
				options.withCredentials = true;
			}
			
			var request = new AjaxRequest(options);
			request.sendRequest(false);
		},
		
		/**
		 * Returns the request object used for an earlier call to `api()`.
		 * 
		 * @param       {Object}        callbackObject  callback object
		 * @return      {AjaxRequest}
		 */
		getRequestObject: function(callbackObject) {
			if (!_requests.has(callbackObject)) {
				throw new Error('Expected a previously used callback object, provided object is unknown.');
			}
			
			return _requests.get(callbackObject);
		}
	};
});
