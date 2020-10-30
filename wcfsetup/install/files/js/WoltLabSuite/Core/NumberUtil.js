/**
 * Provides helper functions for Number handling.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/NumberUtil
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.round = void 0;
    /**
     * Decimal adjustment of a number.
     *
     * @see  https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/round
     */
    function round(value, exp) {
        // If the exp is undefined or zero...
        if (typeof exp === "undefined" || +exp === 0) {
            return Math.round(value);
        }
        value = +value;
        exp = +exp;
        // If the value is not a number or the exp is not an integer...
        if (isNaN(value) || !(typeof exp === "number" && exp % 1 === 0)) {
            return NaN;
        }
        // Shift
        let tmp = value.toString().split("e");
        let exponent = tmp[1] ? +tmp[1] - exp : -exp;
        value = Math.round(+`${tmp[0]}e${exponent}`);
        // Shift back
        tmp = value.toString().split("e");
        exponent = tmp[1] ? +tmp[1] + exp : exp;
        return +`${tmp[0]}e${exponent}`;
    }
    exports.round = round;
});
