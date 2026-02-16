/**
 * Handles the 'mark as read' action for articles.
 *
 * @author  Marcel Werk
 * @copyright  2001-2023 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { dboAction } from "../../Ajax";

async function markAllAsRead(): Promise<void> {
  await dboAction("markAllAsRead", "wcf\\data\\article\\ArticleAction").dispatch();

  document.querySelectorAll(".contentItemList .contentItemBadgeNew").forEach((el: HTMLElement) => el.remove());
  document.querySelectorAll(".boxMenu .active .badgeUpdate").forEach((el: HTMLElement) => el.remove());

  showDefaultSuccessSnackbar();
}

export function setup(): void {
  document.querySelectorAll(".markAllAsReadButton").forEach((el: HTMLElement) => {
    el.addEventListener("click", (event) => {
      event.preventDefault();

      void markAllAsRead();
    });
  });
}
