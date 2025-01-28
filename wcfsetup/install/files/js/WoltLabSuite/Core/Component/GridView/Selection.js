/**
 * Handles the selection of grid view rows.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Core", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Dropdown/Simple"], function (require, exports, tslib_1, Core_1, Util_1, Selector_1, Language_1, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Selection = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importStar(Simple_1);
    // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
    class Selection extends EventTarget {
        #markAll = null;
        #table;
        #selectionBar = null;
        #bulkInteractionButton = null;
        #bulkInteractionsPlaceholder = null;
        #bulkInteractionsLoadingDelay = undefined;
        constructor(gridId, table) {
            super();
            this.#table = table;
            this.#markAll = this.#table.querySelector(".gridView__selectAllRows");
            this.#markAll?.addEventListener("change", () => {
                this.#change(this.#markAll.checked);
            });
            this.#selectionBar = document.getElementById(`${gridId}_selectionBar`);
            this.#bulkInteractionButton = document.getElementById(`${gridId}_bulkInteractionButton`);
            this.#bulkInteractionButton?.addEventListener("click", () => {
                this.#showBulkInteractionMenu();
            });
            document.getElementById(`${gridId}_resetSelectionButton`)?.addEventListener("click", () => {
                this.resetSelection();
            });
            (0, Selector_1.wheneverFirstSeen)(`#${this.#table.id} .gridView__selectRow`, (checkbox) => {
                checkbox.addEventListener("change", () => {
                    this.#change();
                });
            });
            this.#restoreSelection();
        }
        refresh() {
            this.#restoreSelection();
        }
        getSelectedIds() {
            const json = window.localStorage.getItem(this.#getStorageKey());
            if (typeof json !== "string") {
                return [];
            }
            let selectedIds = [];
            try {
                const value = JSON.parse(json);
                if (Array.isArray(value)) {
                    selectedIds = value;
                }
            }
            catch {
                if (window.ENABLE_DEBUG_MODE) {
                    console.error("Failed to deserialize the selection.", json);
                }
                return [];
            }
            return selectedIds;
        }
        #change(forceValue, skipStorage = false) {
            const checkboxes = Array.from(this.#table.querySelectorAll(".gridView__selectRow"));
            if (forceValue === undefined) {
                if (this.#markAll !== null) {
                    const markedCheckboxes = checkboxes.filter((checkbox) => checkbox.checked).length;
                    if (markedCheckboxes === 0) {
                        this.#markAll.checked = false;
                        this.#markAll.indeterminate = false;
                    }
                    else if (markedCheckboxes === checkboxes.length) {
                        this.#markAll.checked = true;
                        this.#markAll.indeterminate = false;
                    }
                    else {
                        this.#markAll.checked = false;
                        this.#markAll.indeterminate = markedCheckboxes > 0 && markedCheckboxes !== checkboxes.length;
                    }
                }
            }
            else {
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
        #saveSelection(checkboxes) {
            const selection = new Map();
            checkboxes.forEach((checkbox) => {
                const row = checkbox.closest(".gridView__row");
                const id = parseInt(row.dataset.objectId);
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
        #restoreSelection() {
            const selectedIds = this.getSelectedIds();
            this.#table.querySelectorAll(".gridView__row").forEach((row) => {
                const id = parseInt(row.dataset.objectId);
                if (!selectedIds.includes(id)) {
                    return;
                }
                const checkbox = row.querySelector(".gridView__selectRow");
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
            this.#change(undefined, true);
        }
        #getStorageKey() {
            return (0, Core_1.getStoragePrefix)() + `gridView-${this.#table.id}-selection`;
        }
        #updateSelectionBar() {
            const selectedIds = this.getSelectedIds();
            if (!this.#selectionBar) {
                return;
            }
            if (selectedIds.length === 0) {
                this.#selectionBar.hidden = true;
                return;
            }
            this.#selectionBar.hidden = false;
            this.#bulkInteractionButton.textContent = (0, Language_1.getPhrase)("wcf.clipboard.button.numberOfSelectedItems", {
                numberOfSelectedItems: selectedIds.length,
            });
        }
        #showBulkInteractionMenu() {
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
        setBulkInteractionContextMenuOptions(options) {
            const fragment = Util_1.default.createFragmentFromHtml(options);
            this.#rebuildBulkInteractions(fragment);
        }
        #rebuildBulkInteractions(fragment) {
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
            const menuId = this.#bulkInteractionButton.parentElement.id;
            const menu = (0, Simple_1.getDropdownMenu)(menuId);
            if (menu === undefined) {
                throw new Error("Could not find the dropdown menu for " + this.#bulkInteractionButton.id);
            }
            const dividers = Array.from(menu.querySelectorAll(".dropdownDivider"));
            const lastDivider = dividers[dividers.length - 1];
            if (fragment === undefined) {
                while (lastDivider.previousElementSibling !== null) {
                    lastDivider.previousElementSibling.remove();
                }
                menu.prepend(this.#bulkInteractionsPlaceholder);
                this.#bulkInteractionsPlaceholder = null;
            }
            else {
                if (this.#bulkInteractionsPlaceholder === null) {
                    this.#bulkInteractionsPlaceholder = lastDivider.previousElementSibling;
                    this.#bulkInteractionsPlaceholder.remove();
                }
                menu.prepend(fragment);
                this.#initBulkInteractions();
            }
            (0, Simple_1.setAlignmentById)(menuId);
        }
        resetSelection() {
            if (this.#markAll !== null) {
                this.#markAll.checked = false;
                this.#markAll.indeterminate = false;
            }
            this.#table
                .querySelectorAll(".gridView__selectRow")
                .forEach((checkbox) => (checkbox.checked = false));
            window.localStorage.removeItem(this.#getStorageKey());
            this.#updateSelectionBar();
        }
        #initBulkInteractions() {
            if (!this.#bulkInteractionButton) {
                return;
            }
            const dropdown = Simple_1.default.getDropdownMenu(this.#bulkInteractionButton.dataset.target);
            dropdown?.querySelectorAll("[data-bulk-interaction]").forEach((element) => {
                element.addEventListener("click", () => {
                    this.#table.dispatchEvent(new CustomEvent("bulk-interaction", {
                        detail: element.dataset,
                    }));
                });
            });
        }
    }
    exports.Selection = Selection;
    exports.default = Selection;
});
