/**
 * Handles the mark as read button for single moderation queue entries.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { dboAction } from "../../Ajax";

const unreadEntries = new WeakSet();

async function markAsRead(entry: HTMLElement): Promise<void> {
  const queueId = parseInt(entry.dataset.queueId!, 10);

  await dboAction("markAsRead", "wcf\\data\\moderation\\queue\\ModerationQueueAction").objectIds([queueId]).dispatch();

  entry.classList.remove("new");
  entry.querySelector(".columnAvatar p")?.removeAttribute("title");
}

export function setup(): void {
  document.querySelectorAll(".moderationQueueEntryList .new .columnAvatar").forEach((el: HTMLElement) => {
    if (!unreadEntries.has(el)) {
      unreadEntries.add(el);

      el.addEventListener(
        "dblclick",
        (event) => {
          event.preventDefault();

          const entry = el.closest(".moderationQueueEntry") as HTMLElement;
          if (!entry.classList.contains("new")) {
            return;
          }
          void markAsRead(entry);
        },
        { once: true },
      );
    }
  });
}
