/**
 * Handles the filter buttons in the reaction summary details dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

export function setup(listViewId: string): void {
  document
    .querySelectorAll<HTMLButtonElement>(`[data-filter-reaction-type-id][data-list-view-id="${listViewId}"]`)
    .forEach((button) => {
      button.addEventListener("click", () => {
        if (button.classList.contains("active")) {
          return;
        }

        document
          .querySelectorAll<HTMLButtonElement>(`[data-filter-reaction-type-id][data-list-view-id="${listViewId}"]`)
          .forEach((button) => {
            button.classList.remove("active");
          });

        button.classList.add("active");

        let reactionTypeID: string | undefined = undefined;
        if (button.dataset.filterReactionTypeId && button.dataset.filterReactionTypeId !== "0") {
          reactionTypeID = button.dataset.filterReactionTypeId;
        }
        const listView = document.getElementById(`${listViewId}_items`);
        listView!.dispatchEvent(new CustomEvent("interaction:set-parameters", { detail: { reactionTypeID } }));
      });
    });
}
