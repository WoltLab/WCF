/**
 * Handles the user content remove clipboard action.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Content/Remove/Clipboard
 * @since       5.4
 */

import AcpUiWorker from "../../../Worker";
import * as Ajax from "../../../../../Ajax";
import * as Language from "../../../../../Language";
import UiDialog from "../../../../../Ui/Dialog";
import { AjaxCallbackSetup } from "../../../../../Ajax/Data";
import { DialogCallbackSetup } from "../../../../../Ui/Dialog/Data";
import * as EventHandler from "../../../../../Event/Handler";

interface AjaxResponse {
  returnValues: {
    template: string;
  };
}

interface EventData {
  data: {
    actionName: string;
    internalData: any[];
    label: string;
    parameters: {
      objectIDs: number[];
      url: string;
    };
  };
  listItem: HTMLElement;
}

export class AcpUserContentRemoveClipboard {
  public userIds: number[];
  private readonly dialogId = "userContentRemoveClipboardPrepareDialog";

  /**
   * Initializes the content remove handler.
   */
  constructor() {
    EventHandler.add("com.woltlab.wcf.clipboard", "com.woltlab.wcf.user", (data: EventData) => {
      if (data.data.actionName === "com.woltlab.wcf.user.deleteUserContent") {
        this.userIds = data.data.parameters.objectIDs;

        Ajax.api(this);
      }
    });
  }

  /**
   * Executes the remove content worker.
   */
  private executeWorker(objectTypes: string[]): void {
    new AcpUiWorker({
      // dialog
      dialogId: "removeContentWorker",
      dialogTitle: Language.get("wcf.acp.content.removeContent"),

      // ajax
      className: "wcf\\system\\worker\\UserContentRemoveWorker",
      parameters: {
        userIDs: this.userIds,
        contentProvider: objectTypes,
      },
    });
  }

  /**
   * Handles a click on the submit button in the overlay.
   */
  private submit(): void {
    const objectTypes = Array.from<HTMLInputElement>(
      this.dialogContent.querySelectorAll("input.contentProviderObjectType"),
    )
      .filter((element) => element.checked)
      .map((element) => element.name);

    UiDialog.close(this.dialogId);

    if (objectTypes.length > 0) {
      window.setTimeout(() => {
        this.executeWorker(objectTypes);
      }, 200);
    }
  }

  get dialogContent(): HTMLElement {
    return UiDialog.getDialog(this.dialogId)!.content;
  }

  _ajaxSuccess(data: AjaxResponse): void {
    UiDialog.open(this, data.returnValues.template);

    const submitButton = this.dialogContent.querySelector('input[type="submit"]') as HTMLElement;
    submitButton.addEventListener("click", () => this.submit());
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "prepareRemoveContent",
        className: "wcf\\data\\user\\UserAction",
        parameters: {
          userIDs: this.userIds,
        },
      },
    };
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: this.dialogId,
      options: {
        title: Language.get("wcf.acp.content.removeContent"),
      },
      source: null,
    };
  }
}

export default AcpUserContentRemoveClipboard;
