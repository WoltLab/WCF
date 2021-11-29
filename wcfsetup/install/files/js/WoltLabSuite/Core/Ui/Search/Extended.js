define(["require", "exports", "tslib", "../../Ajax", "../../Dom/Util", "./Input"], function (require, exports, tslib_1, Ajax_1, DomUtil, Input_1) {
    "use strict";
    DomUtil = (0, tslib_1.__importStar)(DomUtil);
    Input_1 = (0, tslib_1.__importDefault)(Input_1);
    class UISearchExtended {
        constructor() {
            this.lastRequest = undefined;
            this.form = document.getElementById("extendedSearchForm");
            this.queryInput = document.getElementById("searchQuery");
            this.typeInput = document.getElementById("searchType");
            this.usernameInput = document.getElementById("searchAuthor");
            this.initEventListener();
            this.initKeywordSuggestions();
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
            if (this.lastRequest) {
                this.lastRequest.abort();
            }
            const request = (0, Ajax_1.dboAction)("search", "wcf\\data\\search\\SearchAction").payload(this.getFormData());
            this.lastRequest = request.getAbortController();
            const { count, searchID, title, template } = (await request.dispatch());
            if (count > 0) {
                document.querySelector(".contentTitle").textContent = title;
                const fragment = DomUtil.createFragmentFromHtml(template);
                const marker = document.getElementById("searchResultContainer");
                marker.parentElement.insertBefore(fragment, marker);
            }
        }
        getFormData() {
            const data = {};
            new FormData(this.form).forEach((value, key) => {
                data[key] = value;
            });
            return data;
        }
    }
    return UISearchExtended;
});
