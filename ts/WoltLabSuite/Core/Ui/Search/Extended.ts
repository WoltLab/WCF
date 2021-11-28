import { dboAction } from "../../Ajax";
import * as Language from "../../Language";
import * as DomUtil from "../../Dom/Util";

type ResponseSearch = {
  count: number;
  searchID?: number;
  title?: string;
  template?: string;
};

class UISearchExtended {
  private readonly form: HTMLFormElement;
  private readonly queryInput: HTMLInputElement;
  private readonly typeInput: HTMLSelectElement;
  private lastRequest: AbortController | undefined = undefined;

  constructor() {
    this.form = document.getElementById("extendedSearchForm") as HTMLFormElement;
    this.queryInput = document.getElementById("searchQuery") as HTMLInputElement;
    this.typeInput = document.getElementById("searchType") as HTMLSelectElement;

    this.form.addEventListener("submit", (event) => {
      event.preventDefault();
      this.search();
    });
    this.typeInput.addEventListener("change", () => this.changeType());
  }

  private changeType(): void {
    document.querySelectorAll(".objectTypeSearchFilters").forEach((filter: HTMLElement) => {
      filter.hidden = filter.dataset.objectType !== this.typeInput.value;
    });
  }

  private async search(): Promise<void> {
    if (this.lastRequest) {
      this.lastRequest.abort();
    }
    const request = dboAction("search", "wcf\\data\\search\\SearchAction").payload(this.getFormData());
    this.lastRequest = request.getAbortController();
    const { count, searchID, title, template } = (await request.dispatch()) as ResponseSearch;
    if (count > 0) {
      document.querySelector(".contentTitle")!.textContent = title!;
      const fragment = DomUtil.createFragmentFromHtml(template!);

      const marker = document.getElementById("searchResultContainer")!;
      marker.parentElement!.insertBefore(fragment, marker);
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
