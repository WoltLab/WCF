/**
 * Flexible UI element featuring both a list of items and an input field with suggestion support.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/ItemList
 */

import * as Core from "../Core";
import * as DomTraverse from "../Dom/Traverse";
import * as Language from "../Language";
import UiSuggestion from "./Suggestion";
import UiDropdownSimple from "./Dropdown/Simple";
import { DatabaseObjectActionPayload } from "../Ajax/Data";
import DomUtil from "../Dom/Util";

const _data = new Map<string, ElementData>();

/**
 * Creates the DOM structure for target element. If `element` is a `<textarea>`
 * it will be automatically replaced with an `<input>` element.
 */
function createUI(element: ItemListInputElement, options: ItemListOptions): UiData {
  const parentElement = element.parentElement!;

  const list = document.createElement("ol");
  list.className = "inputItemList" + (element.disabled ? " disabled" : "");
  list.dataset.elementId = element.id;
  list.addEventListener("click", (event) => {
    if (event.target === list) {
      element.focus();
    }
  });

  const listItem = document.createElement("li");
  listItem.className = "input";
  list.appendChild(listItem);
  element.addEventListener("keydown", keyDown);
  element.addEventListener("keypress", keyPress);
  element.addEventListener("keyup", keyUp);
  element.addEventListener("paste", paste);

  const hasFocus = element === document.activeElement;
  if (hasFocus) {
    element.blur();
  }
  element.addEventListener("blur", blur);
  parentElement.insertBefore(list, element);
  listItem.appendChild(element);

  if (hasFocus) {
    window.setTimeout(() => {
      element.focus();
    }, 1);
  }

  if (options.maxLength !== -1) {
    element.maxLength = options.maxLength;
  }

  const limitReached = document.createElement("span");
  limitReached.className = "inputItemListLimitReached";
  limitReached.textContent = Language.get("wcf.global.form.input.maxItems");
  DomUtil.hide(limitReached);
  listItem.appendChild(limitReached);

  let shadow: HTMLInputElement | null = null;
  const values: string[] = [];
  if (options.isCSV) {
    shadow = document.createElement("input");
    shadow.className = "itemListInputShadow";
    shadow.type = "hidden";
    shadow.name = element.name;
    element.removeAttribute("name");
    list.parentNode!.insertBefore(shadow, list);

    element.value.split(",").forEach((value) => {
      value = value.trim();
      if (value) {
        values.push(value);
      }
    });

    if (element.nodeName === "TEXTAREA") {
      const inputElement = document.createElement("input");
      inputElement.type = "text";
      parentElement.insertBefore(inputElement, element);
      inputElement.id = element.id;

      element.remove();
      element = inputElement;
    }
  }

  return {
    element: element,
    limitReached: limitReached,
    list: list,
    shadow: shadow,
    values: values,
  };
}

/**
 * Returns true if the input accepts new items.
 */
function acceptsNewItems(elementId: string): boolean {
  const data = _data.get(elementId)!;
  if (data.options.maxItems === -1) {
    return true;
  }

  return data.list.childElementCount - 1 < data.options.maxItems;
}

/**
 * Enforces the maximum number of items.
 */
function handleLimit(elementId: string): void {
  const data = _data.get(elementId)!;
  if (acceptsNewItems(elementId)) {
    DomUtil.show(data.element);
    DomUtil.hide(data.limitReached);
  } else {
    DomUtil.hide(data.element);
    DomUtil.show(data.limitReached);
  }
}

/**
 * Sets the active item list id and handles keyboard access to remove an existing item.
 */
function keyDown(event: KeyboardEvent): void {
  const input = event.currentTarget as HTMLInputElement;

  const lastItem = input.parentElement!.previousElementSibling as HTMLElement | null;
  if (event.key === "Backspace") {
    if (input.value.length === 0) {
      if (lastItem !== null) {
        if (lastItem.classList.contains("active")) {
          removeItem(lastItem);
        } else {
          lastItem.classList.add("active");
        }
      }
    }
  } else if (event.key === "Escape") {
    if (lastItem !== null && lastItem.classList.contains("active")) {
      lastItem.classList.remove("active");
    }
  }
}

/**
 * Handles the `[ENTER]` and `[,]` key to add an item to the list unless it is restricted.
 */
function keyPress(event: KeyboardEvent): void {
  if (event.key === "Enter" || event.key === ",") {
    event.preventDefault();

    const input = event.currentTarget as HTMLInputElement;
    if (_data.get(input.id)!.options.restricted) {
      // restricted item lists only allow results from the dropdown to be picked
      return;
    }
    const value = input.value.trim();
    if (value.length) {
      addItem(input.id, { objectId: 0, value: value });
    }
  }
}

/**
 * Splits comma-separated values being pasted into the input field.
 */
function paste(event: ClipboardEvent): void {
  event.preventDefault();

  const text = event.clipboardData!.getData("text/plain");

  const element = event.currentTarget as HTMLInputElement;
  const elementId = element.id;
  const maxLength = +element.maxLength;
  text.split(/,/).forEach((item) => {
    item = item.trim();
    if (maxLength && item.length > maxLength) {
      // truncating items provides a better UX than throwing an error or silently discarding it
      item = item.substr(0, maxLength);
    }

    if (item.length > 0 && acceptsNewItems(elementId)) {
      addItem(elementId, { objectId: 0, value: item });
    }
  });
}

/**
 * Handles the keyup event to unmark an item for deletion.
 */
function keyUp(event: KeyboardEvent): void {
  const input = event.currentTarget as HTMLInputElement;
  if (input.value.length > 0) {
    const lastItem = input.parentElement!.previousElementSibling;
    if (lastItem !== null) {
      lastItem.classList.remove("active");
    }
  }
}

/**
 * Adds an item to the list.
 */
function addItem(elementId: string, value: ItemData): void {
  const data = _data.get(elementId)!;
  const listItem = document.createElement("li");
  listItem.className = "item";

  const content = document.createElement("span");
  content.className = "content";
  content.dataset.objectId = value.objectId.toString();
  if (value.type) {
    content.dataset.type = value.type;
  }
  content.textContent = value.value;
  listItem.appendChild(content);

  if (!data.element.disabled) {
    const button = document.createElement("a");
    button.className = "icon icon16 fa-times";
    button.addEventListener("click", removeItem);
    listItem.appendChild(button);
  }

  data.list.insertBefore(listItem, data.listItem);
  data.suggestion.addExcludedValue(value.value);
  data.element.value = "";
  if (!data.element.disabled) {
    handleLimit(elementId);
  }

  let values = syncShadow(data);
  if (typeof data.options.callbackChange === "function") {
    if (values === null) {
      values = getValues(elementId);
    }

    data.options.callbackChange(elementId, values);
  }
}

/**
 * Removes an item from the list.
 */
function removeItem(item: Event | HTMLElement, noFocus?: boolean): void {
  if (item instanceof Event) {
    const target = item.currentTarget as HTMLElement;
    item = target.parentElement!;
  }

  const parent = item.parentElement!;
  const elementId = parent.dataset.elementId || "";
  const data = _data.get(elementId)!;
  if (item.children[0].textContent) {
    data.suggestion.removeExcludedValue(item.children[0].textContent);
  }

  item.remove();

  if (!noFocus) {
    data.element.focus();
  }

  handleLimit(elementId);

  let values = syncShadow(data);
  if (typeof data.options.callbackChange === "function") {
    if (values === null) {
      values = getValues(elementId);
    }

    data.options.callbackChange(elementId, values);
  }
}

/**
 * Synchronizes the shadow input field with the current list item values.
 */
function syncShadow(data: ElementData): ItemData[] | null {
  if (!data.options.isCSV) {
    return null;
  }

  if (typeof data.options.callbackSyncShadow === "function") {
    return data.options.callbackSyncShadow(data);
  }

  const values = getValues(data.element.id);

  data.shadow!.value = getValues(data.element.id)
    .map((value) => value.value)
    .join(",");

  return values;
}

/**
 * Handles the blur event.
 */
function blur(event: FocusEvent): void {
  const input = event.currentTarget as HTMLInputElement;
  const data = _data.get(input.id)!;

  if (data.options.restricted) {
    // restricted item lists only allow results from the dropdown to be picked
    return;
  }

  const value = input.value.trim();
  if (value.length) {
    if (!data.suggestion || !data.suggestion.isActive()) {
      addItem(input.id, { objectId: 0, value: value });
    }
  }
}

/**
 * Initializes an item list.
 *
 * The `values` argument must be empty or contain a list of strings or object, e.g.
 * `['foo', 'bar']` or `[{ objectId: 1337, value: 'baz'}, {...}]`
 */
export function init(elementId: string, values: ItemDataOrPlainValue[], opts: Partial<ItemListOptions>): void {
  const element = document.getElementById(elementId) as ItemListInputElement;
  if (element === null) {
    throw new Error("Expected a valid element id, '" + elementId + "' is invalid.");
  }

  // remove data from previous instance
  if (_data.has(elementId)) {
    const tmp = _data.get(elementId)!;
    Object.keys(tmp).forEach((key) => {
      const el = tmp[key];
      if (el instanceof Element && el.parentNode) {
        el.remove();
      }
    });

    UiDropdownSimple.destroy(elementId);
    _data.delete(elementId);
  }

  const options = Core.extend(
    {
      // search parameters for suggestions
      ajax: {
        actionName: "getSearchResultList",
        className: "",
        data: {},
      },
      // list of excluded string values, e.g. `['ignore', 'these strings', 'when', 'searching']`
      excludedSearchValues: [],
      // maximum number of items this list may contain, `-1` for infinite
      maxItems: -1,
      // maximum length of an item value, `-1` for infinite
      maxLength: -1,
      // disallow custom values, only values offered by the suggestion dropdown are accepted
      restricted: false,
      // initial value will be interpreted as comma separated value and submitted as such
      isCSV: false,
      // will be invoked whenever the items change, receives the element id first and list of values second
      callbackChange: null,
      // callback once the form is about to be submitted
      callbackSubmit: null,
      // Callback for the custom shadow synchronization.
      callbackSyncShadow: null,
      // Callback to set values during the setup.
      callbackSetupValues: null,
      // value may contain the placeholder `{$objectId}`
      submitFieldName: "",
    },
    opts
  ) as ItemListOptions;

  const form = DomTraverse.parentByTag(element, "FORM") as HTMLFormElement;
  if (form !== null) {
    if (!options.isCSV) {
      if (!options.submitFieldName.length && typeof options.callbackSubmit !== "function") {
        throw new Error(
          "Expected a valid function for option 'callbackSubmit', a non-empty value for option 'submitFieldName' or enabling the option 'submitFieldCSV'."
        );
      }

      form.addEventListener("submit", () => {
        if (acceptsNewItems(elementId)) {
          const value = _data.get(elementId)!.element.value.trim();
          if (value.length) {
            addItem(elementId, { objectId: 0, value: value });
          }
        }

        const values = getValues(elementId);
        if (options.submitFieldName.length) {
          values.forEach((value) => {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = options.submitFieldName.replace("{$objectId}", value.objectId.toString());
            input.value = value.value;
            form.appendChild(input);
          });
        } else {
          options.callbackSubmit!(form, values);
        }
      });
    } else {
      form.addEventListener("submit", () => {
        if (acceptsNewItems(elementId)) {
          const value = _data.get(elementId)!.element.value.trim();
          if (value.length) {
            addItem(elementId, { objectId: 0, value: value });
          }
        }
      });
    }
  }

  const data = createUI(element, options);

  const suggestion = new UiSuggestion(elementId, {
    ajax: options.ajax as DatabaseObjectActionPayload,
    callbackSelect: addItem,
    excludedSearchValues: options.excludedSearchValues,
  });

  _data.set(elementId, {
    dropdownMenu: null,
    element: data.element,
    limitReached: data.limitReached,
    list: data.list,
    listItem: data.element.parentElement!,
    options: options,
    shadow: data.shadow,
    suggestion: suggestion,
  });

  if (options.callbackSetupValues) {
    values = options.callbackSetupValues();
  } else {
    values = data.values.length ? data.values : values;
  }

  if (Array.isArray(values)) {
    values.forEach((value) => {
      if (typeof value === "string") {
        value = { objectId: 0, value: value };
      }

      addItem(elementId, value);
    });
  }
}

/**
 * Returns the list of current values.
 */
export function getValues(elementId: string): ItemData[] {
  const data = _data.get(elementId);
  if (!data) {
    throw new Error("Element id '" + elementId + "' is unknown.");
  }

  const values: ItemData[] = [];
  data.list.querySelectorAll(".item > span").forEach((span: HTMLSpanElement) => {
    values.push({
      objectId: +(span.dataset.objectId || ""),
      value: span.textContent!.trim(),
      type: span.dataset.type,
    });
  });

  return values;
}

/**
 * Sets the list of current values.
 */
export function setValues(elementId: string, values: ItemData[]): void {
  const data = _data.get(elementId);
  if (!data) {
    throw new Error("Element id '" + elementId + "' is unknown.");
  }

  // remove all existing items first
  DomTraverse.childrenByClass(data.list, "item").forEach((item: HTMLElement) => {
    removeItem(item, true);
  });

  // add new items
  values.forEach((value) => {
    addItem(elementId, value);
  });
}

type ItemListInputElement = HTMLInputElement | HTMLTextAreaElement;

export interface ItemData {
  objectId: number;
  value: string;
  type?: string;
}

type PlainValue = string;

type ItemDataOrPlainValue = ItemData | PlainValue;

export type CallbackChange = (elementId: string, values: ItemData[]) => void;

export type CallbackSetupValues = () => ItemDataOrPlainValue[];

export type CallbackSubmit = (form: HTMLFormElement, values: ItemData[]) => void;

export type CallbackSyncShadow = (data: ElementData) => ItemData[];

export interface ItemListOptions {
  // search parameters for suggestions
  ajax: {
    actionName?: string;
    className: string;
    parameters?: object;
  };

  // list of excluded string values, e.g. `['ignore', 'these strings', 'when', 'searching']`
  excludedSearchValues: string[];

  // maximum number of items this list may contain, `-1` for infinite
  maxItems: number;

  // maximum length of an item value, `-1` for infinite
  maxLength: number;

  // disallow custom values, only values offered by the suggestion dropdown are accepted
  restricted: boolean;

  // initial value will be interpreted as comma separated value and submitted as such
  isCSV: boolean;

  // will be invoked whenever the items change, receives the element id first and list of values second
  callbackChange: CallbackChange | null;

  // callback once the form is about to be submitted
  callbackSubmit: CallbackSubmit | null;

  // Callback for the custom shadow synchronization.
  callbackSyncShadow: CallbackSyncShadow | null;

  // Callback to set values during the setup.
  callbackSetupValues: CallbackSetupValues | null;

  // value may contain the placeholder `{$objectId}`
  submitFieldName: string;
}

export interface ElementData {
  dropdownMenu: HTMLElement | null;
  element: ItemListInputElement;
  limitReached: HTMLSpanElement;
  list: HTMLElement;
  listItem: HTMLElement;
  options: ItemListOptions;
  shadow: HTMLInputElement | null;
  suggestion: UiSuggestion;
}

interface UiData {
  element: ItemListInputElement;
  limitReached: HTMLSpanElement;
  list: HTMLOListElement;
  shadow: HTMLInputElement | null;
  values: string[];
}
