/**
 * Provides access to the lookup function of page handlers, allowing the user to search and
 * select page object ids.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

import * as Language from "../../../Language";
import * as StringUtil from "../../../StringUtil";
import DomUtil from "../../../Dom/Util";
import UiDialog from "../../Dialog";
import { DialogCallbackObject, DialogCallbackSetup } from "../../Dialog/Data";
import UiPageSearchInput from "./Input";
import { DatabaseObjectActionResponse } from "../../../Ajax/Data";

type CallbackSelect = (objectId: number) => void;

interface ItemData {
  description?: string;
  image: string | string[];
  link: string;
  objectID: number;
  title: string;
}

interface AjaxResponse extends DatabaseObjectActionResponse {
  returnValues: ItemData[];
}

class UiPageSearchHandler implements DialogCallbackObject {
  private callbackSuccess?: CallbackSelect = undefined;
  private resultList?: HTMLUListElement = undefined;
  private resultListContainer?: HTMLElement = undefined;
  private searchInput?: HTMLInputElement = undefined;
  private searchInputHandler?: UiPageSearchInput = undefined;
  private searchInputLabel?: HTMLLabelElement = undefined;

  /**
   * Opens the lookup overlay for provided page id.
   */
  open(pageId: number, title: string, callback: CallbackSelect, labelLanguageItem?: string): void {
    this.callbackSuccess = callback;

    UiDialog.open(this);
    UiDialog.setTitle(this, title);

    this.searchInputLabel!.textContent = Language.get(labelLanguageItem || "wcf.page.pageObjectID.search.terms");

    this._getSearchInputHandler().setPageId(pageId);
  }

  /**
   * Builds the result list.
   */
  private buildList(data: AjaxResponse): void {
    this.resetList();

    if (!Array.isArray(data.returnValues) || data.returnValues.length === 0) {
      DomUtil.innerError(this.searchInput!, Language.get("wcf.page.pageObjectID.search.noResults"));
      return;
    }

    data.returnValues.forEach((item) => {
      let image = item.image;
      if (Array.isArray(image)) {
        const [iconName, forceSolid] = image;

        image = `
          <button type="button" class="jsTooltip" title="${Language.get("wcf.global.select")}">
            <fa-icon size="48" name="${iconName}"${forceSolid ? " solid" : ""}></fa-icon>
          </button>
        `;
      }

      const listItem = document.createElement("li");
      listItem.dataset.objectId = item.objectID.toString();

      const description = item.description ? `<p>${item.description}</p>` : "";
      listItem.innerHTML = `<div class="box48">
        ${image}
        <div>
          <div class="containerHeadline">
            <h3>
                <button type="button">${StringUtil.escapeHTML(item.title)}</button>
            </h3>
            ${description}
          </div>
        </div>
      </div>`;

      listItem.addEventListener("click", () => {
        this.click(item.objectID);
      });

      this.resultList!.appendChild(listItem);
    });

    DomUtil.show(this.resultListContainer!);
  }

  /**
   * Resets the list and removes any error elements.
   */
  private resetList(): void {
    DomUtil.innerError(this.searchInput!, false);

    this.resultList!.innerHTML = "";

    DomUtil.hide(this.resultListContainer!);
  }

  /**
   * Initializes the search input handler and returns the instance.
   */
  _getSearchInputHandler(): UiPageSearchInput {
    if (!this.searchInputHandler) {
      const input = document.getElementById("wcfUiPageSearchInput") as HTMLInputElement;
      this.searchInputHandler = new UiPageSearchInput(input, {
        callbackSuccess: this.buildList.bind(this),
      });
    }

    return this.searchInputHandler;
  }

  /**
   * Selects an item from the results.
   */
  private click(objectId: number): void {
    this.callbackSuccess!(objectId);

    UiDialog.close(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "wcfUiPageSearchHandler",
      options: {
        onShow: (content: HTMLElement): void => {
          if (!this.searchInput) {
            this.searchInput = document.getElementById("wcfUiPageSearchInput") as HTMLInputElement;
            this.searchInputLabel = content.querySelector('label[for="wcfUiPageSearchInput"]') as HTMLLabelElement;
            this.resultList = document.getElementById("wcfUiPageSearchResultList") as HTMLUListElement;
            this.resultListContainer = document.getElementById("wcfUiPageSearchResultListContainer") as HTMLElement;
          }

          // clear search input
          this.searchInput.value = "";

          // reset results
          DomUtil.hide(this.resultListContainer!);
          this.resultList!.innerHTML = "";

          this.searchInput.focus();
        },
        title: "",
      },
      source: `<div class="section">
        <dl>
          <dt>
            <label for="wcfUiPageSearchInput">${Language.get("wcf.page.pageObjectID.search.terms")}</label>
          </dt>
          <dd>
            <input type="text" id="wcfUiPageSearchInput" class="long">
          </dd>
        </dl>
      </div>
      <section id="wcfUiPageSearchResultListContainer" class="section sectionContainerList">
        <header class="sectionHeader">
          <h2 class="sectionTitle">${Language.get("wcf.page.pageObjectID.search.results")}</h2>
        </header>
        <ul id="wcfUiPageSearchResultList" class="containerList wcfUiPageSearchResultList"></ul>
      </section>`,
    };
  }
}

let uiPageSearchHandler: UiPageSearchHandler | undefined = undefined;

function getUiPageSearchHandler(): UiPageSearchHandler {
  if (!uiPageSearchHandler) {
    uiPageSearchHandler = new UiPageSearchHandler();
  }

  return uiPageSearchHandler;
}

/**
 * Opens the lookup overlay for provided page id.
 */
export function open(pageId: number, title: string, callback: CallbackSelect, labelLanguageItem?: string): void {
  getUiPageSearchHandler().open(pageId, title, callback, labelLanguageItem);
}
