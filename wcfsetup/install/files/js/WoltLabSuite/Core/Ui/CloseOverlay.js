/**
 * Allows to be informed when a click event bubbled up to the document's body.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/CloseOverlay
 */
define(['CallbackList'], function(CallbackList) {
	"use strict";
	
	var _callbackList = new CallbackList();
	
	/**
	 * @exports	WoltLabSuite/Core/Ui/CloseOverlay
	 */
	var UiCloseOverlay = {
		/**
		 * Sets up global event listener for bubbled clicks events.
		 */
		setup: function() {
			document.body.addEventListener(WCF_CLICK_EVENT, this.execute.bind(this));
		},
		
		/**
		 * @see	WoltLabSuite/Core/CallbackList#add
		 */
		add: _callbackList.add.bind(_callbackList),
		
		/**
		 * @see	WoltLabSuite/Core/CallbackList#remove
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
