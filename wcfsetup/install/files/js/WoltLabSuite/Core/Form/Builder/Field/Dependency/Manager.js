/**
 * Manages form field dependencies.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency
 * @since	3.2
 */
define(['Dictionary'], function(Dictionary) {
	"use strict";
	
	/**
	 * list if fields for which event listeners have been registered
	 * @type	{Dictionary}
	 * @private
	 */
	var _fields = new Dictionary();
	
	/**
	 * list of dependencies grouped by the dependent node they belong to
	 * @type	{Dictionary}
	 * @private
	 */
	var _nodeDependencies = new Dictionary();
	
	return {
		/**
		 * Registers a new form field dependency.
		 * 
		 * @param	{WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract}	dependency	new dependency
		 */
		addDependency: function(dependency) {
			var dependentNode = dependency.getDependentNode();
			if (!_nodeDependencies.has(dependentNode.id)) {
				_nodeDependencies.set(dependentNode.id, [dependency]);
			}
			else {
				_nodeDependencies.get(dependentNode.id).push(dependency);
			}
			
			var fields = dependency.getFields();
			for (var i = 0, length = fields.length; i < length; i++) {
				var field = fields[i];
				if (!_fields.has(field.id)) {
					_fields.set(field.id, field);
					
					if (field.tagName === 'INPUT' && (field.type === 'checkbox' || field.type === 'radio')) {
						field.addEventListener('change', this.checkDependencies.bind(this));
					}
					else {
						field.addEventListener('input', this.checkDependencies.bind(this));
					}
				}
			}
		},
		
		/**
		 * Check all dependencies if they are met.
		 */
		checkDependencies: function() {
			var obsoleteNodes = [];
			
			_nodeDependencies.forEach(function(nodeDependencies, nodeId) {
				var dependentNode = elById(nodeId);
				
				// check if dependent node still exists
				if (dependentNode === null) {
					obsoleteNodes.push(dependentNode);
					return;
				}
				
				for (var i = 0, length = nodeDependencies.length; i < length; i++) {
					// if any dependency is met, the element is visible
					if (nodeDependencies[i].checkDependency()) {
						elShow(dependentNode);
						return;
					}
				}
				
				// no node dependencies is met
				elHide(dependentNode);
			});
			
			// delete dependencies for removed elements
			for (var i = 0, length = obsoleteNodes.length; i < length; i++) {
				_nodeDependencies.delete(obsoleteNodes.id);
			}
		}
	};
});