"use strict";

/**
 * Dictionary implemention relying on an object or if supported on a Map to hold key => value data.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Dictionary
 */
define(function() {
	var _hasMap = window.hasOwnProperty('Map');
	
	/**
	 * @constructor
	 */
	function Dictionary() {
		this._dictionary = (_hasMap) ? new Map() : {};
	};
	Dictionary.prototype = {
		/**
		 * Sets a new key with given value, will overwrite an existing key.
		 * 
		 * @param	{string}	key	key
		 * @param	{*}		value	value
		 */
		set: function(key, value) {
			if (typeof key !== "string") {
				throw new TypeError("Only strings can be used as keys, rejected '" +  + "' (" + typeof key + ").");
				return;
			}
			
			if (_hasMap) this._dictionary.set(key, value);
			else this._dictionary[key] = value;
		},
		
		/**
		 * Removes a key from the dictionary.
		 * 
		 * @param	{string}	key	key
		 */
		remove: function(key) {
			if (_hasMap) this._dictionary.remove(key);
			else this._dictionary[key] = undefined;
		},
		
		/**
		 * Returns true if dictionary contains a value for given key and is not undefined.
		 * 
		 * @param	{string}	key	key
		 * @return	{boolean}	true if key exists and value is not undefined
		 */
		has: function(key) {
			if (_hasMap) return this._dictionary.has(key);
			else {
				return (this._dictionary.hasOwnProperty(key) && typeof this._dictionary[key] !== "undefined");
			}
		},
		
		/**
		 * Retrieves a value by key, returns null if not found or undefined.
		 * 
		 * @param	{string}	key	key
		 * @return	{*}
		 */
		get: function(key) {
			if (this.has(key)) {
				if (_hasMap) return this._dictionary.get(key);
				else return this._dictionary[key];
			}
			
			return null;
		},
		
		/**
		 * Iterates over the dictionary keys and values, callback function should expect the
		 * value as first parameter and the key name second.
		 * 
		 * @param	{function<*, string>}	callback	callback for each iteration
		 */
		forEach: function(callback) {
			if (typeof callback !== "function") {
				throw new TypeError("forEach() expects a callback as first parameter.");
			}
			
			if (_hasMap) {
				this._dictionary.forEach(callback);
			}
			else {
				var keys = Object.keys(this._dictionary);
				for (var i = 0, length = keys.length; i < length; i++) {
					callback(this._dictionary[keys[i]]);
				}
			}
		}
	};
	
	Object.defineProperty(Dictionary.prototype, 'size', {
		enumerable: false,
		configurable: true,
		get: function() {
			if (_hasMap) {
				return this._dictionary.size;
			}
			else {
				return Object.keys(this._dictionary).length;
			}
		}
	});
	
	return Dictionary;
});