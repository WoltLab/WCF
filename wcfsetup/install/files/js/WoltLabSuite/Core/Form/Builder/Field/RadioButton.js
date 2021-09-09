/**
 * Data handler for a radio button form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/RadioButton
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field", "../../../Core"], function (require, exports, tslib_1, Field_1, Core) {
    "use strict";
    Field_1 = (0, tslib_1.__importDefault)(Field_1);
    Core = (0, tslib_1.__importStar)(Core);
    class RadioButton extends Field_1.default {
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
            this._fields = Array.from(document.querySelectorAll("input[name=" + this._fieldId + "]"));
        }
    }
    Core.enableLegacyInheritance(RadioButton);
    return RadioButton;
});
