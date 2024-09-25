/**
 * Provides helper functions for String handling.
 *
 * @author  Tim Duesterhus, Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.addThousandsSeparator = addThousandsSeparator;
    exports.escapeHTML = escapeHTML;
    exports.escapeRegExp = escapeRegExp;
    exports.formatNumeric = formatNumeric;
    exports.lcfirst = lcfirst;
    exports.ucfirst = ucfirst;
    exports.unescapeHTML = unescapeHTML;
    exports.shortUnit = shortUnit;
    exports.toCamelCase = toCamelCase;
    /**
     * Adds thousands separators to a given number.
     *
     * @deprecated 6.0 Use `formatNumeric()` instead.
     */
    function addThousandsSeparator(number) {
        return number.toLocaleString(document.documentElement.lang);
    }
    /**
     * Escapes special HTML-characters within a string
     */
    function escapeHTML(string) {
        return String(string)
            .replace(/&/g, "&amp;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
    }
    /**
     * Escapes a String to work with RegExp.
     *
     * @see    https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/regexp.js#L25
     */
    function escapeRegExp(string) {
        return String(string).replace(/([.*+?^=!:${}()|[\]/\\])/g, "\\$1");
    }
    /**
     * Rounds number to given count of floating point digits, localizes decimal-point and inserts thousands separators.
     */
    function formatNumeric(number, decimalPlaces) {
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
    function lcfirst(string) {
        return String(string).substring(0, 1).toLowerCase() + string.substring(1);
    }
    /**
     * Makes a string's first character uppercase.
     */
    function ucfirst(string) {
        return String(string).substring(0, 1).toUpperCase() + string.substring(1);
    }
    /**
     * Unescapes special HTML-characters within a string.
     */
    function unescapeHTML(string) {
        return String(string)
            .replace(/&quot;/g, '"')
            .replace(/&#039;/g, "'")
            .replace(/&lt;/g, "<")
            .replace(/&gt;/g, ">")
            .replace(/&amp;/g, "&");
    }
    /**
     * Shortens numbers larger than 1000 by using unit suffixes.
     */
    function shortUnit(number) {
        let unitSuffix = "";
        if (number >= 1000000) {
            number /= 1000000;
            if (number > 10) {
                number = Math.floor(number);
            }
            unitSuffix = "M";
        }
        else if (number >= 1000) {
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
    function toCamelCase(value) {
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
});
