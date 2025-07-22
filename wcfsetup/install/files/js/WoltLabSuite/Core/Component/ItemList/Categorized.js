/**
 * Provides a filter input for a categorized item list.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @sice 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/StringUtil"], function (require, exports, Util_1, Language_1, StringUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.CategorizedItemList = void 0;
    class CategorizedItemList {
        #container;
        #elementList;
        #input;
        #value = "";
        #clearButton;
        #categories = [];
        #fragment;
        constructor(elementId) {
            this.#fragment = document.createDocumentFragment();
            const container = document.getElementById(elementId);
            if (!container) {
                throw new Error(`Element with ID ${elementId} not found.`);
            }
            this.#container = container;
            this.#elementList = this.#container.querySelector(".scrollableCheckboxList");
            this.#input = this.#container.querySelector(".inputAddon > input");
            this.#input.addEventListener("keydown", (event) => {
                if (event.key === "Enter") {
                    event.preventDefault();
                }
            });
            this.#input.addEventListener("keyup", () => this.#keyup());
            this.#clearButton = this.#container.querySelector(".inputAddon > .clearButton");
            this.#clearButton.addEventListener("click", (event) => {
                event.preventDefault();
                this.#input.value = "";
                this.#keyup();
            });
            this.#buildItemMap();
        }
        #buildItemMap() {
            let category = null;
            for (const li of this.#elementList.querySelectorAll(":scope > li")) {
                const input = li.querySelector('input[type="radio"]');
                if (input) {
                    if (!category) {
                        throw new Error("Input found without a preceding category.");
                    }
                    category.items.push({
                        element: li,
                        span: li.querySelector("span"),
                        text: li.textContent.trim(),
                    });
                }
                else {
                    const items = [];
                    category = {
                        items: items,
                        element: li,
                    };
                    this.#categories.push(category);
                }
            }
        }
        #keyup() {
            const value = this.#input.value.trim();
            if (this.#value === value) {
                return;
            }
            this.#value = value;
            if (this.#value) {
                this.#clearButton.classList.remove("disabled");
            }
            else {
                this.#clearButton.classList.add("disabled");
            }
            // move list into fragment before editing items, increases performance
            // by avoiding the browser to perform repaint/layout over and over again
            this.#fragment.appendChild(this.#elementList);
            this.#categories.forEach((category) => {
                this.#filterItems(category);
            });
            const hasVisibleItem = this.#elementList.querySelector(".scrollableCheckboxList > li:not([hidden])") !== null;
            this.#container.insertAdjacentElement("beforeend", this.#elementList);
            (0, Util_1.innerError)(this.#container, hasVisibleItem ? false : (0, Language_1.getPhrase)("wcf.global.filter.error.noMatches"));
        }
        #filterItems(category) {
            const regexp = new RegExp("(" + (0, StringUtil_1.escapeRegExp)(this.#value) + ")", "i");
            let hasMatchingItem = false;
            for (const item of category.items) {
                if (this.#value === "") {
                    item.span.innerHTML = item.text; // Reset highlighting
                    hasMatchingItem = true;
                    item.element.hidden = false;
                }
                else if (regexp.test(item.text)) {
                    item.span.innerHTML = item.text.replace(regexp, "<u>$1</u>");
                    item.element.hidden = false;
                    hasMatchingItem = true;
                }
                else {
                    item.element.hidden = true;
                }
            }
            category.element.hidden = !hasMatchingItem;
        }
    }
    exports.CategorizedItemList = CategorizedItemList;
});
