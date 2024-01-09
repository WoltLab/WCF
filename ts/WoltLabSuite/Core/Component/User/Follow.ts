/**
 * Handles the user follow buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { getPhrase } from "WoltLabSuite/Core/Language";
import * as UiNotification from "WoltLabSuite/Core/Ui/Notification";

async function toggleFollow(button: HTMLElement): Promise<void> {
  if (button.dataset.following !== "1") {
    await prepareRequest(button.dataset.followUser!)
      .post({
        action: "follow",
      })
      .fetchAsResponse();

    button.dataset.following = "1";
    button.dataset.tooltip = getPhrase("wcf.user.button.unfollow");
    button.querySelector("fa-icon")?.setIcon("circle-minus");
  } else {
    await prepareRequest(button.dataset.followUser!)
      .post({
        action: "unfollow",
      })
      .fetchAsResponse();

    button.dataset.following = "0";
    button.dataset.tooltip = getPhrase("wcf.user.button.follow");
    button.querySelector("fa-icon")?.setIcon("circle-plus");
  }

  UiNotification.show();
}

export function setup(): void {
  wheneverFirstSeen("[data-follow-user]", (button) => {
    button.addEventListener(
      "click",
      promiseMutex(() => toggleFollow(button)),
    );
  });
}
