/**
 * Data handler for an i18n form builder field in an Ajax form that stores its value in an input's
 * value attribute.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field", "../../../Language/Input"], function (require, exports, tslib_1, Field_1, LanguageInput) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    LanguageInput = tslib_1.__importStar(LanguageInput);
    class ValueI18n extends Field_1.default {
        _getData() {
            const data = {};
            const values = LanguageInput.getValues(this._fieldId);
            if (values.size === 1 && values.has(0)) {
                data[this._fieldId] = values.get(0);
            }
            else {
                // Fields that require per-language values can hold the value of a
                // single language, which must not be sent as a monolingual value.
                data[this._fieldId + "_i18n"] = {};
                values.forEach((value, key) => {
                    data[this._fieldId + "_i18n"][key] = value;
                });
            }
            return data;
        }
        destroy() {
            LanguageInput.unregister(this._fieldId);
        }
    }
    return ValueI18n;
});
