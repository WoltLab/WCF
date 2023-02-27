/**
 * Data handler for a radio button form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, Field_1, Util_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    class RadioButton extends Field_1.default {
        _fields;
        constructor(fieldId) {
            super(fieldId);
            this._fields = Array.from(document.querySelectorAll(`input[name="${(0, Util_1.escapeAttributeSelector)(this._fieldId)}"]`));
        }
        _getData() {
            const data = {};
            this._fields.some((input) => {
                if (input.checked) {
                    data[this._fieldId] = input.value;
                    return true;
                }
                return false;
            });
            return data;
        }
        _readField() {
            /* Does nothing. */
        }
    }
    return RadioButton;
});
