/**
 * Handles interactions that call an RPC endpoint.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { deleteObject } from "WoltLabSuite/Core/Api/DeleteObject";
import { postObject } from "WoltLabSuite/Core/Api/PostObject";
import { ConfirmationType, handleConfirmation, parseAffectedObjects } from "./Confirmation";
import { showDefaultSuccessSnackbar, showSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { InteractionEffect } from "./InteractionEffect";

type Payload = Record<string, string>;
type Response = {
  redirectUrl?: string;
};

async function handleRpcInteraction(
  container: HTMLElement,
  element: HTMLElement,
  objectName: string,
  endpoint: string,
  confirmationType: ConfirmationType,
  customConfirmationMessage: string = "",
  interactionEffect: InteractionEffect = InteractionEffect.ReloadItem,
  affectedObjects: string[] = [],
  detail: Payload,
): Promise<void> {
  const confirmationResult = await handleConfirmation(
    objectName,
    confirmationType,
    customConfirmationMessage,
    affectedObjects,
  );
  if (!confirmationResult.result) {
    return;
  }

  let response: Response = {};
  if (confirmationType == ConfirmationType.Delete) {
    (await deleteObject(endpoint)).unwrap();
  } else {
    response = (
      await postObject<Response>(
        endpoint,
        confirmationResult.reason ? { reason: confirmationResult.reason } : undefined,
      )
    ).unwrap();
  }

  if (interactionEffect === InteractionEffect.Redirect) {
    const { redirectUrl } = response;
    if (redirectUrl) {
      showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
        window.location.href = redirectUrl;
      });

      return;
    }

    // The endpoint did not provide a target, reload the page instead.
    interactionEffect = InteractionEffect.ReloadPage;
  }

  if (interactionEffect === InteractionEffect.ReloadItem || interactionEffect === InteractionEffect.ReloadPage) {
    element.dispatchEvent(
      new CustomEvent<Payload>("interaction:invalidate", {
        bubbles: true,
        detail: {
          ...detail,
          _reloadPage: String(interactionEffect === InteractionEffect.ReloadPage),
        },
      }),
    );
  } else if (interactionEffect === InteractionEffect.ReloadList) {
    container.dispatchEvent(new CustomEvent<Payload>("interaction:invalidate-all", { detail }));
  } else {
    element.dispatchEvent(
      new CustomEvent<Payload>("interaction:remove", {
        bubbles: true,
        detail,
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
  container.addEventListener("interaction:execute", (event: CustomEvent<Payload>) => {
    if (event.detail.interaction === identifier) {
      void handleRpcInteraction(
        container,
        event.target as HTMLElement,
        event.detail.objectName,
        event.detail.endpoint,
        event.detail.confirmationType as ConfirmationType,
        event.detail.confirmationMessage,
        event.detail.interactionEffect as InteractionEffect,
        parseAffectedObjects(event.detail.affectedObjects),
        event.detail,
      );
    }
  });
}
