/**
 * Handles the reaction summary details dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { dboAction } from "../../Ajax";
import { dialogFactory } from "../../Component/Dialog";
import { wheneverFirstSeen } from "../../Helper/Selector";

type ResponseGetReactionDetails = {
  template: string;
  title: string;
};

async function showDetails(objectID: number, objectType: string): Promise<void> {
  const response = (await dboAction("getReactionDetails", "wcf\\data\\reaction\\ReactionAction")
    .payload({
      data: {
        objectID,
        objectType,
      },
    })
    .dispatch()) as ResponseGetReactionDetails;

  const dialog = dialogFactory().fromHtml(response.template).withoutControls();
  dialog.show(response.title);
}

export function setup(): void {
  wheneverFirstSeen("woltlab-core-reaction-summary", (element: WoltlabCoreReactionSummaryElement) => {
    element.addEventListener("showDetails", () => {
      void showDetails(element.objectId, element.objectType);
    });
  });
}
