/**
 * List implementation relying on an array or if supported on a Set to hold values.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/List
 */
define([], function() {
	"use strict";
	
	var _hasSet = objOwns(window, 'Set') && typeof window.Set === 'function';
	
	/**
	 * @constructor
	 */
	function List() {
		this._set = (_hasSet) ? new Set() : [];
	}
	List.prototype = {
		/**
		 * Appends an element to the list, silently rejects adding an already existing value.
		 * 
		 * @param       {?}     value   unique element
		 */
		add: function(value) {
			if (_hasSet) {
				this._set.add(value);
			}
			else if (!this.has(value)) {
				this._set.push(value);
			}
		},
		
		/**
		 * Removes all elements from the list.
		 */
		clear: function() {
			if (_hasSet) {
				this._set.clear();
			}
			else {
				this._set = [];
			}
		},
		
		/**
		 * Removes an element from the list, returns true if the element was in the list.
		 * 
		 * @param       {?}             value   element
		 * @return      {boolean}       true if element was in the list
		 */
		'delete': function(value) {
			if (_hasSet) {
				return this._set['delete'](value);
			}
			else {
				var index = this._set.indexOf(value);
				if (index === -1) {
					return false;
				}
				
				this._set.splice(index, 1);
				return true;
			}
		},
		
		/**
		 * Calls `callback` for each element in the list.
		 */
		forEach: function(callback) {
			if (_hasSet) {
				this._set.forEach(callback);
			}
			else {
				for (var i = 0, length = this._set.length; i < length; i++) {
					callback(this._set[i]);
				}
			}
		},
		
		/**
		 * Returns true if the list contains the element.
		 * 
		 * @param       {?}             value   element
		 * @return      {boolean}       true if element is in the list
		 */
		has: function(value) {
			if (_hasSet) {
				return this._set.has(value);
			}
			else {
				return (this._set.indexOf(value) !== -1);
			}
		}
	};
	
	Object.defineProperty(List.prototype, 'size', {
		enumerable: false,
		configurable: true,
		get: function() {
			if (_hasSet) {
				return this._set.size;
			}
			else {
				return this._set.length;
			}
		}
	});
	
	return List;
});
