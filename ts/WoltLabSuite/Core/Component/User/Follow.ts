/**
 * Handles the user follow buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { followUser } from "WoltLabSuite/Core/Api/Users/FollowUser";
import { unfollowUser } from "WoltLabSuite/Core/Api/Users/UnfollowUser";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { showDefaultSuccessSnackbar } from "../Snackbar";

async function sendRequest(button: HTMLElement, follow: boolean): Promise<void> {
  const value = button.dataset.followUser!;

  // Legacy markup provides the URL of the deprecated `UserFollowAction`.
  if (!/^\d+$/.test(value)) {
    await prepareRequest(value)
      .post({
        action: follow ? "follow" : "unfollow",
      })
      .fetchAsResponse();

    return;
  }

  const userId = parseInt(value, 10);
  if (follow) {
    await followUser(userId);
  } else {
    await unfollowUser(userId);
  }
}

async function toggleFollow(button: HTMLElement): Promise<void> {
  if (button.dataset.following !== "1") {
    await sendRequest(button, true);

    button.dataset.following = "1";

    if (button.dataset.type === "button") {
      button.textContent = getPhrase("wcf.user.button.unfollow");
    } else {
      button.dataset.tooltip = getPhrase("wcf.user.button.unfollow");
      button.querySelector("fa-icon")?.setIcon("user-minus");
      updateInvisibleLabel(button, getPhrase("wcf.user.button.unfollow"));
    }
  } else {
    await sendRequest(button, false);

    button.dataset.following = "0";

    if (button.dataset.type === "button") {
      button.textContent = getPhrase("wcf.user.button.follow");
    } else {
      button.dataset.tooltip = getPhrase("wcf.user.button.follow");
      button.querySelector("fa-icon")?.setIcon("user-plus");
      updateInvisibleLabel(button, getPhrase("wcf.user.button.follow"));
    }
  }

  showDefaultSuccessSnackbar();
}

// Icon buttons in a `.buttonGroupNavigation` show this label in the mobile dropdown.
function updateInvisibleLabel(button: HTMLElement, label: string): void {
  const invisible = button.querySelector(".invisible");
  if (invisible !== null) {
    invisible.textContent = label;
  }
}

export function setup(): void {
  wheneverFirstSeen("[data-follow-user]", (button) => {
    button.addEventListener(
      "click",
      promiseMutex(() => toggleFollow(button)),
    );
  });
}
