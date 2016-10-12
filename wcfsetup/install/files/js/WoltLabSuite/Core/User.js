/**
 * Provides data of the active user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/User
 */
define([], function() {
	"use strict";
	
	var _didInit = false;
	
	/**
	 * @exports	WoltLabSuite/Core/User
	 */
	return {
		/**
		 * Initializes the user object.
		 * 
		 * @param	{int}		userId		id of the user, `0` for guests
		 * @param	{string}	username	name of the user, empty for guests
		 */
		init: function(userId, username) {
			if (_didInit) {
				throw new Error('User has already been initialized.');
			}
			
			// define non-writeable properties for userId and username
			Object.defineProperty(this, 'userId', {
				value: userId,
				writable: false
			});
			Object.defineProperty(this, 'username', {
				value: username,
				writable: false
			});
			
			_didInit = true;
		}
	};
});
