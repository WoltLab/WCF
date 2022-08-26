/**
 * Marks all moderation queue entries as read.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Moderation/MarkAllAsRead
 * @since 6.0
 */

import { dboAction } from "../../Ajax";
import * as UiNotification from "../Notification";

async function markAllAsRead(): Promise<void> {
  await dboAction("markAllAsRead", "wcf\\data\\moderation\\queue\\ModerationQueueAction").dispatch();

  document.querySelectorAll(".moderationQueueEntryList .new").forEach((el: HTMLElement) => {
    el.classList.remove("new");
  });
  document.querySelector("#outstandingModeration .badgeUpdate")?.remove();

  UiNotification.show();
}

export function setup(): void {
  document.querySelectorAll(".markAllAsReadButton").forEach((el: HTMLElement) => {
    el.addEventListener("click", (event) => {
      event.preventDefault();

      void markAllAsRead();
    });
  });
}
