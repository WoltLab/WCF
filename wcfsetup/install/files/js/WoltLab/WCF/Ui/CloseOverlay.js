/**
 * Allows to be informed when a click event bubbled up to the document's body.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/CloseOlveray
 */
define(['CallbackList'], function(CallbackList) {
	"use strict";
	
	var _callbackList = new CallbackList();
	
	/**
	 * @exports	WoltLab/WCF/UI/CloseOverlay
	 */
	var UICloseOverlay = {
		/**
		 * Sets up global event listener for bubbled clicks events.
		 */
		setup: function() {
			document.body.addEventListener('click', this.execute.bind(this));
		},
		
		/**
		 * @see	WoltLab/WCF/CallbackList#add
		 */
		add: _callbackList.add.bind(_callbackList),
		
		/**
		 * @see	WoltLab/WCF/CallbackList#remove
		 */
		remove: _callbackList.remove.bind(_callbackList),
		
		/**
		 * Invokes all registered callbacks.
		 */
		execute: function() {
			_callbackList.forEach(null, function(callback) {
				callback();
			});
		}
	};
	
	UICloseOverlay.setup();
	
	return UICloseOverlay;
});
