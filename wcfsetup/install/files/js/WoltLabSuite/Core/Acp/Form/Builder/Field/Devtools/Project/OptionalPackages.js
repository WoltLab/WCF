/**
 * Manages the packages entered in a devtools project optional package form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @see module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 5.2
 */
define(["require", "exports", "tslib", "./AbstractPackageList", "WoltLabSuite/Core/Language"], function (require, exports, tslib_1, AbstractPackageList_1, Language_1) {
    "use strict";
    AbstractPackageList_1 = tslib_1.__importDefault(AbstractPackageList_1);
    class OptionalPackages extends AbstractPackageList_1.default {
        populateListItem(listItem, packageData) {
            super.populateListItem(listItem, packageData);
            listItem.innerHTML = ` ${(0, Language_1.getPhrase)("wcf.acp.devtools.project.optionalPackage.optionalPackage", {
                packageIdentifier: packageData.packageIdentifier,
            })}`;
        }
    }
    return OptionalPackages;
});
