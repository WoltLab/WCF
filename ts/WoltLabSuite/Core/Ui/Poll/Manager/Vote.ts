/**
 * Handles the poll voting.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/Vote
 * @since   5.5
 */

import AjaxRequest from "../../../Ajax/Request";
import { PollViews, Poll } from "./Poll";
import { ResponseData } from "../../../Ajax/Data";
import * as Core from "../../../Core";

export class Vote {
  private readonly pollManager: Poll;
  private button: HTMLButtonElement;
  private inputs: HTMLInputElement[];

  public constructor(manager: Poll) {
    this.pollManager = manager;

    this.initButton();
    this.initSelects();
  }

  private initButton(): void {
    const button = this.pollManager.getElement().querySelector<HTMLButtonElement>(".votePollButton");

    if (!button) {
      throw new Error(`Could not find vote button for poll "${this.pollManager.pollID}".`);
    }

    this.button = button;

    this.button.addEventListener("click", () => this.submit());
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

  private submit(): void {
    this.button.disabled = true;

    const optionIDs = this.getSelectedOptions();
    const request = new AjaxRequest({
      url: `index.php?poll/&t=${Core.getXsrfToken()}`,
      data: Core.extend({
        actionName: "vote",
        pollID: this.pollManager.pollID,
        optionIDs,
      }),
      success: (data: ResponseData) => {
        this.button.disabled = false;

        this.pollManager.canVote = data.changeableVote ? true : false;
        this.pollManager.canViewResults = true;

        this.pollManager.addView(PollViews.results, data.template);
        this.pollManager.displayView(PollViews.results);
        this.pollManager.changeTotalVotes(data.totalVotes, data.totalVotesTooltip);
      },
    });
    request.sendRequest();
  }

  public checkVisibility(view: PollViews): void {
    this.button.hidden = view !== PollViews.vote;
  }
}

export default Vote;
