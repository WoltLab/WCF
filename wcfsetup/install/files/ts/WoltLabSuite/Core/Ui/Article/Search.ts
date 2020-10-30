import * as Ajax from "../../Ajax";
import { AjaxCallbackObject, CallbackSetup, DatabaseObjectActionResponse } from "../../Ajax/Data";
import { DialogCallbackObject } from "../Dialog/Data";
import DomUtil from "../../Dom/Util";
import * as Language from "../../Language";
import * as StringUtil from "../../StringUtil";
import UiDialog from "../Dialog";

type CallbackSelect = (articleId: number) => void;

interface SearchResult {
  articleID: number;
  displayLink: string;
  name: string;
}

interface AjaxResponse extends DatabaseObjectActionResponse {
  returnValues: SearchResult[];
}

class UiArticleSearch implements AjaxCallbackObject, DialogCallbackObject {
  private callbackSelect?: CallbackSelect = undefined;
  private resultContainer?: HTMLElement = undefined;
  private resultList?: HTMLOListElement = undefined;
  private searchInput?: HTMLInputElement = undefined;

  open(callbackSelect: CallbackSelect) {
    this.callbackSelect = callbackSelect;

    UiDialog.open(this);
  }

  private search(event: KeyboardEvent): void {
    event.preventDefault();

    const inputContainer = this.searchInput!.parentElement!;

    const value = this.searchInput!.value.trim();
    if (value.length < 3) {
      DomUtil.innerError(inputContainer, Language.get("wcf.article.search.error.tooShort"));
      return;
    } else {
      DomUtil.innerError(inputContainer, false);
    }

    Ajax.api(this, {
      parameters: {
        searchString: value,
      },
    });
  }

  private click(event: MouseEvent): void {
    event.preventDefault();

    const target = event.currentTarget as HTMLElement;
    this.callbackSelect!(+target.dataset.articleId!);

    UiDialog.close(this);
  }

  _ajaxSuccess(data: AjaxResponse): void {
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

    this.resultList!.innerHTML = html;

    if (html) {
      DomUtil.show(this.resultList!);
    } else {
      DomUtil.hide(this.resultList!);
    }

    if (html) {
      this.resultList!.querySelectorAll(".containerHeadline").forEach((item) => {
        item.addEventListener("click", this.click.bind(this));
      });
    } else {
      const parent = this.searchInput!.parentElement!;
      DomUtil.innerError(parent, Language.get("wcf.article.search.error.noResults"));
    }
  }

  _ajaxSetup(): ReturnType<CallbackSetup> {
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
          this.searchInput = document.getElementById("wcfUiArticleSearchInput") as HTMLInputElement;
          this.searchInput.addEventListener("keydown", (event) => {
            if (event.key === "Enter") {
              this.search(event);
            }
          });

          const button = this.searchInput.nextElementSibling!;
          button.addEventListener("click", this.search.bind(this));

          this.resultContainer = document.getElementById("wcfUiArticleSearchResultContainer")!;
          this.resultList = document.getElementById("wcfUiArticleSearchResultList") as HTMLOListElement;
        },
        onShow: () => {
          this.searchInput!.focus();
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
                <a href="#" class="inputSuffix"><span class="icon icon16 fa-search"></span></a>
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

let uiArticleSearch: UiArticleSearch | undefined = undefined;

function getUiArticleSearch() {
  if (!uiArticleSearch) {
    uiArticleSearch = new UiArticleSearch();
  }

  return uiArticleSearch;
}

export function open(callbackSelect) {
  getUiArticleSearch().open(callbackSelect);
}
