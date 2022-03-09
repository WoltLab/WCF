/**
 * Form field dependency implementation that requires the value of a field to be empty.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Dependency/Empty
 * @see module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Abstract", "../../../../Core"], function (require, exports, tslib_1, Abstract_1, Core) {
    "use strict";
    Abstract_1 = tslib_1.__importDefault(Abstract_1);
    Core = tslib_1.__importStar(Core);
    class Empty extends Abstract_1.default {
        checkDependency() {
            if (this._field !== null) {
                switch (this._field.tagName) {
                    case "INPUT": {
                        const field = this._field;
                        switch (field.type) {
                            case "checkbox":
                                return !field.checked;
                            case "radio":
                                if (this._noField && this._noField.checked) {
                                    return true;
                                }
                                return !field.checked;
                            default:
                                return field.value.trim().length === 0;
                        }
                    }
                    case "SELECT": {
                        const field = this._field;
                        if (field.multiple) {
                            return this._field.querySelectorAll("option:checked").length === 0;
                        }
                        return field.value == "0" || field.value.length === 0;
                    }
                    case "TEXTAREA": {
                        return this._field.value.trim().length === 0;
                    }
                }
            }
            // Check that none of the fields are checked.
            return this._fields.every((field) => !field.checked);
        }
    }
    Core.enableLegacyInheritance(Empty);
    return Empty;
});
