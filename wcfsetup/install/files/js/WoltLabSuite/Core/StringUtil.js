/**
 * Provides helper functions for String handling.
 *
 * @author  Tim Duesterhus, Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  StringUtil (alias)
 * @module  WoltLabSuite/Core/StringUtil
 */
define(["require", "exports", "tslib", "./Language", "./NumberUtil"], function (require, exports, tslib_1, Language, NumberUtil) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.shortUnit = exports.unescapeHTML = exports.ucfirst = exports.lcfirst = exports.formatNumeric = exports.escapeRegExp = exports.escapeHTML = exports.addThousandsSeparator = void 0;
    Language = tslib_1.__importStar(Language);
    NumberUtil = tslib_1.__importStar(NumberUtil);
    /**
     * Adds thousands separators to a given number.
     *
     * @see    http://stackoverflow.com/a/6502556/782822
     */
    function addThousandsSeparator(number) {
        // Fetch Language, as it cannot be provided because of a circular dependency
        if (Language === undefined) {
            //@ts-ignore
            Language = require("./Language");
        }
        return String(number).replace(/(^-?\d{1,3}|\d{3})(?=(?:\d{3})+(?:$|\.))/g, "$1" + Language.get("wcf.global.thousandsSeparator"));
    }
    exports.addThousandsSeparator = addThousandsSeparator;
    /**
     * Escapes special HTML-characters within a string
     */
    function escapeHTML(string) {
        return String(string).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }
    exports.escapeHTML = escapeHTML;
    /**
     * Escapes a String to work with RegExp.
     *
     * @see    https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/regexp.js#L25
     */
    function escapeRegExp(string) {
        return String(string).replace(/([.*+?^=!:${}()|[\]\/\\])/g, "\\$1");
    }
    exports.escapeRegExp = escapeRegExp;
    /**
     * Rounds number to given count of floating point digits, localizes decimal-point and inserts thousands separators.
     */
    function formatNumeric(number, decimalPlaces) {
        // Fetch Language, as it cannot be provided because of a circular dependency
        if (Language === undefined) {
            //@ts-ignore
            Language = require("./Language");
        }
        let tmp = NumberUtil.round(number, decimalPlaces || -2).toString();
        const numberParts = tmp.split(".");
        tmp = addThousandsSeparator(+numberParts[0]);
        if (numberParts.length > 1)
            tmp += Language.get("wcf.global.decimalPoint") + numberParts[1];
        tmp = tmp.replace("-", "\u2212");
        return tmp;
    }
    exports.formatNumeric = formatNumeric;
    /**
     * Makes a string's first character lowercase.
     */
    function lcfirst(string) {
        return String(string).substring(0, 1).toLowerCase() + string.substring(1);
    }
    exports.lcfirst = lcfirst;
    /**
     * Makes a string's first character uppercase.
     */
    function ucfirst(string) {
        return String(string).substring(0, 1).toUpperCase() + string.substring(1);
    }
    exports.ucfirst = ucfirst;
    /**
     * Unescapes special HTML-characters within a string.
     */
    function unescapeHTML(string) {
        return String(string)
            .replace(/&amp;/g, "&")
            .replace(/&quot;/g, '"')
            .replace(/&lt;/g, "<")
            .replace(/&gt;/g, ">");
    }
    exports.unescapeHTML = unescapeHTML;
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
            else {
                number = NumberUtil.round(number, -1);
            }
            unitSuffix = "M";
        }
        else if (number >= 1000) {
            number /= 1000;
            if (number > 10) {
                number = Math.floor(number);
            }
            else {
                number = NumberUtil.round(number, -1);
            }
            unitSuffix = "k";
        }
        return formatNumeric(number) + unitSuffix;
    }
    exports.shortUnit = shortUnit;
});
