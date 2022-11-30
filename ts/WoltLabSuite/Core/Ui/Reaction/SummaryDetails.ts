/**
 * Handles the reaction summary details dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Reaction/SummaryDetails
 * @since 6.0
 */

import { dboAction } from "../../Ajax";
import { dialogFactory } from "../../Component/Dialog";

type ResponseGetReactionDetails = {
  template: string;
  title: string;
};

export class SummaryDetails {
  readonly #objectType: string;
  readonly #objectId: number;

  constructor(objectType: string, objectId: number) {
    this.#objectType = objectType;
    this.#objectId = objectId;

    const component = document.querySelector(
      `woltlab-core-reaction-summary[object-type="${this.#objectType}"][object-id="${this.#objectId}"]`,
    ) as WoltlabCoreReactionSummaryElement;
    component?.addEventListener("showDetails", () => {
      void this.#loadDetails();
    });
  }

  async #loadDetails(): Promise<void> {
    const response = (await dboAction("getReactionDetails", "wcf\\data\\reaction\\ReactionAction")
      .payload({
        data: {
          objectID: this.#objectId,
          objectType: this.#objectType,
        },
      })
      .dispatch()) as ResponseGetReactionDetails;

    const dialog = dialogFactory().fromHtml(response.template).withoutControls();
    dialog.show(response.title);
  }
}

export default SummaryDetails;
