/**
 * Manages the packages entered in a devtools project suggested package form field.
 *
 * @author  Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @see module:WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/AbstractPackageList
 * @since 6.3
 */
define(["require", "exports", "tslib", "./AbstractPackageList"], function (require, exports, tslib_1, AbstractPackageList_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.SuggestedPackages = void 0;
    AbstractPackageList_1 = tslib_1.__importDefault(AbstractPackageList_1);
    class SuggestedPackages extends AbstractPackageList_1.default {
        populateListItem(listItem, packageData) {
            super.populateListItem(listItem, packageData);
            listItem.textContent = ` ${packageData.packageIdentifier}`;
        }
    }
    exports.SuggestedPackages = SuggestedPackages;
});
