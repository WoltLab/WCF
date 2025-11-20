/**
 * Provides the program logic for list views.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "tslib", "./ListView/State", "../Dom/Change/Listener", "../Dom/Util", "../Api/ListViews/GetItems", "WoltLabSuite/Core/Ui/Scroll", "../Helper/Selector", "../Ui/Dropdown/Simple", "../Api/ListViews/GetItem", "../Api/Interactions/GetBulkContextMenuOptions", "../Helper/PromiseMutex"], function (require, exports, tslib_1, State_1, Listener_1, Util_1, GetItems_1, Scroll_1, Selector_1, Simple_1, GetItem_1, GetBulkContextMenuOptions_1, PromiseMutex_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ListView = void 0;
    State_1 = tslib_1.__importDefault(State_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class ListView {
        #viewClassName;
        #viewElement;
        #state;
        #noItemsNotice;
        #bulkInteractionProviderClassName;
        #listViewParameters;
        constructor(viewId, viewClassName, pageNo, baseUrl = "", sortField = "", sortOrder = "ASC", defaultSortField = "", defaultSortOrder = "ASC", bulkInteractionProviderClassName, listViewParameters) {
            this.#viewClassName = viewClassName;
            this.#viewElement = document.getElementById(`${viewId}_items`);
            this.#noItemsNotice = document.getElementById(`${viewId}_noItemsNotice`);
            this.#bulkInteractionProviderClassName = bulkInteractionProviderClassName;
            this.#listViewParameters = listViewParameters;
            this.#initInteractions();
            this.#state = this.#setupState(viewId, pageNo, baseUrl, sortField, sortOrder, defaultSortField, defaultSortOrder);
            this.#initEventListeners();
        }
        async #loadItems(cause) {
            const response = await (0, GetItems_1.getItems)(this.#viewClassName, this.#state.getPageNo(), this.#state.getSortField(), this.#state.getSortOrder(), this.#state.getActiveFilters(), this.#listViewParameters);
            (0, Util_1.setInnerHtml)(this.#viewElement, response.template);
            this.#viewElement.hidden = response.totalItems === 0;
            this.#noItemsNotice.hidden = response.totalItems !== 0;
            this.#state.updateFromResponse(cause, response.pages, response.filterLabels);
            if (cause === 2 /* StateChangeCause.Pagination */) {
                (0, Scroll_1.element)(this.#viewElement.closest(".listView"));
            }
            (0, Listener_1.trigger)();
        }
        async #refreshItem(item) {
            const { template } = await (0, GetItem_1.getItem)(this.#viewClassName, item.dataset.objectId, this.#state.getActiveFilters(), this.#listViewParameters);
            item.replaceWith((0, Util_1.createFragmentFromHtml)(template));
            this.#state.refreshSelection();
            (0, Listener_1.trigger)();
            this.#checkEmptyList();
        }
        #initInteractions() {
            (0, Selector_1.wheneverFirstSeen)(`#${this.#viewElement.id} .listView__item`, (item) => {
                item.querySelectorAll(".dropdownToggle").forEach((element) => {
                    let dropdown = Simple_1.default.getDropdownMenu(element.dataset.target);
                    if (!dropdown) {
                        dropdown = element.closest(".dropdown").querySelector(".dropdownMenu");
                    }
                    dropdown?.querySelectorAll("[data-interaction]").forEach((element) => {
                        element.addEventListener("click", () => {
                            item.dispatchEvent(new CustomEvent("interaction:execute", {
                                detail: element.dataset,
                                bubbles: true,
                            }));
                        });
                    });
                });
            });
            const listView = this.#viewElement.closest(".listView");
            listView.querySelector(".listView__editMode__toggle")?.addEventListener("click", () => {
                listView.classList.add("listView--editMode");
            });
        }
        #initEventListeners() {
            this.#viewElement.addEventListener("interaction:invalidate-all", () => {
                void this.#loadItems(0 /* StateChangeCause.Change */);
            });
            this.#viewElement.addEventListener("interaction:invalidate", (event) => {
                void this.#refreshItem(event.target);
            });
            this.#viewElement.addEventListener("interaction:remove", (event) => {
                event.target.remove();
                this.#checkEmptyList();
            });
            this.#viewElement.addEventListener("interaction:reset-selection", () => {
                this.#state.resetSelection();
            });
        }
        #setupState(viewId, pageNo, baseUrl, sortField, sortOrder, defaultSortField, defaultSortOrder) {
            const state = new State_1.default(viewId, this.#viewElement, pageNo, baseUrl, sortField, sortOrder, defaultSortField, defaultSortOrder);
            state.addEventListener("list-view:change", (event) => {
                void this.#loadItems(event.detail.source);
            });
            state.addEventListener("list-view:get-bulk-interactions", (0, PromiseMutex_1.promiseMutex)((event) => this.#loadBulkInteractions(event.detail.objectIds)));
            return state;
        }
        async #loadBulkInteractions(objectIds) {
            const { template } = await (0, GetBulkContextMenuOptions_1.getBulkContextMenuOptions)(this.#bulkInteractionProviderClassName, objectIds);
            this.#state.setBulkInteractionContextMenuOptions(template);
        }
        #checkEmptyList() {
            if (this.#viewElement.querySelectorAll(".listView__item").length > 0) {
                return;
            }
            void this.#loadItems(0 /* StateChangeCause.Change */);
        }
    }
    exports.ListView = ListView;
});
