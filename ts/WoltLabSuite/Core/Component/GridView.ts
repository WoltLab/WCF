/**
 * Provides the program logic for grid views.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { getRow } from "../Api/GridViews/GetRow";
import { getRows } from "../Api/GridViews/GetRows";
import { getBulkContextMenuOptions } from "../Api/Interactions/GetBulkContextMenuOptions";
import { postObject } from "../Api/PostObject";
import DomChangeListener from "../Dom/Change/Listener";
import DomUtil from "../Dom/Util";
import { promiseMutex } from "../Helper/PromiseMutex";
import { wheneverFirstSeen } from "../Helper/Selector";
import UiDropdownSimple from "../Ui/Dropdown/Simple";
import { State, StateChangeCause } from "./GridView/State";
import { isOwnInteraction } from "./Interaction/Container";

export class GridView {
  readonly #gridClassName: string;
  readonly #table: HTMLTableElement;
  readonly #state: State;
  readonly #noItemsNotice: HTMLElement;
  readonly #bulkInteractionProviderClassName: string;
  #gridViewParameters?: Map<string, string>;
  readonly #reloadEmptyTable = promiseMutex(() => this.#loadRows(StateChangeCause.Change));

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
    visibleColumns?: string[],
  ) {
    this.#gridClassName = gridClassName;
    this.#table = document.getElementById(`${gridId}_table`) as HTMLTableElement;
    this.#noItemsNotice = document.getElementById(`${gridId}_noItemsNotice`) as HTMLElement;
    this.#bulkInteractionProviderClassName = bulkInteractionProviderClassName;
    this.#gridViewParameters = gridViewParameters;

    this.#initInteractions();
    this.#state = this.#setupState(
      gridId,
      pageNo,
      baseUrl,
      sortField,
      sortOrder,
      defaultSortField,
      defaultSortOrder,
      visibleColumns,
    );
    this.#initEventListeners();
  }

  async #loadRows(cause: StateChangeCause, includeHeader = false): Promise<void> {
    const response = await getRows(
      this.#gridClassName,
      this.#state.getPageNo(),
      this.#state.getSortField(),
      this.#state.getSortOrder(),
      this.#state.getActiveFilters(),
      this.#gridViewParameters,
      this.#state.getVisibleColumns(),
      includeHeader,
    );
    if (response.header !== undefined) {
      this.#replaceHeaderColumns(response.header);
    }
    DomUtil.setInnerHtml(this.#table.querySelector("tbody")!, response.template);

    this.#table.hidden = response.totalRows == 0;
    this.#noItemsNotice.hidden = response.totalRows != 0;
    this.#state.updateFromResponse(cause, response.pages, response.filterLabels);

    DomChangeListener.trigger();
  }

  async #refreshRow(row: HTMLElement): Promise<void> {
    const { template } = await getRow(
      this.#gridClassName,
      row.dataset.objectId!,
      this.#state.getActiveFilters(),
      this.#gridViewParameters,
      this.#state.getVisibleColumns(),
    );

    row.replaceWith(DomUtil.createFragmentFromHtml(template));
    this.#state.refreshSelection();
    DomChangeListener.trigger();
    this.#checkEmptyTable();
  }

  #replaceHeaderColumns(html: string): void {
    const headerRow = this.#table.querySelector<HTMLTableRowElement>("thead .gridView__headerRow")!;
    headerRow.querySelectorAll("th[data-id]").forEach((element) => element.remove());

    // The selection and action columns are not part of the configurable
    // columns and must be preserved.
    const actionColumn = headerRow.querySelector(".gridView__actionColumn");
    headerRow.insertBefore(DomUtil.createFragmentFromHtml(html), actionColumn);
  }

  #initInteractions(): void {
    wheneverFirstSeen(`#${this.#table.id} tbody tr`, (row) => {
      const containers = [row];

      row.querySelectorAll<HTMLElement>(".dropdownToggle").forEach((element) => {
        if (!isOwnInteraction(row, element)) {
          return;
        }

        const dropdown = UiDropdownSimple.getDropdownMenu(element.dataset.target!);
        if (dropdown) {
          containers.push(dropdown);
        }
      });

      for (const container of containers) {
        container.querySelectorAll<HTMLButtonElement>("[data-interaction]").forEach((element) => {
          if (container === row && !isOwnInteraction(row, element)) {
            return;
          }

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

    wheneverFirstSeen(`#${this.#table.id} tbody tr .gridView__row__markAsRead`, (button: HTMLButtonElement) => {
      button.addEventListener(
        "click",
        promiseMutex(async () => {
          await postObject(button.dataset.endpoint!);
          button.closest("tr")?.dispatchEvent(new CustomEvent("interaction:invalidate", { bubbles: true }));
        }),
      );
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
      this.#state.removeSelection(parseInt((event.target as HTMLElement).dataset.objectId!));
      this.#checkEmptyTable();
    });

    this.#table.addEventListener("interaction:reset-selection", () => {
      this.#state.resetSelection();
    });

    this.#table.addEventListener("interaction:bulk-completed", () => {
      this.#checkEmptyTable();
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
    visibleColumns?: string[],
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
      visibleColumns,
    );
    state.addEventListener("grid-view:change", (event) => {
      void this.#loadRows(event.detail.source, event.detail.includeHeader);
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

    this.#reloadEmptyTable();
  }
}
