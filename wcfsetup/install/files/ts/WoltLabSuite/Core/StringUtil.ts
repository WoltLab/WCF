/**
 * Provides helper functions for String handling.
 *
 * @author  Tim Duesterhus, Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  StringUtil (alias)
 * @module  WoltLabSuite/Core/StringUtil
 */

import * as Language from "./Language";
import * as NumberUtil from "./NumberUtil";

/**
 * Adds thousands separators to a given number.
 *
 * @see    http://stackoverflow.com/a/6502556/782822
 */
export function addThousandsSeparator(number: number): string {
  // Fetch Language, as it cannot be provided because of a circular dependency
  if (Language === undefined) {
    // @ts-expect-error: This is required due to a circular dependency.
    Language = require("./Language");
  }

  return String(number).replace(
    /(^-?\d{1,3}|\d{3})(?=(?:\d{3})+(?:$|\.))/g,
    "$1" + Language.get("wcf.global.thousandsSeparator")
  );
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
  return String(string).replace(/([.*+?^=!:${}()|[\]\/\\])/g, "\\$1");
}

/**
 * Rounds number to given count of floating point digits, localizes decimal-point and inserts thousands separators.
 */
export function formatNumeric(number: number, decimalPlaces?: number): string {
  // Fetch Language, as it cannot be provided because of a circular dependency
  if (Language === undefined) {
    // @ts-expect-error: This is required due to a circular dependency.
    Language = require("./Language");
  }

  let tmp = NumberUtil.round(number, decimalPlaces || -2).toString();
  const numberParts = tmp.split(".");

  tmp = addThousandsSeparator(+numberParts[0]);
  if (numberParts.length > 1) tmp += Language.get("wcf.global.decimalPoint") + numberParts[1];

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
