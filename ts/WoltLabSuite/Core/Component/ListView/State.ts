/**
 * Handles the state of a list view.
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
  readonly #listViewFooter: HTMLElement;
  #pageNo: number;

  constructor(
    viewId: string,
    viewElement: HTMLElement,
    pageNo: number,
    baseUrl: string,
    sortField: string,
    sortOrder: string,
  ) {
    super();

    this.#baseUrl = baseUrl;
    this.#pageNo = pageNo;

    this.#listViewFooter = document.getElementById(`${viewId}_footer`) as HTMLElement;

    this.#pagination = document.getElementById(`${viewId}_pagination`) as WoltlabCorePaginationElement;
    this.#pagination.addEventListener("switchPage", (event: CustomEvent) => {
      void this.#switchPage(event.detail, StateChangeCause.Pagination);
    });

    this.#filter = new Filter(viewId);
    this.#filter.addEventListener("list-view:change", () => {
      this.#switchPage(1, StateChangeCause.Change);
    });

    this.#sorting = new Sorting(document.getElementById(`${viewId}_sorting`) ?? undefined, sortField, sortOrder);
    this.#sorting.addEventListener("list-view:change", () => {
      this.#switchPage(1, StateChangeCause.Change);
    });

    this.#selection = new Selection(viewId, viewElement);
    this.#selection.addEventListener("list-view:get-bulk-interactions", (event) => {
      this.dispatchEvent(
        new CustomEvent("list-view:get-bulk-interactions", { detail: { objectIds: event.detail.objectIds } }),
      );
    });
    this.#selection.addEventListener("list-view:update-selection", () => {
      this.#updateListViewFooter();
    });

    window.addEventListener("popstate", () => {
      this.#handlePopState();
    });

    this.#updatePaginationUrl();
    this.#updateListViewFooter();
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
    this.#updatePaginationUrl();
    this.#selection.refresh();

    if (cause === StateChangeCause.Change || cause === StateChangeCause.Pagination) {
      this.#updateQueryString();
    }

    this.#updateListViewFooter();
  }

  #switchPage(pageNo: number, source: StateChangeCause): void {
    this.#pagination.page = pageNo;
    this.#pageNo = pageNo;

    this.dispatchEvent(new CustomEvent("list-view:change", { detail: { source } }));
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

  #updatePaginationUrl(): void {
    if (!this.#baseUrl) {
      return;
    }

    const url = new URL(this.#baseUrl);

    const parameters: [string, string][] = [];
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

    this.#pagination.url = url.toString();
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

  #updateListViewFooter(): void {
    this.#listViewFooter.hidden = this.#pagination.count < 2 && !this.#selection.selectionBarVisible();
  }

  setBulkInteractionContextMenuOptions(options: string): void {
    this.#selection.setBulkInteractionContextMenuOptions(options);
  }

  resetSelection(): void {
    this.#selection.resetSelection();
  }

  refreshSelection(): void {
    this.#selection.refresh();
  }
}

interface StateEventMap {
  "list-view:change": CustomEvent<{ source: StateChangeCause }>;
  "list-view:get-bulk-interactions": CustomEvent<{ objectIds: number[] }>;
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
