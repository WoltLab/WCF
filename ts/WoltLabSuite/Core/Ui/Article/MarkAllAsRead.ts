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
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";

async function markAllAsRead(listView?: HTMLElement): Promise<void> {
  await dboAction("markAllAsRead", "wcf\\data\\article\\ArticleAction").dispatch();

  if (listView !== undefined) {
    listView.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
  }

  document.querySelectorAll(".boxMenu .active .badgeUpdate").forEach((el: HTMLElement) => el.remove());

  showDefaultSuccessSnackbar();
}

export function setup(listView?: HTMLElement): void {
  document.querySelectorAll(".markAllAsReadButton").forEach((el: HTMLElement) => {
    el.addEventListener(
      "click",
      promiseMutex(async () => {
        await markAllAsRead(listView);
      }),
    );
  });
}
