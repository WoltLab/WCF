/**
 * Handles the state of a grid view.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "tslib", "./Filter", "./Selection", "./Sorting"], function (require, exports, tslib_1, Filter_1, Selection_1, Sorting_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.State = void 0;
    Filter_1 = tslib_1.__importDefault(Filter_1);
    Selection_1 = tslib_1.__importDefault(Selection_1);
    Sorting_1 = tslib_1.__importDefault(Sorting_1);
    // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
    class State extends EventTarget {
        #baseUrl;
        #filter;
        #pagination;
        #selection;
        #sorting;
        #gridViewFooter;
        #pageNo;
        constructor(gridId, table, pageNo, baseUrl, sortField, sortOrder) {
            super();
            this.#baseUrl = baseUrl;
            this.#pageNo = pageNo;
            this.#gridViewFooter = document.getElementById(`${gridId}_footer`);
            this.#pagination = document.getElementById(`${gridId}_pagination`);
            this.#pagination.addEventListener("switchPage", (event) => {
                void this.#switchPage(event.detail, 2 /* StateChangeCause.Pagination */);
            });
            this.#filter = new Filter_1.default(gridId);
            this.#filter.addEventListener("grid-view:change", () => {
                this.#switchPage(1, 0 /* StateChangeCause.Change */);
            });
            this.#sorting = new Sorting_1.default(table, sortField, sortOrder);
            this.#sorting.addEventListener("grid-view:change", () => {
                this.#switchPage(1, 0 /* StateChangeCause.Change */);
            });
            this.#selection = new Selection_1.default(gridId, table);
            this.#selection.addEventListener("grid-view:get-bulk-interactions", (event) => {
                this.dispatchEvent(new CustomEvent("grid-view:get-bulk-interactions", { detail: { objectIds: event.detail.objectIds } }));
            });
            this.#selection.addEventListener("grid-view:update-selection", () => {
                this.#updateGridViewFooter();
            });
            window.addEventListener("popstate", () => {
                this.#handlePopState();
            });
            this.#updatePaginationUrl();
            this.#updateGridViewFooter();
        }
        getPageNo() {
            return this.#pageNo;
        }
        getSortField() {
            return this.#sorting.getSortField();
        }
        getSortOrder() {
            return this.#sorting.getSortOrder();
        }
        getActiveFilters() {
            return this.#filter.getActiveFilters();
        }
        getSelectedIds() {
            return this.#selection.getSelectedIds();
        }
        updateFromResponse(cause, count, filterLabels) {
            this.#filter.setFilterLabels(filterLabels);
            this.#pagination.count = count;
            this.#updatePaginationUrl();
            this.#selection.refresh();
            if (cause === 0 /* StateChangeCause.Change */ || cause === 2 /* StateChangeCause.Pagination */) {
                this.#updateQueryString();
            }
        }
        #switchPage(pageNo, source) {
            this.#pagination.page = pageNo;
            this.#pageNo = pageNo;
            this.dispatchEvent(new CustomEvent("grid-view:change", { detail: { source } }));
        }
        #updateQueryString() {
            if (!this.#baseUrl) {
                return;
            }
            const url = new URL(this.#baseUrl);
            const parameters = [];
            if (this.#pageNo > 1) {
                parameters.push(["pageNo", this.#pageNo.toString()]);
            }
            for (const parameter of this.#sorting.getQueryParameters()) {
                parameters.push(parameter);
            }
            for (const parameter of this.#filter.getQueryParameters()) {
                parameters.push(parameter);
            }
            if (parameters.length > 0) {
                url.search += url.search !== "" ? "&" : "?";
                url.search += new URLSearchParams(parameters).toString();
            }
            window.history.pushState({}, document.title, url.toString());
        }
        #updatePaginationUrl() {
            if (!this.#baseUrl) {
                return;
            }
            const url = new URL(this.#baseUrl);
            const parameters = [];
            for (const parameter of this.#sorting.getQueryParameters()) {
                parameters.push(parameter);
            }
            for (const parameter of this.#filter.getQueryParameters()) {
                parameters.push(parameter);
            }
            if (parameters.length > 0) {
                url.search += url.search !== "" ? "&" : "?";
                url.search += new URLSearchParams(parameters).toString();
            }
            this.#pagination.url = url.toString();
        }
        #handlePopState() {
            let pageNo = 1;
            const { searchParams } = new URL(window.location.href);
            const value = searchParams.get("pageNo");
            if (value !== null) {
                pageNo = parseInt(value);
                if (Number.isNaN(pageNo) || pageNo < 1) {
                    pageNo = 1;
                }
            }
            this.#filter.updateFromSearchParams(searchParams);
            this.#sorting.updateFromSearchParams(searchParams);
            this.#switchPage(pageNo, 1 /* StateChangeCause.History */);
        }
        #updateGridViewFooter() {
            this.#gridViewFooter.hidden = this.#pagination.count < 2 && !this.#selection.selectionBarVisible();
        }
        setBulkInteractionContextMenuOptions(options) {
            this.#selection.setBulkInteractionContextMenuOptions(options);
        }
        resetSelection() {
            this.#selection.resetSelection();
        }
        refreshSelection() {
            this.#selection.refresh();
        }
    }
    exports.State = State;
    exports.default = State;
});
