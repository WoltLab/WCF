/**
 * Executes user notification tests.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Devtools/Project/QuickSetup
 */

import * as Ajax from "../../../../Ajax";
import * as Language from "../../../../Language";
import UiDialog from "../../../../Ui/Dialog";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../../../../Ajax/Data";
import { DialogCallbackObject, DialogCallbackSetup } from "../../../../Ui/Dialog/Data";
import DomUtil from "../../../../Dom/Util";

interface AjaxResponse {
  returnValues: {
    eventID: number;
    template: string;
  };
}

class AcpUiDevtoolsNotificationTest implements AjaxCallbackObject, DialogCallbackObject {
  private readonly buttons: HTMLButtonElement[];
  private readonly titles = new Map<number, string>();

  /**
   * Initializes the user notification test handler.
   */
  constructor() {
    this.buttons = Array.from(document.querySelectorAll(".jsTestEventButton"));

    this.buttons.forEach((button) => {
      button.addEventListener("click", (ev) => this.test(ev));

      const eventId = ~~button.dataset.eventId!;
      const title = button.dataset.title!;
      this.titles.set(eventId, title);
    });
  }

  /**
   * Returns the data used to setup the AJAX request object.
   */
  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "testEvent",
        className: "wcf\\data\\user\\notification\\event\\UserNotificationEventAction",
      },
    };
  }

  /**
   * Handles successful AJAX request.
   */
  _ajaxSuccess(data: AjaxResponse): void {
    UiDialog.open(this, data.returnValues.template);
    UiDialog.setTitle(this, this.titles.get(~~data.returnValues.eventID)!);

    const dialog = UiDialog.getDialog(this)!.dialog;

    dialog.querySelectorAll(".formSubmit button").forEach((button: HTMLButtonElement) => {
      button.addEventListener("click", (ev) => this.changeView(ev));
    });

    // fix some margin issues
    const errors: HTMLElement[] = Array.from(dialog.querySelectorAll(".error"));
    if (errors.length === 1) {
      errors[0].style.setProperty("margin-top", "0px");
      errors[0].style.setProperty("margin-bottom", "20px");
    }

    dialog.querySelectorAll(".notificationTestSection").forEach((section: HTMLElement) => {
      section.style.setProperty("margin-top", "0px");
    });

    document.getElementById("notificationTestDialog")!.parentElement!.scrollTop = 0;

    // restore buttons
    this.buttons.forEach((button) => {
      button.innerHTML = Language.get("wcf.acp.devtools.notificationTest.button.test");
      button.disabled = false;
    });
  }

  /**
   * Changes the view after clicking on one of the buttons.
   */
  private changeView(event: MouseEvent): void {
    const button = event.currentTarget as HTMLButtonElement;

    const dialog = UiDialog.getDialog(this)!.dialog;

    dialog.querySelectorAll(".notificationTestSection").forEach((section: HTMLElement) => DomUtil.hide(section));
    const containerId = button.id.replace("Button", "");
    DomUtil.show(document.getElementById(containerId)!);

    const primaryButton = dialog.querySelector(".formSubmit .buttonPrimary") as HTMLElement;
    primaryButton.classList.remove("buttonPrimary");
    primaryButton.classList.add("button");

    button.classList.remove("button");
    button.classList.add("buttonPrimary");

    document.getElementById("notificationTestDialog")!.parentElement!.scrollTop = 0;
  }

  /**
   * Returns the data used to setup the dialog.
   */
  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "notificationTestDialog",
      source: null,
    };
  }

  /**
   * Executes a test after clicking on a test button.
   */
  private test(event: MouseEvent): void {
    const button = event.currentTarget as HTMLButtonElement;

    button.innerHTML = '<span class="icon icon16 fa-spinner"></span>';

    this.buttons.forEach((button) => (button.disabled = true));

    Ajax.api(this, {
      parameters: {
        eventID: ~~button.dataset.eventId!,
      },
    });
  }
}

let acpUiDevtoolsNotificationTest: AcpUiDevtoolsNotificationTest;

/**
 * Initializes the user notification test handler.
 */
export function init(): void {
  if (!acpUiDevtoolsNotificationTest) {
    acpUiDevtoolsNotificationTest = new AcpUiDevtoolsNotificationTest();
  }
}
