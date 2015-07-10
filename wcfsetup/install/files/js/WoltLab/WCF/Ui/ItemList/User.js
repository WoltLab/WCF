/**
 * Provides an item list for users and groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/ItemList/User
 */
define(['WoltLab/WCF/UI/ItemList'], function(UIItemList) {
	"use strict";
	
	/**
	 * @exports	WoltLab/WCF/UI/ItemList/User
	 */
	var UIItemListUser = {
		/**
		 * Initializes user suggestion support for an element.
		 * 
		 * @param	{string}	elementId	input element id
		 * @param	{object}	options		option list
		 */
		init: function(elementId, options) {
			UIItemList.init(elementId, [], {
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
		 * @see	WoltLab/WCF/UI/ItemList::getValues()
		 */
		getValues: function(elementId) {
			return UIItemList.getValues(elementId);
		}
	};
	
	return UIItemListUser;
});
