/**
 * Date picker with time support.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as Core from "../Core";
import * as DateUtil from "./Util";
import * as EventHandler from "../Event/Handler";
import * as Language from "../Language";
import * as UiAlignment from "../Ui/Alignment";
import UiCloseOverlay from "../Ui/CloseOverlay";
import DomUtil from "../Dom/Util";
import { getPageOverlayContainer } from "../Helper/PageOverlay";
import { createFocusTrap, FocusTrap } from "focus-trap";
import { wheneverSeen } from "../Helper/Selector";

let _didInit = false;
let _firstDayOfWeek = 0;
let _focusTrap: FocusTrap;
let _updatePosition: (() => void) | undefined = undefined;

const _data = new Map<HTMLInputElement, DatePickerData>();
let _input: HTMLInputElement | null = null;
let _maxDate: Date;
let _minDate: Date;

const _dateCells: HTMLAnchorElement[] = [];
let _dateGrid: HTMLUListElement;
let _dateHour: HTMLSelectElement;
let _dateMinute: HTMLSelectElement;
let _dateMonth: HTMLSelectElement;
let _dateMonthNext: HTMLButtonElement;
let _dateMonthPrevious: HTMLButtonElement;
let _dateTime: HTMLElement;
let _dateYear: HTMLSelectElement;
let _datePicker: HTMLElement | null = null;

/**
 * Creates the date picker DOM.
 */
function createPicker() {
  if (_datePicker !== null) {
    return;
  }

  _datePicker = document.createElement("div");
  _datePicker.className = "datePicker";
  _datePicker.addEventListener("click", (event) => {
    event.stopPropagation();
  });

  const header = document.createElement("header");
  _datePicker.appendChild(header);

  _dateMonthPrevious = document.createElement("button");
  _dateMonthPrevious.type = "button";
  _dateMonthPrevious.className = "previous jsTooltip";
  _dateMonthPrevious.title = Language.get("wcf.date.datePicker.previousMonth");
  _dateMonthPrevious.setAttribute("aria-label", Language.get("wcf.date.datePicker.previousMonth"));
  _dateMonthPrevious.innerHTML = '<fa-icon name="arrow-left" solid></fa-icon>';
  _dateMonthPrevious.addEventListener("click", () => DatePicker.previousMonth());
  header.appendChild(_dateMonthPrevious);

  const monthYearContainer = document.createElement("span");
  header.appendChild(monthYearContainer);

  _dateMonth = document.createElement("select");
  _dateMonth.className = "month jsTooltip";
  _dateMonth.title = Language.get("wcf.date.datePicker.month");
  _dateMonth.setAttribute("aria-label", Language.get("wcf.date.datePicker.month"));
  _dateMonth.addEventListener("change", changeMonth);
  monthYearContainer.appendChild(_dateMonth);

  let months = "";
  const monthNames = new Intl.DateTimeFormat(document.documentElement.lang, { month: "long" });
  for (let i = 0; i < 12; i++) {
    const monthName = monthNames.format(new Date(2000, i, 15));
    months += `<option value="${i}">${monthName}</option>`;
  }
  _dateMonth.innerHTML = months;

  _dateYear = document.createElement("select");
  _dateYear.className = "year jsTooltip";
  _dateYear.title = Language.get("wcf.date.datePicker.year");
  _dateYear.setAttribute("aria-label", Language.get("wcf.date.datePicker.year"));
  _dateYear.addEventListener("change", changeYear);
  monthYearContainer.appendChild(_dateYear);

  _dateMonthNext = document.createElement("button");
  _dateMonthNext.type = "button";
  _dateMonthNext.className = "next jsTooltip";
  _dateMonthNext.title = Language.get("wcf.date.datePicker.nextMonth");
  _dateMonthNext.setAttribute("aria-label", Language.get("wcf.date.datePicker.nextMonth"));
  _dateMonthNext.innerHTML = '<fa-icon name="arrow-right" solid></fa-icon>';
  _dateMonthNext.addEventListener("click", () => DatePicker.nextMonth());
  header.appendChild(_dateMonthNext);

  _dateGrid = document.createElement("ul");
  _datePicker.appendChild(_dateGrid);

  const item = document.createElement("li");
  item.className = "weekdays";
  _dateGrid.appendChild(item);

  // There is no simple way to access the names
  // of the weekdays. We can work around this by
  // taking todays date and shift it to the start
  // of the week.
  const weekdays = new Intl.DateTimeFormat(document.documentElement.lang, { weekday: "short" });
  const now = new Date();
  const startOfWeekOffset = now.getDay() - _firstDayOfWeek;
  for (let i = 0; i < 7; i++) {
    const offset = i - startOfWeekOffset;
    const day = new Date(new Date().setDate(now.getDate() + offset));

    const span = document.createElement("span");
    span.textContent = weekdays.format(day);
    item.appendChild(span);
  }

  // create date grid
  for (let i = 0; i < 6; i++) {
    const row = document.createElement("li");
    _dateGrid.appendChild(row);

    for (let j = 0; j < 7; j++) {
      const cell = document.createElement("a");
      cell.addEventListener("click", click);
      _dateCells.push(cell);

      row.appendChild(cell);
    }
  }

  _dateTime = document.createElement("footer");
  _datePicker.appendChild(_dateTime);

  _dateHour = document.createElement("select");
  _dateHour.className = "hour";
  _dateHour.title = Language.get("wcf.date.datePicker.hour");
  _dateHour.setAttribute("aria-label", Language.get("wcf.date.datePicker.hour"));
  _dateHour.addEventListener("change", formatValue);

  const date = new Date(2000, 0, 1);
  const timeFormatter = new Intl.DateTimeFormat(document.documentElement.lang, { hour: "numeric" });
  let tmp = "";
  for (let i = 0; i < 24; i++) {
    date.setHours(i);

    let parts = timeFormatter.formatToParts(date);

    // The formatter yields results containing string
    // literals that add noise to the selection without
    // offering any value.
    //
    // One example is the German output which appends
    // ` Uhr` to every hour, even when this is completely
    // useless in this context.
    const containsDayPeriod = parts.some((part) => part.type === "dayPeriod");
    if (parts.length === 2 && !containsDayPeriod) {
      parts = parts.filter((part) => part.type !== "literal");
    }

    const value = parts.map((part) => part.value).join("");
    tmp += `<option value="${i}">${value}</option>`;
  }
  _dateHour.innerHTML = tmp;

  _dateTime.appendChild(_dateHour);

  _dateTime.appendChild(document.createTextNode("\u00A0:\u00A0"));

  _dateMinute = document.createElement("select");
  _dateMinute.className = "minute";
  _dateMinute.title = Language.get("wcf.date.datePicker.minute");
  _dateMinute.setAttribute("aria-label", Language.get("wcf.date.datePicker.minute"));
  _dateMinute.addEventListener("change", formatValue);

  tmp = "";
  for (let i = 0; i < 60; i++) {
    const value = i < 10 ? "0" + i.toString() : i;
    tmp += `<option value="${i}">${value}</option>`;
  }
  _dateMinute.innerHTML = tmp;

  _dateTime.appendChild(_dateMinute);

  getPageOverlayContainer().appendChild(_datePicker);

  _focusTrap = createFocusTrap(_datePicker, {
    allowOutsideClick: true,
    escapeDeactivates(): boolean {
      close();

      return false;
    },
  });
}

/**
 * Initializes the minimum/maximum date range.
 */
function initDateRange(element: HTMLInputElement, now: Date, isMinDate: boolean): void {
  const name = isMinDate ? "minDate" : "maxDate";
  let value = (element.dataset[name] || "").trim();

  if (/^(\d{4})-(\d{2})-(\d{2})$/.exec(value)) {
    // YYYY-mm-dd
    value = new Date(value).getTime().toString();
  } else if (value === "now") {
    value = now.getTime().toString();
  } else if (/^\d{1,3}$/.exec(value)) {
    // relative time span in years
    const date = new Date(now.getTime());
    date.setFullYear(date.getFullYear() + ~~value * (isMinDate ? -1 : 1));

    value = date.getTime().toString();
  } else if (/^datePicker-(.+)$/.exec(value)) {
    // element id, e.g. `datePicker-someOtherElement`
    value = RegExp.$1;

    if (document.getElementById(value) === null) {
      throw new Error(
        "Reference date picker identified by '" + value + "' does not exists (element id: '" + element.id + "').",
      );
    }
  } else if (/^\d{4}-\d{2}-\d{2}T/.test(value)) {
    value = new Date(value).getTime().toString();
  } else {
    value = new Date(isMinDate ? 1902 : 2038, 0, 1).getTime().toString();
  }

  element.dataset[name] = value;
}

function getDateValue(attributeName: string): Date {
  let date = _input!.dataset[attributeName] || "";
  if (/^datePicker-(.+)$/.exec(date)) {
    const referenceElement = document.getElementById(RegExp.$1);
    if (referenceElement === null) {
      throw new Error(`Unable to find an element with the id '${RegExp.$1}'.`);
    }
    date = referenceElement.dataset.value || "";
  }

  return new Date(parseInt(date, 10));
}

/**
 * Opens the date picker.
 */
function open(event: MouseEvent): void {
  event.stopPropagation();

  createPicker();

  const target = event.currentTarget as HTMLInputElement;
  const input = target.nodeName === "INPUT" ? target : (target.previousElementSibling as HTMLInputElement);
  if (input === _input) {
    close();
    return;
  }

  const dialogContent = input.closest(".dialogContent") as HTMLElement;
  if (dialogContent !== null) {
    if (!Core.stringToBool(dialogContent.dataset.hasDatepickerScrollListener || "")) {
      dialogContent.addEventListener("scroll", onDialogScroll);
      dialogContent.dataset.hasDatepickerScrollListener = "1";
    }
  }

  _input = input;
  const data = _data.get(_input) as DatePickerData;
  const value = _input.dataset.value!;
  let date: Date;
  if (value) {
    date = new Date(parseInt(value, 10));

    if (date.toString() === "Invalid Date") {
      date = new Date();
    }
  } else {
    date = new Date();
  }

  // set min/max date
  _minDate = getDateValue("minDate");
  if (_minDate.getTime() > date.getTime()) {
    date = _minDate;
  }

  _maxDate = getDateValue("maxDate");
  if (_input.dataset.maxDate && _maxDate.getTime() < date.getTime()) {
    date = _maxDate;
  }

  if (data.isDateTime) {
    _dateHour.value = date.getHours().toString();
    _dateMinute.value = date.getMinutes().toString();

    _datePicker!.classList.add("datePickerTime");
  } else {
    _datePicker!.classList.remove("datePickerTime");
  }

  _datePicker!.classList[data.isTimeOnly ? "add" : "remove"]("datePickerTimeOnly");

  renderPicker(date.getDate(), date.getMonth(), date.getFullYear());

  _updatePosition = () => {
    UiAlignment.set(_datePicker!, _input!);
  };
  _updatePosition();
  window.addEventListener("scroll", _updatePosition, { passive: false });

  _input.nextElementSibling!.setAttribute("aria-expanded", "true");

  _focusTrap.activate();
}

/**
 * Closes the date picker.
 */
function close() {
  if (_datePicker === null || !_datePicker.classList.contains("active")) {
    return;
  }

  _datePicker.classList.remove("active");

  const data = _data.get(_input!) as DatePickerData;
  if (typeof data.onClose === "function") {
    data.onClose();
  }

  EventHandler.fire("WoltLabSuite/Core/Date/Picker", "close", { element: _input });

  if (typeof _updatePosition !== "undefined") {
    window.removeEventListener("scroll", _updatePosition);
    _updatePosition = undefined;
  }

  const sibling = _input!.nextElementSibling as HTMLElement;
  sibling.setAttribute("aria-expanded", "false");
  _input = null;
  _focusTrap.deactivate();
}

/**
 * Updates the position of the date picker in a dialog if the dialog content
 * is scrolled.
 */
function onDialogScroll(event: WheelEvent): void {
  if (_input === null) {
    return;
  }

  const dialogContent = event.currentTarget as HTMLElement;

  const offset = DomUtil.offset(_input);
  const dialogOffset = DomUtil.offset(dialogContent);

  // check if date picker input field is still (partially) visible
  if (offset.top + _input.clientHeight <= dialogOffset.top) {
    // top check
    close();
  } else if (offset.top >= dialogOffset.top + dialogContent.offsetHeight) {
    // bottom check
    close();
  } else if (offset.left <= dialogOffset.left) {
    // left check
    close();
  } else if (offset.left >= dialogOffset.left + dialogContent.offsetWidth) {
    // right check
    close();
  } else {
    UiAlignment.set(_datePicker!, _input);
  }
}

/**
 * Renders the full picker on init.
 */
function renderPicker(day: number, month: number, year: number): void {
  renderGrid(day, month, year);

  // create options for month and year
  let years = "";
  for (let i = _minDate.getFullYear(), last = _maxDate.getFullYear(); i <= last; i++) {
    years += `<option value="${i}">${i}</option>`;
  }
  _dateYear.innerHTML = years;
  _dateYear.value = year.toString();

  _dateMonth.value = month.toString();

  _datePicker!.classList.add("active");
}

/**
 * Updates the date grid.
 */
function renderGrid(day?: number, month?: number, year?: number): void {
  const hasDay = day !== undefined;
  const hasMonth = month !== undefined;

  if (typeof day !== "number") {
    day = parseInt(day || _dateGrid.dataset.day || "0", 10);
  }
  if (typeof month !== "number") {
    month = parseInt(month || "0", 10);
  }
  if (typeof year !== "number") {
    year = parseInt(year || "0", 10);
  }

  // rebuild cells
  if (hasMonth || year) {
    let rebuildMonths = year !== 0;

    // rebuild grid
    const fragment = document.createDocumentFragment();
    fragment.appendChild(_dateGrid);

    if (!hasMonth) {
      month = parseInt(_dateGrid.dataset.month!, 10);
    }
    if (!year) {
      year = parseInt(_dateGrid.dataset.year!, 10);
    }

    // check if current selection exceeds min/max date
    let date = new Date(
      year.toString() + "-" + ("0" + (month + 1).toString()).slice(-2) + "-" + ("0" + day.toString()).slice(-2),
    );
    if (date < _minDate) {
      year = _minDate.getFullYear();
      month = _minDate.getMonth();
      day = _minDate.getDate();

      _dateMonth.value = month.toString();
      _dateYear.value = year.toString();

      rebuildMonths = true;
    } else if (date > _maxDate) {
      year = _maxDate.getFullYear();
      month = _maxDate.getMonth();
      day = _maxDate.getDate();

      _dateMonth.value = month.toString();
      _dateYear.value = year.toString();

      rebuildMonths = true;
    }

    date = new Date(year.toString() + "-" + ("0" + (month + 1).toString()).slice(-2) + "-01");

    // shift until first displayed day equals first day of week
    while (date.getDay() !== _firstDayOfWeek) {
      date.setDate(date.getDate() - 1);
    }

    // show the last row
    DomUtil.show(_dateCells[35].parentNode as HTMLElement);

    const comparableMinDate = new Date(_minDate.getFullYear(), _minDate.getMonth(), _minDate.getDate());
    for (let i = 0; i < 42; i++) {
      if (i === 35 && date.getMonth() !== month) {
        // skip the last row if it only contains the next month
        DomUtil.hide(_dateCells[35].parentNode as HTMLElement);

        break;
      }

      const cell = _dateCells[i];

      cell.textContent = date.getDate().toString();
      const sameMonth = date.getMonth() === month;
      if (sameMonth) {
        cell.classList.remove("otherMonth");
      } else {
        cell.classList.add("otherMonth");
      }

      let selectable = true;
      if (date < comparableMinDate) {
        selectable = false;
      } else if (date > _maxDate) {
        selectable = false;
      }

      if (selectable) {
        cell.classList.remove("disabled");
      } else {
        cell.classList.add("disabled");
      }

      if (sameMonth && selectable) {
        cell.href = "#";
        cell.setAttribute("role", "button");
        cell.tabIndex = 0;
        cell.title = DateUtil.formatDate(date);
        cell.setAttribute("aria-label", DateUtil.formatDate(date));
      }

      date.setDate(date.getDate() + 1);
    }

    _dateGrid.dataset.month = month.toString();
    _dateGrid.dataset.year = year.toString();

    _datePicker!.insertBefore(fragment, _dateTime);

    if (!hasDay) {
      // check if date is valid
      date = new Date(year, month, day);
      if (date.getDate() !== day) {
        while (date.getMonth() !== month) {
          date.setDate(date.getDate() - 1);
        }

        day = date.getDate();
      }
    }

    if (rebuildMonths) {
      for (let i = 0; i < 12; i++) {
        const currentMonth = _dateMonth.children[i] as HTMLOptionElement;

        currentMonth.disabled =
          (year === _minDate.getFullYear() && +currentMonth.value < _minDate.getMonth()) ||
          (year === _maxDate.getFullYear() && +currentMonth.value > _maxDate.getMonth());
      }

      const nextMonth = new Date(year.toString() + "-" + ("0" + (month + 1).toString()).slice(-2) + "-01");
      nextMonth.setMonth(nextMonth.getMonth() + 1);

      _dateMonthNext.classList[nextMonth < _maxDate ? "add" : "remove"]("active");

      const previousMonth = new Date(year.toString() + "-" + ("0" + (month + 1).toString()).slice(-2) + "-01");
      previousMonth.setDate(previousMonth.getDate() - 1);

      _dateMonthPrevious.classList[previousMonth > _minDate ? "add" : "remove"]("active");
    }
  }

  // update active day
  if (day) {
    for (let i = 0; i < 37; i++) {
      const cell = _dateCells[i];

      let active = +cell.textContent! === day;
      if (cell.classList.contains("otherMonth") || cell.classList.contains("disabled")) {
        active = false;
      }

      if (active) {
        cell.classList.add("active");
      } else {
        cell.classList.remove("active");
      }
    }

    _dateGrid.dataset.day = day.toString();
  }

  formatValue();
}

/**
 * Sets the visible and shadow value
 */
function formatValue(): void {
  const data = _data.get(_input!) as DatePickerData;
  let date: Date;

  if (Core.stringToBool(_input!.dataset.empty || "")) {
    return;
  }

  if (data.isDateTime) {
    date = new Date(
      +_dateGrid.dataset.year!,
      +_dateGrid.dataset.month!,
      +_dateGrid.dataset.day!,
      +_dateHour.value,
      +_dateMinute.value,
    );
  } else {
    date = new Date(+_dateGrid.dataset.year!, +_dateGrid.dataset.month!, +_dateGrid.dataset.day!);
  }

  DatePicker.setDate(_input!, date);
}

/**
 * Handles changes to the month select element.
 */
function changeMonth(event: Event): void {
  const target = event.currentTarget as HTMLSelectElement;
  renderGrid(undefined, +target.value);
}

/**
 * Handles changes to the year select element.
 */
function changeYear(event: Event): void {
  const target = event.currentTarget as HTMLSelectElement;
  renderGrid(undefined, undefined, +target.value);
}

/**
 * Handles clicks on an individual day.
 */
function click(event: MouseEvent): void {
  event.preventDefault();

  const target = event.currentTarget as HTMLAnchorElement;
  if (target.classList.contains("otherMonth") || target.classList.contains("disabled")) {
    return;
  }

  _input!.dataset.empty = "false";

  renderGrid(+target.textContent!);

  const data = _data.get(_input!) as DatePickerData;
  if (!data.isDateTime) {
    close();
  }
}

/**
 * Validates given element or id if it represents an active date picker.
 */
function getElement(element: InputElementOrString): HTMLInputElement {
  if (typeof element === "string") {
    element = document.getElementById(element) as HTMLInputElement;
  }

  if (!(element instanceof HTMLInputElement) || !element.classList.contains("inputDatePicker") || !_data.has(element)) {
    throw new Error("Expected a valid date picker input element or id.");
  }

  return element;
}

const DatePicker = {
  /**
   * Initializes all date and datetime input fields.
   */
  init(): void {
    if (_didInit) {
      return;
    }
    _didInit = true;

    _firstDayOfWeek = parseInt(Language.get("wcf.date.firstDayOfTheWeek"), 10);

    wheneverSeen(
      `input[type="date"]:not(.inputDatePicker), input[type="datetime"]:not(.inputDatePicker)`,
      (element: HTMLInputElement) => {
        const now = new Date();
        element.classList.add("inputDatePicker");
        element.readOnly = true;

        // Use `getAttribute()`, because `.type` is normalized to "text" for unknown values.
        const isDateTime = element.getAttribute("type") === "datetime";
        const isTimeOnly = isDateTime && Core.stringToBool(element.dataset.timeOnly || "");
        const disableClear = Core.stringToBool(element.dataset.disableClear || "");
        const ignoreTimezone = isDateTime && Core.stringToBool(element.dataset.ignoreTimezone || "");
        const isBirthday = element.classList.contains("birthday");

        element.dataset.isDateTime = isDateTime ? "true" : "false";
        element.dataset.isTimeOnly = isTimeOnly ? "true" : "false";

        // convert value
        let date: Date | null = null;
        let value = element.value;
        if (!value) {
          // Some legacy code may incorrectly use `setAttribute("value", value)`.
          value = element.getAttribute("value") || "";
        }

        // ignore the timezone, if the value is only a date (YYYY-MM-DD)
        const isDateOnly = /^\d+-\d+-\d+$/.test(value);

        if (value) {
          if (isTimeOnly) {
            date = new Date();
            const tmp = value.split(":");
            date.setHours(+tmp[0], +tmp[1]);
          } else {
            if (ignoreTimezone || isBirthday || isDateOnly) {
              let timezoneOffset = new Date(value).getTimezoneOffset();
              let timezone = timezoneOffset > 0 ? "-" : "+"; // -120 equals GMT+0200
              timezoneOffset = Math.abs(timezoneOffset);

              const hours = Math.floor(timezoneOffset / 60).toString();
              const minutes = (timezoneOffset % 60).toString();
              timezone += hours.length === 2 ? hours : "0" + hours;
              timezone += ":";
              timezone += minutes.length === 2 ? minutes : "0" + minutes;

              if (isBirthday || isDateOnly) {
                value += "T00:00:00" + timezone;
              } else {
                value = value.replace(/[+-][0-9]{2}:[0-9]{2}$/, timezone);
              }
            }

            date = new Date(value);
          }

          const time = date.getTime();

          // check for invalid dates
          if (isNaN(time)) {
            value = "";
          } else {
            element.dataset.value = time.toString();
            if (isTimeOnly) {
              value = DateUtil.formatTime(date);
            } else {
              if (isDateTime) {
                value = DateUtil.formatDateTime(date);
              } else {
                value = DateUtil.formatDate(date);
              }
            }
          }
        }

        const isEmpty = value.length === 0;

        // handle birthday input
        if (isBirthday) {
          element.dataset.minDate = "120";

          // do not use 'now' here, all though it makes sense, it causes bad UX
          element.dataset.maxDate = new Date().getFullYear().toString() + "-12-31";
        } else {
          if (element.min) {
            element.dataset.minDate = element.min;
          }
          if (element.max) {
            element.dataset.maxDate = element.max;
          }
        }

        initDateRange(element, now, true);
        initDateRange(element, now, false);

        if ((element.dataset.minDate || "") === (element.dataset.maxDate || "")) {
          throw new Error("Minimum and maximum date cannot be the same (element id '" + element.id + "').");
        }

        // change type to prevent browser's datepicker to trigger
        element.type = "text";
        element.value = value;
        element.dataset.empty = isEmpty ? "true" : "false";

        const placeholder = element.dataset.placeholder || "";
        if (placeholder) {
          element.placeholder = placeholder;
        }

        // add a hidden element to hold the actual date
        const shadowElement = document.createElement("input");
        shadowElement.id = element.id + "DatePicker";
        shadowElement.name = element.name;
        shadowElement.type = "hidden";

        if (date !== null) {
          if (isTimeOnly) {
            shadowElement.value = DateUtil.format(date, "H:i");
          } else if (ignoreTimezone) {
            shadowElement.value = DateUtil.format(date, "Y-m-dTH:i:s");
          } else {
            shadowElement.value = DateUtil.format(date, isDateTime ? "c" : "Y-m-d");
          }
        }

        element.parentNode!.insertBefore(shadowElement, element);
        element.removeAttribute("name");

        element.addEventListener("click", open);

        // create input addon
        const container = document.createElement("div");
        container.className = "inputAddon inputAddonDatePicker";

        const openButton = document.createElement("button");
        openButton.type = "button";
        openButton.className = "inputSuffix button jsTooltip";
        if (isTimeOnly) {
          openButton.title = Language.getPhrase("wcf.date.datePicker.time");
        } else {
          openButton.title = Language.getPhrase("wcf.date.datePicker");
        }
        openButton.setAttribute("aria-haspopup", "true");
        openButton.setAttribute("aria-expanded", "false");
        openButton.addEventListener("click", (event) => {
          if (!element.disabled) {
            open(event);
          }
        });
        container.appendChild(openButton);

        let icon = document.createElement("fa-icon");
        if (isTimeOnly) {
          icon.setIcon("clock");
        } else {
          icon.setIcon("calendar");
        }
        openButton.appendChild(icon);

        element.parentNode!.insertBefore(container, element);
        container.insertBefore(element, openButton);

        let clearButton: HTMLButtonElement | null = null;
        if (!disableClear) {
          clearButton = document.createElement("button");
          clearButton.type = "button";
          clearButton.className = "inputSuffix button jsTooltip";
          if (isTimeOnly) {
            clearButton.title = Language.getPhrase("wcf.date.datePicker.time.clear");
          } else {
            clearButton.title = Language.getPhrase("wcf.date.datePicker.clear");
          }
          clearButton.addEventListener("click", () => {
            if (!element.disabled) {
              DatePicker.clear(element);
            }
          });

          if (isEmpty) {
            clearButton.hidden = true;
          }

          container.appendChild(clearButton);

          icon = document.createElement("fa-icon");
          icon.setIcon("xmark");
          clearButton.appendChild(icon);
        }

        // check if the date input has one of the following classes set otherwise default to 'short'
        const knownClasses = ["tiny", "short", "medium", "long"];
        let hasClass = false;
        for (let j = 0; j < 4; j++) {
          if (element.classList.contains(knownClasses[j])) {
            hasClass = true;
          }
        }

        if (!hasClass) {
          element.classList.add("short");
        }

        // Reflect the `disabled` state of the `<input>` element on the buttons.
        const observer = new MutationObserver(() => {
          if (element.disabled) {
            openButton.classList.add("disabled");
            clearButton?.classList.add("disabled");
          } else {
            openButton.classList.remove("disabled");
            clearButton?.classList.remove("disabled");
          }
        });
        observer.observe(element, {
          attributeFilter: ["disabled"],
        });

        if (element.disabled) {
          openButton.classList.add("disabled");
          clearButton?.classList.add("disabled");
        }

        _data.set(element, {
          clearButton,
          shadow: shadowElement,

          disableClear,
          isDateTime,
          isEmpty,
          isTimeOnly,
          ignoreTimezone,

          onClose: null,
        });
      },
    );

    UiCloseOverlay.add("WoltLabSuite/Core/Date/Picker", () => close());
  },

  /**
   * Shows the previous month.
   */
  previousMonth(): void {
    if (_dateMonth.value === "0") {
      _dateMonth.value = "11";
      _dateYear.value = (+_dateYear.value - 1).toString();
    } else {
      _dateMonth.value = (+_dateMonth.value - 1).toString();
    }

    renderGrid(undefined, +_dateMonth.value, +_dateYear.value);
  },

  /**
   * Shows the next month.
   */
  nextMonth(): void {
    if (_dateMonth.value === "11") {
      _dateMonth.value = "0";
      _dateYear.value = (+_dateYear.value + 1).toString();
    } else {
      _dateMonth.value = (+_dateMonth.value + 1).toString();
    }

    renderGrid(undefined, +_dateMonth.value, +_dateYear.value);
  },

  /**
   * Returns the current Date object or null.
   */
  getDate(element: InputElementOrString): Date | null {
    element = getElement(element);

    const value = element.dataset.value || "";
    if (value) {
      return new Date(+value);
    }

    return null;
  },

  /**
   * Sets the date of given element.
   *
   * @param  {(HTMLInputElement|string)}  element    input element or id
   * @param  {Date}              date    Date object
   */
  setDate(element: InputElementOrString, date: Date): void {
    element = getElement(element);
    const data = _data.get(element) as DatePickerData;

    element.dataset.value = date.getTime().toString();

    let format = "";
    let value: string;
    if (data.isDateTime) {
      if (data.isTimeOnly) {
        value = DateUtil.formatTime(date);
        format = "H:i";
      } else if (data.ignoreTimezone) {
        value = DateUtil.formatDateTime(date);
        format = "Y-m-dTH:i:s";
      } else {
        value = DateUtil.formatDateTime(date);
        format = "c";
      }
    } else {
      value = DateUtil.formatDate(date);
      format = "Y-m-d";
    }

    element.value = value;
    data.shadow.value = DateUtil.format(date, format);

    // show clear button
    if (!data.disableClear) {
      data.clearButton!.hidden = false;
    }
  },

  /**
   * Returns the current value.
   */
  getValue(element: InputElementOrString): string {
    element = getElement(element);
    const data = _data.get(element);

    if (data) {
      return data.shadow.value;
    }

    return "";
  },

  /**
   * Clears the date value of given element.
   */
  clear(element: InputElementOrString): void {
    element = getElement(element);
    const data = _data.get(element) as DatePickerData;

    element.removeAttribute("data-value");
    element.value = "";

    if (!data.disableClear) {
      data.clearButton!.hidden = true;
    }

    data.isEmpty = true;
    data.shadow.value = "";
  },

  /**
   * Reverts the date picker into a normal input field.
   */
  destroy(element: InputElementOrString): void {
    element = getElement(element);
    const data = _data.get(element) as DatePickerData;

    const container = element.parentNode as HTMLElement;
    container.parentNode!.insertBefore(element, container);
    container.remove();

    element.setAttribute("type", "date" + (data.isDateTime ? "time" : ""));
    element.name = data.shadow.name;
    element.value = data.shadow.value;

    element.removeAttribute("data-value");
    element.removeEventListener("click", open);
    data.shadow.remove();

    element.classList.remove("inputDatePicker");
    element.readOnly = false;
    _data.delete(element);
  },

  /**
   * Sets the callback invoked on picker close.
   */
  setCloseCallback(element: InputElementOrString, callback: Callback): void {
    element = getElement(element);
    _data.get(element)!.onClose = callback;
  },
};

// backward-compatibility for `$.ui.datepicker` shim
window.__wcf_bc_datePicker = DatePicker;

export = DatePicker;

type InputElementOrString = HTMLInputElement | string;

type Callback = () => void;

interface DatePickerData {
  clearButton: HTMLButtonElement | null;
  shadow: HTMLInputElement;

  disableClear: boolean;
  isDateTime: boolean;
  isEmpty: boolean;
  isTimeOnly: boolean;
  ignoreTimezone: boolean;

  onClose: Callback | null;
}
