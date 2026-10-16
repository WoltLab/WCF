/**
 * Shows the trophies of users.
 *
 * @author Joshua Ruesweg, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";

async function showDialog(userId: number): Promise<void> {
  await dialogFactory()
    .usingListView()
    .fromPreset(
      getPhrase("wcf.user.trophy.trophies"),
      "wcf\\system\\listView\\user\\UserTrophyListView",
      new Map([["userID", userId.toString()]]),
      undefined,
      "time",
      "DESC",
    );
}

export function setup(): void {
  wheneverFirstSeen(".userTrophyOverlayList", (button) => {
    button.addEventListener(
      "click",
      promiseMutex((event) => {
        event.preventDefault();
        return showDialog(parseInt(button.dataset.userId!));
      }),
    );
  });
}
