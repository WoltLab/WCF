/**
 * Data handler for a user form builder field in an Ajax form.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/User
 * @since	5.2
 */
define(['Core', './Field', 'WoltLabSuite/Core/Ui/ItemList'], function(Core, FormBuilderField, UiItemList) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function FormBuilderFieldUser(fieldId) {
		this.init(fieldId);
	};
	Core.inherit(FormBuilderFieldUser, FormBuilderField, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
		 */
		_getData: function() {
			var values = UiItemList.getValues(this._fieldId);
			var usernames = [];
			for (var i = 0, length = values.length; i < length; i++) {
				if (values[i].objectId) {
					usernames.push(values[i].value);
				}
			}
			
			var data = {};
			data[this._fieldId] = usernames.join(',');
			
			return data;
		}
	});
	
	return FormBuilderFieldUser;
});
