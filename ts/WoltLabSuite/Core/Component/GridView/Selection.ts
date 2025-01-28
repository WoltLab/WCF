/**
 * Handles the selection of grid view rows.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { getStoragePrefix } from "WoltLabSuite/Core/Core";
import DomUtil from "WoltLabSuite/Core/Dom/Util";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { getPhrase } from "WoltLabSuite/Core/Language";
import UiDropdownSimple, { getDropdownMenu, setAlignmentById } from "WoltLabSuite/Core/Ui/Dropdown/Simple";

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export class Selection extends EventTarget {
  readonly #markAll: HTMLInputElement | null = null;
  readonly #table: HTMLTableElement;
  readonly #selectionBar: HTMLElement | null = null;
  readonly #bulkInteractionButton: HTMLButtonElement | null = null;
  #bulkInteractionsPlaceholder: HTMLLIElement | null = null;
  #bulkInteractionsLoadingDelay: number | undefined = undefined;

  constructor(gridId: string, table: HTMLTableElement) {
    super();

    this.#table = table;

    this.#markAll = this.#table.querySelector<HTMLInputElement>(".gridView__selectAllRows");
    this.#markAll?.addEventListener("change", () => {
      this.#change(this.#markAll!.checked);
    });

    this.#selectionBar = document.getElementById(`${gridId}_selectionBar`) as HTMLElement;
    this.#bulkInteractionButton = document.getElementById(`${gridId}_bulkInteractionButton`) as HTMLButtonElement;
    this.#bulkInteractionButton?.addEventListener("click", () => {
      this.#showBulkInteractionMenu();
    });

    document.getElementById(`${gridId}_resetSelectionButton`)?.addEventListener("click", () => {
      this.resetSelection();
    });

    wheneverFirstSeen(`#${this.#table.id} .gridView__selectRow`, (checkbox: HTMLInputElement) => {
      checkbox.addEventListener("change", () => {
        this.#change();
      });
    });

    this.#restoreSelection();
  }

  refresh(): void {
    this.#restoreSelection();
  }

  getSelectedIds(): number[] {
    const json = window.localStorage.getItem(this.#getStorageKey());
    if (typeof json !== "string") {
      return [];
    }

    let selectedIds: number[] = [];
    try {
      const value = JSON.parse(json);
      if (Array.isArray(value)) {
        selectedIds = value;
      }
    } catch {
      if (window.ENABLE_DEBUG_MODE) {
        console.error("Failed to deserialize the selection.", json);
      }

      return [];
    }

    return selectedIds;
  }

  #change(forceValue?: boolean, skipStorage = false): void {
    const checkboxes = Array.from(this.#table.querySelectorAll<HTMLInputElement>(".gridView__selectRow"));
    if (forceValue === undefined) {
      if (this.#markAll !== null) {
        const markedCheckboxes = checkboxes.filter((checkbox) => checkbox.checked).length;
        if (markedCheckboxes === 0) {
          this.#markAll.checked = false;
          this.#markAll.indeterminate = false;
        } else if (markedCheckboxes === checkboxes.length) {
          this.#markAll.checked = true;
          this.#markAll.indeterminate = false;
        } else {
          this.#markAll.checked = false;
          this.#markAll.indeterminate = markedCheckboxes > 0 && markedCheckboxes !== checkboxes.length;
        }
      }
    } else {
      for (const checkbox of checkboxes) {
        checkbox.checked = forceValue;
      }
    }

    if (!skipStorage) {
      this.#saveSelection(checkboxes);
    }

    this.#rebuildBulkInteractions();
    this.#updateSelectionBar();
  }

  #saveSelection(checkboxes: HTMLInputElement[]): void {
    const selection = new Map<number, boolean>();
    checkboxes.forEach((checkbox) => {
      const row = checkbox.closest(".gridView__row") as HTMLElement;
      const id = parseInt(row.dataset.objectId!);

      selection.set(id, checkbox.checked);
    });

    // We support selection across pages thus we need to preserve the selection
    // of objects that are not present on the current page.
    const selectedIds = this.getSelectedIds().filter((id) => {
      const checked = selection.get(id);
      if (checked === undefined) {
        // Object does not appear on this page, preserve the id.
        return true;
      }

      return checked;
    });

    // Add any id that was previously not part of the stored selection.
    selection.forEach((checked, id) => {
      if (checked && !selectedIds.includes(id)) {
        selectedIds.push(id);
      }
    });

    window.localStorage.setItem(this.#getStorageKey(), JSON.stringify(selectedIds));
  }

  #restoreSelection(): void {
    const selectedIds = this.getSelectedIds();

    this.#table.querySelectorAll(".gridView__row").forEach((row: HTMLElement) => {
      const id = parseInt(row.dataset.objectId!);
      if (!selectedIds.includes(id)) {
        return;
      }

      const checkbox = row.querySelector<HTMLInputElement>(".gridView__selectRow");
      if (checkbox) {
        checkbox.checked = true;
      }
    });

    this.#change(undefined, true);
  }

  #getStorageKey(): string {
    return getStoragePrefix() + `gridView-${this.#table.id}-selection`;
  }

  #updateSelectionBar(): void {
    const selectedIds = this.getSelectedIds();

    if (!this.#selectionBar) {
      return;
    }

    if (selectedIds.length === 0) {
      this.#selectionBar.hidden = true;
      return;
    }

    this.#selectionBar.hidden = false;
    this.#bulkInteractionButton!.textContent = getPhrase("wcf.clipboard.button.numberOfSelectedItems", {
      numberOfSelectedItems: selectedIds.length,
    });
  }

  #showBulkInteractionMenu(): void {
    if (this.#bulkInteractionsPlaceholder !== null) {
      return;
    }

    this.dispatchEvent(new CustomEvent("getBulkInteractions", { detail: { objectIds: this.getSelectedIds() } }));

    if (this.#bulkInteractionsLoadingDelay !== undefined) {
      window.clearTimeout(this.#bulkInteractionsLoadingDelay);
    }

    // Delays the display of the available actions to prevent flicker and to
    // smooth out the UX.
    this.#bulkInteractionsLoadingDelay = window.setTimeout(() => {
      this.#bulkInteractionsLoadingDelay = undefined;
    }, 200);
  }

  setBulkInteractionContextMenuOptions(options: string): void {
    const fragment = DomUtil.createFragmentFromHtml(options);
    this.#rebuildBulkInteractions(fragment);
  }

  #rebuildBulkInteractions(fragment?: DocumentFragment): void {
    if (fragment === undefined && this.#bulkInteractionsPlaceholder === null) {
      // The call was made before the menu was shown for the first time.
      return;
    }

    if (this.#bulkInteractionsLoadingDelay !== undefined && fragment !== undefined) {
      // The server has already replied but the delay isn't over yet.
      window.setTimeout(() => {
        this.#rebuildBulkInteractions(fragment);
      }, 20);

      return;
    }

    const menuId = this.#bulkInteractionButton!.parentElement!.id;
    const menu = getDropdownMenu(menuId);
    if (menu === undefined) {
      throw new Error("Could not find the dropdown menu for " + this.#bulkInteractionButton!.id);
    }

    const dividers = Array.from(menu.querySelectorAll<HTMLElement>(".dropdownDivider"));
    const lastDivider = dividers[dividers.length - 1];

    if (fragment === undefined) {
      while (lastDivider.previousElementSibling !== null) {
        lastDivider.previousElementSibling.remove();
      }

      menu.prepend(this.#bulkInteractionsPlaceholder!);
      this.#bulkInteractionsPlaceholder = null;
    } else {
      if (this.#bulkInteractionsPlaceholder === null) {
        this.#bulkInteractionsPlaceholder = lastDivider.previousElementSibling as HTMLLIElement;
        this.#bulkInteractionsPlaceholder.remove();
      }

      menu.prepend(fragment);

      this.#initBulkInteractions();
    }

    setAlignmentById(menuId);
  }

  resetSelection(): void {
    if (this.#markAll !== null) {
      this.#markAll.checked = false;
      this.#markAll.indeterminate = false;
    }

    this.#table
      .querySelectorAll<HTMLInputElement>(".gridView__selectRow")
      .forEach((checkbox) => (checkbox.checked = false));

    window.localStorage.removeItem(this.#getStorageKey());

    this.#updateSelectionBar();
  }

  #initBulkInteractions(): void {
    if (!this.#bulkInteractionButton) {
      return;
    }

    const dropdown = UiDropdownSimple.getDropdownMenu(this.#bulkInteractionButton.dataset.target!);
    dropdown?.querySelectorAll<HTMLButtonElement>("[data-bulk-interaction]").forEach((element) => {
      element.addEventListener("click", () => {
        this.#table.dispatchEvent(
          new CustomEvent("bulk-interaction", {
            detail: element.dataset,
          }),
        );
      });
    });
  }
}

interface SelectionEventMap {
  getBulkInteractions: CustomEvent<{ objectIds: number[] }>;
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export interface Selection extends EventTarget {
  addEventListener: {
    <T extends keyof SelectionEventMap>(
      type: T,
      listener: (this: Selection, ev: SelectionEventMap[T]) => any,
      options?: boolean | AddEventListenerOptions,
    ): void;
  } & HTMLElement["addEventListener"];
}

export default Selection;
