/**
 * Provides suggestions using an input field, designed to work with `wcf\data\ISearchAction`.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Search/Input
 */

import * as Ajax from "../../Ajax";
import * as Core from "../../Core";
import DomUtil from "../../Dom/Util";
import UiDropdownSimple from "../Dropdown/Simple";
import { AjaxCallbackSetup, DatabaseObjectActionPayload, DatabaseObjectActionResponse } from "../../Ajax/Data";
import AjaxRequest from "../../Ajax/Request";
import { CallbackDropdownInit, CallbackSelect, SearchInputOptions } from "./Data";

class UiSearchInput {
  private activeItem?: HTMLLIElement = undefined;
  private readonly ajaxPayload: DatabaseObjectActionPayload;
  private readonly autoFocus: boolean;
  private readonly callbackDropdownInit?: CallbackDropdownInit = undefined;
  private readonly callbackSelect?: CallbackSelect = undefined;
  private readonly delay: number;
  private dropdownContainerId = "";
  private readonly element: HTMLInputElement;
  private readonly excludedSearchValues = new Set<string>();
  private list?: HTMLUListElement = undefined;
  private lastValue = "";
  private readonly minLength: number;
  private readonly noResultPlaceholder: string;
  private readonly preventSubmit: boolean;
  private request?: AjaxRequest = undefined;
  private timerDelay?: number = undefined;

  /**
   * Initializes the search input field.
   *
   * @param       {Element}       element         target input[type="text"]
   * @param       {Object}        options         search options and settings
   */
  constructor(element: HTMLInputElement, options: SearchInputOptions) {
    this.element = element;
    if (!(this.element instanceof HTMLInputElement)) {
      throw new TypeError("Expected a valid DOM element.");
    } else if (this.element.nodeName !== "INPUT" || (this.element.type !== "search" && this.element.type !== "text")) {
      throw new Error('Expected an input[type="text"].');
    }

    options = Core.extend(
      {
        ajax: {
          actionName: "getSearchResultList",
          className: "",
          interfaceName: "wcf\\data\\ISearchAction",
        },
        autoFocus: true,
        callbackDropdownInit: undefined,
        callbackSelect: undefined,
        delay: 500,
        excludedSearchValues: [],
        minLength: 3,
        noResultPlaceholder: "",
        preventSubmit: false,
      },
      options,
    ) as SearchInputOptions;

    this.ajaxPayload = options.ajax as DatabaseObjectActionPayload;
    this.autoFocus = options.autoFocus!;
    this.callbackDropdownInit = options.callbackDropdownInit;
    this.callbackSelect = options.callbackSelect;
    this.delay = options.delay!;
    options.excludedSearchValues!.forEach((value) => {
      this.addExcludedSearchValues(value);
    });
    this.minLength = options.minLength!;
    this.noResultPlaceholder = options.noResultPlaceholder!;
    this.preventSubmit = options.preventSubmit!;

    // Disable auto-complete because it collides with the suggestion dropdown.
    this.element.autocomplete = "off";

    this.element.addEventListener("keydown", (ev) => this.keydown(ev));
    this.element.addEventListener("keyup", (ev) => this.keyup(ev));
  }

  /**
   * Adds an excluded search value.
   */
  addExcludedSearchValues(value: string): void {
    this.excludedSearchValues.add(value);
  }

  /**
   * Removes a value from the excluded search values.
   */
  removeExcludedSearchValues(value: string): void {
    this.excludedSearchValues.delete(value);
  }

  /**
   * Handles the 'keydown' event.
   */
  private keydown(event: KeyboardEvent): void {
    if ((this.activeItem !== null && UiDropdownSimple.isOpen(this.dropdownContainerId)) || this.preventSubmit) {
      if (event.key === "Enter") {
        event.preventDefault();
      }
    }

    if (["ArrowUp", "ArrowDown", "Escape"].includes(event.key)) {
      event.preventDefault();
    }
  }

  /**
   * Handles the 'keyup' event, provides keyboard navigation and executes search queries.
   */
  private keyup(event: KeyboardEvent): void {
    // handle dropdown keyboard navigation
    if (this.activeItem !== null || !this.autoFocus) {
      if (UiDropdownSimple.isOpen(this.dropdownContainerId)) {
        if (event.key === "ArrowUp") {
          event.preventDefault();

          return this.keyboardPreviousItem();
        } else if (event.key === "ArrowDown") {
          event.preventDefault();

          return this.keyboardNextItem();
        } else if (event.key === "Enter") {
          event.preventDefault();

          return this.keyboardSelectItem();
        }
      } else {
        this.activeItem = undefined;
      }
    }

    // close list on escape
    if (event.key === "Escape") {
      UiDropdownSimple.close(this.dropdownContainerId);

      return;
    }

    const value = this.element.value.trim();
    if (this.lastValue === value) {
      // value did not change, e.g. previously it was "Test" and now it is "Test ",
      // but the trailing whitespace has been ignored
      return;
    }

    this.lastValue = value;

    if (value.length < this.minLength) {
      if (this.dropdownContainerId) {
        UiDropdownSimple.close(this.dropdownContainerId);
        this.activeItem = undefined;
      }

      // value below threshold
      return;
    }

    if (this.delay) {
      if (this.timerDelay) {
        window.clearTimeout(this.timerDelay);
      }

      this.timerDelay = window.setTimeout(() => {
        this.search(value);
      }, this.delay);
    } else {
      this.search(value);
    }
  }

  /**
   * Queries the server with the provided search string.
   */
  private search(value: string): void {
    if (this.request) {
      this.request.abortPrevious();
    }

    this.request = Ajax.api(this, this.getParameters(value));
  }

  /**
   * Returns additional AJAX parameters.
   */
  protected getParameters(value: string): Partial<DatabaseObjectActionPayload> {
    return {
      parameters: {
        data: {
          excludedSearchValues: this.excludedSearchValues,
          searchString: value,
        },
      },
    };
  }

  /**
   * Selects the next dropdown item.
   */
  private keyboardNextItem(): void {
    let nextItem: HTMLLIElement | undefined = undefined;

    if (this.activeItem) {
      this.activeItem.classList.remove("active");

      if (this.activeItem.nextElementSibling) {
        nextItem = this.activeItem.nextElementSibling as HTMLLIElement;
      }
    }

    this.activeItem = nextItem || (this.list!.children[0] as HTMLLIElement);
    this.activeItem.classList.add("active");
  }

  /**
   * Selects the previous dropdown item.
   */
  private keyboardPreviousItem(): void {
    let nextItem: HTMLLIElement | undefined = undefined;

    if (this.activeItem) {
      this.activeItem.classList.remove("active");

      if (this.activeItem.previousElementSibling) {
        nextItem = this.activeItem.previousElementSibling as HTMLLIElement;
      }
    }

    this.activeItem = nextItem || (this.list!.children[this.list!.childElementCount - 1] as HTMLLIElement);
    this.activeItem.classList.add("active");
  }

  /**
   * Selects the active item from the dropdown.
   */
  private keyboardSelectItem(): void {
    this.selectItem(this.activeItem!);
  }

  /**
   * Selects an item from the dropdown by clicking it.
   */
  private clickSelectItem(event: MouseEvent): void {
    this.selectItem(event.currentTarget as HTMLLIElement);
  }

  /**
   * Selects an item.
   */
  private selectItem(item: HTMLLIElement): void {
    if (this.callbackSelect && !this.callbackSelect(item)) {
      this.element.value = "";
    } else {
      this.element.value = item.dataset.label || "";
    }

    this.activeItem = undefined;
    UiDropdownSimple.close(this.dropdownContainerId);
  }

  /**
   * Handles successful AJAX requests.
   */
  _ajaxSuccess(data: DatabaseObjectActionResponse): void {
    let createdList = false;
    if (!this.list) {
      this.list = document.createElement("ul");
      this.list.className = "dropdownMenu";

      createdList = true;

      if (typeof this.callbackDropdownInit === "function") {
        this.callbackDropdownInit(this.list);
      }
    } else {
      // reset current list
      this.list.innerHTML = "";
    }

    if (typeof data.returnValues === "object") {
      const callbackClick = this.clickSelectItem.bind(this);
      Object.keys(data.returnValues).forEach((key) => {
        const listItem = this.createListItem(data.returnValues[key]);

        listItem.addEventListener("click", callbackClick);
        this.list!.appendChild(listItem);
      });
    }

    if (createdList) {
      this.element.insertAdjacentElement("afterend", this.list);
      const parent = this.element.parentElement!;
      UiDropdownSimple.initFragment(parent, this.list);

      this.dropdownContainerId = DomUtil.identify(parent);
    }

    if (this.dropdownContainerId) {
      this.activeItem = undefined;

      if (!this.list.childElementCount && !this.handleEmptyResult()) {
        UiDropdownSimple.close(this.dropdownContainerId);
      } else {
        UiDropdownSimple.open(this.dropdownContainerId, true, this.element);

        // mark first item as active
        const firstChild = this.list.childElementCount ? (this.list.children[0] as HTMLLIElement) : undefined;
        if (this.autoFocus && firstChild && ~~(firstChild.dataset.objectId || "")) {
          this.activeItem = firstChild;
          this.activeItem.classList.add("active");
        }
      }
    }
  }

  /**
   * Handles an empty result set, return a boolean false to hide the dropdown.
   */
  private handleEmptyResult(): boolean {
    if (!this.noResultPlaceholder) {
      return false;
    }

    const listItem = document.createElement("li");
    listItem.className = "dropdownText";

    const span = document.createElement("span");
    span.textContent = this.noResultPlaceholder;
    listItem.appendChild(span);

    this.list!.appendChild(listItem);

    return true;
  }

  /**
   * Creates an list item from response data.
   */
  protected createListItem(item: ListItemData): HTMLLIElement {
    const listItem = document.createElement("li");
    listItem.dataset.objectId = item.objectID.toString();
    listItem.dataset.label = item.label;

    const span = document.createElement("span");
    span.textContent = item.label;
    listItem.appendChild(span);

    return listItem;
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: this.ajaxPayload,
    };
  }
}

Core.enableLegacyInheritance(UiSearchInput);

export = UiSearchInput;

interface ListItemData {
  label: string;
  objectID: number;
}
