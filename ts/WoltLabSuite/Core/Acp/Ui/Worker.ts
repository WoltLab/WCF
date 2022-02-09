/**
 * Worker manager with support for custom callbacks and loop counts.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Worker
 */

import * as Ajax from "../../Ajax";
import * as Core from "../../Core";
import * as Language from "../../Language";
import UiDialog from "../../Ui/Dialog";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../../Ajax/Data";
import { DialogCallbackObject, DialogCallbackSetup } from "../../Ui/Dialog/Data";
import AjaxRequest from "../../Ajax/Request";

interface AjaxResponse {
  loopCount: number;
  parameters: ArbitraryObject;
  proceedURL: string;
  progress: number;
  template?: string;
}

type CallbackAbort = () => void;
type CallbackSuccess = (data: AjaxResponse) => void;

interface WorkerOptions {
  // dialog
  dialogId: string;
  dialogTitle: string;

  // ajax
  className: string;
  loopCount: number;
  parameters: ArbitraryObject;
  implicitContinue: boolean;

  // callbacks
  callbackAbort: CallbackAbort | null;
  callbackSuccess: CallbackSuccess | null;
}

class AcpUiWorker implements AjaxCallbackObject, DialogCallbackObject {
  private aborted = false;
  private readonly options: WorkerOptions;
  private readonly request: AjaxRequest;

  /**
   * Creates a new worker instance.
   */
  constructor(options: Partial<WorkerOptions>) {
    this.options = Core.extend(
      {
        // dialog
        dialogId: "",
        dialogTitle: "",

        // ajax
        className: "",
        loopCount: -1,
        parameters: {},
        implicitContinue: false,

        // callbacks
        callbackAbort: null,
        callbackSuccess: null,
      },
      options,
    ) as WorkerOptions;
    this.options.dialogId += "Worker";

    // update title
    if (UiDialog.getDialog(this.options.dialogId) !== undefined) {
      UiDialog.setTitle(this.options.dialogId, this.options.dialogTitle);
    }

    this.request = Ajax.api(this);
  }

  _ajaxSuccess(data: AjaxResponse): void {
    if (this.aborted) {
      return;
    }

    if (typeof data.template === "string") {
      UiDialog.open(this, data.template);
    }

    const content = UiDialog.getDialog(this)!.content;

    // update progress
    const progress = content.querySelector("progress")!;
    progress.value = data.progress;
    progress.nextElementSibling!.textContent = `${data.progress}%`;

    // worker is still busy
    if (data.progress < 100) {
      Ajax.api(this, {
        loopCount: data.loopCount,
        parameters: data.parameters,
      });
    } else {
      UiDialog.removeCallback(this, "onClose");

      const spinner = content.querySelector(".fa-spinner") as HTMLSpanElement;
      spinner.classList.remove("fa-spinner");
      spinner.classList.add("fa-check", "green");

      const formSubmit = document.createElement("div");
      formSubmit.className = "formSubmit";
      formSubmit.innerHTML = '<button class="buttonPrimary">' + Language.get("wcf.global.button.next") + "</button>";

      content.appendChild(formSubmit);
      UiDialog.rebuild(this);

      const button = formSubmit.children[0] as HTMLButtonElement;
      if (this.options.implicitContinue) {
        button.disabled = true;

        window.setTimeout(() => {
          this.finalizeWorker(data);
        }, 500);
      } else {
        button.addEventListener("click", (event) => {
          event.preventDefault();

          this.finalizeWorker(data);
        });
        button.focus();
      }
    }
  }

  private finalizeWorker(data: AjaxResponse): void {
    if (typeof this.options.callbackSuccess === "function") {
      this.options.callbackSuccess(data);

      UiDialog.close(this);
    } else {
      window.location.href = data.proceedURL;
    }
  }

  _ajaxFailure(): boolean {
    const dialog = UiDialog.getDialog(this);
    if (dialog !== undefined) {
      const spinner = dialog.content.querySelector(".fa-spinner") as HTMLSpanElement;
      spinner.classList.remove("fa-spinner");
      spinner.classList.add("fa-times", "red");
    }

    return true;
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        className: this.options.className,
        loopCount: this.options.loopCount,
        parameters: this.options.parameters,
      },
      silent: true,
      url: "index.php?worker-proxy/&t=" + Core.getXsrfToken(),
    };
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: this.options.dialogId,
      options: {
        backdropCloseOnClick: false,
        onClose: () => {
          this.aborted = true;
          this.request.abortPrevious();

          if (typeof this.options.callbackAbort === "function") {
            this.options.callbackAbort();
          } else {
            window.location.reload();
          }
        },
        title: this.options.dialogTitle,
      },
      source: null,
    };
  }
}

Core.enableLegacyInheritance(AcpUiWorker);

export = AcpUiWorker;
