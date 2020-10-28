/**
 * Manages the packages entered in a devtools project excluded package form field.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/ExcludedPackages
 * @see 	module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since	5.2
 */
define(['./AbstractPackageList', 'Core', 'Language'], function (AbstractPackageList, Core, Language) {
    "use strict";
    /**
     * @constructor
     */
    function ExcludedPackages(formFieldId, existingPackages) {
        this.init(formFieldId, existingPackages);
    }
    ;
    Core.inherit(ExcludedPackages, AbstractPackageList, {
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#init
         */
        init: function (formFieldId, existingPackages) {
            ExcludedPackages._super.prototype.init.call(this, formFieldId, existingPackages);
            this._version = elById(this._formFieldId + '_version');
            if (this._version === null) {
                throw new Error("Cannot find version form field for packages field with id '" + this._formFieldId + "'.");
            }
            this._version.addEventListener('keypress', this._keyPress.bind(this));
        },
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_createSubmitFields
         */
        _createSubmitFields: function (listElement, index) {
            ExcludedPackages._super.prototype._createSubmitFields.call(this, listElement, index);
            var version = elCreate('input');
            elAttr(version, 'type', 'hidden');
            elAttr(version, 'name', this._formFieldId + '[' + index + '][version]');
            version.value = elData(listElement, 'version');
            this._form.appendChild(version);
        },
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_emptyInput
         */
        _emptyInput: function () {
            ExcludedPackages._super.prototype._emptyInput.call(this);
            this._version.value = '';
        },
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_getInputData
         */
        _getInputData: function () {
            return Core.extend(ExcludedPackages._super.prototype._getInputData.call(this), {
                version: this._version.value
            });
        },
        /**
         * Returns the error element for the version form field.
         * If `createIfNonExistent` is not given or `false`, `null` is returned
         * if there is no error element, otherwise an empty error element
         * is created and returned.
         *
         * @param	{?boolean}	createIfNonExistent
         * @return	{?HTMLElement}
         */
        _getVersionErrorElement: function (createIfNonExistent) {
            return this._getErrorElement(this._version, createIfNonExistent);
        },
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_populateListItem
         */
        _populateListItem: function (listItem, packageData) {
            ExcludedPackages._super.prototype._populateListItem.call(this, listItem, packageData);
            elData(listItem, 'version', packageData.version);
            listItem.innerHTML = ' ' + Language.get('wcf.acp.devtools.project.excludedPackage.excludedPackage', {
                packageIdentifier: packageData.packageIdentifier,
                version: packageData.version
            });
        },
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_validateInput
         */
        _validateInput: function () {
            return ExcludedPackages._super.prototype._validateInput.call(this) && this._validateVersion(this._version.value, this._getVersionErrorElement.bind(this));
        }
    });
    return ExcludedPackages;
});
