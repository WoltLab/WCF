/**
 * Data handler for a form builder field in an Ajax form that stores its value in an input's value
 * attribute.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field"], function (require, exports, tslib_1, Field_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    class Value extends Field_1.default {
        _getData() {
            return {
                [this._fieldId]: this._field.value,
            };
        }
    }
    return Value;
});
