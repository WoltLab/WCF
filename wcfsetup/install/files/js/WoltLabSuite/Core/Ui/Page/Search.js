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
    class UiPageSearch {
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
            const inputContainer = this.searchInput.parentNode;
            const value = this.searchInput.value.trim();
            if (value.length < 3) {
                Util_1.default.innerError(inputContainer, Language.get('wcf.page.search.error.tooShort'));
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
            const pageTitle = page.querySelector('h3');
            this.callbackSelect(page.dataset.pageId + '#' + pageTitle.textContent.replace(/['"]/g, ''));
            Dialog_1.default.close(this);
        }
        _ajaxSuccess(data) {
            const html = data.returnValues
                .map(page => {
                const name = StringUtil.escapeHTML(page.name);
                const displayLink = StringUtil.escapeHTML(page.displayLink);
                return `<li>
          <div class="containerHeadline pointer" data-page-id="${page.pageID}">
            <h3>${name}</h3>
            <small>${displayLink}</small>
          </div>
        </li>`;
            })
                .join('');
            this.resultList.innerHTML = html;
            Util_1.default[html ? 'show' : 'hide'](this.resultContainer);
            if (html) {
                this.resultList.querySelectorAll('.containerHeadline').forEach(item => {
                    item.addEventListener('click', this.click.bind(this));
                });
            }
            else {
                Util_1.default.innerError(this.searchInput.parentElement, Language.get('wcf.page.search.error.noResults'));
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: 'search',
                    className: 'wcf\\data\\page\\PageAction',
                },
            };
        }
        _dialogSetup() {
            return {
                id: 'wcfUiPageSearch',
                options: {
                    onSetup: () => {
                        this.searchInput = document.getElementById('wcfUiPageSearchInput');
                        this.searchInput.addEventListener('keydown', event => {
                            if (event.key === 'Enter') {
                                this.search(event);
                            }
                        });
                        this.searchInput.nextElementSibling.addEventListener('click', this.search.bind(this));
                        this.resultContainer = document.getElementById('wcfUiPageSearchResultContainer');
                        this.resultList = document.getElementById('wcfUiPageSearchResultList');
                    },
                    onShow: () => {
                        this.searchInput.focus();
                    },
                    title: Language.get('wcf.page.search'),
                },
                source: `<div class="section">
        <dl>
          <dt><label for="wcfUiPageSearchInput">${Language.get('wcf.page.search.name')}</label></dt>
          <dd>
            <div class="inputAddon">
              <input type="text" id="wcfUiPageSearchInput" class="long">
              <a href="#" class="inputSuffix"><span class="icon icon16 fa-search"></span></a>
            </div>
          </dd>
        </dl>
      </div>
      <section id="wcfUiPageSearchResultContainer" class="section" style="display: none;">
        <header class="sectionHeader">
          <h2 class="sectionTitle">${Language.get('wcf.page.search.results')}</h2>
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
    exports.open = open;
});
