/**
 * Flexible UI element featuring both a list of items and an input field with suggestion support.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Suggestion
 */
define(["require", "exports", "tslib", "../Ajax", "../Core", "./Dropdown/Simple"], function (require, exports, tslib_1, Ajax, Core, Simple_1) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class UiSuggestion {
        /**
         * Initializes a new suggestion input.
         */
        constructor(elementId, options) {
            this.dropdownMenu = null;
            this.value = "";
            const element = document.getElementById(elementId);
            if (element === null) {
                throw new Error("Expected a valid element id.");
            }
            this.element = element;
            this.ajaxPayload = Core.extend({
                actionName: "getSearchResultList",
                className: "",
                interfaceName: "wcf\\data\\ISearchAction",
                parameters: {
                    data: {},
                },
            }, options.ajax);
            if (typeof options.callbackSelect !== "function") {
                throw new Error("Expected a valid callback for option 'callbackSelect'.");
            }
            this.callbackSelect = options.callbackSelect;
            this.excludedSearchValues = new Set(Array.isArray(options.excludedSearchValues) ? options.excludedSearchValues : []);
            this.threshold = options.threshold === undefined ? 3 : options.threshold;
            this.element.addEventListener("click", (ev) => ev.preventDefault());
            this.element.addEventListener("keydown", (ev) => this.keyDown(ev));
            this.element.addEventListener("keyup", (ev) => this.keyUp(ev));
        }
        /**
         * Adds an excluded search value.
         */
        addExcludedValue(value) {
            this.excludedSearchValues.add(value);
        }
        /**
         * Removes an excluded search value.
         */
        removeExcludedValue(value) {
            this.excludedSearchValues.delete(value);
        }
        /**
         * Returns true if the suggestions are active.
         */
        isActive() {
            return this.dropdownMenu !== null && Simple_1.default.isOpen(this.element.id);
        }
        /**
         * Handles the keyboard navigation for interaction with the suggestion list.
         */
        keyDown(event) {
            if (!this.isActive()) {
                return true;
            }
            if (["ArrowDown", "ArrowUp", "Enter", "Escape"].indexOf(event.key) === -1) {
                return true;
            }
            let active;
            let i = 0;
            const length = this.dropdownMenu.childElementCount;
            while (i < length) {
                active = this.dropdownMenu.children[i];
                if (active.classList.contains("active")) {
                    break;
                }
                i++;
            }
            if (event.key === "Enter") {
                Simple_1.default.close(this.element.id);
                this.select(undefined, active);
            }
            else if (event.key === "Escape") {
                if (Simple_1.default.isOpen(this.element.id)) {
                    Simple_1.default.close(this.element.id);
                }
                else {
                    // let the event pass through
                    return true;
                }
            }
            else {
                let index = 0;
                if (event.key === "ArrowUp") {
                    index = (i === 0 ? length : i) - 1;
                }
                else if (event.key === "ArrowDown") {
                    index = i + 1;
                    if (index === length)
                        index = 0;
                }
                if (index !== i) {
                    active.classList.remove("active");
                    this.dropdownMenu.children[index].classList.add("active");
                }
            }
            event.preventDefault();
            return false;
        }
        select(event, item) {
            if (event instanceof MouseEvent) {
                const target = event.currentTarget;
                item = target.parentNode;
            }
            const anchor = item.children[0];
            this.callbackSelect(this.element.id, {
                objectId: +(anchor.dataset.objectId || 0),
                value: item.textContent || "",
                type: anchor.dataset.type || "",
            });
            if (event instanceof MouseEvent) {
                this.element.focus();
            }
        }
        /**
         * Performs a search for the input value unless it is below the threshold.
         */
        keyUp(event) {
            const target = event.currentTarget;
            const value = target.value.trim();
            if (this.value === value) {
                return;
            }
            else if (value.length < this.threshold) {
                if (this.dropdownMenu !== null) {
                    Simple_1.default.close(this.element.id);
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
        _ajaxSetup() {
            return {
                data: this.ajaxPayload,
            };
        }
        /**
         * Handles successful Ajax requests.
         */
        _ajaxSuccess(data) {
            if (this.dropdownMenu === null) {
                this.dropdownMenu = document.createElement("div");
                this.dropdownMenu.className = "dropdownMenu";
                Simple_1.default.initFragment(this.element, this.dropdownMenu);
            }
            else {
                this.dropdownMenu.innerHTML = "";
            }
            if (Array.isArray(data.returnValues)) {
                data.returnValues.forEach((item, index) => {
                    const anchor = document.createElement("a");
                    if (item.icon) {
                        anchor.className = "box16";
                        anchor.innerHTML = item.icon + " <span></span>";
                        anchor.children[1].textContent = item.label;
                    }
                    else {
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
                    this.dropdownMenu.appendChild(listItem);
                });
                Simple_1.default.open(this.element.id, true);
            }
            else {
                Simple_1.default.close(this.element.id);
            }
        }
    }
    return UiSuggestion;
});
