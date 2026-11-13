/**
 * Provides a tri-state checkbox that selects or deselects all checkboxes of a
 * scrollable checkbox list. If the list is filtered, only the visible items
 * are affected.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function getRelevantCheckboxes(list) {
        // The visibility filter hides inactive items through CSS instead of inline styles.
        const activeOnly = list.dataset.filter === "activeOnly";
        return Array.from(list.querySelectorAll(':scope > li > label > input[type="checkbox"]:not(:disabled)')).filter((checkbox) => {
            const item = checkbox.closest("li");
            if (item.style.getPropertyValue("display") === "none") {
                return false;
            }
            return !activeOnly || item.classList.contains("active");
        });
    }
    function updateState(list, selectAll, container) {
        const checkboxes = getRelevantCheckboxes(list);
        container.hidden = checkboxes.length === 0;
        const checked = checkboxes.filter((checkbox) => checkbox.checked).length;
        selectAll.checked = checked !== 0 && checked === checkboxes.length;
        selectAll.indeterminate = checked !== 0 && checked !== checkboxes.length;
    }
    function setup(listId) {
        const list = document.getElementById(listId);
        if (list === null) {
            throw new Error(`Unknown list '${listId}'.`);
        }
        const selectAll = document.getElementById(`${listId}_selectAll`);
        if (selectAll === null) {
            throw new Error(`Missing select all checkbox for list '${listId}'.`);
        }
        // Move the container directly in front of the list, placing it between the
        // list and the filter input if the list is filterable.
        const container = selectAll.closest(".scrollableCheckboxList__selectAll");
        list.insertAdjacentElement("beforebegin", container);
        let isBulkUpdate = false;
        selectAll.addEventListener("change", () => {
            const checkboxes = getRelevantCheckboxes(list);
            const checked = !checkboxes.every((checkbox) => checkbox.checked);
            isBulkUpdate = true;
            for (const checkbox of checkboxes) {
                if (checkbox.checked !== checked) {
                    checkbox.checked = checked;
                    checkbox.dispatchEvent(new Event("change", { bubbles: true }));
                }
            }
            isBulkUpdate = false;
            updateState(list, selectAll, container);
        });
        list.addEventListener("change", () => {
            if (!isBulkUpdate) {
                updateState(list, selectAll, container);
            }
        });
        list.addEventListener("itemListFilter:change", () => updateState(list, selectAll, container));
        updateState(list, selectAll, container);
    }
});
