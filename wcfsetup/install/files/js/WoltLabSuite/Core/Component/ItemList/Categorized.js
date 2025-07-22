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
                    li.addEventListener("click", (event) => {
                        this.#categoryClick(event, li, items);
                    });
                }
            }
        }
        #categoryClick(event, li, items) {
            event.preventDefault();
            const isOpen = !this.#categoryIsOpen(li);
            li.dataset.open = isOpen ? "true" : "false";
            li.querySelector("fa-icon").setIcon(isOpen ? "chevron-down" : "chevron-right");
            this.#showItems({
                items: items,
                element: li,
            });
        }
        #categoryIsOpen(category) {
            return category.dataset.open === "true";
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
                this.#showItems(category);
            });
            const hasVisibleItems = Array.from(this.#elementList.querySelectorAll(".scrollableCheckboxList > li")).some((li) => {
                return !(0, Util_1.isHidden)(li);
            });
            this.#container.insertAdjacentElement("beforeend", this.#elementList);
            (0, Util_1.innerError)(this.#container, hasVisibleItems ? false : (0, Language_1.getPhrase)("wcf.global.filter.error.noMatches"));
        }
        #showItems(category) {
            const categoryIsOpen = this.#categoryIsOpen(category.element);
            const regexp = new RegExp("(" + (0, StringUtil_1.escapeRegExp)(this.#value) + ")", "i");
            if (this.#value === "") {
                (0, Util_1.show)(category.element);
                category.items.forEach((item) => {
                    item.span.innerHTML = item.text; // Reset highlighting
                    if (categoryIsOpen) {
                        (0, Util_1.show)(item.element);
                    }
                    else {
                        (0, Util_1.hide)(item.element);
                    }
                });
            }
            else {
                if (category.items.some((item) => regexp.test(item.text))) {
                    (0, Util_1.show)(category.element);
                    category.items.forEach((item) => {
                        if (categoryIsOpen && regexp.test(item.text)) {
                            item.span.innerHTML = item.text.replace(regexp, "<u>$1</u>");
                            (0, Util_1.show)(item.element);
                        }
                        else {
                            (0, Util_1.hide)(item.element);
                        }
                    });
                }
                else {
                    (0, Util_1.hide)(category.element);
                    category.items.forEach((item) => {
                        (0, Util_1.hide)(item.element);
                    });
                }
            }
        }
    }
    exports.CategorizedItemList = CategorizedItemList;
});
