/**
 * Handles dismissible user notices.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import { dismissNotice } from "WoltLabSuite/Core/Api/Notices/DismissNotice";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";

/**
 * Initializes dismiss buttons.
 */
export function setup(): void {
  document.querySelectorAll<HTMLElement>(".jsDismissNoticeButton").forEach((button) => {
    button.addEventListener(
      "click",
      promiseMutex(() => click(button)),
    );
  });
}

/**
 * Sends a request to dismiss a notice and removes it afterwards.
 */
async function click(button: HTMLElement): Promise<void> {
  await dismissNotice(parseInt(button.dataset.objectId!, 10));

  button.parentElement!.remove();
}
