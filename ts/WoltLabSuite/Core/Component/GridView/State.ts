/**
 * Handles the state of a grid view.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import Filter from "./Filter";
import Selection from "./Selection";
import Sorting from "./Sorting";

export const enum StateChangeCause {
  Change,
  History,
  Pagination,
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export class State extends EventTarget {
  readonly #baseUrl: string;
  readonly #filter: Filter;
  readonly #pagination: WoltlabCorePaginationElement;
  readonly #selection: Selection;
  readonly #sorting: Sorting;
  #pageNo: number;

  constructor(
    gridId: string,
    table: HTMLTableElement,
    pageNo: number,
    baseUrl: string,
    sortField: string,
    sortOrder: string,
  ) {
    super();

    this.#baseUrl = baseUrl;
    this.#pageNo = pageNo;

    this.#pagination = document.getElementById(`${gridId}_pagination`) as WoltlabCorePaginationElement;
    this.#pagination.addEventListener("switchPage", (event: CustomEvent) => {
      void this.#switchPage(event.detail, StateChangeCause.Pagination);
    });

    this.#filter = new Filter(gridId);
    this.#filter.addEventListener("change", () => {
      this.#switchPage(1, StateChangeCause.Change);
    });

    this.#sorting = new Sorting(table, sortField, sortOrder);
    this.#sorting.addEventListener("change", () => {
      this.#switchPage(1, StateChangeCause.Change);
    });

    this.#selection = new Selection(gridId, table);
    this.#selection.addEventListener("getBulkInteractions", (event) => {
      this.dispatchEvent(new CustomEvent("getBulkInteractions", { detail: { objectIds: event.detail.objectIds } }));
    });

    window.addEventListener("popstate", () => {
      this.#handlePopState();
    });
  }

  getPageNo(): number {
    return this.#pageNo;
  }

  getSortField(): string {
    return this.#sorting.getSortField();
  }

  getSortOrder(): string {
    return this.#sorting.getSortOrder();
  }

  getActiveFilters(): Map<string, string> {
    return this.#filter.getActiveFilters();
  }

  getSelectedIds(): number[] {
    return this.#selection.getSelectedIds();
  }

  updateFromResponse(cause: StateChangeCause, count: number, filterLabels: ArrayLike<string>): void {
    this.#filter.setFilterLabels(filterLabels);
    this.#pagination.count = count;
    this.#selection.refresh();

    if (cause === StateChangeCause.Change || cause === StateChangeCause.Pagination) {
      this.#updateQueryString();
    }
  }

  #switchPage(pageNo: number, source: StateChangeCause): void {
    this.#pagination.page = pageNo;
    this.#pageNo = pageNo;

    this.dispatchEvent(new CustomEvent("change", { detail: { source } }));
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

    for (const parameter of this.#sorting.getQueryParameters()) {
      parameters.push(parameter);
    }

    for (const parameter of this.#filter.getQueryParameters()) {
      parameters.push(parameter);
    }

    if (parameters.length > 0) {
      url.search += url.search !== "" ? "&" : "?";
      url.search += new URLSearchParams(parameters).toString();
    }

    window.history.pushState({}, document.title, url.toString());
  }

  #handlePopState(): void {
    let pageNo = 1;

    const { searchParams } = new URL(window.location.href);
    const value = searchParams.get("pageNo");
    if (value !== null) {
      pageNo = parseInt(value);
      if (Number.isNaN(pageNo) || pageNo < 1) {
        pageNo = 1;
      }
    }

    this.#filter.updateFromSearchParams(searchParams);
    this.#sorting.updateFromSearchParams(searchParams);

    this.#switchPage(pageNo, StateChangeCause.History);
  }

  setBulkInteractionContextMenuOptions(options: string): void {
    this.#selection.setBulkInteractionContextMenuOptions(options);
  }

  resetSelection(): void {
    this.#selection.resetSelection();
  }
}

interface StateEventMap {
  change: CustomEvent<{ source: StateChangeCause }>;
  getBulkInteractions: CustomEvent<{ objectIds: number[] }>;
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export interface State extends EventTarget {
  addEventListener: {
    <T extends keyof StateEventMap>(
      type: T,
      listener: (this: State, ev: StateEventMap[T]) => any,
      options?: boolean | AddEventListenerOptions,
    ): void;
  } & HTMLElement["addEventListener"];
}

export default State;
