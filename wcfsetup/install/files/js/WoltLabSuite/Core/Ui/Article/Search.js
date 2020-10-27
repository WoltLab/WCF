var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
define(["require", "exports", "../../Ajax", "../../Dom/Util", "../../Language", "../../StringUtil", "../Dialog"], function (require, exports, Ajax, Util_1, Language, StringUtil, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.open = void 0;
    Ajax = __importStar(Ajax);
    Util_1 = __importDefault(Util_1);
    Language = __importStar(Language);
    StringUtil = __importStar(StringUtil);
    Dialog_1 = __importDefault(Dialog_1);
    class UiArticleSearch {
        constructor() {
            this.callbackSelect = undefined;
            this.resultContainer = undefined;
            this.resultList = undefined;
            this.searchInput = undefined;
        }
        open(callbackSelect) {
            this.callbackSelect = callbackSelect;
            Dialog_1.default.open(this);
        }
        search(event) {
            event.preventDefault();
            const inputContainer = this.searchInput.parentElement;
            const value = this.searchInput.value.trim();
            if (value.length < 3) {
                Util_1.default.innerError(inputContainer, Language.get('wcf.article.search.error.tooShort'));
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
            let html = data.returnValues
                .map(article => {
                return `<li>
          <div class="containerHeadline pointer" data-article-id="${article.articleID}">
            <h3>${StringUtil.escapeHTML(article.name)}</h3>
            <small>${StringUtil.escapeHTML(article.displayLink)}</small>
          </div>
        </li>`;
            })
                .join('');
            this.resultList.innerHTML = html;
            Util_1.default[html ? 'show' : 'hide'](this.resultList);
            if (html) {
                this.resultList.querySelectorAll('.containerHeadline').forEach(item => {
                    item.addEventListener('click', this.click.bind(this));
                });
            }
            else {
                const parent = this.searchInput.parentElement;
                Util_1.default.innerError(parent, Language.get('wcf.article.search.error.noResults'));
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: 'search',
                    className: 'wcf\\data\\article\\ArticleAction',
                },
            };
        }
        _dialogSetup() {
            return {
                id: 'wcfUiArticleSearch',
                options: {
                    onSetup: () => {
                        this.searchInput = document.getElementById('wcfUiArticleSearchInput');
                        this.searchInput.addEventListener('keydown', event => {
                            if (event.key === 'Enter') {
                                this.search(event);
                            }
                        });
                        const button = this.searchInput.nextElementSibling;
                        button.addEventListener('click', this.search.bind(this));
                        this.resultContainer = document.getElementById('wcfUiArticleSearchResultContainer');
                        this.resultList = document.getElementById('wcfUiArticleSearchResultList');
                    },
                    onShow: () => {
                        this.searchInput.focus();
                    },
                    title: Language.get('wcf.article.search'),
                },
                source: `<div class="section">
          <dl>
            <dt>
              <label for="wcfUiArticleSearchInput">${Language.get('wcf.article.search.name')}</label>
            </dt>
            <dd>
              <div class="inputAddon">
                <input type="text" id="wcfUiArticleSearchInput" class="long">
                <a href="#" class="inputSuffix"><span class="icon icon16 fa-search"></span></a>
              </div>
            </dd>
          </dl>
        </div>
        <section id="wcfUiArticleSearchResultContainer" class="section" style="display: none;">
          <header class="sectionHeader">
            <h2 class="sectionTitle">${Language.get('wcf.article.search.results')}</h2>
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
    exports.open = open;
});
