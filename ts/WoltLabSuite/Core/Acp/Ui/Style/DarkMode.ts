/**
 * Allows the addition of a dark mode to an existing style.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Acp/Ui/Style/DarkMode
 * @since 6.0
 */

import { prepareRequest } from "../../../Ajax/Backend";
import { confirmationFactory } from "../../../Component/Confirmation";
import { getPhrase } from "../../../Language";
import { show as showNotification } from "../../../Ui/Notification";

async function promptConfirmation(endpoint: string, question: string): Promise<void> {
  const ok = await confirmationFactory().custom(question).message(getPhrase("wcf.dialog.confirmation.cannotBeUndone"));
  if (ok) {
    const response = await prepareRequest(endpoint).post().fetchAsResponse();
    if (response?.ok) {
      showNotification(undefined, () => {
        window.location.reload();
      });
    }
  }
}

function setupAddDarkMode(): void {
  const button = document.querySelector<HTMLButtonElement>(".jsButtonAddDarkMode");
  button?.addEventListener("click", () => {
    void promptConfirmation(button.dataset.endpoint!, button.dataset.question!);
  });
}

export function setup(): void {
  setupAddDarkMode();
}
