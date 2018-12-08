/**
 * Provides global helper methods to interact with ignored content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/Ignore
 */
define(['List', 'Dom/ChangeListener'], function(List, DomChangeListener) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_rebuild: function() {},
			_removeClass: function() {}
		};
		return Fake;
	}
	
	var _availableMessages = elByClass('ignoredUserMessage');
	var _callback = null;
	var _knownMessages = new List();
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/User/Ignore
	 */
	return {
		/**
		 * Initializes the click handler for each ignored message and listens for
		 * newly inserted messages.
		 */
		init: function () {
			_callback = this._removeClass.bind(this);
			
			this._rebuild();
			
			DomChangeListener.add('WoltLabSuite/Core/Ui/User/Ignore', this._rebuild.bind(this));
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
			
			// Firefox selects the entire message on click for no reason
			window.getSelection().removeAllRanges();
		}
	};
});
