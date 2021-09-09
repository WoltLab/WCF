/**
 * Data handler for a form builder field in an Ajax form that stores its value in an input's value
 * attribute.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Value
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field", "../../../Core"], function (require, exports, tslib_1, Field_1, Core) {
    "use strict";
    Field_1 = (0, tslib_1.__importDefault)(Field_1);
    Core = (0, tslib_1.__importStar)(Core);
    class Value extends Field_1.default {
        _getData() {
            return {
                [this._fieldId]: this._field.value,
            };
        }
    }
    Core.enableLegacyInheritance(Value);
    return Value;
});
