/**
 * Provides the program logic for the extended search form.
 *
 * @author  Marcel Werk
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Search/Extended
 * @woltlabExcludeBundle all
 */

import { dboAction } from "../../Ajax";
import DatePicker from "../../Date/Picker";
import * as DomUtil from "../../Dom/Util";
import { ucfirst } from "../../StringUtil";
import UiPagination from "../Pagination";
import UiSearchInput from "./Input";
import * as UiScroll from "./../Scroll";

type ResponseSearch = {
  count: number;
  title: string;
  pages?: number;
  pageNo?: number;
  searchID?: number;
  template?: string;
};

type ResponseSearchResults = {
  template: string;
};

type SearchParameters = string[][];

const enum SearchAction {
  Modify,
  Navigation,
  Init,
}

export class UiSearchExtended {
  private readonly form: HTMLFormElement;
  private readonly queryInput: HTMLInputElement;
  private readonly typeInput: HTMLSelectElement;
  private readonly usernameInput: HTMLInputElement;
  private readonly delimiter: HTMLDivElement;
  private searchID: number | undefined = undefined;
  private pages = 0;
  private activePage = 1;
  private lastSearchRequest: AbortController | undefined = undefined;
  private lastSearchResultRequest: AbortController | undefined = undefined;
  private searchParameters: SearchParameters = [];

  constructor() {
    this.form = document.getElementById("extendedSearchForm") as HTMLFormElement;
    this.queryInput = document.getElementById("searchQuery") as HTMLInputElement;
    this.typeInput = document.getElementById("searchType") as HTMLSelectElement;
    this.usernameInput = document.getElementById("searchAuthor") as HTMLInputElement;
    this.delimiter = document.createElement("div");

    this.form.insertAdjacentElement("afterend", this.delimiter);
    this.initEventListener();
    this.initKeywordSuggestions();
    this.initQueryString();
  }

  private initEventListener(): void {
    this.form.addEventListener("submit", (event) => {
      event.preventDefault();
      this.activePage = 1;
      void this.search(SearchAction.Modify);
    });
    this.typeInput.addEventListener("change", () => this.changeType());

    window.addEventListener("popstate", () => {
      this.initQueryString();
    });
  }

  private initKeywordSuggestions(): void {
    new UiSearchInput(this.queryInput, {
      ajax: {
        className: "wcf\\data\\search\\keyword\\SearchKeywordAction",
      },
      autoFocus: false,
    });
  }

  private changeType(): void {
    document.querySelectorAll(".objectTypeSearchFilters").forEach((filter: HTMLElement) => {
      filter.hidden = filter.dataset.objectType !== this.typeInput.value;
    });
  }

  private async search(searchAction: SearchAction): Promise<void> {
    if (!this.queryInput.value.trim() && !this.usernameInput.value.trim()) {
      return;
    }

    this.updateQueryString(searchAction);

    this.lastSearchRequest?.abort();

    const request = dboAction("search", "wcf\\data\\search\\SearchAction").payload(this.getFormData());
    this.lastSearchRequest = request.getAbortController();
    const { count, searchID, title, pages, pageNo, template } = (await request.dispatch()) as ResponseSearch;

    document.querySelector(".contentTitle")!.textContent = title;
    this.searchID = searchID;

    this.removeSearchResults();

    if (count > 0) {
      this.pages = pages!;
      this.activePage = pageNo!;
      this.showSearchResults(template!);
    }
  }

  private updateQueryString(searchAction: SearchAction): void {
    const url = new URL(this.form.action);
    url.search += url.search !== "" ? "&" : "?";

    if (searchAction !== SearchAction.Navigation) {
      this.searchParameters = [];
      new FormData(this.form).forEach((value, key) => {
        if (value.toString().trim()) {
          this.searchParameters.push([key, value.toString().trim()]);
        }
      });
    }
    const parameters = this.searchParameters.slice();

    if (this.activePage > 1) {
      parameters.push(["pageNo", this.activePage.toString()]);
    }
    url.search += new URLSearchParams(parameters);

    if (searchAction === SearchAction.Init) {
      window.history.replaceState({}, document.title, url.toString());
    } else {
      window.history.pushState({}, document.title, url.toString());
    }
  }

  private getFormData(): Record<string, unknown> {
    const data = {};
    new FormData(this.form).forEach((value, key) => {
      if (value.toString()) {
        data[key] = value;
      }
    });
    if (this.activePage > 1) {
      data["pageNo"] = this.activePage;
    }

    return data;
  }

  private initQueryString(): void {
    this.activePage = 1;

    const url = new URL(window.location.href);
    url.searchParams.forEach((value, key) => {
      if (key === "pageNo") {
        this.activePage = parseInt(value, 10);
        if (this.activePage < 1) this.activePage = 1;
        return;
      }

      const element = this.form.elements[key] as HTMLElement;
      if (value && element) {
        if (element instanceof RadioNodeList) {
          let id = "";
          element.forEach((childElement: HTMLElement) => {
            if (childElement.classList.contains("inputDatePicker")) {
              id = childElement.id;
            }
          });
          if (id) {
            DatePicker.setDate(id, new Date(value));
          }
        } else if (element instanceof HTMLInputElement) {
          if (element.type === "checkbox") {
            element.checked = true;
          } else {
            element.value = value;
          }
        } else if (element instanceof HTMLSelectElement) {
          element.value = value;
        }
      }
    });

    this.typeInput.dispatchEvent(new Event("change"));
    void this.search(SearchAction.Init);
  }

  private initPagination(position: "top" | "bottom"): void {
    const wrapperDiv = document.createElement("div");
    wrapperDiv.classList.add("pagination" + ucfirst(position));
    this.form.parentElement!.insertBefore(wrapperDiv, this.delimiter);
    const div = document.createElement("div");
    wrapperDiv.appendChild(div);

    new UiPagination(div, {
      activePage: this.activePage,
      maxPage: this.pages,

      callbackSwitch: (pageNo) => {
        void this.changePage(pageNo).then(() => {
          if (position === "bottom") {
            UiScroll.element(this.form.nextElementSibling as HTMLElement, undefined, "auto");
          }
        });
      },
    });
  }

  private async changePage(pageNo: number): Promise<void> {
    this.lastSearchResultRequest?.abort();

    const request = dboAction("getSearchResults", "wcf\\data\\search\\SearchAction").payload({
      searchID: this.searchID,
      pageNo,
    });
    this.lastSearchResultRequest = request.getAbortController();
    const { template } = (await request.dispatch()) as ResponseSearchResults;
    this.activePage = pageNo;
    this.removeSearchResults();
    this.showSearchResults(template);
    this.updateQueryString(SearchAction.Navigation);
  }

  private removeSearchResults(): void {
    while (this.form.nextSibling !== null && this.form.nextSibling !== this.delimiter) {
      this.form.parentElement!.removeChild(this.form.nextSibling);
    }
  }

  private showSearchResults(template: string): void {
    if (this.pages > 1) {
      this.initPagination("top");
    }

    const fragment = DomUtil.createFragmentFromHtml(template);
    this.form.parentElement!.insertBefore(fragment, this.delimiter);

    if (this.pages > 1) {
      this.initPagination("bottom");
    }
  }
}

export default UiSearchExtended;
