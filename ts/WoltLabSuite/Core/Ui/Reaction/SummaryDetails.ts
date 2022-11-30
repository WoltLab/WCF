/**
 * Handles the reaction summary details dialog.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Reaction/SummaryDetails
 * @since       6.0
 */

import { dboAction } from "../../Ajax";
import { DialogCallbackSetup } from "../Dialog/Data";
import UiDialog from "../Dialog";

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

    UiDialog.open(this, response.template);
    UiDialog.setTitle(`userReactionOverlay-${this.#objectType}`, response.title);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: `userReactionOverlay-${this.#objectType}`,
      options: {
        title: "",
      },
      source: null,
    };
  }
}
