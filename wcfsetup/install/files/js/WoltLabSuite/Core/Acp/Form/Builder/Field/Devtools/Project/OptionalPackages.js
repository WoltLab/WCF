/**
 * Manages the packages entered in a devtools project optional package form field.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/OptionalPackages
 * @see 	module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since	5.2
 */
define(['./AbstractPackageList', 'Core', 'Language'], function (AbstractPackageList, Core, Language) {
    "use strict";
    /**
     * @constructor
     */
    function OptionalPackages(formFieldId, existingPackages) {
        this.init(formFieldId, existingPackages);
    }
    ;
    Core.inherit(OptionalPackages, AbstractPackageList, {
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/AbstractPackageList#_populateListItem
         */
        _populateListItem: function (listItem, packageData) {
            OptionalPackages._super.prototype._populateListItem.call(this, listItem, packageData);
            listItem.innerHTML = ' ' + Language.get('wcf.acp.devtools.project.optionalPackage.optionalPackage', {
                file: packageData.file,
                packageIdentifier: packageData.packageIdentifier
            });
        }
    });
    return OptionalPackages;
});
