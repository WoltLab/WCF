/**
 * Handles the 'mark as read' action for moderation queues.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { markAllModerationQueuesAsRead } from "WoltLabSuite/Core/Api/ModerationQueues/MarkAllModerationQueuesAsRead";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";

async function markAllAsRead(button: HTMLElement, gridView?: HTMLElement): Promise<void> {
  (await markAllModerationQueuesAsRead()).unwrap();

  if (gridView !== undefined) {
    gridView.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
  }

  button.remove();

  showDefaultSuccessSnackbar();
}

export function setup(button: HTMLElement, gridView?: HTMLElement): void {
  button.addEventListener(
    "click",
    promiseMutex(async () => {
      await markAllAsRead(button, gridView);
    }),
  );
}
