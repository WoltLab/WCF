"use strict";
{
    // The client will always be lagging behind the server by at least a
    // fraction of a second. There are severe cases where the client will
    // be behind or ahead a couple minutes, which would severely impact
    // the accuracy of the displayed relative times.
    //
    // Adding the drift to all calculations means that the displayed value
    // will always be correct _relative_ to the client’s clock.
    const drift = Date.now() - window.TIME_NOW * 1000;
    const locale = document.documentElement.lang;
    let todayDayStart;
    let yesterdayDayStart;
    const updateTodayAndYesterday = () => {
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        if (todayDayStart !== today.getTime()) {
            todayDayStart = today.getTime();
            const yesterday = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
            yesterdayDayStart = yesterday.getTime();
        }
    };
    updateTodayAndYesterday();
    const DateFormatter = {
        Date: new Intl.DateTimeFormat(locale, { dateStyle: "long" }),
        DateAndTime: new Intl.DateTimeFormat(locale, { dateStyle: "long", timeStyle: "short" }),
        DayOfWeekAndTime: new Intl.DateTimeFormat(locale, {
            weekday: "long",
            hour: "2-digit",
            minute: "2-digit",
        }),
        Minutes: new Intl.RelativeTimeFormat(locale),
        TodayOrYesterday: new Intl.RelativeTimeFormat(locale, { numeric: "auto" }),
    };
    class WoltlabCoreTimeElement extends HTMLElement {
        #date;
        #timeElement;
        get date() {
            if (this.#date === undefined) {
                const value = this.getAttribute("date");
                if (!value) {
                    throw new Error("The 'date' attribute is missing.");
                }
                this.#date = new Date(value);
            }
            return this.#date;
        }
        set date(date) {
            this.setAttribute("date", date.toISOString());
            this.refresh(true);
        }
        connectedCallback() {
            this.refresh(true);
        }
        refresh(updateTitle) {
            const date = this.date;
            const difference = Math.trunc((Date.now() - date.getTime() - drift) / 1000);
            if (this.#timeElement === undefined) {
                this.#timeElement = document.createElement("time");
                this.#timeElement.dateTime = date.toISOString();
                const shadow = this.attachShadow({ mode: "open" });
                shadow.append(this.#timeElement);
            }
            if (updateTitle) {
                this.#timeElement.title = DateFormatter.DateAndTime.format(date);
            }
            let value;
            if (difference < 60 /* TimePeriod.OneMinute */) {
                value = "TODO: a moment ago"; // Language.get("wcf.date.relative.now");
            }
            else if (difference < 3600 /* TimePeriod.OneHour */) {
                const minutes = Math.trunc(difference / 60 /* TimePeriod.OneMinute */);
                value = DateFormatter.Minutes.format(minutes * -1, "minute");
            }
            else if (date.getTime() > todayDayStart) {
                value = this.#formatTodayOrYesterday(date, 0 /* TodayOrYesterday.Today */);
            }
            else if (date.getTime() > yesterdayDayStart) {
                value = this.#formatTodayOrYesterday(date, -1 /* TodayOrYesterday.Yesterday */);
            }
            else if (difference < 604800 /* TimePeriod.OneWeek */) {
                value = DateFormatter.DayOfWeekAndTime.format(date);
            }
            else {
                value = DateFormatter.Date.format(date);
            }
            value = value.charAt(0).toUpperCase() + value.slice(1);
            this.#timeElement.textContent = value;
        }
        #formatTodayOrYesterday(date, dayOffset) {
            let value = DateFormatter.TodayOrYesterday.format(dayOffset, "day");
            const dateParts = DateFormatter.DayOfWeekAndTime.formatToParts(date);
            if (dateParts[0].type === "weekday") {
                const datePartsWithoutDayOfWeek = dateParts.slice(1).map((part) => part.value);
                datePartsWithoutDayOfWeek.unshift(value);
                value = datePartsWithoutDayOfWeek.join("");
            }
            return value;
        }
    }
    window.customElements.define("woltlab-core-time", WoltlabCoreTimeElement);
    const refreshAllTimeElements = function () {
        document.querySelectorAll("woltlab-core-time").forEach((element) => element.refresh(false));
    };
    let timer = undefined;
    const startTimer = () => {
        timer = window.setInterval(() => {
            updateTodayAndYesterday();
            refreshAllTimeElements();
        }, 60000);
    };
    document.addEventListener("DOMContentLoaded", () => startTimer(), { once: true });
    document.addEventListener("visibilitychange", () => {
        if (document.hidden) {
            // Disable refreshes while the tab is in the background.
            window.clearInterval(timer);
        }
        else {
            // Immediately update the date values when the page is visible again.
            refreshAllTimeElements();
            startTimer();
        }
    });
}
