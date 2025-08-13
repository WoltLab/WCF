/**
 * Handles the 'mark as read' action for articles.
 *
 * @author  Marcel Werk
 * @copyright  2001-2023 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { markAllArticlesAsRead } from "WoltLabSuite/Core/Api/Articles/MarkAllArticlesAsRead";

async function markAllAsRead(): Promise<void> {
  await markAllArticlesAsRead();

  document.querySelectorAll(".contentItemList .contentItemBadgeNew").forEach((el: HTMLElement) => el.remove());
  document.querySelectorAll(".boxMenu .active .badge").forEach((el: HTMLElement) => el.remove());

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
