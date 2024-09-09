import { getRows } from "../Api/GridViews/GetRows";
import DomUtil from "../Dom/Util";

export class GridView {
  readonly #gridClassName: string;
  readonly #table: HTMLTableElement;
  readonly #topPagination: WoltlabCorePaginationElement;
  readonly #bottomPagination: WoltlabCorePaginationElement;
  readonly #baseUrl: string;
  #pageNo: number;
  #sortField: string;
  #sortOrder: string;

  constructor(
    gridId: string,
    gridClassName: string,
    pageNo: number,
    baseUrl: string = "",
    sortField = "",
    sortOrder = "ASC",
  ) {
    this.#gridClassName = gridClassName;
    this.#table = document.getElementById(`${gridId}_table`) as HTMLTableElement;
    this.#topPagination = document.getElementById(`${gridId}_topPagination`) as WoltlabCorePaginationElement;
    this.#bottomPagination = document.getElementById(`${gridId}_bottomPagination`) as WoltlabCorePaginationElement;
    this.#pageNo = pageNo;
    this.#baseUrl = baseUrl;
    this.#sortField = sortField;
    this.#sortOrder = sortOrder;

    this.#initPagination();
    this.#initSorting();
  }

  #initPagination(): void {
    this.#topPagination.addEventListener("switchPage", (event: CustomEvent) => {
      void this.#switchPage(event.detail);
    });
    this.#bottomPagination.addEventListener("switchPage", (event: CustomEvent) => {
      void this.#switchPage(event.detail);
    });
  }

  #initSorting(): void {
    this.#table.querySelectorAll<HTMLTableCellElement>('th[data-sortable="1"]').forEach((element) => {
      const link = document.createElement("a");
      link.role = "button";
      link.addEventListener("click", () => {
        this.#sort(element.dataset.id!);
      });

      link.textContent = element.textContent;
      element.innerHTML = "";
      element.append(link);
    });

    this.#renderActiveSorting();
  }

  #sort(sortField: string): void {
    if (this.#sortField == sortField && this.#sortOrder == "ASC") {
      this.#sortOrder = "DESC";
    } else {
      this.#sortField = sortField;
      this.#sortOrder = "ASC";
    }

    this.#loadRows();
    this.#renderActiveSorting();
  }

  #renderActiveSorting(): void {
    this.#table.querySelectorAll<HTMLTableCellElement>('th[data-sortable="1"]').forEach((element) => {
      element.classList.remove("active", "ASC", "DESC");

      if (element.dataset.id == this.#sortField) {
        element.classList.add("active", this.#sortOrder);
      }
    });
  }

  #switchPage(pageNo: number): void {
    this.#topPagination.page = pageNo;
    this.#bottomPagination.page = pageNo;
    this.#pageNo = pageNo;

    this.#loadRows();
  }

  async #loadRows(): Promise<void> {
    const response = await getRows(this.#gridClassName, this.#pageNo, this.#sortField, this.#sortOrder);
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
    if (this.#sortField) {
      parameters.push(["sortField", this.#sortField]);
      parameters.push(["sortOrder", this.#sortOrder]);
    }

    if (parameters.length > 0) {
      url.search += url.search !== "" ? "&" : "?";
      url.search += new URLSearchParams(parameters).toString();
    }

    window.history.pushState({}, document.title, url.toString());
  }
}
