/**
 * Data handler for a label form field in an Ajax form.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "./Field"], function (require, exports, tslib_1, Field_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    class Label extends Field_1.default {
        _getData() {
            const labelPicker = this._field;
            return {
                [this._fieldId]: labelPicker.multiple ? labelPicker.values : labelPicker.value,
            };
        }
    }
    return Label;
});
