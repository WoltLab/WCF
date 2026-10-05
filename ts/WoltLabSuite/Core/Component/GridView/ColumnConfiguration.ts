/**
 * Handles the configuration of the visible columns of grid views. The
 * configuration is not persisted, but it is part of the url.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { promiseMutex } from "../../Helper/PromiseMutex";
import { dialogFactory } from "../Dialog";

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export class ColumnConfiguration extends EventTarget {
  readonly #button: HTMLButtonElement | null;
  #visibleColumns?: string[];

  constructor(gridId: string, visibleColumns?: string[]) {
    super();

    this.#visibleColumns = visibleColumns;

    this.#button = document.getElementById(`${gridId}_columnConfigurationButton`) as HTMLButtonElement | null;
    this.#button?.addEventListener(
      "click",
      promiseMutex(() => this.#showDialog()),
    );
  }

  /**
   * Returns the ids of the visible columns or `undefined` if the user
   * did not configure the columns.
   */
  getVisibleColumns(): string[] | undefined {
    return this.#visibleColumns !== undefined ? [...this.#visibleColumns] : undefined;
  }

  getQueryParameters(): [string, string][] {
    if (this.#visibleColumns === undefined) {
      return [];
    }

    return this.#visibleColumns.map((column) => ["columns[]", column]);
  }

  /**
   * Reads the visible columns from the given search params and returns
   * true if they differ from the current configuration.
   */
  updateFromSearchParams(params: URLSearchParams): boolean {
    const columns: string[] = [];
    params.forEach((value, key) => {
      if (/^columns\[\d*\]$/.test(key)) {
        columns.push(value);
      }
    });

    const visibleColumns = columns.length > 0 ? columns : undefined;
    const hasChanged = visibleColumns?.join(",") !== this.#visibleColumns?.join(",");
    this.#visibleColumns = visibleColumns;

    return hasChanged;
  }

  async #showDialog(): Promise<void> {
    const url = new URL(this.#button!.dataset.endpoint!);
    this.#visibleColumns?.forEach((column, index) => {
      url.searchParams.set(`columns[${index}]`, column);
    });

    const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<string[] | null>(url.toString());

    if (ok) {
      // `null` represents the default configuration.
      this.#visibleColumns = result ?? undefined;

      this.dispatchEvent(new CustomEvent("grid-view:change"));
    }
  }
}

interface ColumnConfigurationEventMap {
  "grid-view:change": CustomEvent<void>;
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export interface ColumnConfiguration extends EventTarget {
  addEventListener: {
    <T extends keyof ColumnConfigurationEventMap>(
      type: T,
      listener: (this: ColumnConfiguration, ev: ColumnConfigurationEventMap[T]) => any,
      options?: boolean | AddEventListenerOptions,
    ): void;
  } & HTMLElement["addEventListener"];
}

export default ColumnConfiguration;
