/**
 * Shows the dialog that allows the user to configure the dashboard boxes.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { show as showNotification } from "WoltLabSuite/Core/Ui/Notification";

async function showDialog(url: string): Promise<void> {
  const { ok } = await dialogFactory().usingFormBuilder().fromEndpoint<Response>(url);

  if (ok) {
    showNotification(undefined, () => {
      window.location.reload();
    });
  }
}

export function setup(button: HTMLElement): void {
  button.addEventListener(
    "click",
    promiseMutex(() => showDialog(button.dataset.url!)),
  );
}
