/**
 * Data handler for a form builder field in an Ajax form represented by checkboxes.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Checkboxes
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Field", "../../../Core"], function (require, exports, tslib_1, Field_1, Core) {
    "use strict";
    Field_1 = (0, tslib_1.__importDefault)(Field_1);
    Core = (0, tslib_1.__importStar)(Core);
    class Checkboxes extends Field_1.default {
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
    Core.enableLegacyInheritance(Checkboxes);
    return Checkboxes;
});
