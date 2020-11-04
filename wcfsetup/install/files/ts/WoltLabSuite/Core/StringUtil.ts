/**
 * Provides helper functions for String handling.
 *
 * @author  Tim Duesterhus, Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  StringUtil (alias)
 * @module  WoltLabSuite/Core/StringUtil
 */

import * as NumberUtil from "./NumberUtil";

let _decimalPoint = ".";
let _thousandsSeparator = ",";

/**
 * Adds thousands separators to a given number.
 *
 * @see    http://stackoverflow.com/a/6502556/782822
 */
export function addThousandsSeparator(number: number): string {
  return String(number).replace(/(^-?\d{1,3}|\d{3})(?=(?:\d{3})+(?:$|\.))/g, "$1" + _thousandsSeparator);
}

/**
 * Escapes special HTML-characters within a string
 */
export function escapeHTML(string: string): string {
  return String(string).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

/**
 * Escapes a String to work with RegExp.
 *
 * @see    https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/regexp.js#L25
 */
export function escapeRegExp(string: string): string {
  return String(string).replace(/([.*+?^=!:${}()|[\]/\\])/g, "\\$1");
}

/**
 * Rounds number to given count of floating point digits, localizes decimal-point and inserts thousands separators.
 */
export function formatNumeric(number: number, decimalPlaces?: number): string {
  let tmp = NumberUtil.round(number, decimalPlaces || -2).toString();
  const numberParts = tmp.split(".");

  tmp = addThousandsSeparator(+numberParts[0]);
  if (numberParts.length > 1) {
    tmp += _decimalPoint + numberParts[1];
  }

  tmp = tmp.replace("-", "\u2212");

  return tmp;
}

/**
 * Makes a string's first character lowercase.
 */
export function lcfirst(string: string): string {
  return String(string).substring(0, 1).toLowerCase() + string.substring(1);
}

/**
 * Makes a string's first character uppercase.
 */
export function ucfirst(string: string): string {
  return String(string).substring(0, 1).toUpperCase() + string.substring(1);
}

/**
 * Unescapes special HTML-characters within a string.
 */
export function unescapeHTML(string: string): string {
  return String(string)
    .replace(/&amp;/g, "&")
    .replace(/&quot;/g, '"')
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">");
}

/**
 * Shortens numbers larger than 1000 by using unit suffixes.
 */
export function shortUnit(number: number): string {
  let unitSuffix = "";

  if (number >= 1000000) {
    number /= 1000000;

    if (number > 10) {
      number = Math.floor(number);
    } else {
      number = NumberUtil.round(number, -1);
    }

    unitSuffix = "M";
  } else if (number >= 1000) {
    number /= 1000;

    if (number > 10) {
      number = Math.floor(number);
    } else {
      number = NumberUtil.round(number, -1);
    }

    unitSuffix = "k";
  }

  return formatNumeric(number) + unitSuffix;
}

interface I18nValues {
  decimalPoint: string;
  thousandsSeparator: string;
}

export function setupI18n(values: I18nValues): void {
  _decimalPoint = values.decimalPoint;
  _thousandsSeparator = values.thousandsSeparator;
}
