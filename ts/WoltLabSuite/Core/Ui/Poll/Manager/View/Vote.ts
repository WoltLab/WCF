/**
 * Implementation for poll vote views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/View/Results
 * @since   5.5
 */

import { ResponseData } from "../../../../Ajax/Data";
import { PollViews } from "../Manager";
import Abstract from "./Abstract";

export class Vote extends Abstract {
  protected getButtonSelector(): string {
    return ".showVoteFormButton";
  }
  protected getActionName(): string {
    return "getVote";
  }

  protected success(data: ResponseData): void {
    this.pollManager.changeView(PollViews.vote, data.template);
  }

  public checkVisibility(view: PollViews): void {
    if (view === PollViews.vote || !this.pollManager.canVote) {
      this.hideButton();
    } else {
      this.showButton();
    }
  }
}

export default Vote;
