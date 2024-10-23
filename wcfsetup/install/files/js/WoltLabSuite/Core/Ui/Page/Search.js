/**
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Dom/Util", "../../Language", "../../StringUtil", "../Dialog"], function (require, exports, tslib_1, Ajax, Util_1, Language, StringUtil, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.open = open;
    Ajax = tslib_1.__importStar(Ajax);
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    StringUtil = tslib_1.__importStar(StringUtil);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class UiPageSearch {
        callbackSelect = undefined;
        resultContainer = undefined;
        resultList = undefined;
        searchInput = undefined;
        open(callbackSelect) {
            this.callbackSelect = callbackSelect;
            Dialog_1.default.open(this);
        }
        search(event) {
            event.preventDefault();
            const inputContainer = this.searchInput.parentNode;
            const value = this.searchInput.value.trim();
            if (value.length < 3) {
                Util_1.default.innerError(inputContainer, Language.get("wcf.page.search.error.tooShort"));
                return;
            }
            else {
                Util_1.default.innerError(inputContainer, false);
            }
            Ajax.api(this, {
                parameters: {
                    searchString: value,
                },
            });
        }
        click(event) {
            event.preventDefault();
            const page = event.currentTarget;
            const pageTitle = page.querySelector("h3");
            this.callbackSelect(page.dataset.pageId + "#" + pageTitle.textContent.replace(/['"]/g, ""));
            Dialog_1.default.close(this);
        }
        _ajaxSuccess(data) {
            const html = data.returnValues
                .map((page) => {
                const name = StringUtil.escapeHTML(page.name);
                const displayLink = StringUtil.escapeHTML(page.displayLink);
                return `<li>
          <div class="containerHeadline pointer" data-page-id="${page.pageID}">
            <h3>${name}</h3>
            <small>${displayLink}</small>
          </div>
        </li>`;
            })
                .join("");
            this.resultList.innerHTML = html;
            Util_1.default[html ? "show" : "hide"](this.resultContainer);
            if (html) {
                this.resultList.querySelectorAll(".containerHeadline").forEach((item) => {
                    item.addEventListener("click", (ev) => this.click(ev));
                });
            }
            else {
                Util_1.default.innerError(this.searchInput.parentElement, Language.get("wcf.page.search.error.noResults"));
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "search",
                    className: "wcf\\data\\page\\PageAction",
                },
            };
        }
        _dialogSetup() {
            return {
                id: "wcfUiPageSearch",
                options: {
                    onSetup: () => {
                        this.searchInput = document.getElementById("wcfUiPageSearchInput");
                        this.searchInput.addEventListener("keydown", (event) => {
                            if (event.key === "Enter") {
                                this.search(event);
                            }
                        });
                        this.searchInput.nextElementSibling.addEventListener("click", (ev) => this.search(ev));
                        this.resultContainer = document.getElementById("wcfUiPageSearchResultContainer");
                        this.resultList = document.getElementById("wcfUiPageSearchResultList");
                    },
                    onShow: () => {
                        this.searchInput.focus();
                    },
                    title: Language.get("wcf.page.search"),
                },
                source: `<div class="section">
        <dl>
          <dt><label for="wcfUiPageSearchInput">${Language.get("wcf.page.search.name")}</label></dt>
          <dd>
            <div class="inputAddon">
              <input type="text" id="wcfUiPageSearchInput" class="long">
              <button type="button" class="button inputSuffix"><fa-icon name="search" solid></fa-icon></button>
            </div>
          </dd>
        </dl>
      </div>
      <section id="wcfUiPageSearchResultContainer" class="section" style="display: none;">
        <header class="sectionHeader">
          <h2 class="sectionTitle">${Language.get("wcf.page.search.results")}</h2>
        </header>
        <ol id="wcfUiPageSearchResultList" class="containerList"></ol>
      </section>`,
            };
        }
    }
    let uiPageSearch = undefined;
    function getUiPageSearch() {
        if (uiPageSearch === undefined) {
            uiPageSearch = new UiPageSearch();
        }
        return uiPageSearch;
    }
    function open(callbackSelect) {
        getUiPageSearch().open(callbackSelect);
    }
});
