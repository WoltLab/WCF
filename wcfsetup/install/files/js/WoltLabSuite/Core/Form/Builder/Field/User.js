/**
 * Data handler for a user form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/User
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field", "../../../Core", "../../../Ui/ItemList"], function (require, exports, tslib_1, Field_1, Core, UiItemList) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    Core = tslib_1.__importStar(Core);
    UiItemList = tslib_1.__importStar(UiItemList);
    class User extends Field_1.default {
        _getData() {
            const usernames = UiItemList.getValues(this._fieldId).map((item) => item.value);
            return {
                [this._fieldId]: usernames.join(","),
            };
        }
    }
    Core.enableLegacyInheritance(User);
    return User;
});
