/**
 * Provides the program logic for the data import function.
 *
 * @author  Marcel Werk
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/DataImport/Manager
 * @woltlabExcludeBundle all
 */
import * as Ajax from "../../../Ajax";
import * as Core from "../../../Core";
import * as Language from "../../../Language";
import { AjaxCallbackObject, AjaxCallbackSetup, ResponseData } from "../../../Ajax/Data";
import { AjaxResponse } from "../../../Controller/Clipboard/Data";
import { DialogCallbackSetup } from "../../../Ui/Dialog/Data";
import DomUtil from "../../../Dom/Util";
import UiDialog from "../../../Ui/Dialog";

export class AcpUiDataImportManager implements AjaxCallbackObject {
  private readonly queue: string[];
  private readonly redirectUrl: string;
  private currentAction = "";
  private index = -1;

  constructor(queue: string[], redirectUrl: string) {
    this.queue = queue;
    this.redirectUrl = redirectUrl;

    this.invoke();
  }

  private invoke(): void {
    this.index++;
    if (this.index >= this.queue.length) {
      Ajax.apiOnce({
        url: "index.php?cache-clear/&t=" + Core.getXsrfToken(),
        data: {
          noRedirect: 1,
        },
        silent: true,
        failure: (_data: ResponseData, _responseText: string, xhr: XMLHttpRequest) => {
          if (xhr.status === 204) {
            this.showCompletedDialog();

            return false;
          }

          return true;
        },
      });
    } else {
      this.run(Language.get("wcf.acp.dataImport.data." + this.queue[this.index]), this.queue[this.index]);
    }
  }

  private run(currentAction: string, objectType: string): void {
    this.currentAction = currentAction;
    Ajax.api(this, {
      parameters: {
        objectType,
      },
    });
  }

  private showCompletedDialog(): void {
    const content = UiDialog.getDialog(this)!.content;
    content.querySelector("h1")!.textContent = Language.get("wcf.acp.dataImport.completed");
    const spinner = content.querySelector(".fa-spinner") as HTMLSpanElement;
    spinner.classList.remove("fa-spinner");
    spinner.classList.add("fa-check", "green");

    const formSubmit = document.createElement("div");
    formSubmit.className = "formSubmit";
    formSubmit.innerHTML = `<button class="buttonPrimary">${Language.get("wcf.global.button.next")}</button>`;

    content.appendChild(formSubmit);
    UiDialog.rebuild(this);

    const button = formSubmit.children[0] as HTMLButtonElement;
    button.addEventListener("click", (event) => {
      event.preventDefault();
      window.location.href = this.redirectUrl;
    });
    button.focus();
  }

  private updateProgress(title: string, progress: number): void {
    const content = UiDialog.getDialog(this)!.content;
    const progressElement = content.querySelector("progress")!;

    content.querySelector("h1")!.textContent = title;
    progressElement.value = progress;
    progressElement.nextElementSibling!.textContent = `${progress}%`;
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        className: "wcf\\system\\worker\\ImportWorker",
      },
      silent: true,
      url: "index.php?worker-proxy/&t=" + Core.getXsrfToken(),
    };
  }

  _ajaxSuccess(data: AjaxResponse): void {
    if (typeof data.template === "string") {
      UiDialog.open(this, data.template);
    }

    this.updateProgress(this.currentAction, data.progress);

    if (data.progress < 100) {
      Ajax.api(this, {
        loopCount: data.loopCount,
        parameters: data.parameters,
      });
    } else {
      this.invoke();
    }
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: DomUtil.getUniqueId(),
      options: {
        closable: false,
        title: Language.get("wcf.acp.dataImport"),
      },
      source: null,
    };
  }
}

export default AcpUiDataImportManager;
