/**
 * Handles the filterung of grid views.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "../../Helper/PromiseMutex", "../Dialog"], function (require, exports, PromiseMutex_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Filter = void 0;
    // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
    class Filter extends EventTarget {
        #filterButton;
        #filterPills;
        #filters = new Map();
        constructor(gridId) {
            super();
            this.#filterButton = document.getElementById(`${gridId}_filterButton`);
            this.#filterPills = document.getElementById(`${gridId}_filters`);
            this.#setupEventListeners();
        }
        getActiveFilters() {
            return new Map(this.#filters);
        }
        getQueryParameters() {
            const parameters = [];
            for (const [key, value] of this.#filters.entries()) {
                parameters.push([`filters[${key}]`, value]);
            }
            return parameters;
        }
        updateFromSearchParams(params) {
            this.#filters.clear();
            params.forEach((value, key) => {
                const matches = key.match(/^filters\[([a-z0-9_]+)\]$/i);
                if (matches) {
                    this.#filters.set(matches[1], value);
                }
            });
        }
        setFilterLabels(labels) {
            if (this.#filterPills === null) {
                return;
            }
            this.#filterPills.innerHTML = "";
            if (this.#filters.size === 0) {
                return;
            }
            for (const key of this.#filters.keys()) {
                const button = document.createElement("button");
                button.type = "button";
                button.classList.add("button", "small");
                const icon = document.createElement("fa-icon");
                icon.setIcon("circle-xmark");
                button.append(icon, labels[key]);
                button.addEventListener("click", () => {
                    this.#removeFilter(key);
                });
                this.#filterPills.append(button);
            }
        }
        #setupEventListeners() {
            if (this.#filterButton === null) {
                return;
            }
            this.#filterButton.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => this.#showFilterDialog()));
            if (this.#filterPills === null) {
                return;
            }
            const filterButtons = this.#filterPills.querySelectorAll("[data-filter]");
            filterButtons.forEach((button) => {
                this.#filters.set(button.dataset.filter, button.dataset.filterValue);
                button.addEventListener("click", () => {
                    this.#removeFilter(button.dataset.filter);
                });
            });
        }
        async #showFilterDialog() {
            const url = new URL(this.#filterButton.dataset.endpoint);
            if (this.#filters) {
                this.#filters.forEach((value, key) => {
                    url.searchParams.set(`filters[${key}]`, value);
                });
            }
            const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(url.toString());
            if (ok) {
                this.#filters = new Map(Object.entries(result));
                this.dispatchEvent(new CustomEvent("change"));
            }
        }
        #removeFilter(filter) {
            this.#filters.delete(filter);
            this.dispatchEvent(new CustomEvent("change"));
        }
    }
    exports.Filter = Filter;
    exports.default = Filter;
});
