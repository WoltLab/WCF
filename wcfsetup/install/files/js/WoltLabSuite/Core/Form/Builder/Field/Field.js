/**
 * Data handler for a form builder field in an Ajax form.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Field
 * @since	5.2
 */
define([], function() {
	"use strict";
	
	/**
	 * @constructor
	 */
	function FormBuilderField(fieldId) {
		this.init(fieldId);
	};
	FormBuilderField.prototype = {
		/**
		 * Initializes the form field.
		 * 
		 * @param	{string}	fieldId		id of the relevant form builder field
		 */
		init: function(fieldId) {
			this._fieldId = fieldId;
			
			this._readField();
		},
		
		/**
		 * Returns the current data of the field or a promise returning the current data
		 * of the field.
		 * 
		 * @return	{Promise|data}
		 */
		_getData: function() {
			throw new Error("Missing implementation of WoltLabSuite/Core/Form/Builder/Field/Field._getData!");
		},
		
		/**
		 * Reads the field HTML element.
		 */
		_readField: function() {
			this._field = elById(this._fieldId);
			
			if (this._field === null) {
				throw new Error("Unknown field with id '" + this._fieldId + "'.");
			}
		},
		
		/**
		 * Destroys the field.
		 * 
		 * This function is useful for remove registered elements from other APIs like dialogs.
		 */
		destroy: function() {
			// does nothing
		},
		
		/**
		 * Returns a promise returning the current data of the field.
		 * 
		 * @return	{Promise}
		 */
		getData: function() {
			var data = this._getData();
			
			if (!(data instanceof Promise)) {
				return Promise.resolve(data);
			}
			
			return data;
		},
		
		/**
		 * Returns the id of the field.
		 * 
		 * @return	{string}
		 */
		getId: function() {
			return this._fieldId;
		}
	};
	
	return FormBuilderField;
});
