/**
 * Data handler for a captcha form builder field in an Ajax form.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Captcha
 * @since	5.2
 */
define(['Core', './Field', 'WoltLabSuite/Core/Controller/Captcha'], function(Core, FormBuilderField, Captcha) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function FormBuilderFieldCaptcha(fieldId) {
		this.init(fieldId);
	};
	Core.inherit(FormBuilderFieldCaptcha, FormBuilderField, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#getData
		 */
		_getData: function() {
			if (Captcha.has(this._fieldId)) {
				return Captcha.getData(this._fieldId);
			}
			
			return {};
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_readField
		 */
		_readField: function() {
			// does nothing
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#destroy
		 */
		destroy: function() {
			if (Captcha.has(this._fieldId)) {
				Captcha.delete(this._fieldId);
			}
		}
	});
	
	return FormBuilderFieldCaptcha;
});
