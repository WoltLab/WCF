/**
 * Provides the basic core functionality.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Core
 */
define([], function() {
	"use strict";
	
	/**
	 * @constructor
	 */
	function Core() {};
	Core.prototype = {
		/**
		 * Converts WCF 2.0-style URLs into the default URL layout.
		 * 
		 * @param	string	url	target url
		 * @return	rewritten url
		 */
		convertLegacyUrl: function(url) {
			if (URL_LEGACY_MODE) {
				return url;
			}
			
			return url.replace(/^index\.php\/(.*?)\/\?/, function(match, controller) {
				var $parts = controller.split(/([A-Z][a-z0-9]+)/);
				var $controller = '';
				for (var $i = 0, $length = $parts.length; $i < $length; $i++) {
					var $part = $parts[$i].trim();
					if ($part.length) {
						if ($controller.length) $controller += '-';
						$controller += $part.toLowerCase();
					}
				}
				
				return 'index.php?' + $controller + '/&';
			});
		},
		
		/**
		 * Merges objects with the first argument.
		 * 
		 * @param	{object}	out		destination object
		 * @param	{...object}	arguments	variable number of objects to be merged into the destination object
		 * @return	{object}	destination object with all provided objects merged into
		 */
		extend: function(out) {
			out = out || {};
			
			for (var i = 1, length = arguments.length; i < length; i++) {
				var obj = arguments[i];
				
				if (!obj) continue;
				
				for (var key in obj) {
					if (obj.hasOwnProperty(key)) {
						if (!Array.isArray(obj[key]) && typeof obj[key] === 'object') {
							this.extend(out[key], obj[key]);
						}
						else {
							out[key] = obj[key];
						}
					}
				}
			}
			
			return out;
		},
		
		/**
		 * Returns the object's class name.
		 * 
		 * @param	{object}	obj	target object
		 * @return	{string}	object class name
		 */
		getType: function(obj) {
			return Object.prototype.toString.call(obj).replace(/^\[object (.+)\]$/, '$1');
		},
		
		/**
		 * Recursively serializes an object into an encoded URI parameter string.
		 *  
		 * @param	{object}	obj	target object
		 * @return	encoded parameter string
		 */
		serialize: function(obj) {
			var parameters = [];
			
			for (var key in obj) {
				if (obj.hasOwnProperty(key)) {
					var value = obj[key];
					
					if (Array.isArray(value)) {
						for (var i = 0, length = value.length; i < length; i++) {
							parameters.push(key + '[]=' + encodeURIComponent(value[i]));
						}
						
						continue;
					}
					else if (this.getType(value) === 'Object') {
						parameters.push(this.serialize(value));
						
						continue;
					}
					
					parameters.push(key + '=' + encodeURIComponent(value));
				}
			}
			
			return parameters.join('&');
		},
		
		triggerEvent: function(el, eventName) {
			var ev;
			if (document.createEvent) {
				ev = new Event(eventName);
				el.dispatchEvent(ev);
			}
			else {
				ev = document.createEventObject();
				el.fireEvent('on' + eventName, ev);
			}
		}
	};
	
	return new Core();
});
