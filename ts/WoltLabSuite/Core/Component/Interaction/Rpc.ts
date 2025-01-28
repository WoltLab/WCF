/**
 * Handles interactions that call a RPC endpoint.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { deleteObject } from "WoltLabSuite/Core/Api/DeleteObject";
import { postObject } from "WoltLabSuite/Core/Api/PostObject";
import { show as showNotification } from "WoltLabSuite/Core/Ui/Notification";
import { ConfirmationType, handleConfirmation } from "./Confirmation";

async function handleRpcInteraction(
  element: HTMLElement,
  objectName: string,
  endpoint: string,
  confirmationType: ConfirmationType,
  customConfirmationMessage: string = "",
): Promise<void> {
  const confirmationResult = await handleConfirmation(objectName, confirmationType, customConfirmationMessage);
  if (!confirmationResult.result) {
    return;
  }

  if (confirmationType == ConfirmationType.Delete) {
    const result = await deleteObject(endpoint);
    if (!result.ok) {
      return;
    }
  } else {
    const result = await postObject(
      endpoint,
      confirmationResult.reason ? { reason: confirmationResult.reason } : undefined,
    );
    if (!result.ok) {
      return;
    }
  }

  if (confirmationType === ConfirmationType.Delete) {
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
      void handleRpcInteraction(
        event.target as HTMLElement,
        event.detail.objectName,
        event.detail.endpoint,
        event.detail.confirmationType,
        event.detail.confirmationMessage,
      );
    }
  });
}
