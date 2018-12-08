/**
 * Provides a utility class to issue JSONP requests.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ajax/Jsonp
 */
define(['Core'], function(Core) {
	"use strict";
	
	/**
	 * @exports	WoltLabSuite/Core/Ajax/Jsonp
	 */
	return {
		/**
		 * Issues a JSONP request.
		 * 
		 * @param	{string}		url		source URL, must not contain callback parameter
		 * @param	{function}		success		success callback
		 * @param	{function=}		failure		timeout callback
		 * @param	{object<string, *>=}	options		request options
		 */
		send: function(url, success, failure, options) {
			url = (typeof url === 'string') ? url.trim() : '';
			if (url.length === 0) {
				throw new Error("Expected a non-empty string for parameter 'url'.");
			}
			
			if (typeof success !== 'function') {
				throw new TypeError("Expected a valid callback function for parameter 'success'.");
			}
			
			options = Core.extend({
				parameterName: 'callback',
				timeout: 10
			}, options || {});
			
			var callbackName = 'wcf_jsonp_' + Core.getUuid().replace(/-/g, '').substr(0, 8);
			var script;
			
			var timeout = window.setTimeout(function() {
				if (typeof failure === 'function') {
					failure();
				}
				
				window[callbackName] = undefined;
				elRemove(script);
			}, (~~options.timeout || 10) * 1000);
			
			window[callbackName] = function() {
				window.clearTimeout(timeout);
				
				success.apply(null, arguments);
				
				window[callbackName] = undefined;
				elRemove(script);
			};
			
			url += (url.indexOf('?') === -1) ? '?' : '&';
			url += options.parameterName + '=' + callbackName;
			
			script = elCreate('script');
			script.async = true;
			elAttr(script, 'src', url);
			
			document.head.appendChild(script);
		}
	};
});
