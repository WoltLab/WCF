/**
 * Implementation for poll result views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/View/Results
 * @since   5.5
 */

import * as Ajax from "../../../../Ajax";
import { Poll, PollViews } from "../Poll";

type ResultResponseData = {
  template: string;
};

export class Results {
  private readonly pollManager: Poll;
  private readonly button: HTMLButtonElement;

  public constructor(manager: Poll) {
    this.pollManager = manager;

    const button = this.pollManager.getElement().querySelector<HTMLButtonElement>(".showResultsButton");

    if (!button) {
      throw new Error(`Could not find button with selector ".showResultsButton" for poll "${this.pollManager.pollId}"`);
    }

    this.button = button;

    this.button.addEventListener("click", async (event) => {
      if (event) {
        event.preventDefault();
      }

      this.button.disabled = true;

      if (this.pollManager.hasView(PollViews.results)) {
        this.pollManager.displayView(PollViews.results);
      } else {
        await this.loadView();
      }

      this.button.disabled = false;
    });
  }

  private async loadView(): Promise<void> {
    const request = Ajax.dboAction("getResultTemplate", "wcf\\data\\poll\\PollAction");
    request.objectIds([this.pollManager.pollId]);
    const results = (await request.dispatch()) as ResultResponseData;

    this.pollManager.addView(PollViews.results, results.template);
    this.pollManager.displayView(PollViews.results);
  }

  public checkVisibility(view: PollViews): void {
    if (view === PollViews.results) {
      this.button.hidden = true;
    } else {
      this.button.hidden = false;
    }
  }
}

export default Results;
