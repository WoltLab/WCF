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
    exports.setup = void 0;
    const filterByName = document.getElementById("filterByName");
    const projects = new Map();
    function filterProjects() {
        const value = filterByName.value.trim().toLowerCase();
        if (value === "") {
            resetFilter();
            return;
        }
        projects.forEach((row, name) => {
            if (name.includes(value)) {
                row.hidden = false;
            }
            else {
                row.hidden = true;
            }
        });
    }
    function resetFilter() {
        filterByName.value = "";
        projects.forEach((row) => (row.hidden = false));
    }
    function setup() {
        filterByName.addEventListener("input", () => filterProjects());
        filterByName.addEventListener("keyup", (event) => {
            if (event.key === "Escape") {
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
    exports.setup = setup;
});
