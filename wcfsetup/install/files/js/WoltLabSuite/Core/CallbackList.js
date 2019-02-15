/**
 * Simple API to store and invoke multiple callbacks per identifier.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/CallbackList
 */
define(['Dictionary'], function(Dictionary) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function CallbackList() {
		this._dictionary = new Dictionary();
	}
	CallbackList.prototype = {
		/**
		 * Adds a callback for given identifier.
		 * 
		 * @param	{string}	identifier	arbitrary string to group and identify callbacks
		 * @param	{function}	callback	callback function
		 */
		add: function(identifier, callback) {
			if (typeof callback !== 'function') {
				throw new TypeError("Expected a valid callback as second argument for identifier '" + identifier + "'.");
			}
			
			if (!this._dictionary.has(identifier)) {
				this._dictionary.set(identifier, []);
			}
			
			this._dictionary.get(identifier).push(callback);
		},
		
		/**
		 * Removes all callbacks registered for given identifier
		 * 
		 * @param	{string}	identifier	arbitrary string to group and identify callbacks
		 */
		remove: function(identifier) {
			this._dictionary['delete'](identifier);
		},
		
		/**
		 * Invokes callback function on each registered callback.
		 * 
		 * @param	{string|null}		identifier	arbitrary string to group and identify callbacks.
		 * 							null is a wildcard to match every identifier
		 * @param	{function(function)}	callback	function called with the individual callback as parameter
		 */
		forEach: function(identifier, callback) {
			if (identifier === null) {
				this._dictionary.forEach(function(callbacks, identifier) {
					callbacks.forEach(callback);
				});
			}
			else {
				var callbacks = this._dictionary.get(identifier);
				if (callbacks !== undefined) {
					callbacks.forEach(callback);
				}
			}
		}
	};
	
	return CallbackList;
});
