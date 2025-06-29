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
import { ConfirmationType, handleConfirmation } from "./Confirmation";
import { showDefaultSuccessSnackbar, showSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { InteractionEffect } from "./InteractionEffect";

async function handleRpcInteraction(
  container: HTMLElement,
  element: HTMLElement,
  objectName: string,
  endpoint: string,
  confirmationType: ConfirmationType,
  customConfirmationMessage: string = "",
  interactionEffect: InteractionEffect = InteractionEffect.ReloadItem,
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

  if (interactionEffect === InteractionEffect.ReloadItem) {
    element.dispatchEvent(
      new CustomEvent("interaction:invalidate", {
        bubbles: true,
      }),
    );
  } else if (interactionEffect === InteractionEffect.ReloadList) {
    container.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
  } else {
    element.dispatchEvent(
      new CustomEvent("interaction:remove", {
        bubbles: true,
      }),
    );
  }

  if (confirmationType === ConfirmationType.Delete) {
    showSuccessSnackbar(getPhrase("wcf.global.success.delete"));
  } else {
    showDefaultSuccessSnackbar();
  }
}

export function setup(identifier: string, container: HTMLElement): void {
  container.addEventListener("interaction:execute", (event: CustomEvent) => {
    if (event.detail.interaction === identifier) {
      void handleRpcInteraction(
        container,
        event.target as HTMLElement,
        event.detail.objectName,
        event.detail.endpoint,
        event.detail.confirmationType,
        event.detail.confirmationMessage,
        event.detail.interactionEffect,
      );
    }
  });
}
