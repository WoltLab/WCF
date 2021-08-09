/**
 * Provides the trophy icon designer.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Content/Remove/Handler
 * @since       5.2
 */

import AcpUiWorker from "../../../Worker";
import * as Ajax from "../../../../../Ajax";
import * as Language from "../../../../../Language";
import UiDialog from "../../../../../Ui/Dialog";
import { AjaxCallbackSetup } from "../../../../../Ajax/Data";
import { DialogCallbackSetup } from "../../../../../Ui/Dialog/Data";

type CallbackSuccess = (data: AjaxResponseSuccess) => void;

interface AjaxResponseSuccess {
  loopCount: number;
  parameters: ArbitraryObject;
  proceedURL: string;
  progress: number;
  template?: string;
}

interface AjaxResponse {
  returnValues: {
    template: string;
  };
}

class AcpUserContentRemoveHandler {
  private readonly dialogId: string;
  private readonly userId: number;
  private readonly callbackSuccess?: CallbackSuccess;

  /**
   * Initializes the content remove handler.
   */
  constructor(element: HTMLElement, userId: number, callbackSuccess?: CallbackSuccess) {
    this.userId = userId;
    this.dialogId = `userRemoveContentHandler-${this.userId}`;
    this.callbackSuccess = callbackSuccess;

    element.addEventListener("click", (ev) => this.click(ev));
  }

  /**
   * Click on the remove content button.
   */
  private click(event: MouseEvent): void {
    event.preventDefault();

    Ajax.api(this);
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
      className: "\\wcf\\system\\worker\\UserContentRemoveWorker",
      parameters: {
        userID: this.userId,
        contentProvider: objectTypes,
      },
      callbackSuccess: this.callbackSuccess,
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
          userID: this.userId,
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

export = AcpUserContentRemoveHandler;
