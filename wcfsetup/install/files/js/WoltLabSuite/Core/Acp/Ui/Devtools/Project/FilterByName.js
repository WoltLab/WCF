/**
 * Provides a filter input to narrow down the list of projects.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "WoltLabSuite/Core/Environment"], function (require, exports, Environment_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    const filterByName = document.getElementById("filterByName");
    const projects = new Map();
    function filterProjects() {
        const value = filterByName.value.trim().toLowerCase();
        if (value === "") {
            resetFilter();
            return;
        }
        let firstProject = undefined;
        projects.forEach((row, name) => {
            if (name.includes(value)) {
                row.hidden = false;
                if (!firstProject) {
                    firstProject = row;
                }
            }
            else {
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
    function resetFilter() {
        filterByName.value = "";
        projects.forEach((row) => {
            row.hidden = false;
            row.classList.remove("devtoolsProject--highlighted");
        });
    }
    function highlightProject(target) {
        projects.forEach((row) => {
            if (row === target) {
                row.classList.add("devtoolsProject--highlighted");
            }
            else {
                row.classList.remove("devtoolsProject--highlighted");
            }
        });
    }
    function syncHighlightedProject() {
        const row = getHighlightedProject();
        if (row) {
            const button = row.querySelector(".devtoolsProjectSync");
            button.click();
        }
    }
    function highlightPreviousProject() {
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
    function highlightNextProject() {
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
    function getVisibleProjects() {
        return Array.from(projects.values()).filter((project) => project.hidden === false);
    }
    function getHighlightedProject() {
        return Array.from(projects.values()).find((project) => project.classList.contains("devtoolsProject--highlighted"));
    }
    function setup() {
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
        const table = document.getElementById("devtoolsProjectList");
        table.querySelectorAll(".devtoolsProject").forEach((row) => {
            const name = row.dataset.name.toLowerCase();
            projects.set(name, row);
        });
        if (!(0, Environment_1.touch)()) {
            filterByName.focus();
        }
    }
});
