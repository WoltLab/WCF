/**
 * Handles a user disable/enable button.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */

import * as Ajax from "../../../../Ajax";
import * as Core from "../../../../Core";
import { AjaxCallbackObject, AjaxCallbackSetup, DatabaseObjectActionResponse } from "../../../../Ajax/Data";
import * as UiNotification from "../../../../Ui/Notification";
import AbstractUserAction from "./Abstract";
import * as EventHandler from "../../../../Event/Handler";

export class DisableAction extends AbstractUserAction implements AjaxCallbackObject {
  public constructor(button: HTMLElement, userId: number, userDataElement: HTMLElement) {
    super(button, userId, userDataElement);

    this.button.addEventListener("click", (event) => {
      event.preventDefault();
      const isEnabled = Core.stringToBool(this.userDataElement.dataset.enabled!);

      Ajax.api(this, {
        actionName: isEnabled ? "disable" : "enable",
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
    data.objectIDs.forEach((objectId) => {
      if (~~objectId == this.userId) {
        switch (data.actionName) {
          case "enable":
            this.userDataElement.dataset.enabled = "true";
            this.button.textContent = this.button.dataset.disableMessage!;
            break;

          case "disable":
            this.userDataElement.dataset.enabled = "false";
            this.button.textContent = this.button.dataset.enableMessage!;
            break;

          default:
            throw new Error("Unreachable");
        }
      }
    });

    UiNotification.show();

    EventHandler.fire("com.woltlab.wcf.acp.user", "refresh", {
      userIds: [this.userId],
    });
  }
}

export default DisableAction;
