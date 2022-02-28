/**
 * Handles the poll voting.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Vote
 * @since   5.5
 */

import { PollViews, Poll } from "./Poll";
import * as Ajax from "../../Ajax";

type VoteResponseData = {
  changeableVote: number;
  totalVotes: number;
  totalVotesTooltip: string;
  template: string;
};

export class Vote {
  private readonly pollManager: Poll;
  private readonly button: HTMLButtonElement;
  private inputs: HTMLInputElement[];

  public constructor(manager: Poll) {
    this.pollManager = manager;

    const button = this.pollManager.getElement().querySelector<HTMLButtonElement>(".votePollButton");
    if (!button) {
      throw new Error(`Could not find vote button for poll "${this.pollManager.pollId}".`);
    }
    this.button = button;
    this.button.addEventListener("click", () => this.submit());

    this.initSelects();
  }

  public initSelects(): void {
    if (this.pollManager.hasView(PollViews.vote)) {
      const container = this.pollManager.getView(PollViews.vote);

      this.inputs = Array.from(container.querySelectorAll<HTMLInputElement>("input"));

      this.inputs.forEach((input) => {
        input.addEventListener("change", () => this.checkInputs());
      });

      this.checkInputs();
    }
  }

  private checkInputs(): void {
    let selectedInputCount = 0;
    this.inputs.forEach((input) => {
      if (input.checked) {
        selectedInputCount++;
      }

      if (this.pollManager.maxVotes > 1) {
        input.disabled = false;
      }
    });

    if (selectedInputCount === 0) {
      this.button.disabled = true;
    } else {
      if (selectedInputCount >= this.pollManager.maxVotes && this.pollManager.maxVotes > 1) {
        this.inputs.forEach((input) => {
          if (!input.checked) {
            input.disabled = true;
          }
        });
      }

      this.button.disabled = false;
    }
  }

  private getSelectedOptions(): number[] {
    return this.inputs.filter((input) => input.checked).map((input) => parseInt(input.value, 10));
  }

  private async submit(): Promise<void> {
    this.button.disabled = true;

    const optionIDs = this.getSelectedOptions();

    const request = Ajax.dboAction("vote", "wcf\\data\\poll\\PollAction");
    request.objectIds([this.pollManager.pollId]);
    request.payload({
      optionIDs,
    });
    const results = (await request.dispatch()) as VoteResponseData;

    this.pollManager.canVote = !!results.changeableVote;
    this.pollManager.canViewResults = true;
    this.pollManager.addView(PollViews.results, results.template);
    this.pollManager.displayView(PollViews.results);
    this.pollManager.changeTotalVotes(results.totalVotes, results.totalVotesTooltip);

    this.button.disabled = false;
  }

  public checkVisibility(view: PollViews): void {
    this.button.hidden = view !== PollViews.vote;
  }
}

export default Vote;
