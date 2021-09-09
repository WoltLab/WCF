/**
 * Manages the packages entered in a devtools project optional package form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/OptionalPackages
 * @see module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 5.2
 */
define(["require", "exports", "tslib", "./AbstractPackageList", "../../../../../../Core", "../../../../../../Language"], function (require, exports, tslib_1, AbstractPackageList_1, Core, Language) {
    "use strict";
    AbstractPackageList_1 = (0, tslib_1.__importDefault)(AbstractPackageList_1);
    Core = (0, tslib_1.__importStar)(Core);
    Language = (0, tslib_1.__importStar)(Language);
    class OptionalPackages extends AbstractPackageList_1.default {
        populateListItem(listItem, packageData) {
            super.populateListItem(listItem, packageData);
            listItem.innerHTML = ` ${Language.get("wcf.acp.devtools.project.optionalPackage.optionalPackage", {
                packageIdentifier: packageData.packageIdentifier,
            })}`;
        }
    }
    Core.enableLegacyInheritance(OptionalPackages);
    return OptionalPackages;
});
