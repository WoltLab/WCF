define(["require", "exports", "tslib", "../Api/GridViews/GetRows", "../Dom/Util", "../Helper/PromiseMutex", "../Ui/Dropdown/Simple", "./Dialog"], function (require, exports, tslib_1, GetRows_1, Util_1, PromiseMutex_1, Simple_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.GridView = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class GridView {
        #gridClassName;
        #table;
        #topPagination;
        #bottomPagination;
        #baseUrl;
        #filterButton;
        #filterPills;
        #pageNo;
        #sortField;
        #sortOrder;
        #defaultSortField;
        #defaultSortOrder;
        #filters;
        constructor(gridId, gridClassName, pageNo, baseUrl = "", sortField = "", sortOrder = "ASC") {
            this.#gridClassName = gridClassName;
            this.#table = document.getElementById(`${gridId}_table`);
            this.#topPagination = document.getElementById(`${gridId}_topPagination`);
            this.#bottomPagination = document.getElementById(`${gridId}_bottomPagination`);
            this.#filterButton = document.getElementById(`${gridId}_filterButton`);
            this.#filterPills = document.getElementById(`${gridId}_filters`);
            this.#pageNo = pageNo;
            this.#baseUrl = baseUrl;
            this.#sortField = sortField;
            this.#defaultSortField = sortField;
            this.#sortOrder = sortOrder;
            this.#defaultSortOrder = sortOrder;
            this.#initPagination();
            this.#initSorting();
            this.#initActions();
            this.#initFilters();
            window.addEventListener("popstate", () => {
                this.#handlePopState();
            });
        }
        #initPagination() {
            this.#topPagination.addEventListener("switchPage", (event) => {
                void this.#switchPage(event.detail);
            });
            this.#bottomPagination.addEventListener("switchPage", (event) => {
                void this.#switchPage(event.detail);
            });
        }
        #initSorting() {
            this.#table.querySelectorAll('th[data-sortable="1"]').forEach((element) => {
                const link = document.createElement("a");
                link.role = "button";
                link.addEventListener("click", () => {
                    this.#sort(element.dataset.id);
                });
                link.textContent = element.textContent;
                element.innerHTML = "";
                element.append(link);
            });
            this.#renderActiveSorting();
        }
        #sort(sortField) {
            if (this.#sortField == sortField && this.#sortOrder == "ASC") {
                this.#sortOrder = "DESC";
            }
            else {
                this.#sortField = sortField;
                this.#sortOrder = "ASC";
            }
            this.#switchPage(1);
            this.#renderActiveSorting();
        }
        #renderActiveSorting() {
            this.#table.querySelectorAll('th[data-sortable="1"]').forEach((element) => {
                element.classList.remove("active", "ASC", "DESC");
                if (element.dataset.id == this.#sortField) {
                    element.classList.add("active", this.#sortOrder);
                }
            });
        }
        #switchPage(pageNo, updateQueryString = true) {
            this.#topPagination.page = pageNo;
            this.#bottomPagination.page = pageNo;
            this.#pageNo = pageNo;
            void this.#loadRows(updateQueryString);
        }
        async #loadRows(updateQueryString = true) {
            const response = (await (0, GetRows_1.getRows)(this.#gridClassName, this.#pageNo, this.#sortField, this.#sortOrder, this.#filters)).unwrap();
            Util_1.default.setInnerHtml(this.#table.querySelector("tbody"), response.template);
            this.#topPagination.count = response.pages;
            this.#bottomPagination.count = response.pages;
            if (updateQueryString) {
                this.#updateQueryString();
            }
            this.#renderFilters(response.filterLabels);
            this.#initActions();
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
            if (this.#sortField) {
                parameters.push(["sortField", this.#sortField]);
                parameters.push(["sortOrder", this.#sortOrder]);
            }
            this.#filters.forEach((value, key) => {
                parameters.push([`filters[${key}]`, value]);
            });
            if (parameters.length > 0) {
                url.search += url.search !== "" ? "&" : "?";
                url.search += new URLSearchParams(parameters).toString();
            }
            window.history.pushState({}, document.title, url.toString());
        }
        #initActions() {
            this.#table.querySelectorAll("tbody tr").forEach((row) => {
                row.querySelectorAll(".gridViewActions").forEach((element) => {
                    const dropdown = Simple_1.default.getDropdownMenu(element.dataset.target);
                    dropdown?.querySelectorAll("[data-action]").forEach((element) => {
                        element.addEventListener("click", () => {
                            row.dispatchEvent(new CustomEvent("action", {
                                detail: element.dataset,
                                bubbles: true,
                            }));
                        });
                    });
                });
            });
        }
        #initFilters() {
            if (!this.#filterButton) {
                return;
            }
            this.#filterButton.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => this.#showFilterDialog()));
            if (!this.#filterPills) {
                return;
            }
            const filterButtons = this.#filterPills.querySelectorAll("[data-filter]");
            if (!filterButtons.length) {
                return;
            }
            this.#filters = new Map();
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
                this.#switchPage(1);
            }
        }
        #renderFilters(labels) {
            this.#filterPills.innerHTML = "";
            if (!this.#filters) {
                return;
            }
            this.#filters.forEach((value, key) => {
                const button = document.createElement("button");
                button.type = "button";
                button.classList.add("button");
                button.innerText = labels[key];
                button.addEventListener("click", () => {
                    this.#removeFilter(key);
                });
                this.#filterPills.append(button);
            });
        }
        #removeFilter(filter) {
            this.#filters.delete(filter);
            this.#switchPage(1);
        }
        #handlePopState() {
            let pageNo = 1;
            this.#sortField = this.#defaultSortField;
            this.#sortOrder = this.#defaultSortOrder;
            this.#filters = new Map();
            const url = new URL(window.location.href);
            url.searchParams.forEach((value, key) => {
                if (key === "pageNo") {
                    pageNo = parseInt(value, 10);
                    return;
                }
                if (key === "sortField") {
                    this.#sortField = value;
                }
                if (key === "sortOrder") {
                    this.#sortOrder = value;
                }
                const matches = key.match(/^filters\[([a-z0-9_]+)\]$/i);
                if (matches) {
                    this.#filters.set(matches[1], value);
                }
            });
            this.#switchPage(pageNo, false);
        }
    }
    exports.GridView = GridView;
});
