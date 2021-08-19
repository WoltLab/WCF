/**
 * Deletes a given user.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler
 * @since       5.5
 */

import * as UiConfirmation from "../../../../../Ui/Confirmation";
import * as Ajax from "../../../../../Ajax";
import { CallbackSuccess } from "../../../../../Ajax/Data";

export class Delete {
  private userIDs: number[];
  private successCallback: CallbackSuccess;
  private deleteMessage: string;

  public constructor(userIDs: number[], successCallback: CallbackSuccess, deleteMessage: string) {
    this.userIDs = userIDs;
    this.successCallback = successCallback;
    this.deleteMessage = deleteMessage;
  }

  public delete(): void {
    UiConfirmation.show({
      confirm: () => {
        Ajax.apiOnce({
          data: {
            actionName: "delete",
            className: "wcf\\data\\user\\UserAction",
            objectIDs: this.userIDs,
          },
          success: this.successCallback,
        });
      },
      message: this.deleteMessage,
      messageIsHtml: true,
    });
  }
}

export default Delete;
