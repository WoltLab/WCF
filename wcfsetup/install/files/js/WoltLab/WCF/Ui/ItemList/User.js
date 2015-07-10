/**
 * Provides an item list for users and groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/ItemList/User
 */
define(['WoltLab/WCF/Ui/ItemList'], function(UiItemList) {
	"use strict";
	
	/**
	 * @exports	WoltLab/WCF/Ui/ItemList/User
	 */
	var UiItemListUser = {
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
							includeUserGroups: ~~options.includeUserGroups
						}
					}
				},
				callbackChange: (typeof options.callbackChange === 'function' ? options.callbackChange : null),
				excludedSearchValues: (Array.isArray(options.excludedSearchValues) ? options.excludedSearchValues : []),
				isCSV: true,
				maxItems: ~~options.maxItems || -1,
				restricted: true
			});
		},
		
		/**
		 * @see	WoltLab/WCF/Ui/ItemList::getValues()
		 */
		getValues: function(elementId) {
			return UiItemList.getValues(elementId);
		}
	};
	
	return UiItemListUser;
});
