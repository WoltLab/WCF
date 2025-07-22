/**
 * Provides a filter input for a categorized item list.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @sice 6.3
 */

import { innerError, show, hide, isHidden } from "WoltLabSuite/Core/Dom/Util";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { escapeRegExp } from "WoltLabSuite/Core/StringUtil";

type Item = {
  element: HTMLLIElement;
  span: HTMLSpanElement;
  text: string;
};

type Category = {
  items: Item[];
  element: HTMLLIElement;
};

export class CategorizedItemList {
  readonly #container: HTMLElement;
  readonly #elementList: HTMLUListElement;
  readonly #input: HTMLInputElement;
  #value: string = "";
  readonly #clearButton: HTMLButtonElement;
  #categories: Category[] = [];
  readonly #fragment: DocumentFragment;

  constructor(elementId: string) {
    this.#fragment = document.createDocumentFragment();

    const container = document.getElementById(elementId);
    if (!container) {
      throw new Error(`Element with ID ${elementId} not found.`);
    }

    this.#container = container;
    this.#elementList = this.#container.querySelector<HTMLUListElement>(".scrollableCheckboxList")!;

    this.#input = this.#container.querySelector(".inputAddon > input") as HTMLInputElement;
    this.#input.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
      }
    });
    this.#input.addEventListener("keyup", () => this.#keyup());

    this.#clearButton = this.#container.querySelector<HTMLButtonElement>(".inputAddon > .clearButton")!;
    this.#clearButton.addEventListener("click", (event) => {
      event.preventDefault();

      this.#input.value = "";
      this.#keyup();
    });

    this.#buildItemMap();
  }

  #buildItemMap(): void {
    let category: Category | null = null;
    for (const li of this.#elementList.querySelectorAll<HTMLLIElement>(":scope > li")) {
      const input = li.querySelector('input[type="radio"]');
      if (input) {
        if (!category) {
          throw new Error("Input found without a preceding category.");
        }

        category.items.push({
          element: li,
          span: li.querySelector("span")!,
          text: li.textContent!.trim(),
        });
      } else {
        const items: Item[] = [];
        category = {
          items: items,
          element: li,
        };
        this.#categories.push(category);

        li.addEventListener("click", (event) => {
          this.#categoryClick(event, li, items);
        });
      }
    }
  }

  #categoryClick(event: MouseEvent, li: HTMLLIElement, items: Item[]): void {
    event.preventDefault();

    const isOpen = !this.#categoryIsOpen(li);
    li.dataset.open = isOpen ? "true" : "false";

    li.querySelector("fa-icon")!.setIcon(isOpen ? "chevron-down" : "chevron-right");

    this.#showItems({
      items: items,
      element: li,
    });
  }

  #categoryIsOpen(category: HTMLLIElement): boolean {
    return category.dataset.open === "true";
  }

  #keyup(): void {
    const value = this.#input.value.trim();
    if (this.#value === value) {
      return;
    }

    this.#value = value;

    if (this.#value) {
      this.#clearButton.classList.remove("disabled");
    } else {
      this.#clearButton.classList.add("disabled");
    }

    // move list into fragment before editing items, increases performance
    // by avoiding the browser to perform repaint/layout over and over again
    this.#fragment.appendChild(this.#elementList);

    this.#categories.forEach((category) => {
      this.#showItems(category);
    });

    const hasVisibleItems = Array.from(
      this.#elementList.querySelectorAll<HTMLLIElement>(".scrollableCheckboxList > li"),
    ).some((li) => {
      return !isHidden(li);
    });

    this.#container.insertAdjacentElement("beforeend", this.#elementList);

    innerError(this.#container, hasVisibleItems ? false : getPhrase("wcf.global.filter.error.noMatches"));
  }

  #showItems(category: Category): void {
    const categoryIsOpen = this.#categoryIsOpen(category.element);
    const regexp = new RegExp("(" + escapeRegExp(this.#value) + ")", "i");

    if (this.#value === "") {
      show(category.element);

      category.items.forEach((item) => {
        item.span.innerHTML = item.text; // Reset highlighting

        if (categoryIsOpen) {
          show(item.element);
        } else {
          hide(item.element);
        }
      });
    } else {
      if (category.items.some((item) => regexp.test(item.text))) {
        show(category.element);

        category.items.forEach((item) => {
          if (categoryIsOpen && regexp.test(item.text)) {
            item.span.innerHTML = item.text.replace(regexp, "<u>$1</u>");

            show(item.element);
          } else {
            hide(item.element);
          }
        });
      } else {
        hide(category.element);

        category.items.forEach((item) => {
          hide(item.element);
        });
      }
    }
  }
}
