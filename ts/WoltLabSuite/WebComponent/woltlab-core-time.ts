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

  const enum TimePeriod {
    OneMinute = 60,
    OneHour = 3_600,
    OneDay = 86_400,
    OneWeek = 86_400 * 7,
  }

  class WoltlabCoreTimeElement extends HTMLElement {
    #date?: Date;
    #timeElement?: HTMLTimeElement;

    get date(): Date {
      if (this.#date === undefined) {
        const value = this.getAttribute("date");
        if (!value) {
          throw new Error("The 'date' attribute is missing.");
        }

        this.#date = new Date(value);
      }

      return this.#date;
    }

    set date(date: Date) {
      this.setAttribute("date", date.toISOString());

      this.refresh(true);
    }

    connectedCallback() {
      this.refresh(true);
    }

    refresh(updateTitle: boolean) {
      const date = this.date;
      const difference = Math.trunc((Date.now() - date.getTime() - drift) / 1_000);

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
      else if (difference < TimePeriod.OneHour) {
        const minutes = Math.trunc(difference / TimePeriod.OneMinute);
        this.#timeElement.textContent = lessThanADayAgo.format(minutes * -1, "minutes");
      }
      // timestamp is less than 24 hours ago
      else if (difference < TimePeriod.OneDay) {
        const hours = Math.trunc(difference / TimePeriod.OneHour);
        this.#timeElement.textContent = lessThanADayAgo.format(hours * -1, "hours");
      }
      // timestamp is less than 6 days ago
      else if (difference < TimePeriod.OneWeek) {
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
    document.querySelectorAll<WoltlabCoreTimeElement>("woltlab-core-time").forEach((element) => element.refresh(false));
  };

  let timer: number | undefined = undefined;
  const startTimer = () => {
    timer = window.setInterval(() => refreshAllTimeElements(), 60_000);
  };

  document.addEventListener("DOMContentLoaded", () => startTimer(), { once: true });

  document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
      // Disable refreshes while the tab is in the background.
      window.clearInterval(timer);
    } else {
      // Immediately update the date values when the page is visible again.
      refreshAllTimeElements();

      startTimer();
    }
  });
}
