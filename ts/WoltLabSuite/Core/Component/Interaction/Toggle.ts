/**
 * Handles a toggle interaction.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { postObject } from "WoltLabSuite/Core/Api/PostObject";

async function handleToggle(checked: boolean, enableEndpoint: string, disableEndpoint: string): Promise<void> {
  await postObject(checked ? enableEndpoint : disableEndpoint);
}

export function setup(identifier: string, container: HTMLElement): void {
  wheneverFirstSeen(`#${container.id} [data-interaction="${identifier}"]`, (toggleButton) => {
    toggleButton.addEventListener("change", (event: CustomEvent) => {
      void handleToggle(
        event.detail.checked as boolean,
        toggleButton.dataset.enableEndpoint!,
        toggleButton.dataset.disableEndpoint!,
      );
    });
  });
}
