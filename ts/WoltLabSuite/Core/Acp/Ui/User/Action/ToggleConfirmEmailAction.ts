import AbstractUserAction from "./AbstractUserAction";
import * as Ajax from "../../../../Ajax";
import * as Core from "../../../../Core";
import { AjaxCallbackObject, DatabaseObjectActionResponse } from "../../../../Ajax/Data";
import * as UiNotification from "../../../../Ui/Notification";

/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */
export class ToggleConfirmEmailAction extends AbstractUserAction {
  protected init() {
    this.button.addEventListener("click", (event) => {
      event.preventDefault();

      Ajax.api(
        {
          _ajaxSetup: () => {
            const isEmailConfirmed = Core.stringToBool(this.userData.dataset.emailConfirmed!);

            return {
              data: {
                actionName: (isEmailConfirmed ? "un" : "") + "confirmEmail",
                className: "wcf\\data\\user\\UserAction",
                objectIDs: [this.userId],
              },
            };
          },

          _ajaxSuccess: (data: DatabaseObjectActionResponse) => {
            if (data.objectIDs.includes(this.userId)) {
              switch (data.actionName) {
                case "confirmEmail":
                  this.userData.dataset.emailConfirmed = "true";
                  this.button.textContent = this.button.dataset.unconfirmEmailMessage!;
                  break;

                case "unconfirmEmail":
                  this.userData.dataset.emailConfirmed = "false";
                  this.button.textContent = this.button.dataset.confirmEmailMessage!;
                  break;

                default:
                  throw new Error("Unreachable");
              }
            }

            UiNotification.show();
          },
        }
      );
    });
  }
}

export default ToggleConfirmEmailAction;
