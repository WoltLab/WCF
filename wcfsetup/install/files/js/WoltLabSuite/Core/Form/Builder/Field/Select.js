/**
 * Data handler for a form builder field in an Ajax form represented by select element that allows multiple selections.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
define(["require", "exports", "tslib", "./Field"], function (require, exports, tslib_1, Field_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    Field_1 = tslib_1.__importDefault(Field_1);
    class Select extends Field_1.default {
        _getData() {
            const values = Array.from(this._field.querySelectorAll(`option`))
                .map((input) => {
                if (input.selected) {
                    return input.value;
                }
                return null;
            })
                .filter((v) => v !== null);
            return {
                [this._fieldId]: values,
            };
        }
    }
    exports.default = Select;
});
