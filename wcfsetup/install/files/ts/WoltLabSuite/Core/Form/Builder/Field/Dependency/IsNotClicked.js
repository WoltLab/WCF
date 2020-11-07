/**
 * Form field dependency implementation that requires that a button has not been clicked.
 * 
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Form/Builder/Field/Dependency/IsNotClicked
 * @see         module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since       5.4
 */
define(['./Abstract', 'Core', './Manager'], function(Abstract, Core, DependencyManager) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function IsNotClicked(dependentElementId, fieldId) {
		this.init(dependentElementId, fieldId);
		
		this._field.addEventListener('click', () => {
			this._field.dataset.isClicked = 1;
			
			DependencyManager.checkDependencies();
		});
	};
	Core.inherit(IsNotClicked, Abstract, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract#checkDependency
		 */
		checkDependency: function() {
			return this._field.dataset.isClicked !== "1";
		}
	});
	
	return IsNotClicked;
});
