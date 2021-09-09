/**
 * Data handler for an i18n form builder field in an Ajax form that stores its value in an input's
 * value attribute.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/ValueI18n
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field", "../../../Language/Input", "../../../Core"], function (require, exports, tslib_1, Field_1, LanguageInput, Core) {
    "use strict";
    Field_1 = (0, tslib_1.__importDefault)(Field_1);
    LanguageInput = (0, tslib_1.__importStar)(LanguageInput);
    Core = (0, tslib_1.__importStar)(Core);
    class ValueI18n extends Field_1.default {
        _getData() {
            const data = {};
            const values = LanguageInput.getValues(this._fieldId);
            if (values.size > 1) {
                if (!Object.prototype.hasOwnProperty.call(data, this._fieldId + "_i18n")) {
                    data[this._fieldId + "_i18n"] = {};
                }
                values.forEach((value, key) => {
                    data[this._fieldId + "_i18n"][key] = value;
                });
            }
            else {
                data[this._fieldId] = values.get(0);
            }
            return data;
        }
        destroy() {
            LanguageInput.unregister(this._fieldId);
        }
    }
    Core.enableLegacyInheritance(ValueI18n);
    return ValueI18n;
});
