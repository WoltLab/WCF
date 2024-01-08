/**
 * Handles the user follow buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { getPhrase } from "WoltLabSuite/Core/Language";

function toggleFollow(button: HTMLButtonElement): void {
  if (button.dataset.following != "1") {
    button.dataset.following = "1";
    button.dataset.tooltip = getPhrase("wcf.user.button.unfollow");
    button.querySelector("fa-icon")?.setIcon("circle-minus");
    void prepareRequest(button.dataset.endpoint!)
      .post({
        action: "follow",
      })
      .fetchAsResponse();
  } else {
    button.dataset.following = "0";
    button.dataset.tooltip = getPhrase("wcf.user.button.follow");
    button.querySelector("fa-icon")?.setIcon("circle-plus");
    void prepareRequest(button.dataset.endpoint!)
      .post({
        action: "unfollow",
      })
      .fetchAsResponse();
  }
}

export function setup(): void {
  document.querySelectorAll<HTMLButtonElement>(".jsFollowButton").forEach((button) => {
    button.addEventListener("click", () => {
      toggleFollow(button);
    });
  });
}
