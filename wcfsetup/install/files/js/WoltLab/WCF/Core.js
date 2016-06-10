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
		if (typeof variable === 'object' && (Array.isArray(variable) || Core.isPlainObject(variable))) {
			return _cloneObject(variable);
		}
		
		return variable;
	};
	
	var _cloneObject = function(obj) {
		if (!obj) {
			return null;
		}
		
		if (Array.isArray(obj)) {
			return obj.slice();
		}
		
		var newObj = {};
		for (var key in obj) {
			if (objOwns(obj, key) && typeof obj[key] !== 'undefined') {
				newObj[key] = _clone(obj[key]);
			}
		}
		
		return newObj;
	};
	
	/**
	 * @exports	WoltLab/WCF/Core
	 */
	var Core = {
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
					if (objOwns(obj, key)) {
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
		 * Inherits the prototype methods from one constructor to another
		 * constructor.
		 * 
		 * Usage:
		 * 
		 * function MyDerivedClass() {}
		 * Core.inherit(MyDerivedClass, TheAwesomeBaseClass, {
		 *      // regular prototype for `MyDerivedClass`
		 *      
		 *      overwrittenMethodFromBaseClass: function(foo, bar) {
		 *              // do stuff
		 *              
		 *              // invoke parent
		 *              MyDerivedClass._super.prototype.overwrittenMethodFromBaseClass.call(this, foo, bar);
		 *      }
		 * });
		 * 
		 * @see	https://github.com/nodejs/node/blob/7d14dd9b5e78faabb95d454a79faa513d0bbc2a5/lib/util.js#L697-L735
		 * @param	{function}	constructor		inheriting constructor function
		 * @param	{function}	superConstructor	inherited constructor function
		 * @param	{object=}	propertiesObject	additional prototype properties
		 */
		inherit: function(constructor, superConstructor, propertiesObject) {
			if (constructor === undefined || constructor === null) {
				throw new TypeError("The constructor must not be undefined or null.");
			}
			if (superConstructor === undefined || superConstructor === null) {
				throw new TypeError("The super constructor must not be undefined or null.");
			}
			if (superConstructor.prototype === undefined) {
				throw new TypeError("The super constructor must have a prototype.");
			}
			
			constructor._super = superConstructor;
			constructor.prototype = Core.extend(Object.create(superConstructor.prototype, {
				constructor: {
					configurable: true,
					enumerable: false,
					value: constructor,
					writable: true
				}
			}), propertiesObject || {});
		},
		
		/**
		 * Returns true if `obj` is an object literal.
		 * 
		 * @param	{*}	obj	target object
		 * @returns	{boolean}	true if target is an object literal
		 */
		isPlainObject: function(obj) {
			if (typeof obj !== 'object' || obj === null || obj.nodeType) {
				return false;
			}
			
			return (Object.getPrototypeOf(obj) === Object.prototype);
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
				if (objOwns(obj, key)) {
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
		
		/**
		 * Triggers a custom or built-in event.
		 * 
		 * @param	{Element}	element		target element
		 * @param	{string}	eventName	event name
		 */
		triggerEvent: function(element, eventName) {
			var event;
			
			try {
				event = new Event(eventName, {
					bubbles: true,
					cancelable: true
				});
			}
			catch (e) {
				event = document.createEvent('Event');
				event.initEvent(eventName, true, true);
			}
			
			element.dispatchEvent(event);
		}
	};
	
	return Core;
});
