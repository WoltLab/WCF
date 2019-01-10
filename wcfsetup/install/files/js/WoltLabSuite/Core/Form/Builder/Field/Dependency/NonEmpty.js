/**
 * Form field dependency implementation that requires the value of a field not to be empty.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency/NonEmpty
 * @see 	module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since	5.2
 */
define(['./Abstract', 'Core'], function(Abstract, Core) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function NonEmpty(dependentElementId, fieldId) {
		this.init(dependentElementId, fieldId);
	};
	Core.inherit(NonEmpty, Abstract, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract#checkDependency
		 */
		checkDependency: function() {
			switch (this._field.tagName) {
				case 'INPUT':
					switch (this._field.type) {
						case 'checkbox':
							// TODO: check if working
							return this._field.checked;
						
						case 'radio':
							if (this._noField && this._noField.checked) {
								return false;
							}
							
							return this._field.checked;
						
						default:
							return this._field.value.trim().length !== 0;
					}
				
				case 'SELECT':
					// TODO: check if working for multiselect
					return this._field.value.length !== 0;
				
				case 'TEXTAREA':
					// TODO: check if working
					return this._field.value.trim().length !== 0;
			}
		}
	});
	
	return NonEmpty;
});
