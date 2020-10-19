/**
 * Form field dependency implementation that requires the value of a field to be empty.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency/Empty
 * @see 	module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since	5.2
 */
define(['./Abstract', 'Core'], function(Abstract, Core) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function Empty(dependentElementId, fieldId) {
		this.init(dependentElementId, fieldId);
	};
	Core.inherit(Empty, Abstract, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract#checkDependency
		 */
		checkDependency: function() {
			if (this._field !== null) {
				switch (this._field.tagName) {
					case 'INPUT':
						switch (this._field.type) {
							case 'checkbox':
								return !this._field.checked;
							
							case 'radio':
								if (this._noField && this._noField.checked) {
									return true;
								}
								
								return !this._field.checked;
							
							default:
								return this._field.value.trim().length === 0;
						}
					
					case 'SELECT':
						if (this._field.multiple) {
							return elBySelAll('option:checked', this._field).length === 0;
						}
						
						return this._field.value == 0 || this._field.value.length === 0;
					
					case 'TEXTAREA':
						return this._field.value.trim().length === 0;
				}
			}
			else {
				for (var i = 0, length = this._fields.length; i < length; i++) {
					if (this._fields[i].checked) {
						return false;
					}
				}
				
				return true;
			}
		}
	});
	
	return Empty;
});
