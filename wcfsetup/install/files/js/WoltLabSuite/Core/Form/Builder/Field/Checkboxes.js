/**
 * Data handler for a form builder field in an Ajax form represented by checkboxes.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field"], function (require, exports, tslib_1, Field_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    class Checkboxes extends Field_1.default {
        _fields;
        _getData() {
            const values = this._fields
                .map((input) => {
                if (input.checked) {
                    return input.value;
                }
                return null;
            })
                .filter((v) => v !== null);
            return {
                [this._fieldId]: values,
            };
        }
        _readField() {
            this._fields = Array.from(document.querySelectorAll(`input[name="${this._fieldId}[]"]`));
        }
    }
    return Checkboxes;
});
