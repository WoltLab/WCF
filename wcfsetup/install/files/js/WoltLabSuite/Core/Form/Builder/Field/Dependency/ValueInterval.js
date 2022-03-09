/**
 * Form field dependency implementation that requires the value of a field to be in the interval
 * [minimum, maximum].
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Dependency/ValueInterval
 * @see module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since 5.5
 */
define(["require", "exports", "tslib", "./Abstract", "./Manager"], function (require, exports, tslib_1, Abstract_1, DependencyManager) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ValueInterval = void 0;
    Abstract_1 = tslib_1.__importDefault(Abstract_1);
    DependencyManager = tslib_1.__importStar(DependencyManager);
    class ValueInterval extends Abstract_1.default {
        constructor() {
            super(...arguments);
            this._maximum = null;
            this._minimum = null;
        }
        checkDependency() {
            if (this._field) {
                if (DependencyManager.isHiddenByDependencies(this._field)) {
                    return false;
                }
                const value = parseFloat(this._field.value);
                if (isNaN(value)) {
                    return false;
                }
                if (this._minimum !== null && this._minimum > value) {
                    return false;
                }
                else if (this._maximum !== null && this._maximum < value) {
                    return false;
                }
                return true;
            }
            else {
                throw new Error("'ValueInterval' is only supported for individual fields.");
            }
        }
        /**
         * Sets the maximum value of the value interval or unsets the maximum value if `null` is given.
         */
        maximum(maximum) {
            this._maximum = maximum;
            return this;
        }
        /**
         * Sets the minimum value of the value interval or unsets the minimum value if `null` is given.
         */
        minimum(minimum) {
            this._minimum = minimum;
            return this;
        }
    }
    exports.ValueInterval = ValueInterval;
    exports.default = ValueInterval;
});
