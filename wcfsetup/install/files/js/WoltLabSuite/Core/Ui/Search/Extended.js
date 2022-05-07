/**
 * Provides the program logic for the extended search form.
 *
 * @author  Marcel Werk
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Search/Extended
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Date/Picker", "../../Dom/Util", "../../StringUtil", "../Pagination", "./Input", "./../Scroll"], function (require, exports, tslib_1, Ajax_1, Picker_1, DomUtil, StringUtil_1, Pagination_1, Input_1, UiScroll) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UiSearchExtended = void 0;
    Picker_1 = tslib_1.__importDefault(Picker_1);
    DomUtil = tslib_1.__importStar(DomUtil);
    Pagination_1 = tslib_1.__importDefault(Pagination_1);
    Input_1 = tslib_1.__importDefault(Input_1);
    UiScroll = tslib_1.__importStar(UiScroll);
    class UiSearchExtended {
        constructor() {
            this.searchID = undefined;
            this.pages = 0;
            this.activePage = 1;
            this.lastSearchRequest = undefined;
            this.lastSearchResultRequest = undefined;
            this.searchParameters = [];
            this.form = document.getElementById("extendedSearchForm");
            this.queryInput = document.getElementById("searchQuery");
            this.typeInput = document.getElementById("searchType");
            this.usernameInput = document.getElementById("searchAuthor");
            this.delimiter = document.createElement("div");
            this.form.insertAdjacentElement("afterend", this.delimiter);
            this.initEventListener();
            this.initKeywordSuggestions();
            this.initQueryString();
        }
        initEventListener() {
            this.form.addEventListener("submit", (event) => {
                event.preventDefault();
                this.activePage = 1;
                void this.search(0 /* Modify */);
            });
            this.typeInput.addEventListener("change", () => this.changeType());
            window.addEventListener("popstate", () => {
                this.initQueryString();
            });
        }
        initKeywordSuggestions() {
            new Input_1.default(this.queryInput, {
                ajax: {
                    className: "wcf\\data\\search\\keyword\\SearchKeywordAction",
                },
                autoFocus: false,
            });
        }
        changeType() {
            document.querySelectorAll(".objectTypeSearchFilters").forEach((filter) => {
                filter.hidden = filter.dataset.objectType !== this.typeInput.value;
            });
        }
        async search(searchAction) {
            var _a;
            if (!this.queryInput.value.trim() && !this.usernameInput.value.trim()) {
                return;
            }
            this.updateQueryString(searchAction);
            (_a = this.lastSearchRequest) === null || _a === void 0 ? void 0 : _a.abort();
            const request = (0, Ajax_1.dboAction)("search", "wcf\\data\\search\\SearchAction").payload(this.getFormData());
            this.lastSearchRequest = request.getAbortController();
            const { count, searchID, title, pages, pageNo, template } = (await request.dispatch());
            document.querySelector(".contentTitle").textContent = title;
            this.searchID = searchID;
            this.removeSearchResults();
            if (count > 0) {
                this.pages = pages;
                this.activePage = pageNo;
                this.showSearchResults(template);
            }
        }
        updateQueryString(searchAction) {
            const url = new URL(this.form.action);
            url.search += url.search !== "" ? "&" : "?";
            if (searchAction !== 1 /* Navigation */) {
                this.searchParameters = [];
                new FormData(this.form).forEach((value, key) => {
                    if (value.toString().trim()) {
                        this.searchParameters.push([key, value.toString().trim()]);
                    }
                });
            }
            const parameters = this.searchParameters.slice();
            if (this.activePage > 1) {
                parameters.push(["pageNo", this.activePage.toString()]);
            }
            url.search += new URLSearchParams(parameters);
            if (searchAction === 2 /* Init */) {
                window.history.replaceState({}, document.title, url.toString());
            }
            else {
                window.history.pushState({}, document.title, url.toString());
            }
        }
        getFormData() {
            const data = {};
            new FormData(this.form).forEach((value, key) => {
                if (value.toString()) {
                    data[key] = value;
                }
            });
            if (this.activePage > 1) {
                data["pageNo"] = this.activePage;
            }
            return data;
        }
        initQueryString() {
            this.activePage = 1;
            const url = new URL(window.location.href);
            url.searchParams.forEach((value, key) => {
                if (key === "pageNo") {
                    this.activePage = parseInt(value, 10);
                    if (this.activePage < 1)
                        this.activePage = 1;
                    return;
                }
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
            void this.search(2 /* Init */);
        }
        initPagination(position) {
            const wrapperDiv = document.createElement("div");
            wrapperDiv.classList.add("pagination" + (0, StringUtil_1.ucfirst)(position));
            this.form.parentElement.insertBefore(wrapperDiv, this.delimiter);
            const div = document.createElement("div");
            wrapperDiv.appendChild(div);
            new Pagination_1.default(div, {
                activePage: this.activePage,
                maxPage: this.pages,
                callbackSwitch: (pageNo) => {
                    void this.changePage(pageNo).then(() => {
                        if (position === "bottom") {
                            UiScroll.element(this.form.nextElementSibling, undefined, "auto");
                        }
                    });
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
            this.updateQueryString(1 /* Navigation */);
        }
        removeSearchResults() {
            while (this.form.nextSibling !== null && this.form.nextSibling !== this.delimiter) {
                this.form.parentElement.removeChild(this.form.nextSibling);
            }
        }
        showSearchResults(template) {
            if (this.pages > 1) {
                this.initPagination("top");
            }
            const fragment = DomUtil.createFragmentFromHtml(template);
            this.form.parentElement.insertBefore(fragment, this.delimiter);
            if (this.pages > 1) {
                this.initPagination("bottom");
            }
        }
    }
    exports.UiSearchExtended = UiSearchExtended;
    exports.default = UiSearchExtended;
});
