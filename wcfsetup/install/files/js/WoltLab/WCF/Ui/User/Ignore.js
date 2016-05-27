/**
 * Provides global helper methods to interact with ignored content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/User/Ignore
 */
define(['List', 'Dom/ChangeListener'], function(List, DomChangeListener) {
	"use strict";
	
	var _availableMessages = elByClass('ignoredUserMessage');
	var _callback = null;
	var _knownMessages = new List();
	
	/**
	 * @exports     WoltLab/WCF/Ui/User/Ignore
	 */
	return {
		/**
		 * Initializes the click handler for each ignored message and listens for
		 * newly inserted messages.
		 */
		init: function () {
			_callback = this._removeClass.bind(this);
			
			this._rebuild();
			
			DomChangeListener.add('WoltLab/WCF/Ui/User/Ignore', this._rebuild.bind(this));
		},
		
		/**
		 * Adds ignored messages to the collection.
		 * 
		 * @protected
		 */
		_rebuild: function() {
			var message;
			for (var i = 0, length = _availableMessages.length; i < length; i++) {
				message = _availableMessages[i];
				
				if (!_knownMessages.has(message)) {
					message.addEventListener(WCF_CLICK_EVENT, _callback);
					
					_knownMessages.add(message);
				}
			}
		},
		
		/**
		 * Reveals a message on click/tap and disables the listener.
		 * 
		 * @param       {Event}         event   event object
		 * @protected
		 */
		_removeClass: function(event) {
			event.preventDefault();
			
			var message = event.currentTarget;
			message.classList.remove('ignoredUserMessage');
			message.removeEventListener(WCF_CLICK_EVENT, _callback);
			_knownMessages.delete(message);
		}
	};
});
