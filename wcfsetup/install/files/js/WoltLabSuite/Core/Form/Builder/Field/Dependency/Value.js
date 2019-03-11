/**
 * Form field dependency implementation that requires a field to have a certain value.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency/Value
 * @see 	module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since	5.2
 */
define(['./Abstract', 'Core', './Manager'], function(Abstract, Core, Manager) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function Value(dependentElementId, fieldId, isNegated) {
		this.init(dependentElementId, fieldId);
		
		this._isNegated = false;
	};
	Core.inherit(Value, Abstract, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract#checkDependency
		 */
		checkDependency: function() {
			if (!this._values) {
				throw new Error("Values have not been set.");
			}
			
			var value;
			if (this._field) {
				if (Manager.isHiddenByDependencies(this._field)) {
					return false;
				}
				
				value = this._field.value;
			}
			else {
				for (var i = 0, length = this._fields.length, field; i < length; i++) {
					field = this._fields[i];
					
					if (field.checked) {
						if (Manager.isHiddenByDependencies(field)) {
							return false;
						}
						
						value = field.value;
						
						break;
					}
				}
			}
			
			// do not use `Array.prototype.indexOf()` as we use a weak comparision
			for (var i = 0, length = this._values.length; i < length; i++) {
				if (this._values[i] == value) {
					if (this._isNegated) {
						return false;
					}
					
					return true;
				}
			}
			
			if (this._isNegated) {
				return true;
			}
			
			return false;
		},
		
		/**
		 * Sets if the field value may not have any of the set values.
		 * 
		 * @param	{bool}		negate
		 * @return	{WoltLabSuite/Core/Form/Builder/Field/Dependency/Value}
		 */
		negate: function(negate) {
			this._isNegated = negate;
			
			return this;
		},
		
		/**
		 * Sets the possible values the field may have for the dependency to be met.
		 * 
		 * @param	{array}		values
		 * @return	{WoltLabSuite/Core/Form/Builder/Field/Dependency/Value}
		 */
		values: function(values) {
			this._values = values;
			
			return this;
		}
	});
	
	return Value;
});
