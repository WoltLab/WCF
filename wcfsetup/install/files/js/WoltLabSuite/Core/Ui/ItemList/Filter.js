/**
 * Provides a filter input for checkbox lists.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/ItemList/Filter
 */
define(["require", "exports", "tslib", "../../Core", "../../Dom/Util", "../../Language", "../../StringUtil", "../Dropdown/Simple"], function (require, exports, tslib_1, Core, Util_1, Language, StringUtil, Simple_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    StringUtil = tslib_1.__importStar(StringUtil);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class UiItemListFilter {
        /**
         * Creates a new filter input.
         *
         * @param       {string}        elementId       list element id
         * @param       {Object=}       options         options
         */
        constructor(elementId, options) {
            this._dropdownId = "";
            this._dropdown = undefined;
            this._fragment = undefined;
            this._items = new Set();
            this._value = "";
            this._options = Core.extend({
                callbackPrepareItem: undefined,
                enableVisibilityFilter: true,
                filterPosition: "bottom",
            }, options);
            if (this._options.filterPosition !== "top") {
                this._options.filterPosition = "bottom";
            }
            const element = document.getElementById(elementId);
            if (element === null) {
                throw new Error("Expected a valid element id, '" + elementId + "' does not match anything.");
            }
            else if (!element.classList.contains("scrollableCheckboxList") &&
                typeof this._options.callbackPrepareItem !== "function") {
                throw new Error("Filter only works with elements with the CSS class 'scrollableCheckboxList'.");
            }
            if (typeof this._options.callbackPrepareItem !== "function") {
                this._options.callbackPrepareItem = (item) => this._prepareItem(item);
            }
            element.dataset.filter = "showAll";
            const container = document.createElement("div");
            container.className = "itemListFilter";
            element.insertAdjacentElement("beforebegin", container);
            container.appendChild(element);
            const inputAddon = document.createElement("div");
            inputAddon.className = "inputAddon";
            const input = document.createElement("input");
            input.className = "long";
            input.type = "text";
            input.placeholder = Language.get("wcf.global.filter.placeholder");
            input.addEventListener("keydown", (event) => {
                if (event.key === "Enter") {
                    event.preventDefault();
                }
            });
            input.addEventListener("keyup", () => this._keyup());
            const clearButton = document.createElement("a");
            clearButton.href = "#";
            clearButton.className = "button inputSuffix jsTooltip";
            clearButton.title = Language.get("wcf.global.filter.button.clear");
            clearButton.innerHTML = '<span class="icon icon16 fa-times"></span>';
            clearButton.addEventListener("click", (event) => {
                event.preventDefault();
                this.reset();
            });
            inputAddon.appendChild(input);
            inputAddon.appendChild(clearButton);
            if (this._options.enableVisibilityFilter) {
                const visibilityButton = document.createElement("a");
                visibilityButton.href = "#";
                visibilityButton.className = "button inputSuffix jsTooltip";
                visibilityButton.title = Language.get("wcf.global.filter.button.visibility");
                visibilityButton.innerHTML = '<span class="icon icon16 fa-eye"></span>';
                visibilityButton.addEventListener("click", (ev) => this._toggleVisibility(ev));
                inputAddon.appendChild(visibilityButton);
            }
            if (this._options.filterPosition === "bottom") {
                container.appendChild(inputAddon);
            }
            else {
                container.insertBefore(inputAddon, element);
            }
            this._container = container;
            this._element = element;
            this._input = input;
        }
        /**
         * Resets the filter.
         */
        reset() {
            this._input.value = "";
            this._keyup();
        }
        /**
         * Builds the item list and rebuilds the items' DOM for easier manipulation.
         *
         * @protected
         */
        _buildItems() {
            this._items.clear();
            Array.from(this._element.children).forEach((item) => {
                this._items.add(this._options.callbackPrepareItem(item));
            });
        }
        /**
         * Processes an item and returns the meta data.
         */
        _prepareItem(item) {
            const label = item.children[0];
            const text = label.textContent.trim();
            const checkbox = label.children[0];
            while (checkbox.nextSibling) {
                label.removeChild(checkbox.nextSibling);
            }
            label.appendChild(document.createTextNode(" "));
            const span = document.createElement("span");
            span.textContent = text;
            label.appendChild(span);
            return {
                item,
                span,
                text,
            };
        }
        /**
         * Rebuilds the list on keyup, uses case-insensitive matching.
         */
        _keyup() {
            const value = this._input.value.trim();
            if (this._value === value) {
                return;
            }
            if (!this._fragment) {
                this._fragment = document.createDocumentFragment();
                // set fixed height to avoid layout jumps
                this._element.style.setProperty("height", `${this._element.offsetHeight}px`, "");
            }
            // move list into fragment before editing items, increases performance
            // by avoiding the browser to perform repaint/layout over and over again
            this._fragment.appendChild(this._element);
            if (!this._items.size) {
                this._buildItems();
            }
            const regexp = new RegExp("(" + StringUtil.escapeRegExp(value) + ")", "i");
            let hasVisibleItems = value === "";
            this._items.forEach((item) => {
                if (value === "") {
                    item.span.textContent = item.text;
                    Util_1.default.show(item.item);
                }
                else {
                    if (regexp.test(item.text)) {
                        item.span.innerHTML = item.text.replace(regexp, "<u>$1</u>");
                        Util_1.default.show(item.item);
                        hasVisibleItems = true;
                    }
                    else {
                        Util_1.default.hide(item.item);
                    }
                }
            });
            if (this._options.filterPosition === "bottom") {
                this._container.insertAdjacentElement("afterbegin", this._element);
            }
            else {
                this._container.insertAdjacentElement("beforeend", this._element);
            }
            this._value = value;
            Util_1.default.innerError(this._container, hasVisibleItems ? false : Language.get("wcf.global.filter.error.noMatches"));
        }
        /**
         * Toggles the visibility mode for marked items.
         */
        _toggleVisibility(event) {
            event.preventDefault();
            event.stopPropagation();
            const button = event.currentTarget;
            if (!this._dropdown) {
                const dropdown = document.createElement("ul");
                dropdown.className = "dropdownMenu";
                ["activeOnly", "highlightActive", "showAll"].forEach((type) => {
                    const link = document.createElement("a");
                    link.dataset.type = type;
                    link.href = "#";
                    link.textContent = Language.get(`wcf.global.filter.visibility.${type}`);
                    link.addEventListener("click", (ev) => this._setVisibility(ev));
                    const li = document.createElement("li");
                    li.appendChild(link);
                    if (type === "showAll") {
                        li.className = "active";
                        const divider = document.createElement("li");
                        divider.className = "dropdownDivider";
                        dropdown.appendChild(divider);
                    }
                    dropdown.appendChild(li);
                });
                Simple_1.default.initFragment(button, dropdown);
                // add `active` classes required for the visibility filter
                this._setupVisibilityFilter();
                this._dropdown = dropdown;
                this._dropdownId = button.id;
            }
            Simple_1.default.toggleDropdown(button.id, button);
        }
        /**
         * Set-ups the visibility filter by assigning an active class to the
         * list items that hold the checkboxes and observing the checkboxes
         * for any changes.
         *
         * This process involves quite a few DOM changes and new event listeners,
         * therefore we'll delay this until the filter has been accessed for
         * the first time, because none of these changes matter before that.
         */
        _setupVisibilityFilter() {
            const nextSibling = this._element.nextSibling;
            const parent = this._element.parentElement;
            const scrollTop = this._element.scrollTop;
            // mass-editing of DOM elements is slow while they're part of the document
            const fragment = document.createDocumentFragment();
            fragment.appendChild(this._element);
            this._element.querySelectorAll("li").forEach((li) => {
                const checkbox = li.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    if (checkbox.checked) {
                        li.classList.add("active");
                    }
                    checkbox.addEventListener("change", () => {
                        if (checkbox.checked) {
                            li.classList.add("active");
                        }
                        else {
                            li.classList.remove("active");
                        }
                    });
                }
                else {
                    const radioButton = li.querySelector('input[type="radio"]');
                    if (radioButton) {
                        if (radioButton.checked) {
                            li.classList.add("active");
                        }
                        radioButton.addEventListener("change", () => {
                            this._element.querySelectorAll("li").forEach((el) => el.classList.remove("active"));
                            if (radioButton.checked) {
                                li.classList.add("active");
                            }
                            else {
                                li.classList.remove("active");
                            }
                        });
                    }
                }
            });
            // re-insert the modified DOM
            parent.insertBefore(this._element, nextSibling);
            this._element.scrollTop = scrollTop;
        }
        /**
         * Sets the visibility of marked items.
         */
        _setVisibility(event) {
            event.preventDefault();
            const link = event.currentTarget;
            const type = link.dataset.type;
            Simple_1.default.close(this._dropdownId);
            if (this._element.dataset.filter === type) {
                // filter did not change
                return;
            }
            this._element.dataset.filter = type;
            const activeElement = this._dropdown.querySelector(".active");
            activeElement.classList.remove("active");
            link.parentElement.classList.add("active");
            const button = document.getElementById(this._dropdownId);
            if (type === "showAll") {
                button.classList.remove("active");
            }
            else {
                button.classList.add("active");
            }
            const icon = button.querySelector(".icon");
            if (type === "showAll") {
                icon.classList.add("fa-eye");
                icon.classList.remove("fa-eye-slash");
            }
            else {
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
    }
    Core.enableLegacyInheritance(UiItemListFilter);
    return UiItemListFilter;
});
