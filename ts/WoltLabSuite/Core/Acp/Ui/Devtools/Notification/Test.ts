/**
 * Executes user notification tests.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { getPhrase } from "WoltLabSuite/Core/Language";
import UiDialog from "../../../../Ui/Dialog";
import { DialogCallbackObject, DialogCallbackSetup } from "../../../../Ui/Dialog/Data";
import DomUtil from "../../../../Dom/Util";
import { testNotificationEvent } from "WoltLabSuite/Core/Api/Devtools/Notifications/TestNotificationEvent";

class AcpUiDevtoolsNotificationTest implements DialogCallbackObject {
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
   * Shows the rendered test results of the event.
   */
  private showResults(eventId: number, template: string): void {
    UiDialog.open(this, template);
    UiDialog.setTitle(this, this.titles.get(eventId)!);

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
  private async test(event: MouseEvent): Promise<void> {
    const button = event.currentTarget as HTMLButtonElement;
    const eventId = ~~button.dataset.eventId!;

    button.innerHTML = '<fa-icon name="spinner" solid></fa-icon>';

    this.buttons.forEach((button) => (button.disabled = true));

    try {
      const { template } = await testNotificationEvent(eventId);
      this.showResults(eventId, template);
    } finally {
      this.buttons.forEach((button) => {
        button.innerHTML = getPhrase("wcf.acp.devtools.notificationTest.button.test");
        button.disabled = false;
      });
    }
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
