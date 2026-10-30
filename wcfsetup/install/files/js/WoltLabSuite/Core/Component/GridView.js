/**
 * Provides the program logic for grid views.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "tslib", "../Api/GridViews/GetRow", "../Api/GridViews/GetRows", "../Api/Interactions/GetBulkContextMenuOptions", "../Api/PostObject", "../Dom/Change/Listener", "../Dom/Util", "../Helper/PromiseMutex", "../Helper/Selector", "../Ui/Dropdown/Simple", "./GridView/State", "./Interaction/Container"], function (require, exports, tslib_1, GetRow_1, GetRows_1, GetBulkContextMenuOptions_1, PostObject_1, Listener_1, Util_1, PromiseMutex_1, Selector_1, Simple_1, State_1, Container_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.GridView = void 0;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class GridView {
        #gridClassName;
        #table;
        #state;
        #noItemsNotice;
        #bulkInteractionProviderClassName;
        #gridViewParameters;
        #reloadEmptyTable = (0, PromiseMutex_1.promiseMutex)(() => this.#loadRows(0 /* StateChangeCause.Change */));
        constructor(gridId, gridClassName, pageNo, baseUrl = "", sortField = "", sortOrder = "ASC", defaultSortField = "", defaultSortOrder = "ASC", bulkInteractionProviderClassName, gridViewParameters, visibleColumns) {
            this.#gridClassName = gridClassName;
            this.#table = document.getElementById(`${gridId}_table`);
            this.#noItemsNotice = document.getElementById(`${gridId}_noItemsNotice`);
            this.#bulkInteractionProviderClassName = bulkInteractionProviderClassName;
            this.#gridViewParameters = gridViewParameters;
            this.#initInteractions();
            this.#state = this.#setupState(gridId, pageNo, baseUrl, sortField, sortOrder, defaultSortField, defaultSortOrder, visibleColumns);
            this.#initEventListeners();
        }
        async #loadRows(cause, includeHeader = false) {
            const response = await (0, GetRows_1.getRows)(this.#gridClassName, this.#state.getPageNo(), this.#state.getSortField(), this.#state.getSortOrder(), this.#state.getActiveFilters(), this.#gridViewParameters, this.#state.getVisibleColumns(), includeHeader);
            if (response.header !== undefined) {
                this.#replaceHeaderColumns(response.header);
            }
            Util_1.default.setInnerHtml(this.#table.querySelector("tbody"), response.template);
            this.#table.hidden = response.totalRows == 0;
            this.#noItemsNotice.hidden = response.totalRows != 0;
            this.#state.updateFromResponse(cause, response.pages, response.filterLabels);
            Listener_1.default.trigger();
        }
        async #refreshRow(row) {
            const { template } = await (0, GetRow_1.getRow)(this.#gridClassName, row.dataset.objectId, this.#state.getActiveFilters(), this.#gridViewParameters, this.#state.getVisibleColumns());
            row.replaceWith(Util_1.default.createFragmentFromHtml(template));
            this.#state.refreshSelection();
            Listener_1.default.trigger();
            this.#checkEmptyTable();
        }
        #replaceHeaderColumns(html) {
            const headerRow = this.#table.querySelector("thead .gridView__headerRow");
            headerRow.querySelectorAll("th[data-id]").forEach((element) => element.remove());
            // The selection and action columns are not part of the configurable
            // columns and must be preserved.
            const actionColumn = headerRow.querySelector(".gridView__actionColumn");
            headerRow.insertBefore(Util_1.default.createFragmentFromHtml(html), actionColumn);
        }
        #initInteractions() {
            (0, Selector_1.wheneverFirstSeen)(`#${this.#table.id} tbody tr`, (row) => {
                const containers = [row];
                row.querySelectorAll(".dropdownToggle").forEach((element) => {
                    if (!(0, Container_1.isOwnInteraction)(row, element)) {
                        return;
                    }
                    const dropdown = Simple_1.default.getDropdownMenu(element.dataset.target);
                    if (dropdown) {
                        containers.push(dropdown);
                    }
                });
                for (const container of containers) {
                    container.querySelectorAll("[data-interaction]").forEach((element) => {
                        if (container === row && !(0, Container_1.isOwnInteraction)(row, element)) {
                            return;
                        }
                        element.addEventListener("click", () => {
                            row.dispatchEvent(new CustomEvent("interaction:execute", {
                                detail: element.dataset,
                                bubbles: true,
                            }));
                        });
                    });
                }
            });
            (0, Selector_1.wheneverFirstSeen)(`#${this.#table.id} tbody tr .gridView__row__markAsRead`, (button) => {
                button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(async () => {
                    await (0, PostObject_1.postObject)(button.dataset.endpoint);
                    button.closest("tr")?.dispatchEvent(new CustomEvent("interaction:invalidate", { bubbles: true }));
                }));
            });
        }
        #initEventListeners() {
            this.#table.addEventListener("interaction:invalidate-all", () => {
                void this.#loadRows(0 /* StateChangeCause.Change */);
            });
            this.#table.addEventListener("interaction:invalidate", (event) => {
                void this.#refreshRow(event.target);
            });
            this.#table.addEventListener("interaction:remove", (event) => {
                event.target.remove();
                this.#state.removeSelection(parseInt(event.target.dataset.objectId));
                this.#checkEmptyTable();
            });
            this.#table.addEventListener("interaction:reset-selection", () => {
                this.#state.resetSelection();
            });
            this.#table.addEventListener("interaction:bulk-completed", () => {
                this.#checkEmptyTable();
            });
        }
        #setupState(gridId, pageNo, baseUrl, sortField, sortOrder, defaultSortField, defaultSortOrder, visibleColumns) {
            const state = new State_1.State(gridId, this.#table, pageNo, baseUrl, sortField, sortOrder, defaultSortField, defaultSortOrder, visibleColumns);
            state.addEventListener("grid-view:change", (event) => {
                void this.#loadRows(event.detail.source, event.detail.includeHeader);
            });
            state.addEventListener("grid-view:get-bulk-interactions", (0, PromiseMutex_1.promiseMutex)((event) => this.#loadBulkInteractions(event.detail.objectIds)));
            return state;
        }
        async #loadBulkInteractions(objectIds) {
            const { template } = await (0, GetBulkContextMenuOptions_1.getBulkContextMenuOptions)(this.#bulkInteractionProviderClassName, objectIds);
            this.#state.setBulkInteractionContextMenuOptions(template);
        }
        #checkEmptyTable() {
            if (this.#table.querySelectorAll("tbody tr").length > 0) {
                return;
            }
            this.#reloadEmptyTable();
        }
    }
    exports.GridView = GridView;
});
