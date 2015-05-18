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
						if (typeof obj[key] === 'object') {
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
