/**
 * Abstract implementation of a form field dependency.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since	5.2
 */
define(['./Manager'], function(DependencyManager) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function Abstract(dependentElementId, fieldId) {
		this.init(dependentElementId, fieldId);
	};
	Abstract.prototype = {
		/**
		 * Checks if the dependency is met.
		 * 
		 * @abstract
		 */
		checkDependency: function() {
			throw new Error("Missing implementation of WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract.checkDependency!");
		},
		
		/**
		 * Return the node whose availability depends on the value of a field.
		 * 
		 * @return	{HtmlElement}	dependent node
		 */
		getDependentNode: function() {
			return this._dependentElement;
		},
		
		/**
		 * Returns the field the availability of the element dependents on.
		 * 
		 * @return	{HtmlElement}	field controlling element availability
		 */
		getField: function() {
			return this._field;
		},
		
		/**
		 * Returns all fields requiring `change` event listeners for this
		 * dependency to be properly resolved.
		 * 
		 * @return	{HtmlElement[]}		fields to register event listeners on
		 */
		getFields: function() {
			return this._fields;
		},
		
		/**
		 * Initializes the new dependency object.
		 * 
		 * @param	{string}	dependentElementId	id of the (container of the) dependent element
		 * @param	{string}	fieldId			id of the field controlling element availability
		 * 
		 * @throws	{Error}					if either depenent element id or field id are invalid
		 */
		init: function(dependentElementId, fieldId) {
			this._dependentElement = elById(dependentElementId);
			if (this._dependentElement === null) {
				throw new Error("Unknown dependent element with container id '" + dependentElementId + "Container'.");
			}
			
			this._field = elById(fieldId);
			if (this._field === null) {
				this._fields = [];
				elBySelAll('input[type=radio][name=' + fieldId + ']', undefined, function(field) {
					this._fields.push(field);
				}.bind(this));
				
				if (!this._fields.length) {
					throw new Error("Unknown field with id '" + fieldId + "'.");
				}
			}
			else {
				this._fields = [this._field];
				
				// handle special case of boolean form fields that have to form fields
				if (this._field.tagName === 'INPUT' && this._field.type === 'radio' && elData(this._field, 'no-input-id') !== '') {
					this._noField = elById(elData(this._field, 'no-input-id'));
					if (this._noField === null) {
						throw new Error("Cannot find 'no' input field for input field '" + fieldId + "'");
					}
					
					this._fields.push(this._noField);
				}
			}
			
			DependencyManager.addDependency(this);
		}
	};
	
	return Abstract;
});
