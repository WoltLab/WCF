"use strict";

/**
 * Simple API to store and invoke multiple callbacks per identifier.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/CallbackList
 */
define(['Dictionary'], function(Dictionary) {
	/**
	 * @constructor
	 */
	function CallbackList() {
		this._dictionary = new Dictionary();
	};
	CallbackList.prototype = {
		/**
		 * Adds a callback for given identifier.
		 * 
		 * @param	{string}	identifier	arbitrary string to group and identify callbacks
		 * @param	{function}	callback	callback function
		 * @return	{boolean}	false if callback is not a function
		 */
		add: function(identifier, callback) {
			if (typeof callback !== 'function') {
				throw new TypeError("Expected a valid callback as second argument for identifier '" + identifier + "'.");
				return false;
			}
			
			if (!this._dictionary.has(identifier)) {
				this._dictionary.set(identifier, []);
			}
			
			this._dictionary.get(identifier).push(callback);
			
			return true;
		},
		
		/**
		 * Removes all callbacks registered for given identifier
		 * 
		 * @param	{string}	identifier	arbitrary string to group and identify callbacks
		 */
		remove: function(identifier) {
			this._dictionary.remove(identifier);
		},
		
		/**
		 * Invokes callback function on each registered callback.
		 * 
		 * @param	{string}		identifier	arbitrary string to group and identify callbacks
		 * @param	{function(function)}	callback	function called with the individual callback as parameter
		 */
		forEach: function(identifier, callback) {
			var callbacks = this._dictionary.get(identifier);
			if (callbacks !== null) {
				callbacks.forEach(callback);
			}
		}
	};
	
	return CallbackList;
});