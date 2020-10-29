/**
 * Provides helper functions for Number handling.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/NumberUtil
 */

/**
 * Decimal adjustment of a number.
 *
 * @see  https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/round
 */
export function round(value: number, exp: number): number {
  // If the exp is undefined or zero...
  if (typeof exp === "undefined" || +exp === 0) {
    return Math.round(value);
  }
  value = +value;
  exp = +exp;

  // If the value is not a number or the exp is not an integer...
  if (isNaN(value) || !(typeof (exp as any) === "number" && exp % 1 === 0)) {
    return NaN;
  }

  // Shift
  let tmp = value.toString().split("e");
  value = Math.round(+(tmp[0] + "e" + (tmp[1] ? +tmp[1] - exp : -exp)));

  // Shift back
  tmp = value.toString().split("e");
  return +(tmp[0] + "e" + (tmp[1] ? +tmp[1] + exp : exp));
}
