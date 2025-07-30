/**
 * Handles interactions that open a form builder dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { InteractionEffect } from "./InteractionEffect";

type Payload = Record<string, string>;

async function handleFormBuilderDialogAction(
  container: HTMLElement,
  element: HTMLElement,
  endpoint: string,
  interactionEffect: InteractionEffect = InteractionEffect.ReloadItem,
  detail: Payload,
): Promise<void> {
  const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<Payload>(endpoint);

  if (!ok) {
    return;
  }

  if (interactionEffect === InteractionEffect.ReloadItem || interactionEffect === InteractionEffect.ReloadPage) {
    element.dispatchEvent(
      new CustomEvent<Payload>("interaction:invalidate", {
        bubbles: true,
        detail: {
          ...detail,
          ...result,
          _reloadPage: String(interactionEffect === InteractionEffect.ReloadPage),
        },
      }),
    );
  } else if (interactionEffect === InteractionEffect.ReloadList) {
    container.dispatchEvent(
      new CustomEvent<Payload>("interaction:invalidate-all", {
        detail: {
          ...detail,
          ...result,
        },
      }),
    );
  } else {
    element.dispatchEvent(
      new CustomEvent<Payload>("interaction:remove", {
        bubbles: true,
        detail: {
          ...detail,
          ...result,
        },
      }),
    );
  }

  showDefaultSuccessSnackbar();
}

export function setup(identifier: string, container: HTMLElement): void {
  container.addEventListener("interaction:execute", (event: CustomEvent<Payload>) => {
    if (event.detail.interaction === identifier) {
      void handleFormBuilderDialogAction(
        container,
        event.target as HTMLElement,
        event.detail.endpoint,
        event.detail.interactionEffect as InteractionEffect,
        event.detail,
      );
    }
  });
}
