/**
 * Provides the program logic for grid views.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { getRow } from "../Api/Gridviews/GetRow";
import { getRows } from "../Api/Gridviews/GetRows";
import { getBulkContextMenuOptions } from "../Api/Interactions/GetBulkContextMenuOptions";
import DomChangeListener from "../Dom/Change/Listener";
import DomUtil from "../Dom/Util";
import { promiseMutex } from "../Helper/PromiseMutex";
import { wheneverFirstSeen } from "../Helper/Selector";
import UiDropdownSimple from "../Ui/Dropdown/Simple";
import { State, StateChangeCause } from "./GridView/State";

export class GridView {
  readonly #gridClassName: string;
  readonly #table: HTMLTableElement;
  readonly #state: State;
  readonly #noItemsNotice: HTMLElement;
  readonly #bulkInteractionProviderClassName: string;
  #gridViewParameters?: Map<string, string>;

  constructor(
    gridId: string,
    gridClassName: string,
    pageNo: number,
    baseUrl: string = "",
    sortField = "",
    sortOrder = "ASC",
    defaultSortField = "",
    defaultSortOrder = "ASC",
    bulkInteractionProviderClassName: string,
    gridViewParameters?: Map<string, string>,
  ) {
    this.#gridClassName = gridClassName;
    this.#table = document.getElementById(`${gridId}_table`) as HTMLTableElement;
    this.#noItemsNotice = document.getElementById(`${gridId}_noItemsNotice`) as HTMLElement;
    this.#bulkInteractionProviderClassName = bulkInteractionProviderClassName;
    this.#gridViewParameters = gridViewParameters;

    this.#initInteractions();
    this.#state = this.#setupState(gridId, pageNo, baseUrl, sortField, sortOrder, defaultSortField, defaultSortOrder);
    this.#initEventListeners();
  }

  async #loadRows(cause: StateChangeCause): Promise<void> {
    const response = await getRows(
      this.#gridClassName,
      this.#state.getPageNo(),
      this.#state.getSortField(),
      this.#state.getSortOrder(),
      this.#state.getActiveFilters(),
      this.#gridViewParameters,
    );
    DomUtil.setInnerHtml(this.#table.querySelector("tbody")!, response.template);

    this.#table.hidden = response.totalRows == 0;
    this.#noItemsNotice.hidden = response.totalRows != 0;
    this.#state.updateFromResponse(cause, response.pages, response.filterLabels);

    DomChangeListener.trigger();
  }

  async #refreshRow(row: HTMLElement): Promise<void> {
    const { template } = await getRow(this.#gridClassName, row.dataset.objectId!, this.#gridViewParameters);

    row.replaceWith(DomUtil.createFragmentFromHtml(template));
    this.#state.refreshSelection();
    DomChangeListener.trigger();
  }

  #initInteractions(): void {
    wheneverFirstSeen(`#${this.#table.id} tbody tr`, (row) => {
      const containers = [row];

      row.querySelectorAll<HTMLElement>(".dropdownToggle").forEach((element) => {
        const dropdown = UiDropdownSimple.getDropdownMenu(element.dataset.target!);
        if (dropdown) {
          containers.push(dropdown);
        }
      });

      for (const container of containers) {
        container.querySelectorAll<HTMLButtonElement>("[data-interaction]").forEach((element) => {
          element.addEventListener("click", () => {
            row.dispatchEvent(
              new CustomEvent("interaction:execute", {
                detail: element.dataset,
                bubbles: true,
              }),
            );
          });
        });
      }
    });
  }

  #initEventListeners(): void {
    this.#table.addEventListener("interaction:invalidate-all", () => {
      void this.#loadRows(StateChangeCause.Change);
    });

    this.#table.addEventListener("interaction:invalidate", (event) => {
      void this.#refreshRow(event.target as HTMLElement);
    });

    this.#table.addEventListener("interaction:remove", (event) => {
      (event.target as HTMLElement).remove();
      this.#checkEmptyTable();
    });

    this.#table.addEventListener("interaction:reset-selection", () => {
      this.#state.resetSelection();
    });
  }

  #setupState(
    gridId: string,
    pageNo: number,
    baseUrl: string,
    sortField: string,
    sortOrder: string,
    defaultSortField: string,
    defaultSortOrder: string,
  ): State {
    const state = new State(
      gridId,
      this.#table,
      pageNo,
      baseUrl,
      sortField,
      sortOrder,
      defaultSortField,
      defaultSortOrder,
    );
    state.addEventListener("grid-view:change", (event) => {
      void this.#loadRows(event.detail.source);
    });
    state.addEventListener(
      "grid-view:get-bulk-interactions",
      promiseMutex((event) => this.#loadBulkInteractions(event.detail.objectIds)),
    );

    return state;
  }

  async #loadBulkInteractions(objectIds: number[]): Promise<void> {
    const { template } = await getBulkContextMenuOptions(this.#bulkInteractionProviderClassName, objectIds);
    this.#state.setBulkInteractionContextMenuOptions(template);
  }

  #checkEmptyTable(): void {
    if (this.#table.querySelectorAll("tbody tr").length > 0) {
      return;
    }

    void this.#loadRows(StateChangeCause.Change);
  }
}
