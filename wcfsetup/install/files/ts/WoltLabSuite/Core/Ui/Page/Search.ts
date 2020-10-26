import * as Ajax from '../../Ajax';
import { AjaxCallbackObject, DatabaseObjectActionResponse } from '../../Ajax/Data';
import { DialogCallbackObject } from '../Dialog/Data';
import DomUtil from '../../Dom/Util';
import * as Language from '../../Language';
import * as StringUtil from '../../StringUtil';
import UiDialog from '../Dialog';

type CallbackSelect = (value: string) => void

interface SearchResult {
  displayLink: string;
  name: string;
  pageID: number;
}

interface AjaxResponse extends DatabaseObjectActionResponse {
  returnValues: SearchResult[];
}

class UiPageSearch implements AjaxCallbackObject, DialogCallbackObject {
  private callbackSelect?: CallbackSelect = undefined;
  private resultContainer?: HTMLElement = undefined;
  private resultList?: HTMLOListElement = undefined;
  private searchInput?: HTMLInputElement = undefined;

  open(callbackSelect: CallbackSelect): void {
    this.callbackSelect = callbackSelect;

    UiDialog.open(this);
  }

  _search(event: KeyboardEvent): void {
    event.preventDefault();

    const inputContainer = this.searchInput!.parentNode as HTMLElement;

    const value = this.searchInput!.value.trim();
    DomUtil.innerError(inputContainer, value.length < 3 ? Language.get('wcf.page.search.error.tooShort') : false);

    Ajax.api(this, {
      parameters: {
        searchString: value,
      },
    });
  }

  _click(event: MouseEvent): void {
    event.preventDefault();

    const page = event.currentTarget as HTMLElement;
    const pageTitle = page.querySelector('h3')!;

    this.callbackSelect!(page.dataset.pageId! + '#' + pageTitle.textContent!.replace(/['"]/g, ''));

    UiDialog.close(this);
  }

  _ajaxSuccess(data: AjaxResponse): void {
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

    this.resultList!.innerHTML = html;

    DomUtil[html ? 'show' : 'hide'](this.resultContainer!);

    if (html) {
      this.resultList!.querySelectorAll('.containerHeadline').forEach(item => {
        item.addEventListener('click', this._click.bind(this));
      });
    } else {
      DomUtil.innerError(this.searchInput!.parentElement!, Language.get('wcf.page.search.error.noResults'));
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
          this.searchInput = document.getElementById('wcfUiPageSearchInput') as HTMLInputElement;
          this.searchInput.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
              this._search(event);
            }
          });

          this.searchInput.nextElementSibling!.addEventListener('click', this._search.bind(this));

          this.resultContainer = document.getElementById('wcfUiPageSearchResultContainer') as HTMLElement;
          this.resultList = document.getElementById('wcfUiPageSearchResultList') as HTMLOListElement;
        },
        onShow: () => {
          this.searchInput!.focus();
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

let uiPageSearch: UiPageSearch | undefined = undefined;

function getUiPageSearch(): UiPageSearch {
  if (uiPageSearch === undefined) {
    uiPageSearch = new UiPageSearch();
  }

  return uiPageSearch;
}

export function open(callbackSelect) {
  getUiPageSearch().open(callbackSelect);
}

  
