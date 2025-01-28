/**
 * Handles the filterung of grid views.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { promiseMutex } from "../../Helper/PromiseMutex";
import { dialogFactory } from "../Dialog";

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export class Filter extends EventTarget {
  readonly #filterButton: HTMLButtonElement | null;
  readonly #filterPills: HTMLElement | null;
  #filters: Map<string, string> = new Map();

  constructor(gridId: string) {
    super();

    this.#filterButton = document.getElementById(`${gridId}_filterButton`) as HTMLButtonElement;
    this.#filterPills = document.getElementById(`${gridId}_filters`) as HTMLElement;

    this.#setupEventListeners();
  }

  getActiveFilters(): Map<string, string> {
    return new Map(this.#filters);
  }

  getQueryParameters(): [string, string][] {
    const parameters: [string, string][] = [];

    for (const [key, value] of this.#filters.entries()) {
      parameters.push([`filters[${key}]`, value]);
    }

    return parameters;
  }

  updateFromSearchParams(params: URLSearchParams): void {
    this.#filters.clear();

    params.forEach((value, key) => {
      const matches = key.match(/^filters\[([a-z0-9_]+)\]$/i);
      if (matches) {
        this.#filters.set(matches[1], value);
      }
    });
  }

  setFilterLabels(labels: ArrayLike<string>): void {
    if (this.#filterPills === null) {
      return;
    }

    this.#filterPills.innerHTML = "";
    if (this.#filters.size === 0) {
      return;
    }

    for (const key of this.#filters.keys()) {
      const button = document.createElement("button");
      button.type = "button";
      button.classList.add("button", "small");
      const icon = document.createElement("fa-icon");
      icon.setIcon("circle-xmark");
      button.append(icon, labels[key]);
      button.addEventListener("click", () => {
        this.#removeFilter(key);
      });

      this.#filterPills.append(button);
    }
  }

  #setupEventListeners(): void {
    if (this.#filterButton === null) {
      return;
    }

    this.#filterButton.addEventListener(
      "click",
      promiseMutex(() => this.#showFilterDialog()),
    );

    if (this.#filterPills === null) {
      return;
    }

    const filterButtons = this.#filterPills.querySelectorAll<HTMLButtonElement>("[data-filter]");
    filterButtons.forEach((button) => {
      this.#filters.set(button.dataset.filter!, button.dataset.filterValue!);
      button.addEventListener("click", () => {
        this.#removeFilter(button.dataset.filter!);
      });
    });
  }

  async #showFilterDialog(): Promise<void> {
    const url = new URL(this.#filterButton!.dataset.endpoint!);
    if (this.#filters) {
      this.#filters.forEach((value, key) => {
        url.searchParams.set(`filters[${key}]`, value);
      });
    }

    const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint(url.toString());

    if (ok) {
      this.#filters = new Map(Object.entries(result as ArrayLike<string>));

      this.dispatchEvent(new CustomEvent("change"));
    }
  }

  #removeFilter(filter: string): void {
    this.#filters.delete(filter);

    this.dispatchEvent(new CustomEvent("change"));
  }
}

interface FilterEventMap {
  change: CustomEvent<void>;
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export interface Filter extends EventTarget {
  addEventListener: {
    <T extends keyof FilterEventMap>(
      type: T,
      listener: (this: Filter, ev: FilterEventMap[T]) => any,
      options?: boolean | AddEventListenerOptions,
    ): void;
  } & HTMLElement["addEventListener"];
}

export default Filter;
