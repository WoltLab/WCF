/**
 * Form field dependency implementation that requires a field to have a certain value.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Dependency/Value
 * @see module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since 5.2
 */
define(["require", "exports", "tslib", "./Abstract", "./Manager", "../../../../Core"], function (require, exports, tslib_1, Abstract_1, DependencyManager, Core) {
    "use strict";
    Abstract_1 = tslib_1.__importDefault(Abstract_1);
    DependencyManager = tslib_1.__importStar(DependencyManager);
    Core = tslib_1.__importStar(Core);
    class Value extends Abstract_1.default {
        constructor() {
            super(...arguments);
            this._isNegated = false;
        }
        checkDependency() {
            if (!this._values) {
                throw new Error("Values have not been set.");
            }
            let values = [];
            if (this._field) {
                if (DependencyManager.isHiddenByDependencies(this._field)) {
                    return false;
                }
                values.push(this._field.value);
            }
            else {
                let hasCheckedField = true;
                this._fields.forEach((field) => {
                    if (field.checked) {
                        if (DependencyManager.isHiddenByDependencies(field)) {
                            hasCheckedField = false;
                            return false;
                        }
                        values.push(field.value);
                    }
                });
                if (!hasCheckedField) {
                    return false;
                }
            }
            let foundMatch = false;
            this._values.forEach((value) => {
                values.forEach((selectedValue) => {
                    if (value == selectedValue) {
                        foundMatch = true;
                    }
                });
            });
            if (foundMatch) {
                return !this._isNegated;
            }
            return this._isNegated;
        }
        /**
         * Sets if the field value may not have any of the set values.
         */
        negate(negate) {
            this._isNegated = negate;
            return this;
        }
        /**
         * Sets the possible values the field may have for the dependency to be met.
         */
        values(values) {
            this._values = values;
            return this;
        }
    }
    Core.enableLegacyInheritance(Value);
    return Value;
});
