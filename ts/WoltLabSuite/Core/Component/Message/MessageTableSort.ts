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

import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";

function getHeaderCells(table: HTMLTableElement): HTMLTableCellElement[] | null {
  const thead = table.querySelector(":scope > thead");
  if (thead) {
    const ths = thead.querySelectorAll<HTMLTableCellElement>(":scope > tr > th");
    if (ths.length > 0) {
      return Array.from(ths);
    }
  }

  return null;
}

function hasComplexLayout(table: HTMLTableElement): boolean {
  const cells = table.querySelectorAll("td, th");
  for (const cell of cells) {
    if (
      (cell instanceof HTMLTableCellElement && cell.rowSpan > 1) ||
      (cell instanceof HTMLTableCellElement && cell.colSpan > 1)
    ) {
      return true;
    }
  }
  return false;
}

function getBodyRows(table: HTMLTableElement, headerCells: HTMLTableCellElement[]): HTMLTableRowElement[] {
  const headerRow = headerCells[0].closest("tr")!;
  const rows: HTMLTableRowElement[] = [];

  const allRows = table.querySelectorAll<HTMLTableRowElement>(":scope > tbody > tr, :scope > tr");
  for (const row of allRows) {
    if (row !== headerRow) {
      rows.push(row);
    }
  }

  return rows;
}

function getCellValue(row: HTMLTableRowElement, columnIndex: number): string {
  const cell = row.cells[columnIndex];
  if (!cell) {
    return "";
  }
  return (cell.textContent || "").trim();
}

function createComparator(order: "ASC" | "DESC"): (a: string, b: string) => number {
  const multiplier = order === "ASC" ? 1 : -1;

  return (a: string, b: string): number => {
    const numA = parseFloat(a);
    const numB = parseFloat(b);

    if (!isNaN(numA) && !isNaN(numB) && String(numA) === a && String(numB) === b) {
      return (numA - numB) * multiplier;
    }

    return a.localeCompare(b) * multiplier;
  };
}

function sortByColumn(
  table: HTMLTableElement,
  headerCells: HTMLTableCellElement[],
  columnIndex: number,
  order: "ASC" | "DESC",
): void {
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

function initTable(table: HTMLTableElement): void {
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
      let newOrder: "ASC" | "DESC";
      if (th.classList.contains("ASC")) {
        newOrder = "DESC";
      } else {
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

export function setup(): void {
  wheneverFirstSeen(".htmlContent .sortableTable", (table: HTMLTableElement) => {
    initTable(table);
  });
}
