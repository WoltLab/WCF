import { dboAction } from "../../Ajax";
import { dialogFactory, ModalDialog } from "../../Dialog";
import { findUniqueElements } from "../../Dom/Observer";
import { innerError } from "../../Dom/Util";
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

  let dialog: ModalDialog;
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

  dialog.title = Language.get("wcf.moderation.report.reportContent");
  dialog.show();
}

function validateReport(dialog: ModalDialog): boolean {
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

async function submitReport(dialog: ModalDialog, objectType: string, objectId: number): Promise<void> {
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

export function setup(): void {
  findUniqueElements("[data-report-content]", (element: HTMLElement) => {
    if (validateButton(element)) {
      element.addEventListener("click", (event) => {
        if (element.tagName === "A") {
          event.preventDefault();
        }

        void openReportDialog(element);
      });
    }
  });
}
