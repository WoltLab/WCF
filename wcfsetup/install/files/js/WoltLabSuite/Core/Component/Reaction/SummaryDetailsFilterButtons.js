/**
 * Handles the filter buttons in the reaction summary details dialog.
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
    function setup(listViewId) {
        document
            .querySelectorAll(`[data-filter-reaction-type-id][data-list-view-id="${listViewId}"]`)
            .forEach((button) => {
            button.addEventListener("click", () => {
                if (button.classList.contains("active")) {
                    return;
                }
                document
                    .querySelectorAll(`[data-filter-reaction-type-id][data-list-view-id="${listViewId}"]`)
                    .forEach((button) => {
                    button.classList.remove("active");
                });
                button.classList.add("active");
                let reactionTypeID = undefined;
                if (button.dataset.filterReactionTypeId && button.dataset.filterReactionTypeId !== "0") {
                    reactionTypeID = button.dataset.filterReactionTypeId;
                }
                const listView = document.getElementById(`${listViewId}_items`);
                listView.dispatchEvent(new CustomEvent("interaction:set-parameters", { detail: { reactionTypeID } }));
            });
        });
    }
});
