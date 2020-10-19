/**
 * Data handler for a form builder field in an Ajax form represented by checkboxes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Checkboxes
 * @since	5.2
 */
define(['Core', './Field'], function(Core, FormBuilderField) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function FormBuilderFieldCheckboxes(fieldId) {
		this.init(fieldId);
	};
	Core.inherit(FormBuilderFieldCheckboxes, FormBuilderField, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
		 */
		_getData: function() {
			var data = {};
			
			data[this._fieldId] = [];
			
			for (var i = 0, length = this._fields.length; i < length; i++) {
				if (this._fields[i].checked) {
					data[this._fieldId].push(this._fields[i].value);
				}
			}
			
			return data;
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_readField
		 */
		_readField: function() {
			this._fields = elBySelAll('input[name="' + this._fieldId + '[]"]');
		}
	});
	
	return FormBuilderFieldCheckboxes;
});
