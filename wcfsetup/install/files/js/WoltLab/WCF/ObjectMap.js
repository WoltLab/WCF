/**
 * Simple `object` to `object` map using a native Map on supported browsers, otherwise a set of two arrays.
 * 
 * If you're looking for a dictionary with string keys, please see `WoltLab/WCF/Dictionary`.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/ObjectMap
 */
define([], function() {
	"use strict";
	
	var _hasMap = window.hasOwnProperty('Map');
	
	/**
	 * @constructor
	 */
	function ObjectMap() {
		this._map = (_hasMap) ? new Map() : { key: [], value: [] };
	};
	ObjectMap.prototype = {
			/**
			 * Sets a new key with given value, will overwrite an existing key.
			 * 
			 * @param	{object}	key	key
			 * @param	{object}	value	value
			 */
			set: function(key, value) {
				if (typeof key !== 'object' || key === null) {
					throw new TypeError("Only objects can be used as key");
				}
				
				if (typeof value !== 'object' || value === null) {
					throw new TypeError("Only objects can be used as value");
				}
				
				if (_hasMap) {
					this._map.set(key, value);
				}
				else {
					this._map.key.push(key);
					this._map.value.push(value);
				}
			},
			
			/**
			 * Removes a key from the map.
			 * 
			 * @param	{object}	key	key
			 */
			'delete': function(key) {
				if (_hasMap) {
					this._map.['delete'](_key);
				}
				else {
					var index = this._map.key.indexOf(key);
					this._map.key.splice(index);
					this._map.value.splice(index);
				}
			},
			
			/**
			 * Returns true if dictionary contains a value for given key.
			 * 
			 * @param	{object}	key	key
			 * @return	{boolean}	true if key exists
			 */
			has: function(key) {
				if (_hasMap) {
					return this._map.has(key);
				}
				else {
					return (this._map.key.indexOf(key) !== -1);
				}
			},
			
			/**
			 * Retrieves a value by key, returns undefined if there is no match.
			 * 
			 * @param	{object}	key	key
			 * @return	{*}
			 */
			get: function(key) {
				if (_hasMap) {
					return this._map.get(key);
				}
				else {
					var index = this._map.key.indexOf(key);
					if (index !== -1) {
						return this._map.value[index];
					}
					
					return undefined;
				}
			},
			
			/**
			 * Iterates over the object map, callback function should expect the
			 * value as first parameter and the key second.
			 * 
			 * @param	{function<object, object>}	callback	callback for each iteration
			 */
			forEach: function(callback) {
				if (typeof callback !== "function") {
					throw new TypeError("forEach() expects a callback as first parameter.");
				}
				
				if (_hasMap) {
					this._map.forEach(callback);
				}
				else {
					for (var i = 0, length = this._map.keys.length; i < length; i++) {
						callback(this._map.value[i], this._map.key[i]);
					}
				}
			}
	};
});
