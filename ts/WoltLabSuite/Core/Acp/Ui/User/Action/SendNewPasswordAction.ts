import * as EventHandler from "../../../../Event/Handler";
import * as Language from "../../../../Language";
import AbstractUserAction from "./AbstractUserAction";

/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */
export class SendNewPasswordAction extends AbstractUserAction {
  protected init() {
    this.button.addEventListener("click", (event) => {
      event.preventDefault();

      // emulate clipboard selection
      EventHandler.fire("com.woltlab.wcf.clipboard", "com.woltlab.wcf.user", {
        data: {
          actionName: "com.woltlab.wcf.user.sendNewPassword",
          parameters: {
            confirmMessage: Language.get("wcf.acp.user.action.sendNewPassword.confirmMessage"),
            objectIDs: [this.userId],
          },
        },
        responseData: {
          actionName: "com.woltlab.wcf.user.sendNewPassword",
          objectIDs: [this.userId],
        },
      });
    });
  }
}

export default SendNewPasswordAction;
