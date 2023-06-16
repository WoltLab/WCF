/**
 * Provides a filter input to narrow down the list of projects.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { touch as isTouch } from "WoltLabSuite/Core/Environment";

const filterByName = document.getElementById("filterByName") as HTMLInputElement;
const projects: Map<string, HTMLTableRowElement> = new Map();

function filterProjects(): void {
  const value = filterByName.value.trim().toLowerCase();
  if (value === "") {
    resetFilter();
    return;
  }

  projects.forEach((row, name) => {
    if (name.includes(value)) {
      row.hidden = false;
    } else {
      row.hidden = true;
    }
  });
}

function resetFilter(): void {
  filterByName.value = "";
  projects.forEach((row) => (row.hidden = false));
}

export function setup(): void {
  filterByName.addEventListener("input", () => filterProjects());
  filterByName.addEventListener("keyup", (event) => {
    if (event.key === "Escape") {
      resetFilter();
    }
  });

  const table = document.getElementById("devtoolsProjectList") as HTMLTableElement;
  table.querySelectorAll<HTMLTableRowElement>(".devtoolsProject").forEach((row) => {
    const name = row.dataset.name!.toLowerCase();
    projects.set(name, row);
  });

  if (!isTouch()) {
    filterByName.focus();
  }
}
