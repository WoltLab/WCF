define(["require", "exports", "tslib", "../../Ajax", "../../Dom/Util", "./Input"], function (require, exports, tslib_1, Ajax_1, DomUtil, Input_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UiSearchExtended = void 0;
    DomUtil = (0, tslib_1.__importStar)(DomUtil);
    Input_1 = (0, tslib_1.__importDefault)(Input_1);
    class UiSearchExtended {
        constructor() {
            this.lastRequest = undefined;
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
                this.search();
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
            if (!this.queryInput.value.trim() && !this.usernameInput.value.trim()) {
                return;
            }
            this.updateQueryString();
            if (this.lastRequest) {
                this.lastRequest.abort();
            }
            const request = (0, Ajax_1.dboAction)("search", "wcf\\data\\search\\SearchAction").payload(this.getFormData());
            this.lastRequest = request.getAbortController();
            const { count, searchID, title, template } = (await request.dispatch());
            document.querySelector(".contentTitle").textContent = title;
            while (this.form.nextSibling !== null) {
                this.form.parentElement.removeChild(this.form.nextSibling);
            }
            if (count > 0) {
                const fragment = DomUtil.createFragmentFromHtml(template);
                this.form.parentElement.appendChild(fragment);
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
                if (value && this.form.elements[key]) {
                    this.form.elements[key].value = value;
                }
            });
            this.typeInput.dispatchEvent(new Event("change"));
            this.search();
        }
    }
    exports.UiSearchExtended = UiSearchExtended;
    exports.default = UiSearchExtended;
});
