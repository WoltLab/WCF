/**
 * Flexible UI element featuring both a list of items and an input field with suggestion support.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Suggestion
 */

import * as Ajax from "../Ajax";
import * as Core from "../Core";
import {
  AjaxCallbackObject,
  CallbackSetup,
  DatabaseObjectActionPayload,
  DatabaseObjectActionResponse,
} from "../Ajax/Data";
import UiDropdownSimple from "./Dropdown/Simple";

class UiSuggestion implements AjaxCallbackObject {
  private readonly ajaxPayload: DatabaseObjectActionPayload;
  private readonly callbackSelect: CallbackSelect;
  private dropdownMenu: HTMLElement | null = null;
  private readonly excludedSearchValues: Set<string>;
  private readonly element: HTMLElement;
  private readonly threshold: number;
  private value = "";

  /**
   * Initializes a new suggestion input.
   */
  constructor(elementId: string, options: SuggestionOptions) {
    const element = document.getElementById(elementId);
    if (element === null) {
      throw new Error("Expected a valid element id.");
    }

    this.element = element;

    this.ajaxPayload = Core.extend(
      {
        actionName: "getSearchResultList",
        className: "",
        interfaceName: "wcf\\data\\ISearchAction",
        parameters: {
          data: {},
        },
      },
      options.ajax
    ) as DatabaseObjectActionPayload;

    if (typeof options.callbackSelect !== "function") {
      throw new Error("Expected a valid callback for option 'callbackSelect'.");
    }
    this.callbackSelect = options.callbackSelect;

    this.excludedSearchValues = new Set(
      Array.isArray(options.excludedSearchValues) ? options.excludedSearchValues : []
    );
    this.threshold = options.threshold === undefined ? 3 : options.threshold;

    this.element.addEventListener("click", (ev) => ev.preventDefault());
    this.element.addEventListener("keydown", (ev) => this.keyDown(ev));
    this.element.addEventListener("keyup", (ev) => this.keyUp(ev));
  }

  /**
   * Adds an excluded search value.
   */
  addExcludedValue(value: string): void {
    this.excludedSearchValues.add(value);
  }

  /**
   * Removes an excluded search value.
   */
  removeExcludedValue(value: string): void {
    this.excludedSearchValues.delete(value);
  }

  /**
   * Returns true if the suggestions are active.
   */
  isActive(): boolean {
    return this.dropdownMenu !== null && UiDropdownSimple.isOpen(this.element.id);
  }

  /**
   * Handles the keyboard navigation for interaction with the suggestion list.
   */
  private keyDown(event: KeyboardEvent): boolean {
    if (!this.isActive()) {
      return true;
    }

    if (["ArrowDown", "ArrowUp", "Enter", "Escape"].indexOf(event.key) === -1) {
      return true;
    }

    let active!: HTMLElement;
    let i = 0;
    const length = this.dropdownMenu!.childElementCount;
    while (i < length) {
      active = this.dropdownMenu!.children[i] as HTMLElement;
      if (active.classList.contains("active")) {
        break;
      }
      i++;
    }

    if (event.key === "Enter") {
      UiDropdownSimple.close(this.element.id);
      this.select(undefined, active);
    } else if (event.key === "Escape") {
      if (UiDropdownSimple.isOpen(this.element.id)) {
        UiDropdownSimple.close(this.element.id);
      } else {
        // let the event pass through
        return true;
      }
    } else {
      let index = 0;
      if (event.key === "ArrowUp") {
        index = (i === 0 ? length : i) - 1;
      } else if (event.key === "ArrowDown") {
        index = i + 1;
        if (index === length) index = 0;
      }
      if (index !== i) {
        active.classList.remove("active");
        this.dropdownMenu!.children[index].classList.add("active");
      }
    }

    event.preventDefault();
    return false;
  }

  /**
   * Selects an item from the list.
   */
  private select(event: MouseEvent): void;
  private select(event: undefined, item: HTMLElement): void;
  private select(event: MouseEvent | undefined, item?: HTMLElement): void {
    if (event instanceof MouseEvent) {
      const target = event.currentTarget as HTMLElement;
      item = target.parentNode as HTMLElement;
    }

    const anchor = item!.children[0] as HTMLElement;
    this.callbackSelect(this.element.id, {
      objectId: +(anchor.dataset.objectId || 0),
      value: item!.textContent || "",
      type: anchor.dataset.type || "",
    });

    if (event instanceof MouseEvent) {
      this.element.focus();
    }
  }

  /**
   * Performs a search for the input value unless it is below the threshold.
   */
  private keyUp(event: KeyboardEvent): void {
    const target = event.currentTarget as HTMLInputElement;
    const value = target.value.trim();
    if (this.value === value) {
      return;
    } else if (value.length < this.threshold) {
      if (this.dropdownMenu !== null) {
        UiDropdownSimple.close(this.element.id);
      }

      this.value = value;
      return;
    }

    this.value = value;
    Ajax.api(this, {
      parameters: {
        data: {
          excludedSearchValues: Array.from(this.excludedSearchValues),
          searchString: value,
        },
      },
    });
  }

  _ajaxSetup(): ReturnType<CallbackSetup> {
    return {
      data: this.ajaxPayload,
    };
  }

  /**
   * Handles successful Ajax requests.
   */
  _ajaxSuccess(data: DatabaseObjectActionResponse): void {
    if (this.dropdownMenu === null) {
      this.dropdownMenu = document.createElement("div");
      this.dropdownMenu.className = "dropdownMenu";
      UiDropdownSimple.initFragment(this.element, this.dropdownMenu);
    } else {
      this.dropdownMenu.innerHTML = "";
    }

    if (Array.isArray(data.returnValues)) {
      data.returnValues.forEach((item, index) => {
        const anchor = document.createElement("a");
        if (item.icon) {
          anchor.className = "box16";
          anchor.innerHTML = item.icon + " <span></span>";
          anchor.children[1].textContent = item.label;
        } else {
          anchor.textContent = item.label;
        }

        anchor.dataset.objectId = item.objectID;
        if (item.type) {
          anchor.dataset.type = item.type;
        }
        anchor.addEventListener("click", (ev) => this.select(ev));

        const listItem = document.createElement("li");
        if (index === 0) {
          listItem.className = "active";
        }
        listItem.appendChild(anchor);
        this.dropdownMenu!.appendChild(listItem);
      });

      UiDropdownSimple.open(this.element.id, true);
    } else {
      UiDropdownSimple.close(this.element.id);
    }
  }
}

export = UiSuggestion;

interface CallbackSelectData {
  objectId: number;
  value: string;
  type: string;
}

type CallbackSelect = (elementId: string, data: CallbackSelectData) => void;

interface SuggestionOptions {
  ajax: DatabaseObjectActionPayload;

  // will be executed once a value from the dropdown has been selected
  callbackSelect: CallbackSelect;

  // list of excluded search values
  excludedSearchValues?: string[];

  // minimum number of characters required to trigger a search request
  threshold?: number;
}
