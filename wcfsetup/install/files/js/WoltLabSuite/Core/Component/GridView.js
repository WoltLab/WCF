define(["require", "exports", "tslib", "../Api/GridViews/GetRows", "../Dom/Util"], function (require, exports, tslib_1, GetRows_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.GridView = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    class GridView {
        #gridClassName;
        #table;
        #topPagination;
        #bottomPagination;
        #baseUrl;
        #pageNo;
        #sortField;
        #sortOrder;
        constructor(gridId, gridClassName, pageNo, baseUrl = "", sortField = "", sortOrder = "ASC") {
            this.#gridClassName = gridClassName;
            this.#table = document.getElementById(`${gridId}_table`);
            this.#topPagination = document.getElementById(`${gridId}_topPagination`);
            this.#bottomPagination = document.getElementById(`${gridId}_bottomPagination`);
            this.#pageNo = pageNo;
            this.#baseUrl = baseUrl;
            this.#sortField = sortField;
            this.#sortOrder = sortOrder;
            this.#initPagination();
            this.#initSorting();
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
            this.#loadRows();
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
        #switchPage(pageNo) {
            this.#topPagination.page = pageNo;
            this.#bottomPagination.page = pageNo;
            this.#pageNo = pageNo;
            this.#loadRows();
        }
        async #loadRows() {
            const response = await (0, GetRows_1.getRows)(this.#gridClassName, this.#pageNo, this.#sortField, this.#sortOrder);
            Util_1.default.setInnerHtml(this.#table.querySelector("tbody"), response.unwrap().template);
            this.#updateQueryString();
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
    }
    exports.GridView = GridView;
});
