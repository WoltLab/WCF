/**
 * Provides the program logic for the extended search form.
 *
 * @author  Marcel Werk
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Search/Extended
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Date/Picker", "../../Dom/Util", "../../StringUtil", "../Pagination", "./Input"], function (require, exports, tslib_1, Ajax_1, Picker_1, DomUtil, StringUtil_1, Pagination_1, Input_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UiSearchExtended = void 0;
    Picker_1 = (0, tslib_1.__importDefault)(Picker_1);
    DomUtil = (0, tslib_1.__importStar)(DomUtil);
    Pagination_1 = (0, tslib_1.__importDefault)(Pagination_1);
    Input_1 = (0, tslib_1.__importDefault)(Input_1);
    class UiSearchExtended {
        constructor() {
            this.pages = 0;
            this.activePage = 1;
            this.lastSearchRequest = undefined;
            this.lastSearchResultRequest = undefined;
            this.form = document.getElementById("extendedSearchForm");
            this.queryInput = document.getElementById("searchQuery");
            this.typeInput = document.getElementById("searchType");
            this.usernameInput = document.getElementById("searchAuthor");
            this.initEventListener();
            this.initKeywordSuggestions();
            this.initQueryString();
        }
        initEventListener() {
            this.form.addEventListener("submit", (event) => {
                event.preventDefault();
                void this.search();
            });
            this.typeInput.addEventListener("change", () => this.changeType());
        }
        initKeywordSuggestions() {
            new Input_1.default(this.queryInput, {
                ajax: {
                    className: "wcf\\data\\search\\keyword\\SearchKeywordAction",
                },
            });
        }
        changeType() {
            document.querySelectorAll(".objectTypeSearchFilters").forEach((filter) => {
                filter.hidden = filter.dataset.objectType !== this.typeInput.value;
            });
        }
        async search() {
            var _a;
            if (!this.queryInput.value.trim() && !this.usernameInput.value.trim()) {
                return;
            }
            this.updateQueryString();
            (_a = this.lastSearchRequest) === null || _a === void 0 ? void 0 : _a.abort();
            const request = (0, Ajax_1.dboAction)("search", "wcf\\data\\search\\SearchAction").payload(this.getFormData());
            this.lastSearchRequest = request.getAbortController();
            const { count, searchID, title, pages, template } = (await request.dispatch());
            document.querySelector(".contentTitle").textContent = title;
            this.searchID = searchID;
            this.activePage = 1;
            this.removeSearchResults();
            if (count > 0) {
                this.pages = pages;
                this.showSearchResults(template);
            }
        }
        updateQueryString() {
            const url = new URL(this.form.action);
            new FormData(this.form).forEach((value, key) => {
                if (value.toString()) {
                    url.search += url.search !== "" ? "&" : "?";
                    url.search += encodeURIComponent(key) + "=" + encodeURIComponent(value.toString());
                }
            });
            window.history.replaceState({}, document.title, url.toString());
        }
        getFormData() {
            const data = {};
            new FormData(this.form).forEach((value, key) => {
                if (value.toString()) {
                    data[key] = value;
                }
            });
            return data;
        }
        initQueryString() {
            const url = new URL(window.location.href);
            url.searchParams.forEach((value, key) => {
                const element = this.form.elements[key];
                if (value && element) {
                    if (element instanceof RadioNodeList) {
                        let id = "";
                        element.forEach((childElement) => {
                            if (childElement.classList.contains("inputDatePicker")) {
                                id = childElement.id;
                            }
                        });
                        if (id) {
                            Picker_1.default.setDate(id, new Date(value));
                        }
                    }
                    else if (element instanceof HTMLInputElement) {
                        if (element.type === "checkbox") {
                            element.checked = true;
                        }
                        else {
                            element.value = value;
                        }
                    }
                    else if (element instanceof HTMLSelectElement) {
                        element.value = value;
                    }
                }
            });
            this.typeInput.dispatchEvent(new Event("change"));
            void this.search();
        }
        initPagination(position) {
            const wrapperDiv = document.createElement("div");
            wrapperDiv.classList.add("pagination" + (0, StringUtil_1.ucfirst)(position));
            this.form.parentElement.appendChild(wrapperDiv);
            const div = document.createElement("div");
            wrapperDiv.appendChild(div);
            new Pagination_1.default(div, {
                activePage: this.activePage,
                maxPage: this.pages,
                callbackSwitch: (pageNo) => {
                    void this.changePage(pageNo);
                },
            });
        }
        async changePage(pageNo) {
            var _a;
            (_a = this.lastSearchResultRequest) === null || _a === void 0 ? void 0 : _a.abort();
            const request = (0, Ajax_1.dboAction)("getSearchResults", "wcf\\data\\search\\SearchAction").payload({
                searchID: this.searchID,
                pageNo,
            });
            this.lastSearchResultRequest = request.getAbortController();
            const { template } = (await request.dispatch());
            this.activePage = pageNo;
            this.removeSearchResults();
            this.showSearchResults(template);
        }
        removeSearchResults() {
            while (this.form.nextSibling !== null) {
                this.form.parentElement.removeChild(this.form.nextSibling);
            }
        }
        showSearchResults(template) {
            if (this.pages > 1) {
                this.initPagination("top");
            }
            const fragment = DomUtil.createFragmentFromHtml(template);
            this.form.parentElement.appendChild(fragment);
            if (this.pages > 1) {
                this.initPagination("bottom");
            }
        }
    }
    exports.UiSearchExtended = UiSearchExtended;
    exports.default = UiSearchExtended;
});
