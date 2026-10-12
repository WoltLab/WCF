/**
 * Shows the activity point list for users.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { getUserActivityPointList } from "WoltLabSuite/Core/Api/Users/GetUserActivityPointList";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";

async function showDialog(userId: number): Promise<void> {
  const { template } = await getUserActivityPointList(userId);

  const dialog = dialogFactory().fromHtml(template).withoutControls();
  dialog.show(getPhrase("wcf.user.activityPoint"));
}

export function setup(): void {
  wheneverFirstSeen(".activityPointsDisplay", (button) => {
    button.addEventListener(
      "click",
      promiseMutex((event) => {
        event.preventDefault();
        return showDialog(parseInt(button.dataset.userId!));
      }),
    );
  });
}
