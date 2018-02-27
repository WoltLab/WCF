/**
 * Provides data of the active user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/User
 */
define([], function() {
	"use strict";
	
	var _didInit = false;
	var _link;
	
	/**
	 * @exports	WoltLabSuite/Core/User
	 */
	return {
		/**
		 * Returns the link to the active user's profile or an empty string
		 * if the active user is a guest.
		 * 
		 * @return	{string}
		 */
		getLink: function() {
			return _link;
		},
		
		/**
		 * Initializes the user object.
		 * 
		 * @param	{int}		userId		id of the user, `0` for guests
		 * @param	{string}	username	name of the user, empty for guests
		 * @param	{string}	userLink	link to the user's profile, empty for guests
		 */
		init: function(userId, username, userLink) {
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
			
			_link = userLink;
			
			_didInit = true;
		}
	};
});
