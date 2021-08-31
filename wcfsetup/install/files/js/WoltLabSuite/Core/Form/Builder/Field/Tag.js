/**
 * Data handler for a tag form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Tag
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field", "../../../Ui/ItemList", "../../../Core"], function (require, exports, tslib_1, Field_1, UiItemList, Core) {
    "use strict";
    Field_1 = (0, tslib_1.__importDefault)(Field_1);
    UiItemList = (0, tslib_1.__importStar)(UiItemList);
    Core = (0, tslib_1.__importStar)(Core);
    class Tag extends Field_1.default {
        _getData() {
            const values = UiItemList.getValues(this._fieldId).map((item) => item.value);
            return {
                [this._fieldId]: values,
            };
        }
    }
    Core.enableLegacyInheritance(Tag);
    return Tag;
});
