/**
 * Handles interactions that open a form builder dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { show as showNotification } from "WoltLabSuite/Core/Ui/Notification";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";

async function handleFormBuilderDialogAction(element: HTMLElement, endpoint: string): Promise<void> {
  const { ok } = await dialogFactory().usingFormBuilder().fromEndpoint(endpoint);

  if (!ok) {
    return;
  }

  element.dispatchEvent(
    new CustomEvent("refresh", {
      bubbles: true,
    }),
  );

  // TODO: This shows a generic success message and should be replaced with a more specific message.
  showNotification();
}

export function setup(identifier: string, container: HTMLElement): void {
  container.addEventListener("interaction", (event: CustomEvent) => {
    if (event.detail.interaction === identifier) {
      void handleFormBuilderDialogAction(event.target as HTMLElement, event.detail.endpoint);
    }
  });
}
