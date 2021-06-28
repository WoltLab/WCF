/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */

import * as Core from "../../../../Core";
import AbstractUserAction from "./AbstractUserAction";
import BanHandler from "./Handler/Ban";
import * as UiNotification from "../../../../Ui/Notification";
import * as EventHandler from "../../../../Event/Handler";

export class BanAction extends AbstractUserAction {
  private banHandler: BanHandler;

  protected init(): void {
    this.banHandler = new BanHandler([this.userId]);

    this.button.addEventListener("click", (event) => {
      event.preventDefault();

      const isBanned = Core.stringToBool(this.userDataElement.dataset.banned!);

      if (isBanned) {
        this.banHandler.unban(() => {
          this.userDataElement.dataset.banned = "false";
          this.button.textContent = this.button.dataset.banMessage!;

          UiNotification.show();

          EventHandler.fire("com.woltlab.wcf.acp.user", "refresh", {
            userIds: [this.userId],
          });
        });
      } else {
        this.banHandler.ban(() => {
          this.userDataElement.dataset.banned = "true";
          this.button.textContent = this.button.dataset.unbanMessage!;

          UiNotification.show();

          EventHandler.fire("com.woltlab.wcf.acp.user", "refresh", {
            userIds: [this.userId],
          });
        });
      }
    });
  }
}

export default BanAction;
