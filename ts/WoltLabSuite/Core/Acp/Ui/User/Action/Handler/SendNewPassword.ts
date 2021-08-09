/**
 * Handles a send new password action.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler
 * @since       5.5
 */

import * as Language from "../../../../../Language";
import * as UiConfirmation from "../../../../../Ui/Confirmation";
import AcpUiWorker from "../../../Worker";

interface AjaxResponse {
  loopCount: number;
  parameters: ArbitraryObject;
  proceedURL: string;
  progress: number;
  template?: string;
}

type CallbackSuccess = (data: AjaxResponse) => void;

export class SendNewPassword {
  private userIDs: number[];
  private successCallback: CallbackSuccess | null;

  public constructor(userIDs: number[], successCallback: CallbackSuccess | null) {
    this.userIDs = userIDs;
    this.successCallback = successCallback;
  }

  send(): void {
    UiConfirmation.show({
      confirm: () => {
        new AcpUiWorker({
          dialogId: "sendingNewPasswords",
          dialogTitle: Language.get("wcf.acp.user.sendNewPassword.workerTitle"),
          className: "wcf\\system\\worker\\SendNewPasswordWorker",
          parameters: {
            userIDs: this.userIDs,
          },
          callbackSuccess: this.successCallback,
        });
      },
      message: Language.get("wcf.acp.user.action.sendNewPassword.confirmMessage"),
    });
  }
}

export default SendNewPassword;
