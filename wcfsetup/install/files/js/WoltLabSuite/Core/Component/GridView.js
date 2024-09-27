define(["require", "exports", "tslib", "../Api/GridViews/GetRows", "../Dom/Util", "../Ui/Dropdown/Simple"], function (require, exports, tslib_1, GetRows_1, Util_1, Simple_1) {
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
        #pageNo;
        #sortField;
        #sortOrder;
        #defaultSortField;
        #defaultSortOrder;
        constructor(gridId, gridClassName, pageNo, baseUrl = "", sortField = "", sortOrder = "ASC") {
            this.#gridClassName = gridClassName;
            this.#table = document.getElementById(`${gridId}_table`);
            this.#topPagination = document.getElementById(`${gridId}_topPagination`);
            this.#bottomPagination = document.getElementById(`${gridId}_bottomPagination`);
            this.#pageNo = pageNo;
            this.#baseUrl = baseUrl;
            this.#sortField = sortField;
            this.#defaultSortField = sortField;
            this.#sortOrder = sortOrder;
            this.#defaultSortOrder = sortOrder;
            this.#initPagination();
            this.#initSorting();
            this.#initActions();
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
            const response = await (0, GetRows_1.getRows)(this.#gridClassName, this.#pageNo, this.#sortField, this.#sortOrder);
            Util_1.default.setInnerHtml(this.#table.querySelector("tbody"), response.unwrap().template);
            if (updateQueryString) {
                this.#updateQueryString();
            }
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
        #handlePopState() {
            let pageNo = 1;
            this.#sortField = this.#defaultSortField;
            this.#sortOrder = this.#defaultSortOrder;
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
            });
            this.#switchPage(pageNo, false);
        }
    }
    exports.GridView = GridView;
});
