/**
 * Transforms <time> elements to display the elapsed time relative to the current time.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Date/Time/Relative
 */

import * as Core from "../../Core";
import { wheneverFirstSeen } from "../../Helper/Selector";
import * as Language from "../../Language";
import RepeatingTimer from "../../Timer/Repeating";

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

const enum TimePeriod {
  OneMinute = 60,
  OneHour = 3_600,
  OneDay = 86_400,
  OneWeek = 86_400 * 7,
}

function onVisibilityChange(): void {
  if (document.hidden) {
    _isActive = false;
    _isPending = false;
  } else {
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
    if (!_isPending) _isPending = true;
    return;
  }

  document.querySelectorAll("time").forEach((element) => {
    rebuild(element);
  });
}

function rebuild(element: HTMLTimeElement): void {
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
  else if (difference < TimePeriod.OneHour) {
    const minutes = Math.trunc(difference / TimePeriod.OneMinute);
    element.textContent = lessThanADayAgo.format(minutes * -1, "minutes");
  }
  // timestamp is less than 24 hours ago
  else if (difference < TimePeriod.OneDay) {
    const hours = Math.trunc(difference / TimePeriod.OneHour);
    element.textContent = lessThanADayAgo.format(hours * -1, "hours");
  }
  // timestamp is less than 6 days ago
  else if (difference < TimePeriod.OneWeek) {
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
export function setup(): void {
  new RepeatingTimer(() => refresh(), 60_000);

  document.addEventListener("visibilitychange", () => onVisibilityChange());

  wheneverFirstSeen("time", (element: HTMLTimeElement) => rebuild(element));
}
