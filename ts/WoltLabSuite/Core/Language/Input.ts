/**
 * I18n interface for input and textarea fields.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Language/Input
 */

import DomUtil from "../Dom/Util";
import * as Language from "../Language";
import { NotificationAction } from "../Ui/Dropdown/Data";
import UiDropdownSimple from "../Ui/Dropdown/Simple";
import * as StringUtil from "../StringUtil";

type LanguageId = number;

export interface I18nValues {
  // languageID => value
  [key: string]: string;
}

export interface Languages {
  // languageID => languageName
  [key: string]: string;
}

type Values = Map<LanguageId, string>;

export type InputOrTextarea = HTMLInputElement | HTMLTextAreaElement;

type CallbackEvent = "select" | "submit";
type Callback = (element: InputOrTextarea) => void;

interface ElementData {
  buttonLabel: HTMLElement;
  callbacks: Map<CallbackEvent, Callback>;
  element: InputOrTextarea;
  languageId: number;
  isEnabled: boolean;
  forceSelection: boolean;
}

const _elements = new Map<string, ElementData>();
const _forms = new WeakMap<HTMLFormElement, string[]>();
const _values = new Map<string, Values>();

/**
 * Sets up DOM and event listeners for an input field.
 */
function initElement(
  elementId: string,
  element: InputOrTextarea,
  values: Values,
  availableLanguages: Languages,
  forceSelection: boolean,
): void {
  let container = element.parentElement!;
  if (!container.classList.contains("inputAddon")) {
    container = document.createElement("div");
    container.className = "inputAddon";
    if (element.nodeName === "TEXTAREA") {
      container.classList.add("inputAddonTextarea");
    }
    container.dataset.inputId = elementId;

    const hasFocus = document.activeElement === element;

    // DOM manipulation causes focused element to lose focus
    element.insertAdjacentElement("beforebegin", container);
    container.appendChild(element);

    if (hasFocus) {
      element.focus();
    }
  }

  container.classList.add("dropdown");
  const button = document.createElement("span");
  button.className = "button dropdownToggle inputPrefix";

  const buttonLabel = document.createElement("span");
  buttonLabel.textContent = Language.get("wcf.global.button.disabledI18n");

  button.appendChild(buttonLabel);
  container.insertBefore(button, element);

  const dropdownMenu = document.createElement("ul");
  dropdownMenu.className = "dropdownMenu";
  button.insertAdjacentElement("afterend", dropdownMenu);

  const callbackClick = (event: MouseEvent | HTMLElement): void => {
    let target: HTMLElement;
    if (event instanceof HTMLElement) {
      target = event;
    } else {
      target = event.currentTarget as HTMLElement;
    }

    const languageId = ~~target.dataset.languageId!;

    const activeItem = dropdownMenu.querySelector(".active");
    if (activeItem !== null) {
      activeItem.classList.remove("active");
    }

    if (languageId) {
      target.classList.add("active");
    }

    const isInit = event instanceof HTMLElement;
    select(elementId, languageId, isInit);
  };

  // build language dropdown
  Object.entries(availableLanguages).forEach(([languageId, languageName]) => {
    const listItem = document.createElement("li");
    listItem.dataset.languageId = languageId;

    const span = document.createElement("span");
    span.textContent = languageName;

    listItem.appendChild(span);
    listItem.addEventListener("click", callbackClick);
    dropdownMenu.appendChild(listItem);
  });

  if (!forceSelection) {
    const divider = document.createElement("li");
    divider.className = "dropdownDivider";
    dropdownMenu.appendChild(divider);

    const listItem = document.createElement("li");
    listItem.dataset.languageId = "0";
    listItem.addEventListener("click", callbackClick);

    const span = document.createElement("span");
    span.textContent = Language.get("wcf.global.button.disabledI18n");
    listItem.appendChild(span);

    dropdownMenu.appendChild(listItem);
  }

  let activeItem: HTMLElement | undefined = undefined;
  if (forceSelection || values.size) {
    activeItem = Array.from(dropdownMenu.children).find((element: HTMLElement) => {
      return +element.dataset.languageId! === window.LANGUAGE_ID;
    }) as HTMLElement;
  }

  UiDropdownSimple.init(button);
  UiDropdownSimple.registerCallback(container.id, dropdownToggle);

  _elements.set(elementId, {
    buttonLabel,
    callbacks: new Map<CallbackEvent, Callback>(),
    element,
    languageId: 0,
    isEnabled: true,
    forceSelection,
  });

  // bind to submit event
  const form = element.closest("form");
  if (form !== null) {
    form.addEventListener("submit", submit);

    let elementIds = _forms.get(form);
    if (elementIds === undefined) {
      elementIds = [];
      _forms.set(form, elementIds);
    }

    elementIds.push(elementId);
  }

  if (activeItem) {
    callbackClick(activeItem);
  }
}

/**
 * Selects a language or non-i18n from the dropdown list.
 */
function select(elementId: string, languageId: number, isInit: boolean): void {
  const data = _elements.get(elementId)!;

  const dropdownMenu = UiDropdownSimple.getDropdownMenu(data.element.closest(".inputAddon")!.id)!;

  const item = dropdownMenu.querySelector(`[data-language-id="${languageId}"]`);
  const label = item ? item.textContent! : "";

  // save current value
  if (data.languageId !== languageId) {
    const values = _values.get(elementId)!;

    if (data.languageId) {
      values.set(data.languageId, data.element.value);
    }

    if (languageId === 0) {
      _values.set(elementId, new Map<LanguageId, string>());
    } else if (data.buttonLabel.classList.contains("active") || isInit) {
      data.element.value = values.get(languageId) || "";
    }

    // update label
    data.buttonLabel.textContent = label;
    data.buttonLabel.classList[languageId ? "add" : "remove"]("active");

    data.languageId = languageId;
  }

  if (!isInit) {
    data.element.blur();
    data.element.focus();
  }

  if (data.callbacks.has("select")) {
    data.callbacks.get("select")!(data.element);
  }
}

/**
 * Callback for dropdowns being opened, flags items with a missing value for one or more languages.
 */
function dropdownToggle(containerId: string, action: NotificationAction): void {
  if (action !== "open") {
    return;
  }

  const dropdownMenu = UiDropdownSimple.getDropdownMenu(containerId)!;
  const container = document.getElementById(containerId)!;
  const elementId = container.dataset.inputId!;
  const data = _elements.get(elementId)!;
  const values = _values.get(elementId)!;

  Array.from(dropdownMenu.children).forEach((item: HTMLElement) => {
    const languageId = ~~(item.dataset.languageId || "");

    if (languageId) {
      let hasMissingValue = false;
      if (data.languageId) {
        if (languageId === data.languageId) {
          hasMissingValue = data.element.value.trim() === "";
        } else {
          hasMissingValue = !values.get(languageId);
        }
      }

      if (hasMissingValue) {
        item.classList.add("missingValue");
      } else {
        item.classList.remove("missingValue");
      }
    }
  });
}

/**
 * Inserts hidden fields for i18n input on submit.
 */
function submit(event: Event): void {
  const form = event.currentTarget as HTMLFormElement;
  const elementIds = _forms.get(form)!;

  elementIds.forEach((elementId) => {
    const data = _elements.get(elementId)!;
    if (!data.isEnabled) {
      return;
    }

    const values = _values.get(elementId)!;

    if (data.callbacks.has("submit")) {
      data.callbacks.get("submit")!(data.element);
    }

    // update with current value
    if (data.languageId) {
      values.set(data.languageId, data.element.value);
    }

    if (values.size) {
      values.forEach(function (value, languageId) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = `${elementId}_i18n[${languageId}]`;
        input.value = value;

        form.appendChild(input);
      });

      // remove name attribute to enforce i18n values
      data.element.removeAttribute("name");
    }
  });
}

/**
 * Initializes an input field.
 */
export function init(
  elementId: string,
  values: I18nValues,
  availableLanguages: Languages,
  forceSelection: boolean,
): void {
  if (_values.has(elementId)) {
    return;
  }

  const element = document.getElementById(elementId) as InputOrTextarea;
  if (element === null) {
    throw new Error(`Expected a valid element id, cannot find '${elementId}'.`);
  }

  // unescape values
  const unescapedValues = new Map<LanguageId, string>();
  Object.entries(values).forEach(([languageId, value]) => {
    unescapedValues.set(+languageId, StringUtil.unescapeHTML(value));
  });

  _values.set(elementId, unescapedValues);

  initElement(elementId, element, unescapedValues, availableLanguages, forceSelection);
}

/**
 * Registers a callback for an element.
 */
export function registerCallback(elementId: string, eventName: CallbackEvent, callback: Callback): void {
  if (!_values.has(elementId)) {
    throw new Error(`Unknown element id '${elementId}'.`);
  }

  _elements.get(elementId)!.callbacks.set(eventName, callback);
}

/**
 * Unregisters the element with the given id.
 *
 * @since  5.2
 */
export function unregister(elementId: string): void {
  if (!_values.has(elementId)) {
    throw new Error(`Unknown element id '${elementId}'.`);
  }

  _values.delete(elementId);
  _elements.delete(elementId);
}

/**
 * Returns the values of an input field.
 */
export function getValues(elementId: string): Values {
  const element = _elements.get(elementId)!;
  if (element === undefined) {
    throw new Error(`Expected a valid i18n input element, '${elementId}' is not i18n input field.`);
  }

  const values = _values.get(elementId)!;

  // update with current value
  values.set(element.languageId, element.element.value);

  return values;
}

/**
 * Sets the values of an input field.
 */
export function setValues(elementId: string, newValues: Values | I18nValues): void {
  const element = _elements.get(elementId);
  if (element === undefined) {
    throw new Error(`Expected a valid i18n input element, '${elementId}' is not i18n input field.`);
  }

  element.element.value = "";

  const values = new Map<LanguageId, string>(
    Object.entries(newValues).map(([languageId, value]) => {
      return [+languageId, value];
    }),
  );

  if (values.has(0)) {
    element.element.value = values.get(0)!;
    values.delete(0);

    _values.set(elementId, values);
    select(elementId, 0, true);

    return;
  }

  _values.set(elementId, values);

  element.languageId = 0;
  select(elementId, window.LANGUAGE_ID, true);
}

/**
 * Disables the i18n interface for an input field.
 */
export function disable(elementId: string): void {
  const element = _elements.get(elementId);
  if (element === undefined) {
    throw new Error(`Expected a valid element, '${elementId}' is not an i18n input field.`);
  }

  if (!element.isEnabled) {
    return;
  }

  element.isEnabled = false;

  // hide language dropdown
  const buttonContainer = element.buttonLabel.parentElement!;
  DomUtil.hide(buttonContainer);
  const dropdownContainer = buttonContainer.parentElement!;
  dropdownContainer.classList.remove("inputAddon", "dropdown");
}

/**
 * Enables the i18n interface for an input field.
 */
export function enable(elementId: string): void {
  const element = _elements.get(elementId);
  if (element === undefined) {
    throw new Error(`Expected a valid i18n input element, '${elementId}' is not i18n input field.`);
  }

  if (element.isEnabled) {
    return;
  }

  element.isEnabled = true;

  // show language dropdown
  const buttonContainer = element.buttonLabel.parentElement!;
  DomUtil.show(buttonContainer);
  const dropdownContainer = buttonContainer.parentElement!;
  dropdownContainer.classList.add("inputAddon", "dropdown");
}

/**
 * Returns true if i18n input is enabled for an input field.
 */
export function isEnabled(elementId: string): boolean {
  const element = _elements.get(elementId);
  if (element === undefined) {
    throw new Error(`Expected a valid i18n input element, '${elementId}' is not i18n input field.`);
  }

  return element.isEnabled;
}

/**
 * Returns true if the value of an i18n input field is valid.
 *
 * If the element is disabled, true is returned.
 */
export function validate(elementId: string, permitEmptyValue: boolean): boolean {
  const element = _elements.get(elementId)!;
  if (element === undefined) {
    throw new Error(`Expected a valid i18n input element, '${elementId}' is not i18n input field.`);
  }

  if (!element.isEnabled) {
    return true;
  }

  const values = _values.get(elementId)!;

  const dropdownMenu = UiDropdownSimple.getDropdownMenu(element.element.parentElement!.id)!;

  if (element.languageId) {
    values.set(element.languageId, element.element.value);
  }

  let hasEmptyValue = false;
  let hasNonEmptyValue = false;
  Array.from(dropdownMenu.children).forEach((item: HTMLElement) => {
    const languageId = ~~item.dataset.languageId!;

    if (languageId) {
      if (!values.has(languageId) || values.get(languageId)!.length === 0) {
        // input has non-empty value for previously checked language
        if (hasNonEmptyValue) {
          return false;
        }

        hasEmptyValue = true;
      } else {
        // input has empty value for previously checked language
        if (hasEmptyValue) {
          return false;
        }

        hasNonEmptyValue = true;
      }
    }
  });

  return !hasEmptyValue || permitEmptyValue;
}
