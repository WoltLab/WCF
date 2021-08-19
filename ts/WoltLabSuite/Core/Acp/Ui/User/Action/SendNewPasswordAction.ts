/**
 * Handles a send new password button.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */

import AbstractUserAction from "./Abstract";
import SendNewPassword from "./Handler/SendNewPassword";

export class SendNewPasswordAction extends AbstractUserAction {
  public constructor(button: HTMLElement, userId: number, userDataElement: HTMLElement) {
    super(button, userId, userDataElement);

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
