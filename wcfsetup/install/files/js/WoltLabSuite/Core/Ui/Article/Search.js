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
    class UiArticleSearch {
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
            const inputContainer = this.searchInput.parentElement;
            const value = this.searchInput.value.trim();
            if (value.length < 3) {
                Util_1.default.innerError(inputContainer, Language.get("wcf.article.search.error.tooShort"));
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
            const target = event.currentTarget;
            this.callbackSelect(+target.dataset.articleId);
            Dialog_1.default.close(this);
        }
        _ajaxSuccess(data) {
            const html = data.returnValues
                .map((article) => {
                return `<li>
          <div class="containerHeadline pointer" data-article-id="${article.articleID}">
            <h3>${StringUtil.escapeHTML(article.name)}</h3>
            <small>${StringUtil.escapeHTML(article.displayLink)}</small>
          </div>
        </li>`;
            })
                .join("");
            this.resultList.innerHTML = html;
            if (html) {
                Util_1.default.show(this.resultContainer);
            }
            else {
                Util_1.default.hide(this.resultContainer);
            }
            if (html) {
                this.resultList.querySelectorAll(".containerHeadline").forEach((item) => {
                    item.addEventListener("click", this.click.bind(this));
                });
            }
            else {
                const parent = this.searchInput.parentElement;
                Util_1.default.innerError(parent, Language.get("wcf.article.search.error.noResults"));
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "search",
                    className: "wcf\\data\\article\\ArticleAction",
                },
            };
        }
        _dialogSetup() {
            return {
                id: "wcfUiArticleSearch",
                options: {
                    onSetup: () => {
                        this.searchInput = document.getElementById("wcfUiArticleSearchInput");
                        this.searchInput.addEventListener("keydown", (event) => {
                            if (event.key === "Enter") {
                                this.search(event);
                            }
                        });
                        const button = this.searchInput.nextElementSibling;
                        button.addEventListener("click", this.search.bind(this));
                        this.resultContainer = document.getElementById("wcfUiArticleSearchResultContainer");
                        this.resultList = document.getElementById("wcfUiArticleSearchResultList");
                    },
                    onShow: () => {
                        this.searchInput.focus();
                    },
                    title: Language.get("wcf.article.search"),
                },
                source: `<div class="section">
          <dl>
            <dt>
              <label for="wcfUiArticleSearchInput">${Language.get("wcf.article.search.name")}</label>
            </dt>
            <dd>
              <div class="inputAddon">
                <input type="text" id="wcfUiArticleSearchInput" class="long">
                <a href="#" class="inputSuffix"><fa-icon name="search" solid></fa-icon></a>
              </div>
            </dd>
          </dl>
        </div>
        <section id="wcfUiArticleSearchResultContainer" class="section" style="display: none;">
          <header class="sectionHeader">
            <h2 class="sectionTitle">${Language.get("wcf.article.search.results")}</h2>
          </header>
          <ol id="wcfUiArticleSearchResultList" class="containerList"></ol>
        </section>`,
            };
        }
    }
    let uiArticleSearch = undefined;
    function getUiArticleSearch() {
        if (!uiArticleSearch) {
            uiArticleSearch = new UiArticleSearch();
        }
        return uiArticleSearch;
    }
    function open(callbackSelect) {
        getUiArticleSearch().open(callbackSelect);
    }
});
