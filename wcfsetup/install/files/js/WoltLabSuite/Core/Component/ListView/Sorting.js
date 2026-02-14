/**
 * Handles the sorting of list view items.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Sorting = void 0;
    // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
    class Sorting extends EventTarget {
        #defaultSortField;
        #defaultSortOrder;
        #sortField;
        #sortOrder;
        #dropdownMenu;
        constructor(dropdownMenu, sortField, sortOrder, defaultSortField, defaultSortOrder) {
            super();
            this.#sortField = sortField;
            this.#defaultSortField = defaultSortField;
            this.#sortOrder = sortOrder;
            this.#defaultSortOrder = defaultSortOrder;
            this.#dropdownMenu = dropdownMenu;
            this.#dropdownMenu?.querySelectorAll("[data-sort-id]").forEach((element) => {
                element.addEventListener("click", () => {
                    this.#sort(element.dataset.sortId);
                });
            });
            this.#renderActiveSorting();
        }
        getSortField() {
            return this.#sortField;
        }
        getSortOrder() {
            return this.#sortOrder;
        }
        getQueryParameters() {
            if (this.#sortField === "") {
                return [];
            }
            if (this.#sortField === this.#defaultSortField) {
                if (this.#sortOrder !== this.#defaultSortOrder) {
                    return [["sortOrder", this.#sortOrder]];
                }
                else {
                    return [];
                }
            }
            return [
                ["sortField", this.#sortField],
                ["sortOrder", this.#sortOrder],
            ];
        }
        updateFromSearchParams(params) {
            this.#sortField = this.#defaultSortField;
            this.#sortOrder = this.#defaultSortOrder;
            params.forEach((value, key) => {
                if (key === "sortField") {
                    this.#sortField = value;
                }
                else if (key === "sortOrder") {
                    this.#sortOrder = value;
                }
            });
        }
        #sort(sortField) {
            if (this.#sortField == sortField && this.#sortOrder == "ASC") {
                this.#sortOrder = "DESC";
            }
            else {
                this.#sortField = sortField;
                this.#sortOrder = "ASC";
            }
            this.#renderActiveSorting();
            this.dispatchEvent(new CustomEvent("list-view:change"));
        }
        #renderActiveSorting() {
            this.#dropdownMenu?.querySelectorAll("[data-sort-id]").forEach((element) => {
                element.classList.remove("active", "ASC", "DESC");
                if (element.dataset.sortId == this.#sortField) {
                    element.classList.add("active", this.#sortOrder);
                }
            });
        }
    }
    exports.Sorting = Sorting;
    exports.default = Sorting;
});
