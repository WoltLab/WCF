/**
 * Form field dependency implementation that requires a field to have a certain value.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency
 * @see 	module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since	3.2
 */
define(['./Abstract', 'Core'], function(Abstract, Core) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function Value(dependentElementId, fieldId) {
		this.init(dependentElementId, fieldId);
	};
	Core.inherit(Value, Abstract, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract#checkDependency
		 */
		checkDependency: function() {
			if (!this._values) {
				throw new Error("Values have not been set.");
			}
			
			// do not use `Array.prototype.indexOf()` as we use a weak comparision
			for (var i = 0, length = this._values.length; i < length; i++) {
				if (this._values[i] == this._field.value) {
					return true;
				}
			}
			
			return false;
		},
		
		/**
		 * Sets the possible values the field may have for the dependency to be met.
		 * 
		 * @param	{array}		values
		 */
		values: function(values) {
			this._values = values;
		}
	});
	
	return Value;
});