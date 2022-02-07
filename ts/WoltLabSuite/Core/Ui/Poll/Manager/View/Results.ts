/**
 * Implementation for poll result views.
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

export class Results extends Abstract {
  protected getButtonSelector(): string {
    return ".showResultsButton";
  }

  protected getActionName(): string {
    return "getResult";
  }

  protected success(data: ResponseData): void {
    this.pollManager.changeView(PollViews.results, data.template);
  }

  public checkVisibility(view: PollViews): void {
    if (view === PollViews.results) {
      this.hideButton();
    } else {
      this.showButton();
    }
  }
}

export default Results;
