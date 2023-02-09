/**
 * Provides helper functions for String handling.
 *
 * @author  Tim Duesterhus, Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * Adds thousands separators to a given number.
 *
 * @deprecated 6.0 Use `formatNumeric()` instead.
 */
export function addThousandsSeparator(number: number): string {
  return number.toLocaleString(document.documentElement.lang);
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
  const maximumFractionDigits = decimalPlaces ? -decimalPlaces : 2;

  return number
    .toLocaleString(document.documentElement.lang, {
      maximumFractionDigits,
    })
    .replace("-", "\u2212");
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
    }

    unitSuffix = "M";
  } else if (number >= 1000) {
    number /= 1000;

    if (number > 10) {
      number = Math.floor(number);
    }

    unitSuffix = "k";
  }

  return formatNumeric(number, -1) + unitSuffix;
}

/**
 * Converts a lower-case string containing dashed to camelCase for use
 * with the `dataset` property.
 */
export function toCamelCase(value: string): string {
  if (!value.includes("-")) {
    return value;
  }

  return value
    .split("-")
    .map((part, index) => {
      if (index > 0) {
        part = ucfirst(part);
      }

      return part;
    })
    .join("");
}
