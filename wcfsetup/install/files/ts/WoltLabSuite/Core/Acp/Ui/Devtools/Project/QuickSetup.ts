/**
 * Handles quick setup of all projects within a path.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Devtools/Project/QuickSetup
 */

import * as Ajax from "../../../../Ajax";
import DomUtil from "../../../../Dom/Util";
import * as Language from "../../../../Language";
import UiDialog from "../../../../Ui/Dialog";
import * as UiNotification from "../../../../Ui/Notification";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../../../../Ajax/Data";
import { DialogCallbackObject, DialogCallbackSetup } from "../../../../Ui/Dialog/Data";

interface AjaxResponse {
  returnValues: {
    errorMessage?: string;
    successMessage: string;
  };
}

class AcpUiDevtoolsProjectQuickSetup implements AjaxCallbackObject, DialogCallbackObject {
  private readonly pathInput: HTMLInputElement;
  private readonly submitButton: HTMLButtonElement;

  /**
   * Initializes the project quick setup handler.
   */
  constructor() {
    document.querySelectorAll(".jsDevtoolsProjectQuickSetupButton").forEach((button: HTMLAnchorElement) => {
      button.addEventListener("click", (ev) => this.showDialog(ev));
    });

    this.submitButton = document.getElementById("projectQuickSetupSubmit") as HTMLButtonElement;
    this.submitButton.addEventListener("click", (ev) => this.submit(ev));

    this.pathInput = document.getElementById("projectQuickSetupPath") as HTMLInputElement;
    this.pathInput.addEventListener("keypress", (ev) => this.keyPress(ev));
  }

  /**
   * Returns the data used to setup the AJAX request object.
   */
  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "quickSetup",
        className: "wcf\\data\\devtools\\project\\DevtoolsProjectAction",
      },
    };
  }

  /**
   * Handles successful AJAX request.
   */
  _ajaxSuccess(data: AjaxResponse): void {
    if (data.returnValues.errorMessage) {
      DomUtil.innerError(this.pathInput, data.returnValues.errorMessage);

      this.submitButton.disabled = false;

      return;
    }

    UiDialog.close(this);

    UiNotification.show(data.returnValues.successMessage, () => {
      window.location.reload();
    });
  }

  /**
   * Returns the data used to setup the dialog.
   */
  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "projectQuickSetup",
      options: {
        onShow: () => this.onDialogShow(),
        title: Language.get("wcf.acp.devtools.project.quickSetup"),
      },
    };
  }

  /**
   * Handles the `[ENTER]` key to submit the form.
   */
  private keyPress(event: KeyboardEvent): void {
    if (event.key === "Enter") {
      this.submit(event);
    }
  }

  /**
   * Is called every time the dialog is shown.
   */
  private onDialogShow(): void {
    // reset path input
    this.pathInput.value = "";
    this.pathInput.focus();

    // hide error
    DomUtil.innerError(this.pathInput, false);
  }

  /**
   * Shows the dialog after clicking on the related button.
   */
  private showDialog(event: MouseEvent): void {
    event.preventDefault();

    UiDialog.open(this);
  }

  /**
   * Is called if the dialog form is submitted.
   */
  private submit(event: Event): void {
    event.preventDefault();

    // check if path is empty
    if (this.pathInput.value === "") {
      DomUtil.innerError(this.pathInput, Language.get("wcf.global.form.error.empty"));

      return;
    }

    Ajax.api(this, {
      parameters: {
        path: this.pathInput.value,
      },
    });

    this.submitButton.disabled = true;
  }
}

let acpUiDevtoolsProjectQuickSetup: AcpUiDevtoolsProjectQuickSetup;

/**
 * Initializes the project quick setup handler.
 */
export function init(): void {
  if (!acpUiDevtoolsProjectQuickSetup) {
    acpUiDevtoolsProjectQuickSetup = new AcpUiDevtoolsProjectQuickSetup();
  }
}
