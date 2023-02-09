/**
 * Provides the dialog to report content.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { dboAction } from "../../Ajax";
import { dialogFactory } from "../../Component/Dialog";
import { innerError } from "../../Dom/Util";
import WoltlabCoreDialogElement from "../../Element/woltlab-core-dialog";
import { wheneverFirstSeen } from "../../Helper/Selector";
import * as Language from "../../Language";
import * as UiNotification from "../Notification";

type ResponsePrepareReport = {
  alreadyReported: 0 | 1;
  template: string;
};

async function openReportDialog(element: HTMLElement): Promise<void> {
  const objectId = parseInt(element.dataset.objectId || "");
  const objectType = element.dataset.reportContent!;

  const response = (await dboAction("prepareReport", "wcf\\data\\moderation\\queue\\ModerationQueueReportAction")
    .payload({
      objectID: objectId,
      objectType,
    })
    .dispatch()) as ResponsePrepareReport;

  let dialog: WoltlabCoreDialogElement;
  if (response.alreadyReported) {
    dialog = dialogFactory().fromHtml(response.template).asAlert();
  } else {
    dialog = dialogFactory().fromHtml(response.template).asPrompt();
    dialog.addEventListener("validate", (event) => {
      if (!validateReport(dialog)) {
        event.preventDefault();
      }
    });
    dialog.addEventListener("primary", () => {
      void submitReport(dialog, objectType, objectId);
    });
  }

  dialog.show(Language.get("wcf.moderation.report.reportContent"));
}

function validateReport(dialog: WoltlabCoreDialogElement): boolean {
  const message = dialog.content.querySelector(".jsReportMessage") as HTMLTextAreaElement;
  const dl = message.closest("dl")!;
  if (message.value.trim() === "") {
    dl.classList.add("formError");
    innerError(message, Language.get("wcf.global.form.error.empty"));

    return false;
  }

  dl.classList.remove("formError");
  innerError(message, false);

  return true;
}

async function submitReport(dialog: WoltlabCoreDialogElement, objectType: string, objectId: number): Promise<void> {
  const message = dialog.content.querySelector(".jsReportMessage") as HTMLTextAreaElement;
  const value = message.value.trim();

  await dboAction("report", "wcf\\data\\moderation\\queue\\ModerationQueueReportAction")
    .payload({
      message: value,
      objectID: objectId,
      objectType,
    })
    .dispatch();

  UiNotification.show();
}

function validateButton(element: HTMLElement): boolean {
  if (element.dataset.reportContent === "") {
    console.error("Missing the value for [data-report-content]", element);
    return false;
  }

  const objectId = parseInt(element.dataset.objectId || "");
  if (!objectId) {
    console.error("Expected a valid integer for [data-object-id]", element);
    return false;
  }

  return true;
}

function registerButton(element: HTMLElement): void {
  if (validateButton(element)) {
    element.addEventListener("click", (event) => {
      if (element.tagName === "A" || element.dataset.isLegacyButton === "true") {
        event.preventDefault();
      }

      void openReportDialog(element);
    });
  }
}

/**
 * @deprecated 6.0 Use the attribute `[data-report-content]` instead.
 */
export function registerLegacyButton(element: HTMLElement, objectType: string): void {
  element.dataset.reportContent = objectType;
  element.dataset.isLegacyButton = "true";

  registerButton(element);
}

export function setup(): void {
  wheneverFirstSeen("[data-report-content]", (element) => registerButton(element));
}
