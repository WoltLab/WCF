/**
 * Handles the state of a list view.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Helper/Selector", "./Filter", "./Selection", "./Sorting", "WoltLabSuite/Core/Api/PostObject", "WoltLabSuite/Core/Helper/PromiseMutex"], function (require, exports, tslib_1, Selector_1, Filter_1, Selection_1, Sorting_1, PostObject_1, PromiseMutex_1) {
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
        #listViewFooter;
        #pageNo;
        constructor(viewId, viewElement, pageNo, baseUrl, sortField, sortOrder, defaultSortField, defaultSortOrder) {
            super();
            this.#baseUrl = baseUrl;
            this.#pageNo = pageNo;
            this.#listViewFooter = document.getElementById(`${viewId}_footer`);
            this.#pagination = document.getElementById(`${viewId}_pagination`);
            this.#pagination.addEventListener("switchPage", (event) => {
                void this.#switchPage(event.detail, 2 /* StateChangeCause.Pagination */);
            });
            this.#filter = new Filter_1.default(viewId);
            this.#filter.addEventListener("list-view:change", () => {
                this.#switchPage(1, 0 /* StateChangeCause.Change */);
            });
            this.#sorting = new Sorting_1.default(document.getElementById(`${viewId}_sorting`) ?? undefined, sortField, sortOrder, defaultSortField, defaultSortOrder);
            this.#sorting.addEventListener("list-view:change", () => {
                this.#switchPage(1, 0 /* StateChangeCause.Change */);
            });
            this.#selection = new Selection_1.default(viewId, viewElement);
            this.#selection.addEventListener("list-view:get-bulk-interactions", (event) => {
                this.dispatchEvent(new CustomEvent("list-view:get-bulk-interactions", { detail: { objectIds: event.detail.objectIds } }));
            });
            this.#selection.addEventListener("list-view:update-selection", () => {
                this.#updateListViewFooter();
            });
            window.addEventListener("popstate", () => {
                this.#handlePopState();
            });
            (0, Selector_1.wheneverFirstSeen)(`#${viewId}_items .listView__item__markAsRead`, (button) => {
                button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(async () => {
                    await (0, PostObject_1.postObject)(button.dataset.endpoint);
                    button
                        .closest(".listView__item")
                        ?.dispatchEvent(new CustomEvent("interaction:invalidate", { bubbles: true }));
                }));
            });
            this.#updatePaginationUrl();
            this.#updateListViewFooter();
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
            this.#updateListViewFooter();
        }
        #switchPage(pageNo, source) {
            this.#pagination.page = pageNo;
            this.#pageNo = pageNo;
            this.dispatchEvent(new CustomEvent("list-view:change", { detail: { source } }));
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
        #updateListViewFooter() {
            this.#listViewFooter.hidden = this.#pagination.count < 2 && !this.#selection.selectionBarVisible();
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
