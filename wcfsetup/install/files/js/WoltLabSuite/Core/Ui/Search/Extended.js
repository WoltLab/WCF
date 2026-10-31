/**
 * Provides the program logic for the extended search form.
 *
 * @author  Marcel Werk
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Date/Picker", "../../Dom/Util", "./Input", "../ItemList"], function (require, exports, tslib_1, Ajax_1, Picker_1, DomUtil, Input_1, ItemList_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UiSearchExtended = void 0;
    Picker_1 = tslib_1.__importDefault(Picker_1);
    DomUtil = tslib_1.__importStar(DomUtil);
    Input_1 = tslib_1.__importDefault(Input_1);
    class UiSearchExtended {
        form;
        queryInput;
        typeInput;
        delimiter;
        filtersContainer;
        activePage = 1;
        lastSearchRequest = undefined;
        searchParameters = [];
        constructor() {
            this.form = document.getElementById("extendedSearchForm");
            this.queryInput = document.getElementById("searchQuery");
            this.typeInput = document.getElementById("searchType");
            this.filtersContainer = document.querySelector(".searchFiltersContainer");
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
                void this.search(0 /* SearchAction.Modify */);
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
            let hasVisibleFilters = false;
            document.querySelectorAll(".objectTypeSearchFilters").forEach((filter) => {
                if (filter.dataset.objectType === this.typeInput.value) {
                    hasVisibleFilters = true;
                    filter.hidden = false;
                }
                else {
                    filter.hidden = true;
                }
            });
            const title = document.querySelector(".searchFiltersTitle");
            if (hasVisibleFilters) {
                const selectedOption = this.typeInput.selectedOptions.item(0);
                title.textContent = selectedOption.textContent.trim();
                title.hidden = false;
            }
            else {
                title.hidden = true;
            }
        }
        async search(searchAction) {
            if (!this.queryInput.value.trim()) {
                return;
            }
            this.updateQueryString(searchAction);
            this.lastSearchRequest?.abort();
            const request = (0, Ajax_1.dboAction)("search", "wcf\\data\\search\\SearchAction").payload(this.getFormData());
            this.lastSearchRequest = request.getAbortController();
            const { count, title, pageNo, template } = (await request.dispatch());
            document.querySelector(".contentTitle").textContent = title;
            this.removeSearchResults();
            if (count > 0) {
                this.activePage = pageNo;
                this.showSearchResults(template);
            }
            else if (Object.keys(this.getFormData()).length > 4) {
                // Show the advanced filters when there are no results
                // but advanced filters are applied.
                this.filtersContainer.open = true;
            }
        }
        updateQueryString(searchAction) {
            const url = new URL(this.form.action);
            url.search += url.search !== "" ? "&" : "?";
            if (searchAction !== 1 /* SearchAction.Navigation */) {
                this.searchParameters = [];
                new FormData(this.form).forEach((value, key) => {
                    // eslint-disable-next-line @typescript-eslint/no-base-to-string
                    const trimmed = value.toString().trim();
                    if (trimmed) {
                        this.searchParameters.push([key, trimmed]);
                    }
                });
            }
            const parameters = this.searchParameters.slice();
            if (this.activePage > 1) {
                parameters.push(["pageNo", this.activePage.toString()]);
            }
            url.search += new URLSearchParams(parameters).toString();
            if (searchAction === 2 /* SearchAction.Init */) {
                window.history.replaceState({ searchAction }, document.title, url.toString());
            }
            else {
                window.history.pushState({ searchAction }, document.title, url.toString());
            }
        }
        getFormData() {
            const data = {};
            new FormData(this.form).forEach((value, key) => {
                // eslint-disable-next-line @typescript-eslint/no-base-to-string
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
                            return;
                        }
                        element.value = value;
                    }
                    else if (element instanceof HTMLInputElement) {
                        if (element.classList.contains("itemListInputShadow")) {
                            const itemList = element.nextElementSibling;
                            if (itemList?.classList.contains("inputItemList")) {
                                (0, ItemList_1.setValues)(itemList.dataset.elementId, value.split(",").map((value) => {
                                    return {
                                        objectId: 0,
                                        value: value.trim(),
                                    };
                                }));
                            }
                            return;
                        }
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
            void this.search(2 /* SearchAction.Init */);
        }
        removeSearchResults() {
            while (this.form.nextSibling !== null && this.form.nextSibling !== this.delimiter) {
                this.form.parentElement.removeChild(this.form.nextSibling);
            }
        }
        showSearchResults(template) {
            const container = document.createElement("div");
            container.classList.add("section");
            this.form.parentElement.insertBefore(container, this.delimiter);
            DomUtil.setInnerHtml(container, template);
            // The list view handles the pagination, but the page number is part of the search url.
            container.querySelector("woltlab-core-pagination")?.addEventListener("switchPage", (event) => {
                this.activePage = event.detail;
                this.updateQueryString(1 /* SearchAction.Navigation */);
            });
        }
    }
    exports.UiSearchExtended = UiSearchExtended;
    exports.default = UiSearchExtended;
});
