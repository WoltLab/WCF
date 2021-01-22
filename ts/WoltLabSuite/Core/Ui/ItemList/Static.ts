/**
 * Flexible UI element featuring both a list of items and an input field.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/ItemList/Static
 */

import * as Core from "../../Core";
import * as DomTraverse from "../../Dom/Traverse";
import * as Language from "../../Language";
import UiDropdownSimple from "../Dropdown/Simple";

export type CallbackChange = (elementId: string, values: ItemData[]) => void;
export type CallbackSubmit = (form: HTMLFormElement, values: ItemData[]) => void;

export interface ItemListStaticOptions {
  maxItems: number;
  maxLength: number;
  isCSV: boolean;
  callbackChange: CallbackChange | null;
  callbackSubmit: CallbackSubmit | null;
  submitFieldName: string;
}

type ItemListInputElement = HTMLInputElement | HTMLTextAreaElement;

export interface ItemData {
  objectId: number;
  value: string;
  type?: string;
}

type PlainValue = string;

type ItemDataOrPlainValue = ItemData | PlainValue;

interface UiData {
  element: HTMLInputElement | HTMLTextAreaElement;
  list: HTMLOListElement;
  shadow?: HTMLInputElement;
  values: string[];
}

interface ElementData {
  dropdownMenu: HTMLElement | null;
  element: ItemListInputElement;
  list: HTMLOListElement;
  listItem: HTMLElement;
  options: ItemListStaticOptions;
  shadow?: HTMLInputElement;
}

const _data = new Map<string, ElementData>();

/**
 * Creates the DOM structure for target element. If `element` is a `<textarea>`
 * it will be automatically replaced with an `<input>` element.
 */
function createUI(element: ItemListInputElement, options: ItemListStaticOptions): UiData {
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

  element.addEventListener("keydown", (ev: KeyboardEvent) => keyDown(ev));
  element.addEventListener("keypress", (ev: KeyboardEvent) => keyPress(ev));
  element.addEventListener("keyup", (ev: KeyboardEvent) => keyUp(ev));
  element.addEventListener("paste", (ev: ClipboardEvent) => paste(ev));
  element.addEventListener("blur", (ev: FocusEvent) => blur(ev));

  element.insertAdjacentElement("beforebegin", list);
  listItem.appendChild(element);

  if (options.maxLength !== -1) {
    element.maxLength = options.maxLength;
  }

  let shadow: HTMLInputElement | undefined;
  let values: string[] = [];
  if (options.isCSV) {
    shadow = document.createElement("input");
    shadow.className = "itemListInputShadow";
    shadow.type = "hidden";
    shadow.name = element.name;
    element.removeAttribute("name");

    list.insertAdjacentElement("beforebegin", shadow);

    values = element.value
      .split(",")
      .map((s) => s.trim())
      .filter((s) => s.length > 0);

    if (element.nodeName === "TEXTAREA") {
      const inputElement = document.createElement("input");
      inputElement.type = "text";
      element.parentElement!.insertBefore(inputElement, element);
      inputElement.id = element.id;

      element.remove();
      element = inputElement;
    }
  }

  return {
    element,
    list,
    shadow,
    values,
  };
}

/**
 * Enforces the maximum number of items.
 */
function handleLimit(elementId: string): void {
  const data = _data.get(elementId)!;
  if (data.options.maxItems === -1) {
    return;
  }

  if (data.list.childElementCount - 1 < data.options.maxItems) {
    if (data.element.disabled) {
      data.element.disabled = false;
      data.element.removeAttribute("placeholder");
    }
  } else if (!data.element.disabled) {
    data.element.disabled = true;
    data.element.placeholder = Language.get("wcf.global.form.input.maxItems");
  }
}

/**
 * Sets the active item list id and handles keyboard access to remove an existing item.
 */
function keyDown(event: KeyboardEvent): void {
  const input = event.currentTarget as HTMLInputElement;
  const lastItem = input.parentElement!.previousElementSibling as HTMLElement;

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
 * Handles the `[ENTER]` and `[,]` key to add an item to the list.
 */
function keyPress(event: KeyboardEvent): void {
  if (event.key === "Enter" || event.key === "Comma") {
    event.preventDefault();

    const input = event.currentTarget as HTMLInputElement;
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
  const input = event.currentTarget as HTMLInputElement;

  const text = event.clipboardData!.getData("text/plain");
  text
    .split(",")
    .map((s) => s.trim())
    .filter((s) => s.length > 0)
    .forEach((s) => {
      addItem(input.id, { objectId: 0, value: s });
    });

  event.preventDefault();
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
function addItem(elementId: string, value: ItemData, forceRemoveIcon?: boolean): void {
  const data = _data.get(elementId)!;

  const listItem = document.createElement("li");
  listItem.className = "item";

  const content = document.createElement("span");
  content.className = "content";
  content.dataset.objectId = value.objectId.toString();
  content.textContent = value.value;
  listItem.appendChild(content);

  if (forceRemoveIcon || !data.element.disabled) {
    const button = document.createElement("a");
    button.className = "icon icon16 fa-times";
    button.addEventListener("click", (ev) => removeItem(ev));
    listItem.appendChild(button);
  }

  data.list.insertBefore(listItem, data.listItem);
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
function removeItem(item: MouseEvent | HTMLElement, noFocus?: boolean): void {
  if (item instanceof Event) {
    item = (item.currentTarget as HTMLElement).parentElement as HTMLElement;
  }

  const parent = item.parentElement!;
  const elementId = parent.dataset.elementId!;
  const data = _data.get(elementId)!;

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

  const values = getValues(data.element.id);

  data.shadow!.value = values.map((v) => v.value).join(",");

  return values;
}

/**
 * Handles the blur event.
 */
function blur(event: FocusEvent): void {
  const input = event.currentTarget as HTMLInputElement;

  window.setTimeout(() => {
    const value = input.value.trim();
    if (value.length) {
      addItem(input.id, { objectId: 0, value: value });
    }
  }, 100);
}

/**
 * Initializes an item list.
 *
 * The `values` argument must be empty or contain a list of strings or object, e.g.
 * `['foo', 'bar']` or `[{ objectId: 1337, value: 'baz'}, {...}]`
 */
export function init(elementId: string, values: ItemDataOrPlainValue[], opts: Partial<ItemListStaticOptions>): void {
  const element = document.getElementById(elementId) as HTMLInputElement | HTMLTextAreaElement;
  if (element === null) {
    throw new Error("Expected a valid element id, '" + elementId + "' is invalid.");
  }

  // remove data from previous instance
  if (_data.has(elementId)) {
    const tmp = _data.get(elementId)!;

    Object.values(tmp).forEach((value) => {
      if (value instanceof HTMLElement && value.parentElement) {
        value.remove();
      }
    });

    UiDropdownSimple.destroy(elementId);
    _data.delete(elementId);
  }

  const options = Core.extend(
    {
      // maximum number of items this list may contain, `-1` for infinite
      maxItems: -1,
      // maximum length of an item value, `-1` for infinite
      maxLength: -1,

      // initial value will be interpreted as comma separated value and submitted as such
      isCSV: false,

      // will be invoked whenever the items change, receives the element id first and list of values second
      callbackChange: null,
      // callback once the form is about to be submitted
      callbackSubmit: null,
      // value may contain the placeholder `{$objectId}`
      submitFieldName: "",
    },
    opts,
  ) as ItemListStaticOptions;

  const form = DomTraverse.parentByTag(element, "FORM") as HTMLFormElement;
  if (form !== null) {
    if (!options.isCSV) {
      if (!options.submitFieldName.length && typeof options.callbackSubmit !== "function") {
        throw new Error(
          "Expected a valid function for option 'callbackSubmit', a non-empty value for option 'submitFieldName' or enabling the option 'submitFieldCSV'.",
        );
      }

      form.addEventListener("submit", () => {
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
    }
  }

  const data = createUI(element, options);
  _data.set(elementId, {
    dropdownMenu: null,
    element: data.element,
    list: data.list,
    listItem: data.element.parentElement!,
    options: options,
    shadow: data.shadow,
  });

  values = data.values.length ? data.values : values;
  if (Array.isArray(values)) {
    const forceRemoveIcon = !data.element.disabled;

    values.forEach((value) => {
      if (typeof value === "string") {
        value = { objectId: 0, value: value };
      }

      addItem(elementId, value, forceRemoveIcon);
    });
  }
}

/**
 * Returns the list of current values.
 */
export function getValues(elementId: string): ItemData[] {
  if (!_data.has(elementId)) {
    throw new Error(`Element id '${elementId}' is unknown.`);
  }

  const data = _data.get(elementId)!;

  const values: ItemData[] = [];
  data.list.querySelectorAll(".item > span").forEach((span: HTMLElement) => {
    values.push({
      objectId: ~~span.dataset.objectId!,
      value: span.textContent!,
    });
  });

  return values;
}

/**
 * Sets the list of current values.
 */
export function setValues(elementId: string, values: ItemData[]): void {
  if (!_data.has(elementId)) {
    throw new Error(`Element id '${elementId}' is unknown.`);
  }

  const data = _data.get(elementId)!;

  // remove all existing items first
  const items = DomTraverse.childrenByClass(data.list, "item");
  items.forEach((item: HTMLElement) => removeItem(item, true));

  // add new items
  values.forEach((v) => addItem(elementId, v));
}
