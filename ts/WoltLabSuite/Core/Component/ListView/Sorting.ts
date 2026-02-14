/**
 * Handles the sorting of list view items.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export class Sorting extends EventTarget {
  #defaultSortField: string;
  #defaultSortOrder: string;
  #sortField: string;
  #sortOrder: string;
  #dropdownMenu: HTMLElement | undefined;

  constructor(
    dropdownMenu: HTMLElement | undefined,
    sortField: string,
    sortOrder: string,
    defaultSortField: string,
    defaultSortOrder: string,
  ) {
    super();

    this.#sortField = sortField;
    this.#defaultSortField = defaultSortField;
    this.#sortOrder = sortOrder;
    this.#defaultSortOrder = defaultSortOrder;
    this.#dropdownMenu = dropdownMenu;

    this.#dropdownMenu?.querySelectorAll<HTMLElement>("[data-sort-id]").forEach((element) => {
      element.addEventListener("click", () => {
        this.#sort(element.dataset.sortId!);
      });
    });

    this.#renderActiveSorting();
  }

  getSortField(): string {
    return this.#sortField;
  }

  getSortOrder(): string {
    return this.#sortOrder;
  }

  getQueryParameters(): [string, string][] {
    if (this.#sortField === "") {
      return [];
    }

    if (this.#sortField === this.#defaultSortField) {
      if (this.#sortOrder !== this.#defaultSortOrder) {
        return [["sortOrder", this.#sortOrder]];
      } else {
        return [];
      }
    }

    return [
      ["sortField", this.#sortField],
      ["sortOrder", this.#sortOrder],
    ];
  }

  updateFromSearchParams(params: URLSearchParams): void {
    this.#sortField = this.#defaultSortField;
    this.#sortOrder = this.#defaultSortOrder;

    params.forEach((value, key) => {
      if (key === "sortField") {
        this.#sortField = value;
      } else if (key === "sortOrder") {
        this.#sortOrder = value;
      }
    });
  }

  #sort(sortField: string): void {
    if (this.#sortField == sortField && this.#sortOrder == "ASC") {
      this.#sortOrder = "DESC";
    } else {
      this.#sortField = sortField;
      this.#sortOrder = "ASC";
    }

    this.#renderActiveSorting();

    this.dispatchEvent(new CustomEvent("list-view:change"));
  }

  #renderActiveSorting(): void {
    this.#dropdownMenu?.querySelectorAll<HTMLElement>("[data-sort-id]").forEach((element) => {
      element.classList.remove("active", "ASC", "DESC");

      if (element.dataset.sortId == this.#sortField) {
        element.classList.add("active", this.#sortOrder);
      }
    });
  }
}

interface SortingEventMap {
  "list-view:change": CustomEvent<void>;
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export interface Sorting extends EventTarget {
  addEventListener: {
    <T extends keyof SortingEventMap>(
      type: T,
      listener: (this: Sorting, ev: SortingEventMap[T]) => any,
      options?: boolean | AddEventListenerOptions,
    ): void;
  } & HTMLElement["addEventListener"];
}

export default Sorting;
