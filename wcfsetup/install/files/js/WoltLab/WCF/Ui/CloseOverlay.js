/**
 * Allows to be informed when a click event bubbled up to the document's body.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/CloseOverlay
 */
define(['CallbackList'], function(CallbackList) {
	"use strict";
	
	var _callbackList = new CallbackList();
	
	/**
	 * @exports	WoltLab/WCF/Ui/CloseOverlay
	 */
	var UiCloseOverlay = {
		/**
		 * Sets up global event listener for bubbled clicks events.
		 */
		setup: function() {
			document.body.addEventListener(WCF_CLICK_EVENT, this.execute.bind(this));
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
	
	UiCloseOverlay.setup();
	
	return UiCloseOverlay;
});
