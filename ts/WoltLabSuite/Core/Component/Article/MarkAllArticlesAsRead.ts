/**
 * Handles the 'mark as read' action for articles.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { markAllArticlesAsRead } from "WoltLabSuite/Core/Api/Articles/MarkAllArticlesAsRead";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";

async function markAllAsRead(button: HTMLElement, listView?: HTMLElement): Promise<void> {
  await markAllArticlesAsRead();

  if (listView !== undefined) {
    listView.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
  }

  document.querySelectorAll(".boxMenu .active .badgeUpdate").forEach((el: HTMLElement) => el.remove());

  button.remove();

  showDefaultSuccessSnackbar();
}

export function setup(button: HTMLElement, listView?: HTMLElement): void {
  button.addEventListener(
    "click",
    promiseMutex(async () => {
      await markAllAsRead(button, listView);
    }),
  );
}
