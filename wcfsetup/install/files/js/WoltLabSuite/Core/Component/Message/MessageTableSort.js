/**
 * Adds client-side sorting to tables in message content that have header cells (<th>).
 * Clicking a header cell sorts the table rows by that column, toggling between
 * ascending and descending order.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/Selector"], function (require, exports, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function getHeaderCells(table) {
        const thead = table.querySelector(":scope > thead");
        if (thead) {
            const ths = thead.querySelectorAll(":scope > tr > th");
            if (ths.length > 0) {
                return Array.from(ths);
            }
        }
        return null;
    }
    function hasComplexLayout(table) {
        const cells = table.querySelectorAll("td, th");
        for (const cell of cells) {
            if ((cell instanceof HTMLTableCellElement && cell.rowSpan > 1) ||
                (cell instanceof HTMLTableCellElement && cell.colSpan > 1)) {
                return true;
            }
        }
        return false;
    }
    function getBodyRows(table, headerCells) {
        const headerRow = headerCells[0].closest("tr");
        const rows = [];
        const allRows = table.querySelectorAll(":scope > tbody > tr, :scope > tr");
        for (const row of allRows) {
            if (row !== headerRow) {
                rows.push(row);
            }
        }
        return rows;
    }
    function getCellValue(row, columnIndex) {
        const cell = row.cells[columnIndex];
        if (!cell) {
            return "";
        }
        return (cell.textContent || "").trim();
    }
    function createComparator(order) {
        const multiplier = order === "ASC" ? 1 : -1;
        return (a, b) => {
            const numA = parseFloat(a);
            const numB = parseFloat(b);
            if (!isNaN(numA) && !isNaN(numB) && String(numA) === a && String(numB) === b) {
                return (numA - numB) * multiplier;
            }
            return a.localeCompare(b) * multiplier;
        };
    }
    function sortByColumn(table, headerCells, columnIndex, order) {
        const rows = getBodyRows(table, headerCells);
        const container = rows[0]?.parentElement;
        if (!container || rows.length === 0) {
            return;
        }
        const comparator = createComparator(order);
        rows.sort((rowA, rowB) => {
            const valueA = getCellValue(rowA, columnIndex);
            const valueB = getCellValue(rowB, columnIndex);
            return comparator(valueA, valueB);
        });
        for (const row of rows) {
            container.appendChild(row);
        }
    }
    function initTable(table) {
        if (hasComplexLayout(table)) {
            return;
        }
        const headerCells = getHeaderCells(table);
        if (!headerCells) {
            return;
        }
        headerCells.forEach((th, columnIndex) => {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "messageTableSort__trigger";
            while (th.firstChild) {
                button.appendChild(th.firstChild);
            }
            th.appendChild(button);
            th.setAttribute("aria-sort", "none");
            button.addEventListener("click", () => {
                let newOrder;
                if (th.classList.contains("ASC")) {
                    newOrder = "DESC";
                }
                else {
                    newOrder = "ASC";
                }
                for (const sibling of headerCells) {
                    sibling.classList.remove("ASC", "DESC");
                    sibling.setAttribute("aria-sort", "none");
                }
                th.classList.add(newOrder);
                th.setAttribute("aria-sort", newOrder === "ASC" ? "ascending" : "descending");
                sortByColumn(table, headerCells, columnIndex, newOrder);
            });
        });
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)(".htmlContent .sortableTable", (table) => {
            initTable(table);
        });
    }
});
