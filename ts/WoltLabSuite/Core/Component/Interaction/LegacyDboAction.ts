/**
 * Handles interactions that call legacy DBO actions.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @deprecated 6.2 DBO actions are considered outdated and should be migrated to RPC endpoints.
 */

import { dboAction } from "WoltLabSuite/Core/Ajax";
import { show as showNotification } from "WoltLabSuite/Core/Ui/Notification";
import { ConfirmationType, handleConfirmation } from "./Confirmation";

async function handleDboAction(
  element: HTMLElement,
  objectName: string,
  className: string,
  actionName: string,
  confirmationType: ConfirmationType,
  customConfirmationMessage: string = "",
): Promise<void> {
  const confirmationResult = await handleConfirmation(objectName, confirmationType, customConfirmationMessage);
  if (!confirmationResult.result) {
    return;
  }

  await dboAction(actionName, className)
    .objectIds([parseInt(element.dataset.objectId!)])
    .payload(confirmationResult.reason ? { reason: confirmationResult.reason } : {})
    .dispatch();

  if (confirmationType == ConfirmationType.Delete) {
    // TODO: This shows a generic success message and should be replaced with a more specific message.
    showNotification(undefined, () => {
      element.dispatchEvent(
        new CustomEvent("remove", {
          bubbles: true,
        }),
      );
    });
  } else {
    element.dispatchEvent(
      new CustomEvent("refresh", {
        bubbles: true,
      }),
    );

    // TODO: This shows a generic success message and should be replaced with a more specific message.
    showNotification();
  }
}

export function setup(identifier: string, container: HTMLElement): void {
  container.addEventListener("interaction", (event: CustomEvent) => {
    if (event.detail.interaction === identifier) {
      void handleDboAction(
        event.target as HTMLElement,
        event.detail.objectName,
        event.detail.className,
        event.detail.actionName,
        event.detail.confirmationType,
        event.detail.confirmationMessage,
      );
    }
  });
}
