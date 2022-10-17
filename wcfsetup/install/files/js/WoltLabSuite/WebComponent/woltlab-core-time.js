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
    const lessThanADayAgo = new Intl.RelativeTimeFormat(locale);
    const lessThanAWeekAgo = new Intl.DateTimeFormat(locale, {
        weekday: "long",
        hour: "2-digit",
        minute: "2-digit",
    });
    const moreThanAWeekAgo = new Intl.DateTimeFormat(locale, { dateStyle: "long" });
    const fullDate = new Intl.DateTimeFormat(locale, { dateStyle: "long", timeStyle: "short" });
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
                this.#timeElement.title = fullDate.format(date);
            }
            // timestamp is less than 60 seconds ago
            if (difference < 60) {
                this.#timeElement.textContent = "TODO: a moment ago"; // Language.get("wcf.date.relative.now");
            }
            // timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
            else if (difference < 3600 /* TimePeriod.OneHour */) {
                const minutes = Math.trunc(difference / 60 /* TimePeriod.OneMinute */);
                this.#timeElement.textContent = lessThanADayAgo.format(minutes * -1, "minutes");
            }
            // timestamp is less than 24 hours ago
            else if (difference < 86400 /* TimePeriod.OneDay */) {
                const hours = Math.trunc(difference / 3600 /* TimePeriod.OneHour */);
                this.#timeElement.textContent = lessThanADayAgo.format(hours * -1, "hours");
            }
            // timestamp is less than 6 days ago
            else if (difference < 604800 /* TimePeriod.OneWeek */) {
                this.#timeElement.textContent = lessThanAWeekAgo.format(date);
            }
            // timestamp is between ~700 million years BC and last week
            else {
                this.#timeElement.textContent = moreThanAWeekAgo.format(date);
            }
        }
    }
    window.customElements.define("woltlab-core-time", WoltlabCoreTimeElement);
    const refreshAllTimeElements = function () {
        document.querySelectorAll("woltlab-core-time").forEach((element) => element.refresh(false));
    };
    let timer = undefined;
    const startTimer = () => {
        timer = window.setInterval(() => refreshAllTimeElements(), 60000);
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
