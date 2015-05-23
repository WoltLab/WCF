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
	 * @constructor
	 */
	function Ajax() {};
	Ajax.prototype = {
		/**
		 * Shorthand function to perform a request against the WCF-API.
		 * 
		 * @param	{object}		callbackObject	callback object
		 * @param	{object<string, *>=}	data		request data
		 * @return	{AjaxRequest}
		 */
		api: function(callbackObject, data) {
			var request = _requests.get(callbackObject);
			if (request !== undefined) {
				data = data || {};
				
				request.setData(data || {});
				request.sendRequest();
				
				return request;
			}
			
			if (typeof callbackObject._ajaxSetup !== 'function') {
				throw new TypeError("Callback object must implement at least _ajaxSetup().");
			}
			
			var options = callbackObject._ajaxSetup();
			if (typeof data === 'object') {
				options.data = Core.extend(data, options.data);
			}
			
			options.pinData = true;
			options.callbackObject = callbackObject;
			
			if (!options.url) options.url = 'index.php/AJAXProxy/?t=' + SECURITY_TOKEN;
			
			request = new AjaxRequest(options);
			request.sendRequest();
			
			_requests.set(callbackObject, request);
			
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
	
	return new Ajax();
});
