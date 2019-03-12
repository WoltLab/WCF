/**
 * Provides reliable checks for common key presses, uses `Event.key` on supported browsers
 * or the deprecated `Event.which`.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Event/Key
 */
define([], function() {
	"use strict";
	
	function _isKey(event, key, which) {
		if (!(event instanceof Event)) {
			throw new TypeError("Expected a valid event when testing for key '" + key + "'.");
		}
		
		return event.key === key || event.which === which;
	}
	
	/**
	 * @exports     WoltLabSuite/Core/Event/Key
	 */
	return {
		/**
		 * Returns true if the pressed key equals 'ArrowDown'.
		 * 
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		ArrowDown: function(event) {
			return _isKey(event, 'ArrowDown', 40);
		},
		
		/**
		 * Returns true if the pressed key equals 'ArrowLeft'.
		 * 
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		ArrowLeft: function(event) {
			return _isKey(event, 'ArrowLeft', 37);
		},
		
		/**
		 * Returns true if the pressed key equals 'ArrowRight'.
		 * 
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		ArrowRight: function(event) {
			return _isKey(event, 'ArrowRight', 39);
		},
		
		/**
		 * Returns true if the pressed key equals 'ArrowUp'.
		 * 
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		ArrowUp: function(event) {
			return _isKey(event, 'ArrowUp', 38);
		},
		
		/**
		 * Returns true if the pressed key equals 'Comma'.
		 * 
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		Comma: function(event) {
			return _isKey(event, ',', 44);
		},
		
		/**
		 * Returns true if the pressed key equals 'End'.
		 *
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		End: function(event) {
			return _isKey(event, 'End', 35);
		},
		
		/**
		 * Returns true if the pressed key equals 'Enter'.
		 * 
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		Enter: function(event) {
			return _isKey(event, 'Enter', 13);
		},
		
		/**
		 * Returns true if the pressed key equals 'Escape'.
		 * 
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		Escape: function(event) {
			return _isKey(event, 'Escape', 27);
		},
		
		/**
		 * Returns true if the pressed key equals 'Home'.
		 *
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		Home: function(event) {
			return _isKey(event, 'Home', 36);
		},
		
		/**
		 * Returns true if the pressed key equals 'Space'.
		 *
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		Space: function(event) {
			return _isKey(event, 'Space', 32);
		},
		
		/**
		 * Returns true if the pressed key equals 'Tab'.
		 * 
		 * @param       {Event}         event           event object
		 * @return      {boolean}
		 */
		Tab: function(event) {
			return _isKey(event, 'Tab', 9);
		}
	};
});
