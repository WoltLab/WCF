/**
 * Handles bulk interactions that call a RPC endpoint.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { deleteObject } from "WoltLabSuite/Core/Api/DeleteObject";
import { postObject } from "WoltLabSuite/Core/Api/PostObject";
import { show as showNotification } from "WoltLabSuite/Core/Ui/Notification";
import { ConfirmationType, handleConfirmation } from "../Confirmation";

async function handleRpcInteraction(
  container: HTMLElement,
  objectIds: number[],
  endpoint: string,
  confirmationType: ConfirmationType,
  customConfirmationMessage: string = "",
): Promise<void> {
  const confirmationResult = await handleConfirmation("", confirmationType, customConfirmationMessage);
  if (!confirmationResult.result) {
    return;
  }

  if (confirmationType == ConfirmationType.Delete) {
    for (let i = 0; i < objectIds.length; i++) {
      const result = await deleteObject(endpoint.replace(/%s/, objectIds[i].toString()));
      if (!result.ok) {
        return;
      }
    }
  } else {
    for (let i = 0; i < objectIds.length; i++) {
      const result = await postObject(
        endpoint.replace(/%s/, objectIds[i].toString()),
        confirmationResult.reason ? { reason: confirmationResult.reason } : undefined,
      );
      if (!result.ok) {
        return;
      }
    }
  }

  if (confirmationType === ConfirmationType.Delete) {
    // TODO: This shows a generic success message and should be replaced with a more specific message.
    showNotification(undefined, () => {
      for (let i = 0; i < objectIds.length; i++) {
        const element = container.querySelector(`[data-object-id="${objectIds[i]}"]`);
        if (!element) {
          continue;
        }

        element.dispatchEvent(
          new CustomEvent("remove", {
            bubbles: true,
          }),
        );
      }
    });
  } else {
    for (let i = 0; i < objectIds.length; i++) {
      const element = container.querySelector(`[data-object-id="${objectIds[i]}"]`);
      if (!element) {
        continue;
      }

      element.dispatchEvent(
        new CustomEvent("refresh", {
          bubbles: true,
        }),
      );
    }

    // TODO: This shows a generic success message and should be replaced with a more specific message.
    showNotification();
  }

  container.dispatchEvent(new CustomEvent("reset-selection"));
}

export function setup(identifier: string, container: HTMLElement): void {
  container.addEventListener("bulk-interaction", (event: CustomEvent) => {
    if (event.detail.bulkInteraction === identifier) {
      void handleRpcInteraction(
        container,
        JSON.parse(event.detail.objectIds),
        event.detail.endpoint,
        event.detail.confirmationType,
        event.detail.confirmationMessage,
      );
    }
  });
}
