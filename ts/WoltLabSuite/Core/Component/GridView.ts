import { getRows } from "../Api/GridViews/GetRows";
import DomUtil from "../Dom/Util";

export class GridView {
  readonly #gridClassName: string;
  readonly #table: HTMLTableElement;
  readonly #topPagination: WoltlabCorePaginationElement;
  readonly #bottomPagination: WoltlabCorePaginationElement;
  readonly #baseUrl: string;
  #pageNo: number;

  constructor(gridId: string, gridClassName: string, pageNo: number, baseUrl: string = "") {
    this.#gridClassName = gridClassName;
    this.#table = document.getElementById(`${gridId}_table`) as HTMLTableElement;
    this.#topPagination = document.getElementById(`${gridId}_topPagination`) as WoltlabCorePaginationElement;
    this.#bottomPagination = document.getElementById(`${gridId}_bottomPagination`) as WoltlabCorePaginationElement;
    this.#pageNo = pageNo;
    this.#baseUrl = baseUrl;

    this.#initPagination();
  }

  #initPagination(): void {
    this.#topPagination.addEventListener("switchPage", (event: CustomEvent) => {
      this.#switchPage(event.detail);
    });
    this.#bottomPagination.addEventListener("switchPage", (event: CustomEvent) => {
      this.#switchPage(event.detail);
    });
  }

  async #switchPage(pageNo: number): Promise<void> {
    this.#topPagination.page = pageNo;
    this.#bottomPagination.page = pageNo;

    const response = await getRows(this.#gridClassName, pageNo);
    this.#pageNo = pageNo;
    DomUtil.setInnerHtml(this.#table.querySelector("tbody")!, response.unwrap().template);
    this.#updateQueryString();
  }

  #updateQueryString(): void {
    if (!this.#baseUrl) {
      return;
    }

    const url = new URL(this.#baseUrl);

    const parameters: [string, string][] = [];
    if (this.#pageNo > 1) {
      parameters.push(["pageNo", this.#pageNo.toString()]);
    }

    if (parameters.length > 0) {
      url.search += url.search !== "" ? "&" : "?";
      url.search += new URLSearchParams(parameters).toString();
    }

    window.history.pushState({ name: "gridView" }, document.title, url.toString());
  }
}
