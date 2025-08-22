/**
 * Provides the program logic for grid views.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "tslib", "../Api/Gridviews/GetRow", "../Api/Gridviews/GetRows", "../Api/Interactions/GetBulkContextMenuOptions", "../Dom/Change/Listener", "../Dom/Util", "../Helper/PromiseMutex", "../Helper/Selector", "../Ui/Dropdown/Simple", "./GridView/State"], function (require, exports, tslib_1, GetRow_1, GetRows_1, GetBulkContextMenuOptions_1, Listener_1, Util_1, PromiseMutex_1, Selector_1, Simple_1, State_1) {
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
        constructor(gridId, gridClassName, pageNo, baseUrl = "", sortField = "", sortOrder = "ASC", bulkInteractionProviderClassName, gridViewParameters) {
            this.#gridClassName = gridClassName;
            this.#table = document.getElementById(`${gridId}_table`);
            this.#noItemsNotice = document.getElementById(`${gridId}_noItemsNotice`);
            this.#bulkInteractionProviderClassName = bulkInteractionProviderClassName;
            this.#gridViewParameters = gridViewParameters;
            this.#initInteractions();
            this.#state = this.#setupState(gridId, pageNo, baseUrl, sortField, sortOrder);
            this.#initEventListeners();
        }
        async #loadRows(cause) {
            const response = await (0, GetRows_1.getRows)(this.#gridClassName, this.#state.getPageNo(), this.#state.getSortField(), this.#state.getSortOrder(), this.#state.getActiveFilters(), this.#gridViewParameters);
            Util_1.default.setInnerHtml(this.#table.querySelector("tbody"), response.template);
            this.#table.hidden = response.totalRows == 0;
            this.#noItemsNotice.hidden = response.totalRows != 0;
            this.#state.updateFromResponse(cause, response.pages, response.filterLabels);
            Listener_1.default.trigger();
        }
        async #refreshRow(row) {
            const { template } = await (0, GetRow_1.getRow)(this.#gridClassName, row.dataset.objectId, this.#gridViewParameters);
            row.replaceWith(Util_1.default.createFragmentFromHtml(template));
            this.#state.refreshSelection();
            Listener_1.default.trigger();
        }
        #initInteractions() {
            (0, Selector_1.wheneverFirstSeen)(`#${this.#table.id} tbody tr`, (row) => {
                row.querySelectorAll(".dropdownToggle").forEach((element) => {
                    let dropdown = Simple_1.default.getDropdownMenu(element.dataset.target);
                    if (!dropdown) {
                        dropdown = element.closest(".dropdown").querySelector(".dropdownMenu");
                    }
                    dropdown?.querySelectorAll("[data-interaction]").forEach((element) => {
                        element.addEventListener("click", () => {
                            row.dispatchEvent(new CustomEvent("interaction:execute", {
                                detail: element.dataset,
                                bubbles: true,
                            }));
                        });
                    });
                });
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
                this.#checkEmptyTable();
            });
            this.#table.addEventListener("interaction:reset-selection", () => {
                this.#state.resetSelection();
            });
        }
        #setupState(gridId, pageNo, baseUrl, sortField, sortOrder) {
            const state = new State_1.State(gridId, this.#table, pageNo, baseUrl, sortField, sortOrder);
            state.addEventListener("grid-view:change", (event) => {
                void this.#loadRows(event.detail.source);
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
            void this.#loadRows(0 /* StateChangeCause.Change */);
        }
    }
    exports.GridView = GridView;
});
