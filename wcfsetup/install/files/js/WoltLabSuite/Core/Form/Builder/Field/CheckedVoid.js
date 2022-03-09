/**
 * Data handler for a form builder field in an Ajax form that stores its value via a checkbox being
 * checked or not.
 *
 * This differs from `Checked` by not sending any value if the checkbox is not checked.
 *
 * @author  Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/CheckedVoid
 * @since 5.4
 */
define(["require", "exports", "tslib", "./Field"], function (require, exports, tslib_1, Field_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.CheckedVoid = void 0;
    Field_1 = tslib_1.__importDefault(Field_1);
    class CheckedVoid extends Field_1.default {
        _getData() {
            if (this._field.checked) {
                return {
                    [this._fieldId]: 1,
                };
            }
            else {
                return {};
            }
        }
    }
    exports.CheckedVoid = CheckedVoid;
    exports.default = CheckedVoid;
});
