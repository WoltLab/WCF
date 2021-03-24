/**
 * Provides suggestions using an input field, designed to work with `wcf\data\ISearchAction`.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Search/Input
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Core", "../../Dom/Util", "../Dropdown/Simple"], function (require, exports, tslib_1, Ajax, Core, Util_1, Simple_1) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class UiSearchInput {
        /**
         * Initializes the search input field.
         *
         * @param       {Element}       element         target input[type="text"]
         * @param       {Object}        options         search options and settings
         */
        constructor(element, options) {
            this.activeItem = undefined;
            this.callbackDropdownInit = undefined;
            this.callbackSelect = undefined;
            this.dropdownContainerId = "";
            this.excludedSearchValues = new Set();
            this.list = undefined;
            this.lastValue = "";
            this.request = undefined;
            this.timerDelay = undefined;
            this.element = element;
            if (!(this.element instanceof HTMLInputElement)) {
                throw new TypeError("Expected a valid DOM element.");
            }
            else if (this.element.nodeName !== "INPUT" || (this.element.type !== "search" && this.element.type !== "text")) {
                throw new Error('Expected an input[type="text"].');
            }
            options = Core.extend({
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
            }, options);
            this.ajaxPayload = options.ajax;
            this.autoFocus = options.autoFocus;
            this.callbackDropdownInit = options.callbackDropdownInit;
            this.callbackSelect = options.callbackSelect;
            this.delay = options.delay;
            options.excludedSearchValues.forEach((value) => {
                this.addExcludedSearchValues(value);
            });
            this.minLength = options.minLength;
            this.noResultPlaceholder = options.noResultPlaceholder;
            this.preventSubmit = options.preventSubmit;
            // Disable auto-complete because it collides with the suggestion dropdown.
            this.element.autocomplete = "off";
            this.element.addEventListener("keydown", (ev) => this.keydown(ev));
            this.element.addEventListener("keyup", (ev) => this.keyup(ev));
        }
        /**
         * Adds an excluded search value.
         */
        addExcludedSearchValues(value) {
            this.excludedSearchValues.add(value);
        }
        /**
         * Removes a value from the excluded search values.
         */
        removeExcludedSearchValues(value) {
            this.excludedSearchValues.delete(value);
        }
        /**
         * Handles the 'keydown' event.
         */
        keydown(event) {
            if ((this.activeItem !== null && Simple_1.default.isOpen(this.dropdownContainerId)) || this.preventSubmit) {
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
        keyup(event) {
            // handle dropdown keyboard navigation
            if (this.activeItem !== null || !this.autoFocus) {
                if (Simple_1.default.isOpen(this.dropdownContainerId)) {
                    if (event.key === "ArrowUp") {
                        event.preventDefault();
                        return this.keyboardPreviousItem();
                    }
                    else if (event.key === "ArrowDown") {
                        event.preventDefault();
                        return this.keyboardNextItem();
                    }
                    else if (event.key === "Enter") {
                        event.preventDefault();
                        return this.keyboardSelectItem();
                    }
                }
                else {
                    this.activeItem = undefined;
                }
            }
            // close list on escape
            if (event.key === "Escape") {
                Simple_1.default.close(this.dropdownContainerId);
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
                    Simple_1.default.close(this.dropdownContainerId);
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
            }
            else {
                this.search(value);
            }
        }
        /**
         * Queries the server with the provided search string.
         */
        search(value) {
            if (this.request) {
                this.request.abortPrevious();
            }
            this.request = Ajax.api(this, this.getParameters(value));
        }
        /**
         * Returns additional AJAX parameters.
         */
        getParameters(value) {
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
        keyboardNextItem() {
            let nextItem = undefined;
            if (this.activeItem) {
                this.activeItem.classList.remove("active");
                if (this.activeItem.nextElementSibling) {
                    nextItem = this.activeItem.nextElementSibling;
                }
            }
            this.activeItem = nextItem || this.list.children[0];
            this.activeItem.classList.add("active");
        }
        /**
         * Selects the previous dropdown item.
         */
        keyboardPreviousItem() {
            let nextItem = undefined;
            if (this.activeItem) {
                this.activeItem.classList.remove("active");
                if (this.activeItem.previousElementSibling) {
                    nextItem = this.activeItem.previousElementSibling;
                }
            }
            this.activeItem = nextItem || this.list.children[this.list.childElementCount - 1];
            this.activeItem.classList.add("active");
        }
        /**
         * Selects the active item from the dropdown.
         */
        keyboardSelectItem() {
            this.selectItem(this.activeItem);
        }
        /**
         * Selects an item from the dropdown by clicking it.
         */
        clickSelectItem(event) {
            this.selectItem(event.currentTarget);
        }
        /**
         * Selects an item.
         */
        selectItem(item) {
            if (this.callbackSelect && !this.callbackSelect(item)) {
                this.element.value = "";
            }
            else {
                this.element.value = item.dataset.label || "";
            }
            this.activeItem = undefined;
            Simple_1.default.close(this.dropdownContainerId);
        }
        /**
         * Handles successful AJAX requests.
         */
        _ajaxSuccess(data) {
            let createdList = false;
            if (!this.list) {
                this.list = document.createElement("ul");
                this.list.className = "dropdownMenu";
                createdList = true;
                if (typeof this.callbackDropdownInit === "function") {
                    this.callbackDropdownInit(this.list);
                }
            }
            else {
                // reset current list
                this.list.innerHTML = "";
            }
            if (typeof data.returnValues === "object") {
                const callbackClick = this.clickSelectItem.bind(this);
                Object.keys(data.returnValues).forEach((key) => {
                    const listItem = this.createListItem(data.returnValues[key]);
                    listItem.addEventListener("click", callbackClick);
                    this.list.appendChild(listItem);
                });
            }
            if (createdList) {
                this.element.insertAdjacentElement("afterend", this.list);
                const parent = this.element.parentElement;
                Simple_1.default.initFragment(parent, this.list);
                this.dropdownContainerId = Util_1.default.identify(parent);
            }
            if (this.dropdownContainerId) {
                this.activeItem = undefined;
                if (!this.list.childElementCount && !this.handleEmptyResult()) {
                    Simple_1.default.close(this.dropdownContainerId);
                }
                else {
                    Simple_1.default.open(this.dropdownContainerId, true, this.element);
                    // mark first item as active
                    const firstChild = this.list.childElementCount ? this.list.children[0] : undefined;
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
        handleEmptyResult() {
            if (!this.noResultPlaceholder) {
                return false;
            }
            const listItem = document.createElement("li");
            listItem.className = "dropdownText";
            const span = document.createElement("span");
            span.textContent = this.noResultPlaceholder;
            listItem.appendChild(span);
            this.list.appendChild(listItem);
            return true;
        }
        /**
         * Creates an list item from response data.
         */
        createListItem(item) {
            const listItem = document.createElement("li");
            listItem.dataset.objectId = item.objectID.toString();
            listItem.dataset.label = item.label;
            const span = document.createElement("span");
            span.textContent = item.label;
            listItem.appendChild(span);
            return listItem;
        }
        _ajaxSetup() {
            return {
                data: this.ajaxPayload,
            };
        }
    }
    Core.enableLegacyInheritance(UiSearchInput);
    return UiSearchInput;
});
