/**
 * Handles bulk interactions that open a form builder dialog.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { InteractionEffect } from "../InteractionEffect";

type Payload = Record<string, string>;

async function handleFormBuilderDialogAction(
  container: HTMLElement,
  objectIds: number[],
  endpoint: string,
  interactionEffect: InteractionEffect = InteractionEffect.ReloadItem,
): Promise<void> {
  const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<Payload>(endpoint);

  if (!ok) {
    return;
  }

  container.dispatchEvent(new CustomEvent("interaction:reset-selection"));

  if (interactionEffect === InteractionEffect.Redirect) {
    const { redirectUrl } = result;
    if (redirectUrl) {
      showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
        window.location.href = redirectUrl;
      });

      return;
    }

    // The endpoint did not provide a target, reload the page instead.
    interactionEffect = InteractionEffect.ReloadPage;
  }

  if (interactionEffect === InteractionEffect.ReloadPage) {
    showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
      window.location.reload();
    });

    return;
  }

  if (interactionEffect === InteractionEffect.ReloadList) {
    container.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
  } else {
    const eventName =
      interactionEffect === InteractionEffect.RemoveItem ? "interaction:remove" : "interaction:invalidate";

    for (let i = 0; i < objectIds.length; i++) {
      const element = container.querySelector(`[data-object-id="${objectIds[i]}"]`);
      if (!element) {
        continue;
      }

      element.dispatchEvent(
        new CustomEvent(eventName, {
          bubbles: true,
        }),
      );
    }
  }

  showDefaultSuccessSnackbar();
}

export function setup(identifier: string, container: HTMLElement): void {
  container.addEventListener("bulk-interaction", (event: CustomEvent) => {
    if (event.detail.bulkInteraction === identifier) {
      void handleFormBuilderDialogAction(
        container,
        JSON.parse(event.detail.objectIds),
        event.detail.endpoint,
        event.detail.interactionEffect as InteractionEffect,
      );
    }
  });
}
