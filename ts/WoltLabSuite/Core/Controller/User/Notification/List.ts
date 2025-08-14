/**
 * Handles the buttons on the notifcation list page.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 * @woltlabExcludeBundle tiny
 */

import { confirmationFactory } from "WoltLabSuite/Core/Component/Confirmation";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { markAllUserNotificationsAsRead } from "WoltLabSuite/Core/Api/Users/Notifications/MarkAllUserNotificationsAsRead";
import { markUserNotificationAsRead } from "WoltLabSuite/Core/Api/Users/Notifications/MarkUserNotificationAsRead";

function initMarkAllAsRead(): void {
  document.querySelector(".jsMarkAllAsConfirmed")?.addEventListener(
    "click",
    promiseMutex(() => markAllAsRead()),
  );
}

async function markAllAsRead(): Promise<void> {
  const result = await confirmationFactory()
    .custom(getPhrase("wcf.user.notification.markAllAsConfirmed.confirmMessage"))
    .withoutMessage();
  if (!result) {
    return;
  }

  await markAllUserNotificationsAsRead();

  showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
    window.location.reload();
  });
}

function initMarkAsRead(): void {
  document.querySelectorAll<HTMLElement>('.notificationListItem[data-is-read="false"]').forEach((element) => {
    element.querySelector(".notificationListItem__markAsRead")?.addEventListener(
      "click",
      promiseMutex(() => markAsRead(element)),
    );
  });
}

async function markAsRead(element: HTMLElement): Promise<void> {
  await markUserNotificationAsRead(parseInt(element.dataset.objectId!, 10));

  element.querySelector(".notificationListItem__unread")?.remove();
  element.dataset.isRead = "true";
}

export function setup(): void {
  initMarkAllAsRead();
  initMarkAsRead();
}
