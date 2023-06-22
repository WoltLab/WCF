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

  let firstProject: HTMLTableRowElement | undefined = undefined;
  projects.forEach((row, name) => {
    if (name.includes(value)) {
      row.hidden = false;

      if (!firstProject) {
        firstProject = row;
      }
    } else {
      row.hidden = true;
    }
  });

  // Prefer exact matches when selecting packages.
  for (const [name, project] of projects) {
    if (name === value) {
      firstProject = project;
      break;
    }
  }

  if (firstProject) {
    highlightProject(firstProject);
  }
}

function resetFilter(): void {
  filterByName.value = "";
  projects.forEach((row) => {
    row.hidden = false;
    row.classList.remove("devtoolsProject--highlighted");
  });
}

function highlightProject(target: HTMLTableRowElement): void {
  projects.forEach((row) => {
    if (row === target) {
      row.classList.add("devtoolsProject--highlighted");
    } else {
      row.classList.remove("devtoolsProject--highlighted");
    }
  });
}

function syncHighlightedProject(): void {
  const row = getHighlightedProject();

  if (row) {
    const button = row.querySelector(".devtoolsProjectSync") as HTMLAnchorElement;
    button.click();
  }
}

function highlightPreviousProject(): void {
  const projects = getVisibleProjects();
  const current = getHighlightedProject();
  if (!current) {
    return;
  }

  let index = projects.indexOf(current) - 1;
  if (index < 0) {
    index = projects.length - 1;
  }

  highlightProject(projects[index]);
}

function highlightNextProject(): void {
  const projects = getVisibleProjects();
  const current = getHighlightedProject();
  if (!current) {
    return;
  }

  let index = projects.indexOf(current) + 1;
  if (index >= projects.length) {
    index = 0;
  }

  highlightProject(projects[index]);
}

function getVisibleProjects(): HTMLTableRowElement[] {
  return Array.from(projects.values()).filter((project) => project.hidden === false);
}

function getHighlightedProject(): HTMLTableRowElement | undefined {
  return Array.from(projects.values()).find((project) => project.classList.contains("devtoolsProject--highlighted"));
}

export function setup(): void {
  filterByName.addEventListener("input", () => filterProjects());
  filterByName.addEventListener("keydown", (event) => {
    if (event.key === "ArrowDown") {
      event.preventDefault();

      highlightNextProject();
    }

    if (event.key === "ArrowUp") {
      event.preventDefault();

      highlightPreviousProject();
    }

    if (event.key === "Enter") {
      event.preventDefault();

      syncHighlightedProject();
    }

    if (event.key === "Escape") {
      event.preventDefault();

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
