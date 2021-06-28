/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */

import AbstractUserAction from "./AbstractUserAction";
import * as Ajax from "../../../../Ajax";
import * as Core from "../../../../Core";
import { AjaxCallbackSetup, DatabaseObjectActionResponse } from "../../../../Ajax/Data";
import * as UiNotification from "../../../../Ui/Notification";

export class ToggleConfirmEmailAction extends AbstractUserAction {
  protected init(): void {
    this.button.addEventListener("click", (event) => {
      event.preventDefault();
      const isEmailConfirmed = Core.stringToBool(this.userDataElement.dataset.emailConfirmed!);

      Ajax.api(this, {
        actionName: (isEmailConfirmed ? "un" : "") + "confirmEmail",
      });
    });
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        className: "wcf\\data\\user\\UserAction",
        objectIDs: [this.userId],
      },
    };
  }

  _ajaxSuccess(data: DatabaseObjectActionResponse): void {
    if (data.objectIDs.includes(this.userId)) {
      switch (data.actionName) {
        case "confirmEmail":
          this.userDataElement.dataset.emailConfirmed = "true";
          this.button.textContent = this.button.dataset.unconfirmEmailMessage!;
          break;

        case "unconfirmEmail":
          this.userDataElement.dataset.emailConfirmed = "false";
          this.button.textContent = this.button.dataset.confirmEmailMessage!;
          break;

        default:
          throw new Error("Unreachable");
      }
    }

    UiNotification.show();
  }
}

export default ToggleConfirmEmailAction;
