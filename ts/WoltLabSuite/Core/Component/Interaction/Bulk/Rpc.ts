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
import { ConfirmationType, handleConfirmation } from "../Confirmation";
import { showProgressSnackbar } from "WoltLabSuite/Core/Component/Snackbar";

async function handleRpcInteraction(
  container: HTMLElement,
  objectIds: number[],
  endpoint: string,
  label: string,
  confirmationType: ConfirmationType,
  customConfirmationMessage: string = "",
): Promise<void> {
  const confirmationResult = await handleConfirmation("", confirmationType, customConfirmationMessage);
  if (!confirmationResult.result) {
    return;
  }

  const snackbar = showProgressSnackbar(label, objectIds.length);

  for (let i = 0; i < objectIds.length; i++) {
    if (confirmationType == ConfirmationType.Delete) {
      await deleteObject(endpoint.replace(/%s/, objectIds[i].toString()));
    } else {
      await postObject(
        endpoint.replace(/%s/, objectIds[i].toString()),
        confirmationResult.reason ? { reason: confirmationResult.reason } : undefined,
      );
    }

    snackbar.setIteration(i + 1);

    const element = container.querySelector(`[data-object-id="${objectIds[i]}"]`);
    if (!element) {
      continue;
    }

    if (confirmationType == ConfirmationType.Delete) {
      element.dispatchEvent(
        new CustomEvent("interaction:remove", {
          bubbles: true,
        }),
      );
    } else {
      element.dispatchEvent(
        new CustomEvent("interaction:invalidate", {
          bubbles: true,
        }),
      );
    }
  }

  snackbar.markAsDone();
  container.dispatchEvent(new CustomEvent("interaction:reset-selection"));
}

export function setup(identifier: string, container: HTMLElement): void {
  container.addEventListener("bulk-interaction", (event: CustomEvent) => {
    if (event.detail.bulkInteraction === identifier) {
      void handleRpcInteraction(
        container,
        JSON.parse(event.detail.objectIds),
        event.detail.endpoint,
        event.detail.label,
        event.detail.confirmationType,
        event.detail.confirmationMessage,
      );
    }
  });
}
