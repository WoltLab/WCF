/**
 * Handles the reaction summary details dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { getPhrase } from "WoltLabSuite/Core/Language";
import { dialogFactory } from "../../Component/Dialog";
import { wheneverFirstSeen } from "../../Helper/Selector";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";

export function setup(): void {
  wheneverFirstSeen("woltlab-core-reaction-summary", (element: WoltlabCoreReactionSummaryElement) => {
    element.addEventListener(
      "showDetails",
      promiseMutex(() => {
        return dialogFactory()
          .usingListView()
          .fromPreset(
            getPhrase("wcf.reactions.summary.title"),
            "wcf\\system\\listView\\user\\ReactionSummaryDetailsListView",
            1,
            "",
            "ASC",
            undefined,
            new Map([
              ["objectID", element.objectId.toString()],
              ["objectType", element.objectType],
            ]),
          );
      }),
    );
  });
}
