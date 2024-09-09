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
        constructor(gridId, gridClassName, pageNo, baseUrl = "") {
            this.#gridClassName = gridClassName;
            this.#table = document.getElementById(`${gridId}_table`);
            this.#topPagination = document.getElementById(`${gridId}_topPagination`);
            this.#bottomPagination = document.getElementById(`${gridId}_bottomPagination`);
            this.#pageNo = pageNo;
            this.#baseUrl = baseUrl;
            this.#initPagination();
        }
        #initPagination() {
            this.#topPagination.addEventListener("switchPage", (event) => {
                this.#switchPage(event.detail);
            });
            this.#bottomPagination.addEventListener("switchPage", (event) => {
                this.#switchPage(event.detail);
            });
        }
        async #switchPage(pageNo) {
            this.#topPagination.page = pageNo;
            this.#bottomPagination.page = pageNo;
            const response = await (0, GetRows_1.getRows)(this.#gridClassName, pageNo);
            this.#pageNo = pageNo;
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
            if (parameters.length > 0) {
                url.search += url.search !== "" ? "&" : "?";
                url.search += new URLSearchParams(parameters).toString();
            }
            window.history.pushState({ name: "gridView" }, document.title, url.toString());
        }
    }
    exports.GridView = GridView;
});
