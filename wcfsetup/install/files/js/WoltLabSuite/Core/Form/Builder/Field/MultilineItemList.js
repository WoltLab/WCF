/**
 * Data handler for an multiline item list form builder field in an Ajax form.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Form/Builder/Field/Field", "./Controller/MultilineItemList"], function (require, exports, tslib_1, Field_1, MultilineItemList_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.MultilineItemList = void 0;
    Field_1 = tslib_1.__importDefault(Field_1);
    class MultilineItemList extends Field_1.default {
        _getData() {
            return {
                [this._fieldId]: (0, MultilineItemList_1.getValues)(this._fieldId),
            };
        }
    }
    exports.MultilineItemList = MultilineItemList;
    exports.default = MultilineItemList;
});
