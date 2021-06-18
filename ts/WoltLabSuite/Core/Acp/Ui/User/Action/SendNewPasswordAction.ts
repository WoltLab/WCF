import AbstractUserAction from "./AbstractUserAction";
import SendNewPassword from "./Handler/SendNewPassword";

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

      const sendNewPasswordHandler = new SendNewPassword([this.userId], () => {
        location.reload();
      });
      sendNewPasswordHandler.send();
    });
  }
}

export default SendNewPasswordAction;
