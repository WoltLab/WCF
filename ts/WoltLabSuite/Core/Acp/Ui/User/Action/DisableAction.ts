import * as Ajax from "../../../../Ajax";
import * as Core from "../../../../Core";
import { AjaxCallbackObject, DatabaseObjectActionResponse } from "../../../../Ajax/Data";
import * as UiNotification from "../../../../Ui/Notification";
import AbstractUserAction from "./AbstractUserAction";
import * as EventHandler from "../../../../Event/Handler";

/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */
export class DisableAction extends AbstractUserAction {
  protected init() {
    this.button.addEventListener("click", (event) => {
      event.preventDefault();

      Ajax.api(
        {
          _ajaxSetup: () => {
            const isEnabled = Core.stringToBool(this.userData.dataset.enabled!);

            return {
              data: {
                actionName: (isEnabled ? "disable" : "enable"),
                className: "wcf\\data\\user\\UserAction",
                objectIDs: [this.userId],
              },
            };
          },

          _ajaxSuccess: (data: DatabaseObjectActionResponse) => {
            if (data.objectIDs.includes(this.userId)) {
              switch (data.actionName) {
                case "enable":
                  this.userData.dataset.enabled = "true";
                  this.button.textContent = this.button.dataset.disableMessage!;
                  break;

                case "disable":
                  this.userData.dataset.enabled = "false";
                  this.button.textContent = this.button.dataset.enableMessage!;
                  break;

                default:
                  throw new Error("Unreachable");
              }
            }

            UiNotification.show();

            EventHandler.fire("com.woltlab.wcf.acp.user", "refresh", {
              userIds: [this.userId]
            });
          },
        }
      );
    });
  }
}

export default DisableAction;
