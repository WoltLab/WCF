/**
 * Data handler for a simple acl form builder field in an Ajax form.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/SimpleAcl
 * @since	5.2
 */
define(['Core', './Field'], function(Core, FormBuilderField) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function FormBuilderFieldSimpleAcl(fieldId) {
		this.init(fieldId);
	};
	Core.inherit(FormBuilderFieldSimpleAcl, FormBuilderField, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
		 */
		_getData: function() {
			var groupIds = [];
			elBySelAll('input[name="' + this._fieldId + '[group][]"]', undefined, function(input) {
				groupIds.push(~~input.value);
			});
			
			var usersIds = [];
			elBySelAll('input[name="' + this._fieldId + '[user][]"]', undefined, function(input) {
				usersIds.push(~~input.value);
			});
			
			var data = {};
			
			data[this._fieldId] = {
				group: groupIds,
				user: usersIds
			};
			
			return data;
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_readField
		 */
		_readField: function() {
			// does nothing
		}
	});
	
	return FormBuilderFieldSimpleAcl;
});
