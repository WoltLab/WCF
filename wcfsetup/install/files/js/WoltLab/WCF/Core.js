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
	
	var _clone = function(variable) {
		if (typeof variable === 'object' && variable !== null) {
			return _cloneObject(variable);
		}
		
		return variable;
	};
	
	var _cloneArray = function(oldArray) {
		return oldArray.slice();
	};
	
	var _cloneObject = function(obj) {
		if (!obj) {
			return null;
		}
		
		if (Array.isArray(obj)) {
			return _cloneArray(obj);
		}
		
		var newObj = {};
		for (var key in obj) {
			if (obj.hasOwnProperty(key) && typeof obj[key] !== 'undefined') {
				newObj[key] = _clone(obj[key]);
			}
		}
		
		return newObj;
	};
	
	/**
	 * @constructor
	 */
	function Core() {};
	Core.prototype = {
		/**
		 * Deep clones an object.
		 * 
		 * @param	{object}	obj	source object
		 * @return	{object}	cloned object
		 */
		clone: function(obj) {
			return _clone(obj);
		},
		
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
				var parts = controller.split(/([A-Z][a-z0-9]+)/);
				var controller = '';
				for (var i = 0, length = parts.length; i < length; i++) {
					var part = parts[i].trim();
					if (part.length) {
						if (controller.length) controller += '-';
						controller += part.toLowerCase();
					}
				}
				
				return 'index.php?' + controller + '/&';
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
			var newObj = this.clone(out);
			
			for (var i = 1, length = arguments.length; i < length; i++) {
				var obj = arguments[i];
				
				if (!obj) continue;
				
				for (var key in obj) {
					if (obj.hasOwnProperty(key)) {
						if (!Array.isArray(obj[key]) && typeof obj[key] === 'object') {
							if (this.isPlainObject(obj[key])) {
								// object literals have the prototype of Object which in return has no parent prototype
								newObj[key] = this.extend(out[key], obj[key]);
							}
							else {
								newObj[key] = obj[key];
							}
						}
						else {
							newObj[key] = obj[key];
						}
					}
				}
			}
			
			return newObj;
		},
		
		/**
		 * Returns true if `obj` is an object literal.
		 * 
		 * @param	{*}	obj	target object
		 * @returns	{boolean}	true if target is an object literal
		 */
		isPlainObject: function(obj) {
			if (obj === window || obj.nodeType) {
				return false;
			}
			
			if (obj.constructor && !obj.constructor.prototype.hasOwnProperty('isPrototypeOf')) {
				return false;
			}
			
			return true;
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
		 * Returns a RFC4122 version 4 compilant UUID.
		 * 
		 * @see		http://stackoverflow.com/a/2117523
		 * @return	{string}
		 */
		getUuid: function() {
			return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
				var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
				return v.toString(16);
			});
		},
		
		/**
		 * Recursively serializes an object into an encoded URI parameter string.
		 *  
		 * @param	{object}	obj	target object
		 * @param	{string=}	prefix	parameter prefix
		 * @return	encoded parameter string
		 */
		serialize: function(obj, prefix) {
			var parameters = [];
			
			for (var key in obj) {
				if (obj.hasOwnProperty(key)) {
					var parameterKey = (prefix) ? prefix + '[' + key + ']' : key;
					var value = obj[key];
					
					if (typeof value === 'object') {
						parameters.push(this.serialize(value, parameterKey));
					}
					else {
						parameters.push(encodeURIComponent(parameterKey) + '=' + encodeURIComponent(value));
					}
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
