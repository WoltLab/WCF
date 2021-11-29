import { dboAction } from "../../Ajax";
import * as DomUtil from "../../Dom/Util";
import UiSearchInput from "./Input";

type ResponseSearch = {
  count: number;
  title: string;
  searchID?: number;
  template?: string;
};

class UISearchExtended {
  private readonly form: HTMLFormElement;
  private readonly queryInput: HTMLInputElement;
  private readonly typeInput: HTMLSelectElement;
  private readonly usernameInput: HTMLInputElement;
  private lastRequest: AbortController | undefined = undefined;

  constructor() {
    this.form = document.getElementById("extendedSearchForm") as HTMLFormElement;
    this.queryInput = document.getElementById("searchQuery") as HTMLInputElement;
    this.typeInput = document.getElementById("searchType") as HTMLSelectElement;
    this.usernameInput = document.getElementById("searchAuthor") as HTMLInputElement;

    this.initEventListener();
    this.initKeywordSuggestions();
  }

  private initEventListener(): void {
    this.form.addEventListener("submit", (event) => {
      event.preventDefault();
      this.search();
    });
    this.typeInput.addEventListener("change", () => this.changeType());
  }

  private initKeywordSuggestions(): void {
    new UiSearchInput(this.queryInput, {
      ajax: {
        className: "wcf\\data\\search\\keyword\\SearchKeywordAction",
      },
    });
  }

  private changeType(): void {
    document.querySelectorAll(".objectTypeSearchFilters").forEach((filter: HTMLElement) => {
      filter.hidden = filter.dataset.objectType !== this.typeInput.value;
    });
  }

  private async search(): Promise<void> {
    if (!this.queryInput.value.trim() && !this.usernameInput.value.trim()) {
      return;
    }

    if (this.lastRequest) {
      this.lastRequest.abort();
    }
    const request = dboAction("search", "wcf\\data\\search\\SearchAction").payload(this.getFormData());
    this.lastRequest = request.getAbortController();
    const { count, searchID, title, template } = (await request.dispatch()) as ResponseSearch;
    document.querySelector(".contentTitle")!.textContent = title;
    
    while (this.form.nextSibling !== null) {
      this.form.parentElement!.removeChild(this.form.nextSibling);
    }
    
    if (count > 0) {
      const fragment = DomUtil.createFragmentFromHtml(template!);
      this.form.parentElement!.appendChild(fragment);
    }
  }

  private getFormData(): Record<string, unknown> {
    const data = {};
    new FormData(this.form).forEach((value, key) => {
      data[key] = value;
    });

    return data;
  }
}

export = UISearchExtended;
