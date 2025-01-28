/**
 * Shows the dialog that shows exception details.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { renderException } from "WoltLabSuite/Core/Api/Exceptions/RenderException";
import { copyTextToClipboard } from "WoltLabSuite/Core/Clipboard";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { getPhrase } from "WoltLabSuite/Core/Language";

async function showDialog(button: HTMLElement): Promise<void> {
  const response = await renderException(button.closest("tr")!.dataset.objectId!);
  if (!response.ok) {
    return;
  }

  const dialog = dialogFactory().fromHtml(response.value.template).withoutControls();
  dialog.content.querySelector(".jsCopyButton")?.addEventListener("click", () => {
    void copyTextToClipboard(dialog.content.querySelector<HTMLTextAreaElement>(".jsCopyException")!.value);
  });

  dialog.show(getPhrase("wcf.acp.exceptionLog.exception.message"));
}

export function setup(): void {
  wheneverFirstSeen(".jsExceptionLogEntry", (button) => {
    button.addEventListener(
      "click",
      promiseMutex(() => showDialog(button)),
    );
  });
}
