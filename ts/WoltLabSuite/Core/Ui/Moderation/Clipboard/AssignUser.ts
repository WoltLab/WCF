/**
 * Handles the dialog to select the user when assigning a user to multiple moderation queue entries
 * via clipboard.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Moderation/Clipboard/AssignUser
 */

import * as EventHandler from "../../../Event/Handler";
import { AjaxResponse, ClipboardActionData } from "../../../Controller/Clipboard/Data";
import * as UiNotification from "../../Notification";
import User from "../../../User";
import * as StringUtil from "../../../StringUtil";
import * as Language from "../../../Language";
import UiUserSearchInput from "../../User/Search/Input";
import * as DomTraverse from "../../../Dom/Traverse";
import * as Ajax from "../../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup, ResponseData } from "../../../Ajax/Data";
import DomUtil from "../../../Dom/Util";
import { DialogCallbackObject, DialogCallbackSetup } from "../../Dialog/Data";
import UiDialog from "../../Dialog";

interface EventData {
  data: ClipboardActionData;
  listItem: HTMLLIElement;
  responseData: AjaxResponse;
}

class UiModerationClipboardAssignUser implements AjaxCallbackObject, DialogCallbackObject {
  /**
   * ids of the moderation queue entries currently being handled
   */
  protected queueIds: number[] = [];

  public _ajaxFailure(data: ResponseData): boolean {
    if (data.returnValues?.fieldName === "assignedUsername") {
      let errorMessage = "";

      const dialog = UiDialog.getDialog(this)!.content;
      const assignedUsername = dialog.querySelector("input[name=assignedUsername]") as HTMLInputElement;

      const errorType: string = data.returnValues.errorType;
      switch (errorType) {
        case "empty":
          errorMessage = Language.get("wcf.global.form.error.empty");
          break;

        case "notAffected":
          errorMessage = Language.get("wcf.moderation.assignedUser.error.notAffected");
          break;

        default:
          errorMessage = Language.get(`wcf.user.username.error.${errorType}`, {
            username: assignedUsername.value,
          });
          break;
      }

      DomUtil.innerError(assignedUsername, errorMessage);

      return false;
    }

    return true;
  }

  public _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "assignUserByClipboard",
        className: "wcf\\data\\moderation\\queue\\ModerationQueueAction",
      },
    };
  }

  public _ajaxSuccess(): void {
    UiDialog.close(this);

    UiNotification.show(undefined, () => window.location.reload());
  }

  public _dialogSetup(): ReturnType<DialogCallbackSetup> {
    const submitCallback = () => this.submitDialog();

    return {
      id: "moderationQueueClipboardAssignUser",
      options: {
        onSetup(content: HTMLElement): void {
          const username = content.querySelector("input[name=assignedUsername]") as HTMLInputElement;
          new UiUserSearchInput(username, {});

          username.addEventListener("click", (event) => {
            const assignedUserId = DomTraverse.prevBySel(
              event.currentTarget as HTMLElement,
              "input[name=assignedUserID]",
            ) as HTMLInputElement;
            assignedUserId.click();
          });

          content.querySelector("button[data-type=submit]")!.addEventListener("click", submitCallback);
        },
        onShow(content: HTMLElement): void {
          // Reset dialog to initial state.
          const assignedUsername = content.querySelector("input[name=assignedUsername]") as HTMLInputElement;
          (content.querySelector("input[name=assignedUserID]:checked") as HTMLInputElement).checked = false;
          (content.querySelector("input[name=assignedUserID]") as HTMLInputElement).checked = true;

          assignedUsername.value = "";
          DomUtil.innerError(assignedUsername, "");
        },
        title: Language.get("wcf.moderation.assignedUser.change"),
      },
      source: `
<div class="section">
  <dl>
    <dt>${Language.get("wcf.moderation.assignedUser")}</dt>
    <dd>
      <ul>
        <li>
          <label>
            <input type="radio" name="assignedUserID" value="${User.userId}" checked>
            ${StringUtil.escapeHTML(User.username)}
          </label>
        </li>
        <li>
          <label>
            <input type="radio" name="assignedUserID" value="0">
            ${Language.get("wcf.moderation.assignedUser.nobody")}
          </label>
        </li>
        <li>
          <input type="radio" name="assignedUserID" value="-1">
          <input type="text" name="assignedUsername" value="">
        </li>
      </ul>
    </dd>
  </dl>
</div>
<div class="formSubmit">
  <button class="buttonPrimary" data-type="submit">${Language.get("wcf.global.button.save")}</button>
</div>`,
    };
  }

  public showDialog(queueIds: number[]): void {
    this.queueIds = queueIds;

    UiDialog.open(this);
  }

  public submitDialog(): void {
    const dialog = UiDialog.getDialog(this)!.content;
    const assignedUserId = dialog.querySelector("input[name=assignedUserID]:checked") as HTMLInputElement;
    const assignedUsername = dialog.querySelector("input[name=assignedUsername]") as HTMLInputElement;

    Ajax.api(this, {
      objectIDs: this.queueIds,
      parameters: {
        assignedUserID: assignedUserId.value,
        assignedUsername: assignedUsername.value,
      },
    });
  }
}

let isSetUp = false;

export function setup(): void {
  if (isSetUp) {
    return;
  }

  const handler = new UiModerationClipboardAssignUser();

  EventHandler.add("com.woltlab.wcf.clipboard", "com.woltlab.wcf.moderation.queue", (data: EventData) => {
    if (
      data.data.actionName === "com.woltlab.wcf.moderation.queue.assignUserByClipboard" &&
      data.responseData === null
    ) {
      handler.showDialog(data.data.parameters.objectIDs);
    }
  });

  isSetUp = true;
}
