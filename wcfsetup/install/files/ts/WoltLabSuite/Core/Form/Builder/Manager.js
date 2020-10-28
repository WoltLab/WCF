/**
 * Manager for registered Ajax forms and its fields that can be used to retrieve the current data
 * of the registered forms.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Manager
 * @since	5.2
 */
define([
	'Core',
	'Dictionary',
	'EventHandler',
	'./Field/Dependency/Manager',
	'./Field/Field'
], function(
	Core,
	Dictionary,
	EventHandler,
	FormBuilderFieldDependencyManager,
	FormBuilderField
) {
	"use strict";
	
	var _fields = new Dictionary();
	var _forms = new Dictionary();
	
	return {
		/**
		 * Returns a promise returning the data of the form with the given id.
		 * 
		 * @param	{string}	formId
		 * @return	{Promise}
		 */
		getData: function(formId) {
			if (!this.hasForm(formId)) {
				throw new Error("Unknown form with id '" + formId + "'.");
			}
			
			var promises = [];
			
			_fields.get(formId).forEach(function(field) {
				var fieldData = field.getData();
				
				if (!(fieldData instanceof Promise)) {
					throw new TypeError("Data for field with id '" + field.getId() + "' is no promise.");
				}
				
				promises.push(fieldData);
			});
			
			return Promise.all(promises).then(function(promiseData) {
				var data = {};
				
				for (var i = 0, length = promiseData.length; i < length; i++) {
					data = Core.extend(data, promiseData[i]);
				}
				
				return data;
			});
		},
		
		/**
		 * Returns the registered form field with given id.
		 * 
		 * @param	{string}	formId
		 * @return	{WoltLabSuite/Core/Form/Builder/Field/Field}
		 * @since	5.2.3
		 */
		getField: function(formId, fieldId) {
			if (!this.hasField(formId, fieldId)) {
				throw new Error("Unknown field with id '" + formId + "' for form with id '"  + fieldId + "'.");
			}
			
			return _fields.get(formId).get(fieldId);
		},
		
		/**
		 * Returns the registered form with given id.
		 * 
		 * @param	{string}	formId
		 * @return	{HTMLElement}
		 */
		getForm: function(formId) {
			if (!this.hasForm(formId)) {
				throw new Error("Unknown form with id '" + formId + "'.");
			}
			
			return _forms.get(formId);
		},
		
		/**
		 * Returns `true` if a field with the given id has been registered for the form with
		 * the given id and `false` otherwise.
		 * 
		 * @param	{string}	formId
		 * @param	{string}	fieldId
		 * @return	{boolean}
		 */
		hasField: function(formId, fieldId) {
			if (!this.hasForm(formId)) {
				throw new Error("Unknown form with id '" + formId + "'.");
			}
			
			return _fields.get(formId).has(fieldId);
		},
		
		/**
		 * Returns `true` if a form with the given id has been registered and `false`
		 * otherwise.
		 * 
		 * @param	{string}	formId
		 * @return	{boolean}
		 */
		hasForm: function(formId) {
			return _forms.has(formId);
		},
		
		/**
		 * Registers the given field for the form with the given id.
		 * 
		 * @param	{string}					formId
		 * @param	{WoltLabSuite/Core/Form/Builder/Field/Field}	field
		 */
		registerField: function(formId, field) {
			if (!this.hasForm(formId)) {
				throw new Error("Unknown form with id '" + formId + "'.");
			}
			
			if (!(field instanceof FormBuilderField)) {
				throw new Error("Add field is no instance of 'WoltLabSuite/Core/Form/Builder/Field/Field'.");
			}
			
			var fieldId = field.getId();
			
			if (this.hasField(formId, fieldId)) {
				throw new Error("Form field with id '" + fieldId + "' has already been registered for form with id '" + formId + "'.");
			}
			
			_fields.get(formId).set(fieldId, field);
		},
		
		/**
		 * Registers the form with the given id.
		 * 
		 * @param	{string}	formId
		 */
		registerForm: function(formId) {
			if (this.hasForm(formId)) {
				throw new Error("Form with id '" + formId + "' has already been registered.");
			}
			
			var form = elById(formId);
			if (form === null) {
				throw new Error("Unknown form with id '" + formId + "'.");
			}
			
			_forms.set(formId, form);
			_fields.set(formId, new Dictionary());
			
			EventHandler.fire('WoltLabSuite/Core/Form/Builder/Manager', 'registerForm', {
				formId: formId
			});
		},
		
		/**
		 * Unregisters the form with the given id.
		 * 
		 * @param	{string}	formId
		 */
		unregisterForm: function(formId) {
			if (!this.hasForm(formId)) {
				throw new Error("Unknown form with id '" + formId + "'.");
			}
			
			EventHandler.fire('WoltLabSuite/Core/Form/Builder/Manager', 'beforeUnregisterForm', {
				formId: formId
			});
			
			_forms.delete(formId);
			
			_fields.get(formId).forEach(function(field) {
				field.destroy();
			});
			
			_fields.delete(formId);
			
			FormBuilderFieldDependencyManager.unregister(formId);
			
			EventHandler.fire('WoltLabSuite/Core/Form/Builder/Manager', 'afterUnregisterForm', {
				formId: formId
			});
		}
	};
});
