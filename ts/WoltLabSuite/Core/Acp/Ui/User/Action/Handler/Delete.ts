import * as Language from "../../../../../Language";
import * as UiConfirmation from "../../../../../Ui/Confirmation";
import * as Ajax from "../../../../../Ajax";
import { CallbackSuccess } from "../../../../../Ajax/Data";

/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler
 * @since       5.5
 */
export class Delete {
  private userIDs: number[];
  private successCallback: CallbackSuccess;
  private deleteMessage: string;

  public constructor(userIDs: number[], successCallback: CallbackSuccess, deleteMessage?: string) {
    this.userIDs = userIDs;
    this.successCallback = successCallback;
    if (deleteMessage) {
      this.deleteMessage = deleteMessage;
    }
    else {
      this.deleteMessage = Language.get("wcf.button.delete.confirmMessage"); // @todo find better variable for a generic message
    }
  }

  delete(): void {
    UiConfirmation.show({
      confirm: () => {
        Ajax.api(
          {
            _ajaxSetup: () => {
              return {
                data: {
                  actionName: "delete",
                  className: "wcf\\data\\user\\UserAction",
                  objectIDs: this.userIDs,
                },
              };
            },
            _ajaxSuccess: this.successCallback,
          }
        );
      },
      message: this.deleteMessage,
      messageIsHtml: true,
    });
  }
}

export default Delete;
