/**
 * Handles the buttons that allow the user to clear the cache.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { confirmationFactory } from "WoltLabSuite/Core/Component/Confirmation";
import { getPhrase } from "WoltLabSuite/Core/Language";
import * as UiNotification from "WoltLabSuite/Core/Ui/Notification";

function initButton(button: HTMLButtonElement): void {
  button.addEventListener("click", () => {
    void clearCache(button.dataset.endpoint!);
  });
}

async function clearCache(endpoint: string): Promise<void> {
  const result = await confirmationFactory().custom(getPhrase("wcf.acp.cache.clear.sure")).withoutMessage();
  if (result) {
    await prepareRequest(endpoint).post().fetchAsResponse();
    UiNotification.show();
  }
}

export function setup(): void {
  document.querySelectorAll<HTMLButtonElement>(".jsCacheClearButton").forEach((button) => {
    initButton(button);
  });
}
