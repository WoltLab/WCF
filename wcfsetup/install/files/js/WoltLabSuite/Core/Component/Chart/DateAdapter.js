/**
 * Date adapter for the time scales of Chart.js, based on `Intl.DateTimeFormat`.
 *
 * Set `adapters.date.utc` on a time scale to treat its timestamps as calendar
 * dates in UTC rather than as points in time in the browser’s time zone.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "chart.js", "WoltLabSuite/Core/Language"], function (require, exports, chart_js_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    const UNIT_LENGTH = {
        millisecond: 1,
        second: 1_000,
        minute: 60_000,
        hour: 3_600_000,
        day: 86_400_000,
        week: 604_800_000,
    };
    const FORMATS = {
        datetime: { dateStyle: "medium", timeStyle: "medium" },
        date: { dateStyle: "medium" },
        millisecond: { hour: "numeric", minute: "2-digit", second: "2-digit", fractionalSecondDigits: 3 },
        second: { hour: "numeric", minute: "2-digit", second: "2-digit" },
        minute: { hour: "numeric", minute: "2-digit" },
        hour: { hour: "numeric", minute: "2-digit" },
        day: { month: "short", day: "numeric" },
        week: { month: "short", day: "numeric" },
        month: { month: "short", year: "numeric" },
        quarter: { month: "short", year: "numeric" },
        year: { year: "numeric" },
    };
    const formatters = new Map();
    function isUtc(adapter) {
        return adapter.options.utc === true;
    }
    function toParts(timestamp, utc) {
        const date = new Date(timestamp);
        if (utc) {
            return [
                date.getUTCFullYear(),
                date.getUTCMonth(),
                date.getUTCDate(),
                date.getUTCHours(),
                date.getUTCMinutes(),
                date.getUTCSeconds(),
                date.getUTCMilliseconds(),
            ];
        }
        return [
            date.getFullYear(),
            date.getMonth(),
            date.getDate(),
            date.getHours(),
            date.getMinutes(),
            date.getSeconds(),
            date.getMilliseconds(),
        ];
    }
    // Out of range values roll over, e.g. day 0 is the last day of the previous month.
    function fromParts(parts, utc) {
        if (utc) {
            return Date.UTC(...parts);
        }
        return new Date(...parts).getTime();
    }
    function getFirstDayOfWeek() {
        return (0, Language_1.getPhrase)("wcf.date.firstDayOfTheWeek") === "1" ? 1 : 0;
    }
    function addMonths(timestamp, amount, utc) {
        const [year, month, day, ...time] = toParts(timestamp, utc);
        const daysInMonth = toParts(fromParts([year, month + amount + 1, 0, 0, 0, 0, 0], utc), utc)[2];
        return fromParts([year, month + amount, Math.min(day, daysInMonth), ...time], utc);
    }
    function diffMonths(a, b, utc) {
        const [yearA, monthA] = toParts(a, utc);
        const [yearB, monthB] = toParts(b, utc);
        let months = (yearA - yearB) * 12 + (monthA - monthB);
        if (months > 0 && addMonths(b, months, utc) > a) {
            months--;
        }
        else if (months < 0 && addMonths(b, months, utc) < a) {
            months++;
        }
        return months;
    }
    function startOf(timestamp, unit, weekday) {
        const utc = isUtc(this);
        const [year, month, day, hour, minute, second] = toParts(timestamp, utc);
        switch (unit) {
            case "millisecond":
                return timestamp;
            case "second":
                return fromParts([year, month, day, hour, minute, second, 0], utc);
            case "minute":
                return fromParts([year, month, day, hour, minute, 0, 0], utc);
            case "hour":
                return fromParts([year, month, day, hour, 0, 0, 0], utc);
            case "day":
                return fromParts([year, month, day, 0, 0, 0, 0], utc);
            case "week":
            case "isoWeek": {
                let firstDay;
                if (unit === "week") {
                    firstDay = getFirstDayOfWeek();
                }
                else {
                    // Chart.js passes the ISO weekday (1 = Monday, 7 = Sunday) or `true` for Monday.
                    firstDay = typeof weekday === "number" ? weekday % 7 : 1;
                }
                const dayOfWeek = new Date(fromParts([year, month, day, 0, 0, 0, 0], utc))[utc ? "getUTCDay" : "getDay"]();
                return fromParts([year, month, day - ((dayOfWeek - firstDay + 7) % 7), 0, 0, 0, 0], utc);
            }
            case "month":
                return fromParts([year, month, 1, 0, 0, 0, 0], utc);
            case "quarter":
                return fromParts([year, month - (month % 3), 1, 0, 0, 0, 0], utc);
            case "year":
                return fromParts([year, 0, 1, 0, 0, 0, 0], utc);
        }
    }
    function add(timestamp, amount, unit) {
        const utc = isUtc(this);
        switch (unit) {
            case "millisecond":
            case "second":
            case "minute":
            case "hour":
                return timestamp + amount * UNIT_LENGTH[unit];
            case "day":
            case "week": {
                const [year, month, day, ...time] = toParts(timestamp, utc);
                return fromParts([year, month, day + amount * (unit === "week" ? 7 : 1), ...time], utc);
            }
            case "month":
                return addMonths(timestamp, amount, utc);
            case "quarter":
                return addMonths(timestamp, amount * 3, utc);
            case "year":
                return addMonths(timestamp, amount * 12, utc);
        }
    }
    function setup() {
        chart_js_1._adapters._date.override({
            formats() {
                return {
                    datetime: "datetime",
                    millisecond: "millisecond",
                    second: "second",
                    minute: "minute",
                    hour: "hour",
                    day: "day",
                    week: "week",
                    month: "month",
                    quarter: "quarter",
                    year: "year",
                };
            },
            parse(value) {
                if (typeof value === "number") {
                    return Number.isFinite(value) ? value : null;
                }
                if (value instanceof Date) {
                    const timestamp = value.getTime();
                    return Number.isNaN(timestamp) ? null : timestamp;
                }
                if (typeof value === "string") {
                    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
                    if (match) {
                        return fromParts([parseInt(match[1], 10), parseInt(match[2], 10) - 1, parseInt(match[3], 10), 0, 0, 0, 0], isUtc(this));
                    }
                    const timestamp = Date.parse(value);
                    return Number.isNaN(timestamp) ? null : timestamp;
                }
                return null;
            },
            format(timestamp, format) {
                const utc = isUtc(this);
                const key = `${utc ? "utc" : "local"}:${format}`;
                let formatter = formatters.get(key);
                if (formatter === undefined) {
                    formatter = new Intl.DateTimeFormat(document.documentElement.lang, {
                        ...(FORMATS[format] ?? FORMATS.datetime),
                        timeZone: utc ? "UTC" : undefined,
                    });
                    formatters.set(key, formatter);
                }
                return formatter.format(timestamp);
            },
            add,
            diff(a, b, unit) {
                const utc = isUtc(this);
                switch (unit) {
                    case "millisecond":
                    case "second":
                    case "minute":
                    case "hour":
                        return Math.trunc((a - b) / UNIT_LENGTH[unit]);
                    case "day":
                    case "week":
                        // Compares the wall-clock dates, a day across a DST change is not 24 hours long.
                        return Math.trunc((Date.UTC(...toParts(a, utc)) - Date.UTC(...toParts(b, utc))) / UNIT_LENGTH[unit]);
                    case "month":
                        return diffMonths(a, b, utc);
                    case "quarter":
                        return Math.trunc(diffMonths(a, b, utc) / 3);
                    case "year":
                        return Math.trunc(diffMonths(a, b, utc) / 12);
                }
            },
            startOf,
            endOf(timestamp, unit) {
                return add.call(this, startOf.call(this, timestamp, unit), 1, unit) - 1;
            },
        });
    }
});
