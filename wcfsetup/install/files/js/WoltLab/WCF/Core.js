"use strict";

/**
 * Provides the basic core functionality.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Core
 */
define(['jQuery'], function($) {
	/**
	 * @constructor
	 */
	function Core() {};
	Core.prototype = {
		/**
		 * Initializes the core UI modifications and unblocks jQuery's ready event.
		 */
		setup: function() {
			require(['WoltLab/WCF/Date/Time/Relative', 'UI/SimpleDropdown', 'WoltLab/WCF/UI/Mobile', 'WoltLab/WCF/UI/TabMenu'], function(relativeTime, simpleDropdown, uiMobile, TabMenu) {
				relativeTime.setup();
				simpleDropdown.setup();
				uiMobile.setup();
				TabMenu.init();
				
				$.holdReady(false);
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
