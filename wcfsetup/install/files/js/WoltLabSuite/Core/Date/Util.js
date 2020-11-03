/**
 * Provides utility functions for date operations.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  DateUtil (alias)
 * @module  WoltLabSuite/Core/Date/Util
 */
define(["require", "exports", "tslib", "../Language"], function (require, exports, tslib_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getTimezoneDate = exports.getTimeElement = exports.gmdate = exports.format = exports.formatDateTime = exports.formatTime = exports.formatDate = void 0;
    Language = tslib_1.__importStar(Language);
    /**
     * Returns the formatted date.
     */
    function formatDate(date) {
        return format(date, Language.get("wcf.date.dateFormat"));
    }
    exports.formatDate = formatDate;
    /**
     * Returns the formatted time.
     */
    function formatTime(date) {
        return format(date, Language.get("wcf.date.timeFormat"));
    }
    exports.formatTime = formatTime;
    /**
     * Returns the formatted date time.
     */
    function formatDateTime(date) {
        const dateTimeFormat = Language.get("wcf.date.dateTimeFormat");
        const dateFormat = Language.get("wcf.date.dateFormat");
        const timeFormat = Language.get("wcf.date.timeFormat");
        return format(date, dateTimeFormat.replace(/%date%/, dateFormat).replace(/%time%/, timeFormat));
    }
    exports.formatDateTime = formatDateTime;
    /**
     * Formats a date using PHP's `date()` modifiers.
     */
    function format(date, format) {
        // ISO 8601 date, best recognition by PHP's strtotime()
        if (format === "c") {
            format = "Y-m-dTH:i:sP";
        }
        let out = "";
        for (let i = 0, length = format.length; i < length; i++) {
            let char;
            switch (format[i]) {
                // seconds
                case "s":
                    // `00` through `59`
                    char = date.getSeconds().toString().padStart(2, "0");
                    break;
                // minutes
                case "i":
                    // `00` through `59`
                    char = date.getMinutes().toString().padStart(2, "0");
                    break;
                // hours
                case "a":
                    // `am` or `pm`
                    char = date.getHours() > 11 ? "pm" : "am";
                    break;
                case "g": {
                    // `1` through `12`
                    const hours = date.getHours();
                    if (hours === 0) {
                        char = "12";
                    }
                    else if (hours > 12) {
                        char = (hours - 12).toString();
                    }
                    else {
                        char = hours.toString();
                    }
                    break;
                }
                case "h": {
                    // `01` through `12`
                    const hours = date.getHours();
                    if (hours === 0) {
                        char = "12";
                    }
                    else if (hours > 12) {
                        char = (hours - 12).toString();
                    }
                    else {
                        char = hours.toString();
                    }
                    char = char.padStart(2, "0");
                    break;
                }
                case "A":
                    // `AM` or `PM`
                    char = date.getHours() > 11 ? "PM" : "AM";
                    break;
                case "G":
                    // `0` through `23`
                    char = date.getHours().toString();
                    break;
                case "H":
                    // `00` through `23`
                    char = date.getHours().toString().padStart(2, "0");
                    break;
                // day
                case "d":
                    // `01` through `31`
                    char = date.getDate().toString().padStart(2, "0");
                    break;
                case "j":
                    // `1` through `31`
                    char = date.getDate().toString();
                    break;
                case "l":
                    // `Monday` through `Sunday` (localized)
                    char = Language.get("__days")[date.getDay()];
                    break;
                case "D":
                    // `Mon` through `Sun` (localized)
                    char = Language.get("__daysShort")[date.getDay()];
                    break;
                case "S":
                    // ignore english ordinal suffix
                    char = "";
                    break;
                // month
                case "m":
                    // `01` through `12`
                    char = (date.getMonth() + 1).toString().padStart(2, "0");
                    break;
                case "n":
                    // `1` through `12`
                    char = (date.getMonth() + 1).toString();
                    break;
                case "F":
                    // `January` through `December` (localized)
                    char = Language.get("__months")[date.getMonth()];
                    break;
                case "M":
                    // `Jan` through `Dec` (localized)
                    char = Language.get("__monthsShort")[date.getMonth()];
                    break;
                // year
                case "y":
                    // `00` through `99`
                    char = date.getFullYear().toString().slice(-2);
                    break;
                case "Y":
                    // Examples: `1988` or `2015`
                    char = date.getFullYear().toString();
                    break;
                // timezone
                case "P": {
                    let offset = date.getTimezoneOffset();
                    char = offset > 0 ? "-" : "+";
                    offset = Math.abs(offset);
                    char += (~~(offset / 60)).toString().padStart(2, "0");
                    char += ":";
                    char += (offset % 60).toString().padStart(2, "0");
                    break;
                }
                // specials
                case "r":
                    char = date.toString();
                    break;
                case "U":
                    char = Math.round(date.getTime() / 1000).toString();
                    break;
                // escape sequence
                case "\\":
                    char = "";
                    if (i + 1 < length) {
                        char = format[++i];
                    }
                    break;
                default:
                    char = format[i];
                    break;
            }
            out += char;
        }
        return out;
    }
    exports.format = format;
    /**
     * Returns UTC timestamp, if date is not given, current time will be used.
     */
    function gmdate(date) {
        if (!(date instanceof Date)) {
            date = new Date();
        }
        return Math.round(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDay(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds()) / 1000);
    }
    exports.gmdate = gmdate;
    /**
     * Returns a `time` element based on the given date just like a `time`
     * element created by `wcf\system\template\plugin\TimeModifierTemplatePlugin`.
     *
     * Note: The actual content of the element is empty and is expected
     * to be automatically updated by `WoltLabSuite/Core/Date/Time/Relative`
     * (for dates not in the future) after the DOM change listener has been triggered.
     */
    function getTimeElement(date) {
        const time = document.createElement("time");
        time.className = "datetime";
        const formattedDate = formatDate(date);
        const formattedTime = formatTime(date);
        time.setAttribute("datetime", format(date, "c"));
        time.dataset.timestamp = ((date.getTime() - date.getMilliseconds()) / 1000).toString();
        time.dataset.date = formattedDate;
        time.dataset.time = formattedTime;
        time.dataset.offset = (date.getTimezoneOffset() * 60).toString(); // PHP returns minutes, JavaScript returns seconds
        if (date.getTime() > Date.now()) {
            time.dataset.isFutureDate = "true";
            time.textContent = Language.get("wcf.date.dateTimeFormat")
                .replace("%time%", formattedTime)
                .replace("%date%", formattedDate);
        }
        return time;
    }
    exports.getTimeElement = getTimeElement;
    /**
     * Returns a Date object with precise offset (including timezone and local timezone).
     */
    function getTimezoneDate(timestamp, offset) {
        const date = new Date(timestamp);
        const localOffset = date.getTimezoneOffset() * 60000;
        return new Date(timestamp + localOffset + offset);
    }
    exports.getTimezoneDate = getTimezoneDate;
});
