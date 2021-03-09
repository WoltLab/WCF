/**
 * Transforms <time> elements to display the elapsed time relative to the current time.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Date/Time/Relative
 */

import * as Core from "../../Core";
import * as DateUtil from "../Util";
import DomChangeListener from "../../Dom/Change/Listener";
import * as Language from "../../Language";
import RepeatingTimer from "../../Timer/Repeating";

let _isActive = true;
let _isPending = false;
let _offset: number;

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

  const date = new Date();
  const timestamp = (date.getTime() - date.getMilliseconds()) / 1_000;

  document.querySelectorAll("time").forEach((element) => {
    rebuild(element, date, timestamp);
  });
}

function rebuild(element: HTMLTimeElement, date: Date, timestamp: number): void {
  if (!element.classList.contains("datetime") || Core.stringToBool(element.dataset.isFutureDate || "")) {
    return;
  }

  const elTimestamp = parseInt(element.dataset.timestamp!, 10) + _offset;
  const elDate = element.dataset.date!;
  const elTime = element.dataset.time!;
  const elOffset = element.dataset.offset!;

  if (!element.title) {
    element.title = Language.get("wcf.date.dateTimeFormat")
      .replace(/%date%/, elDate)
      .replace(/%time%/, elTime);
  }

  // timestamp is less than 60 seconds ago
  if (elTimestamp >= timestamp || timestamp < elTimestamp + 60) {
    element.textContent = Language.get("wcf.date.relative.now");
  }
  // timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
  else if (timestamp < elTimestamp + 3540) {
    const minutes = Math.max(Math.round((timestamp - elTimestamp) / 60), 1);
    element.textContent = Language.get("wcf.date.relative.minutes", { minutes: minutes });
  }
  // timestamp is less than 24 hours ago
  else if (timestamp < elTimestamp + 86400) {
    const hours = Math.round((timestamp - elTimestamp) / 3600);
    element.textContent = Language.get("wcf.date.relative.hours", { hours: hours });
  }
  // timestamp is less than 6 days ago
  else if (timestamp < elTimestamp + 518400) {
    const midnight = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const days = Math.ceil((midnight.getTime() / 1000 - elTimestamp) / 86400);

    // get day of week
    const dateObj = DateUtil.getTimezoneDate(elTimestamp * 1000, parseInt(elOffset, 10) * 1000);
    const dow = dateObj.getDay();
    const day = ((Language.get("__days") as any) as string[])[dow];

    element.textContent = Language.get("wcf.date.relative.pastDays", { days: days, day: day, time: elTime });
  }
  // timestamp is between ~700 million years BC and last week
  else {
    element.textContent = Language.get("wcf.date.shortDateTimeFormat")
      .replace(/%date%/, elDate)
      .replace(/%time%/, elTime);
  }
}

/**
 * Transforms <time> elements on init and binds event listeners.
 */
export function setup(): void {
  _offset = Math.trunc(Date.now() / 1_000 - window.TIME_NOW);

  new RepeatingTimer(refresh, 60_000);

  DomChangeListener.add("WoltLabSuite/Core/Date/Time/Relative", refresh);

  document.addEventListener("visibilitychange", onVisibilityChange);
}
