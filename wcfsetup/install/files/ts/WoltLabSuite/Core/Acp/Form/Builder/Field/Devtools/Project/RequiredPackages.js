/**
 * Manages the packages entered in a devtools project required package form field.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Acp/Builder/Field/Devtools/Project/RequiredPackages
 * @see 	module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since	5.2
 */
define(['./AbstractPackageList', 'Core', 'Language'], function(AbstractPackageList, Core, Language) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function RequiredPackages(formFieldId, existingPackages) {
		this.init(formFieldId, existingPackages);
	};
	Core.inherit(RequiredPackages, AbstractPackageList, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#init
		 */
		init: function(formFieldId, existingPackages) {
			RequiredPackages._super.prototype.init.call(this, formFieldId, existingPackages);
			
			this._minVersion = elById(this._formFieldId + '_minVersion');
			if (this._minVersion === null) {
				throw new Error("Cannot find minimum version form field for packages field with id '" + this._formFieldId + "'.");
			}
			this._minVersion.addEventListener('keypress', this._keyPress.bind(this));
			
			this._file = elById(this._formFieldId + '_file');
			if (this._file === null) {
				throw new Error("Cannot find file form field for required field with id '" + this._formFieldId + "'.");
			}
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_createSubmitFields
		 */
		_createSubmitFields: function(listElement, index) {
			RequiredPackages._super.prototype._createSubmitFields.call(this, listElement, index);
			
			var minVersion = elCreate('input');
			elAttr(minVersion, 'type', 'hidden');
			elAttr(minVersion, 'name', this._formFieldId + '[' + index + '][minVersion]')
			minVersion.value = elData(listElement, 'min-version');
			this._form.appendChild(minVersion);
			
			var file = elCreate('input');
			elAttr(file, 'type', 'hidden');
			elAttr(file, 'name', this._formFieldId + '[' + index + '][file]')
			file.value = elData(listElement, 'file');
			this._form.appendChild(file);
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_emptyInput
		 */
		_emptyInput: function() {
			RequiredPackages._super.prototype._emptyInput.call(this);
			
			this._minVersion.value = '';
			this._file.checked = false;
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_getInputData
		 */
		_getInputData: function() {
			return Core.extend(RequiredPackages._super.prototype._getInputData.call(this), {
				file: this._file.checked,
				minVersion: this._minVersion.value
			});
		},
		
		/**
		 * Returns the error element for the minimum version form field.
		 * If `createIfNonExistent` is not given or `false`, `null` is returned
		 * if there is no error element, otherwise an empty error element
		 * is created and returned.
		 *
		 * @param	{?boolean}	createIfNonExistent
		 * @return	{?HTMLElement}
		 */
		_getMinVersionErrorElement: function(createIfNonExistent) {
			return this._getErrorElement(this._minVersion, createIfNonExistent);
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_populateListItem
		 */
		_populateListItem: function(listItem, packageData) {
			RequiredPackages._super.prototype._populateListItem.call(this, listItem, packageData);
			
			elData(listItem, 'min-version', packageData.minVersion);
			elData(listItem, 'file', ~~packageData.file);
			listItem.innerHTML = ' ' + Language.get('wcf.acp.devtools.project.requiredPackage.requiredPackage', {
				file: ~~packageData.file,
				minVersion: packageData.minVersion,
				packageIdentifier: packageData.packageIdentifier
			});
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_validateInput
		 */
		_validateInput: function() {
			return RequiredPackages._super.prototype._validateInput.call(this) && this._validateVersion(
				this._minVersion.value,
				this._getMinVersionErrorElement.bind(this)
			);
		}
	});
	
	return RequiredPackages;
});
