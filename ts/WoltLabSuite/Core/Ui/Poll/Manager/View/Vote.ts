/**
 * Implementation for poll vote views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/View/Results
 * @since   5.5
 */

import * as Ajax from "../../../../Ajax";
import { Poll, PollViews } from "../Poll";

type VoteResponseData = {
  template: string;
};

export class Vote {
  private readonly pollManager: Poll;
  private readonly button: HTMLButtonElement;

  public constructor(manager: Poll) {
    this.pollManager = manager;

    const button = this.pollManager.getElement().querySelector<HTMLButtonElement>(".showVoteFormButton");

    if (!button) {
      throw new Error(
        `Could not find button with selector ".showVoteFormButton" for poll "${this.pollManager.pollId}"`,
      );
    }

    this.button = button;

    this.button.addEventListener("click", async (event) => {
      if (event) {
        event.preventDefault();
      }

      this.button.disabled = true;

      if (this.pollManager.hasView(PollViews.vote)) {
        this.pollManager.displayView(PollViews.vote);
      } else {
        await this.loadView();
      }

      this.button.disabled = false;
    });
  }

  private async loadView(): Promise<void> {
    const request = Ajax.dboAction("getVoteTemplate", "wcf\\data\\poll\\PollAction");
    request.objectIds([this.pollManager.pollId]);
    const results = (await request.dispatch()) as VoteResponseData;

    this.pollManager.addView(PollViews.vote, results.template);
    this.pollManager.displayView(PollViews.vote);
  }

  public checkVisibility(view: PollViews): void {
    if (view === PollViews.vote || !this.pollManager.canVote) {
      this.button.hidden = true;
    } else {
      this.button.hidden = false;
    }
  }
}

export default Vote;
