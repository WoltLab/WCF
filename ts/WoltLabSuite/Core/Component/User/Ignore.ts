/**
 * Handles the user ignore buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { show as showNotification } from "WoltLabSuite/Core/Ui/Notification";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";

type Response = {
  type: number;
};

async function toggleIgnore(button: HTMLElement): Promise<void> {
  const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<Response>(button.dataset.ignoreUser!);

  if (ok) {
    if (result.type) {
      button.dataset.ignored = "1";
      button.dataset.tooltip = getPhrase("wcf.user.button.unignore");
      button.querySelector("fa-icon")?.setIcon("eye", true);
    } else {
      button.dataset.ignored = "0";
      button.dataset.tooltip = getPhrase("wcf.user.button.ignore");
      button.querySelector("fa-icon")?.setIcon("eye-slash", true);
    }

    showNotification();
  }
}

export function setup(): void {
  wheneverFirstSeen("[data-ignore-user]", (button) => {
    button.addEventListener(
      "click",
      promiseMutex(() => toggleIgnore(button)),
    );
  });
}
