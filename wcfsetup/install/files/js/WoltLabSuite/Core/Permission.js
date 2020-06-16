/**
 * Manages user permissions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	Permission (alias)
 * @module	WoltLabSuite/Core/Permission
 */
define(['Dictionary'], function(Dictionary) {
	"use strict";
	
	var _permissions = new Dictionary();
	
	/**
	 * @exports	WoltLabSuite/Core/Permission
	 */
	return {
		/**
		 * Adds a single permission to the store.
		 * 
		 * @param	{string}	permission	permission name
		 * @param	{boolean}	value		permission value
		 */
		add: function(permission, value) {
			if (typeof value !== "boolean") {
				throw new TypeError("Permission value has to be boolean.");
			}
			
			_permissions.set(permission, value);
		},
		
		/**
		 * Adds all the permissions in the given object to the store.
		 * 
		 * @param	{Object.<string, boolean>}	object		permission list
		 */
		addObject: function(object) {
			for (var key in object) {
				if (objOwns(object, key)) {
					this.add(key, object[key]);
				}
			}
		},
		
		/**
		 * Returns the value of a permission.
		 * 
		 * If the permission is unknown, false is returned.
		 * 
		 * @param	{string}	permission	permission name
		 * @return	{boolean}	permission value
		 */
		get: function(permission) {
			if (_permissions.has(permission)) {
				return _permissions.get(permission);
			}
			
			return false;
		}
	};
});
