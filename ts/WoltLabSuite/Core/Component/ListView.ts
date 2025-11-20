/**
 * Provides the program logic for list views.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import State, { StateChangeCause } from "./ListView/State";
import { trigger as triggerDomChange } from "../Dom/Change/Listener";
import { setInnerHtml, createFragmentFromHtml } from "../Dom/Util";
import { getItems } from "../Api/ListViews/GetItems";
import { element as scrollToElement } from "WoltLabSuite/Core/Ui/Scroll";
import { wheneverFirstSeen } from "../Helper/Selector";
import UiDropdownSimple from "../Ui/Dropdown/Simple";
import { getItem } from "../Api/ListViews/GetItem";
import { getBulkContextMenuOptions } from "../Api/Interactions/GetBulkContextMenuOptions";
import { promiseMutex } from "../Helper/PromiseMutex";

export class ListView {
  readonly #viewClassName: string;
  readonly #viewElement: HTMLElement;
  readonly #state: State;
  readonly #noItemsNotice: HTMLElement;
  readonly #bulkInteractionProviderClassName: string;
  #listViewParameters?: Map<string, string>;

  constructor(
    viewId: string,
    viewClassName: string,
    pageNo: number,
    baseUrl: string = "",
    sortField = "",
    sortOrder = "ASC",
    defaultSortField = "",
    defaultSortOrder = "ASC",
    bulkInteractionProviderClassName: string,
    listViewParameters?: Map<string, string>,
  ) {
    this.#viewClassName = viewClassName;
    this.#viewElement = document.getElementById(`${viewId}_items`) as HTMLElement;
    this.#noItemsNotice = document.getElementById(`${viewId}_noItemsNotice`) as HTMLElement;
    this.#bulkInteractionProviderClassName = bulkInteractionProviderClassName;
    this.#listViewParameters = listViewParameters;

    this.#initInteractions();
    this.#state = this.#setupState(viewId, pageNo, baseUrl, sortField, sortOrder, defaultSortField, defaultSortOrder);
    this.#initEventListeners();
  }

  async #loadItems(cause: StateChangeCause): Promise<void> {
    const response = await getItems(
      this.#viewClassName,
      this.#state.getPageNo(),
      this.#state.getSortField(),
      this.#state.getSortOrder(),
      this.#state.getActiveFilters(),
      this.#listViewParameters,
    );
    setInnerHtml(this.#viewElement, response.template);

    this.#viewElement.hidden = response.totalItems === 0;
    this.#noItemsNotice.hidden = response.totalItems !== 0;
    this.#state.updateFromResponse(cause, response.pages, response.filterLabels);
    if (cause === StateChangeCause.Pagination) {
      scrollToElement(this.#viewElement.closest(".listView")!);
    }

    triggerDomChange();
  }

  async #refreshItem(item: HTMLElement): Promise<void> {
    const { template } = await getItem(this.#viewClassName, item.dataset.objectId!, this.#listViewParameters);
    item.replaceWith(createFragmentFromHtml(template));
    this.#state.refreshSelection();
    triggerDomChange();
  }

  #initInteractions(): void {
    wheneverFirstSeen(`#${this.#viewElement.id} .listView__item`, (item) => {
      item.querySelectorAll<HTMLElement>(".dropdownToggle").forEach((element) => {
        let dropdown = UiDropdownSimple.getDropdownMenu(element.dataset.target!);
        if (!dropdown) {
          dropdown = element.closest(".dropdown")!.querySelector<HTMLElement>(".dropdownMenu")!;
        }

        dropdown?.querySelectorAll<HTMLButtonElement>("[data-interaction]").forEach((element) => {
          element.addEventListener("click", () => {
            item.dispatchEvent(
              new CustomEvent("interaction:execute", {
                detail: element.dataset,
                bubbles: true,
              }),
            );
          });
        });
      });
    });

    const listView = this.#viewElement.closest(".listView") as HTMLElement;
    listView.querySelector<HTMLButtonElement>(".listView__editMode__toggle")?.addEventListener("click", () => {
      listView.classList.add("listView--editMode");
    });
  }

  #initEventListeners(): void {
    this.#viewElement.addEventListener("interaction:invalidate-all", () => {
      void this.#loadItems(StateChangeCause.Change);
    });

    this.#viewElement.addEventListener("interaction:invalidate", (event) => {
      void this.#refreshItem(event.target as HTMLElement);
    });

    this.#viewElement.addEventListener("interaction:remove", (event) => {
      (event.target as HTMLElement).remove();
      this.#state.removeSelection(parseInt((event.target as HTMLElement).dataset.objectId!));
      this.#checkEmptyList();
    });

    this.#viewElement.addEventListener("interaction:reset-selection", () => {
      this.#state.resetSelection();
    });
  }

  #setupState(
    viewId: string,
    pageNo: number,
    baseUrl: string,
    sortField: string,
    sortOrder: string,
    defaultSortField: string,
    defaultSortOrder: string,
  ): State {
    const state = new State(
      viewId,
      this.#viewElement,
      pageNo,
      baseUrl,
      sortField,
      sortOrder,
      defaultSortField,
      defaultSortOrder,
    );
    state.addEventListener("list-view:change", (event) => {
      void this.#loadItems(event.detail.source);
    });
    state.addEventListener(
      "list-view:get-bulk-interactions",
      promiseMutex((event) => this.#loadBulkInteractions(event.detail.objectIds)),
    );

    return state;
  }

  async #loadBulkInteractions(objectIds: number[]): Promise<void> {
    const { template } = await getBulkContextMenuOptions(this.#bulkInteractionProviderClassName, objectIds);
    this.#state.setBulkInteractionContextMenuOptions(template);
  }

  #checkEmptyList(): void {
    if (this.#viewElement.querySelectorAll(".listView__item").length > 0) {
      return;
    }

    void this.#loadItems(StateChangeCause.Change);
  }
}
