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
import { ResponseData } from "../../../../Ajax/Data";
import { Poll, PollViews } from "../Poll";

export class Vote {
  protected readonly pollManager: Poll;
  protected button: HTMLButtonElement;

  public constructor(manager: Poll) {
    this.pollManager = manager;

    const button = this.pollManager.getElement().querySelector<HTMLButtonElement>(".showVoteFormButton");

    if (!button) {
      throw new Error(
        `Could not find button with selector ".showVoteFormButton" for poll "${this.pollManager.pollID}"`,
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

  protected async loadView(): Promise<void> {
    const request = Ajax.dboAction("getVoteTemplate", "wcf\\data\\poll\\PollAction");
    request.objectIds([this.pollManager.pollID]);
    const results = (await request.dispatch()) as ResponseData;

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
