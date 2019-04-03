/**
 * Data handler for an i18n form builder field in an Ajax form that stores its value in an input's
 * value attribute.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/ValueI18n
 * @since	5.2
 */
define(['Core', './Field', 'WoltLabSuite/Core/Language/Input'], function(Core, FormBuilderField, LanguageInput) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function FormBuilderFieldValueI18n(fieldId) {
		this.init(fieldId);
	};
	Core.inherit(FormBuilderFieldValueI18n, FormBuilderField, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
		 */
		_getData: function() {
			var data = {};
			
			var values = LanguageInput.getValues(this._fieldId);
			if (values.size > 1) {
				data[this._fieldId + '_i18n'] = values.toObject();
			}
			else {
				data[this._fieldId] = values.get(0);
			}
			
			return data;
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#destroy
		 */
		destroy: function() {
			LanguageInput.unregister(this._fieldId);
		}
	});
	
	return FormBuilderFieldValueI18n;
});
