/**
 * Handles dismissible user notices.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Controller/Notice/Dismiss
 */

import * as Ajax from "../../Ajax";

/**
 * Initializes dismiss buttons.
 */
export function setup(): void {
  document.querySelectorAll(".jsDismissNoticeButton").forEach((button) => {
    button.addEventListener("click", (ev) => click(ev));
  });
}

/**
 * Sends a request to dismiss a notice and removes it afterwards.
 */
function click(event: Event): void {
  const button = event.currentTarget as HTMLElement;

  Ajax.apiOnce({
    data: {
      actionName: "dismiss",
      className: "wcf\\data\\notice\\NoticeAction",
      objectIDs: [button.dataset.objectId!],
    },
    success: () => {
      button.parentElement!.remove();
    },
  });
}
