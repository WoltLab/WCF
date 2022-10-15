/**
 * Transforms <time> elements to display the elapsed time relative to the current time.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Date/Time/Relative
 */
define(["require", "exports", "tslib", "../../Core", "../../Helper/Selector", "../../Language", "../../Timer/Repeating"], function (require, exports, tslib_1, Core, Selector_1, Language, Repeating_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    Repeating_1 = tslib_1.__importDefault(Repeating_1);
    let _isActive = true;
    let _isPending = false;
    const locale = document.documentElement.lang;
    const lessThanADayAgo = new Intl.RelativeTimeFormat(locale);
    const lessThanAWeekAgo = new Intl.DateTimeFormat(locale, {
        weekday: "long",
        hour: "2-digit",
        minute: "2-digit",
    });
    const moreThanAWeekAgo = new Intl.DateTimeFormat(locale, { dateStyle: "long" });
    const fullDate = new Intl.DateTimeFormat(locale, { dateStyle: "long", timeStyle: "short" });
    function onVisibilityChange() {
        if (document.hidden) {
            _isActive = false;
            _isPending = false;
        }
        else {
            _isActive = true;
            // force immediate refresh
            if (_isPending) {
                refresh();
                _isPending = false;
            }
        }
    }
    function refresh() {
        // activity is suspended while the tab is hidden, but force an
        // immediate refresh once the page is active again
        if (!_isActive) {
            if (!_isPending)
                _isPending = true;
            return;
        }
        document.querySelectorAll("time").forEach((element) => {
            rebuild(element);
        });
    }
    function rebuild(element) {
        if (!element.classList.contains("datetime") || Core.stringToBool(element.dataset.isFutureDate || "")) {
            return;
        }
        const date = new Date(element.dateTime);
        const difference = Math.trunc((Date.now() - date.getTime()) / 1000);
        if (!element.title) {
            element.title = fullDate.format(new Date(element.dateTime));
        }
        // timestamp is less than 60 seconds ago
        if (difference < 60) {
            element.textContent = Language.get("wcf.date.relative.now");
        }
        // timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
        else if (difference < 3600 /* TimePeriod.OneHour */) {
            const minutes = Math.trunc(difference / 60 /* TimePeriod.OneMinute */);
            element.textContent = lessThanADayAgo.format(minutes * -1, "minutes");
        }
        // timestamp is less than 24 hours ago
        else if (difference < 86400 /* TimePeriod.OneDay */) {
            const hours = Math.trunc(difference / 3600 /* TimePeriod.OneHour */);
            element.textContent = lessThanADayAgo.format(hours * -1, "hours");
        }
        // timestamp is less than 6 days ago
        else if (difference < 604800 /* TimePeriod.OneWeek */) {
            element.textContent = lessThanAWeekAgo.format(new Date(element.dateTime));
        }
        // timestamp is between ~700 million years BC and last week
        else {
            element.textContent = moreThanAWeekAgo.format(new Date(element.dateTime));
        }
    }
    /**
     * Transforms <time> elements on init and binds event listeners.
     */
    function setup() {
        new Repeating_1.default(() => refresh(), 60000);
        document.addEventListener("visibilitychange", () => onVisibilityChange());
        (0, Selector_1.wheneverFirstSeen)("time", (element) => rebuild(element));
    }
    exports.setup = setup;
});
