/**
 * Allows to be informed when the DOM may have changed and
 * new elements that are relevant to you may have been added.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Dom/Change/Listener
 */
define(['CallbackList'], function(CallbackList) {
	"use strict";
	
	var _callbackList = new CallbackList();
	var _hot = false;
	
	/**
	 * @exports	WoltLabSuite/Core/Dom/Change/Listener
	 */
	return {
		/**
		 * @see	WoltLabSuite/Core/CallbackList#add
		 */
		add: _callbackList.add.bind(_callbackList),
		
		/**
		 * @see	WoltLabSuite/Core/CallbackList#remove
		 */
		remove: _callbackList.remove.bind(_callbackList),
		
		/**
		 * Triggers the execution of all the listeners.
		 * Use this function when you added new elements to the DOM that might
		 * be relevant to others.
		 * While this function is in progress further calls to it will be ignored.
		 */
		trigger: function() {
			if (_hot) return;
			
			try {
				_hot = true;
				_callbackList.forEach(null, function(callback) {
					callback();
				});
			}
			finally {
				_hot = false;
			}
		}
	};
});
