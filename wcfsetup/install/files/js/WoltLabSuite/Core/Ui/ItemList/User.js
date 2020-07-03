/**
 * Provides an item list for users and groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/ItemList/User
 */
define(['WoltLabSuite/Core/Ui/ItemList'], function(UiItemList) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			getValues: function() {}
		};
		return Fake;
	}
	
	/**
	 * @exports	WoltLabSuite/Core/Ui/ItemList/User
	 */
	return {
		/**
		 * Initializes user suggestion support for an element.
		 * 
		 * @param	{string}	elementId	input element id
		 * @param	{object}	options		option list
		 */
		init: function(elementId, options) {
			UiItemList.init(elementId, [], {
				ajax: {
					className: 'wcf\\data\\user\\UserAction',
					parameters: {
						data: {
							includeUserGroups: ~~options.includeUserGroups,
							restrictUserGroupIDs: (Array.isArray(options.restrictUserGroupIDs) ? options.restrictUserGroupIDs : [])
						}
					}
				},
				callbackChange: (typeof options.callbackChange === 'function' ? options.callbackChange : null),
				callbackSyncShadow: options.csvPerType ? this._syncShadow.bind(this) : null,
				callbackSetupValues: (typeof options.callbackSetupValues === 'function' ? options.callbackSetupValues : null),
				excludedSearchValues: (Array.isArray(options.excludedSearchValues) ? options.excludedSearchValues : []),
				isCSV: true,
				maxItems: ~~options.maxItems || -1,
				restricted: true
			});
		},
		
		/**
		 * @see	WoltLabSuite/Core/Ui/ItemList::getValues()
		 */
		getValues: function(elementId) {
			return UiItemList.getValues(elementId);
		},
		
		_syncShadow: function(data) {
			var values = this.getValues(data.element.id);
			var users = [], groups = [];
			
			values.forEach(function(value) {
				if (value.type && value.type === 'group') groups.push(value.objectId);
				else users.push(value.value);
			});
			
			data.shadow.value = users.join(',');
			if (!data._shadowGroups) {
				data._shadowGroups = elCreate('input');
				data._shadowGroups.type = 'hidden';
				data._shadowGroups.name = data.shadow.name + 'GroupIDs';
				data.shadow.parentNode.insertBefore(data._shadowGroups, data.shadow);
			}
			data._shadowGroups.value = groups.join(',');
			
			return values;
		}
	};
});
