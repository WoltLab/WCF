/**
 * Handles AJAX requests.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ajax
 */
define(['AjaxRequest', 'Core', 'ObjectMap'], function(AjaxRequest, Core, ObjectMap) {
	"use strict";
	
	var _requests = new ObjectMap();
	
	/**
	 * @exports	WoltLab/WCF/Ajax
	 */
	var Ajax = {
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
			if (typeof data !== 'object') data = {};
			
			var request = _requests.get(callbackObject);
			if (request === undefined) {
				if (typeof callbackObject._ajaxSetup !== 'function') {
					throw new TypeError("Callback object must implement at least _ajaxSetup().");
				}
				
				var options = callbackObject._ajaxSetup();
				
				options.pinData = true;
				options.callbackObject = callbackObject;
				
				if (!options.url) options.url = 'index.php/AJAXProxy/?t=' + SECURITY_TOKEN;
				
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
			options.pinData = false;
			options.callbackObject = null;
			if (!options.url) options.url = 'index.php/AJAXProxy/?t=' + SECURITY_TOKEN;
			
			var request = new AjaxRequest(options);
			request.sendRequest();
		}
	};
	
	return Ajax;
});
