/**
 * Handles the button on the moderation report page.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 * @since 6.2
 */

import { changeJustifiedStatus } from "WoltLabSuite/Core/Api/ModerationQueues/ChangeJustifiedStatus";
import { closeReport } from "WoltLabSuite/Core/Api/ModerationQueues/CloseReport";
import { deleteContent } from "WoltLabSuite/Core/Api/ModerationQueues/DeleteContent";
import { confirmationFactory } from "WoltLabSuite/Core/Component/Confirmation";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { getPhrase } from "WoltLabSuite/Core/Language";

async function handleRemoveContent(queueId: number, objectName: string, redirectUrl: string): Promise<void> {
  const { result, reason } = await confirmationFactory().softDelete(objectName, true);

  if (result) {
    await deleteContent(queueId, reason);

    showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
      window.location.href = redirectUrl;
    });
  }
}

async function handleCloseReport(queueId: number, redirectUrl: string): Promise<void> {
  const { result, dialog } = await confirmationFactory()
    .custom(getPhrase("wcf.moderation.report.removeReport.confirmMessage"))
    .withFormElements((dialog) => {
      const label = document.createElement("label");
      const input = document.createElement("input");
      input.type = "checkbox";

      dialog.content.append(label);
      label.append(input, " ", getPhrase("wcf.moderation.report.removeReport.markAsJustified"));
    });

  if (result) {
    await closeReport(queueId, dialog.content.querySelector("input")!.checked);

    showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
      window.location.href = redirectUrl;
    });
  }
}

async function handleChangeJustifiedStatus(queueId: number, justified: boolean, redirectUrl: string): Promise<void> {
  const { result, dialog } = await confirmationFactory()
    .custom(getPhrase("wcf.moderation.report.changeJustifiedStatus.confirmMessage"))
    .withFormElements((dialog) => {
      const label = document.createElement("label");
      const input = document.createElement("input");
      input.type = "checkbox";
      input.checked = justified;

      dialog.content.append(label);
      label.append(input, " ", getPhrase("wcf.moderation.report.changeJustifiedStatus.markAsJustified"));
    });

  if (result) {
    await changeJustifiedStatus(queueId, dialog.content.querySelector("input")!.checked);

    showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
      window.location.href = redirectUrl;
    });
  }
}

export function setup(
  removeContentButton: HTMLElement | undefined,
  closeReportButton: HTMLElement | undefined,
  changeJustifiedStatusButton: HTMLElement | undefined,
): void {
  if (removeContentButton) {
    removeContentButton.addEventListener(
      "click",
      promiseMutex(() =>
        handleRemoveContent(
          parseInt(removeContentButton.dataset.objectId!),
          removeContentButton.dataset.objectName!,
          removeContentButton.dataset.redirectUrl!,
        ),
      ),
    );
  }

  if (closeReportButton) {
    closeReportButton.addEventListener(
      "click",
      promiseMutex(() =>
        handleCloseReport(parseInt(closeReportButton.dataset.objectId!), closeReportButton.dataset.redirectUrl!),
      ),
    );
  }

  if (changeJustifiedStatusButton) {
    changeJustifiedStatusButton.addEventListener(
      "click",
      promiseMutex(() =>
        handleChangeJustifiedStatus(
          parseInt(changeJustifiedStatusButton.dataset.objectId!),
          changeJustifiedStatusButton.dataset.justified === "true",
          changeJustifiedStatusButton.dataset.redirectUrl!,
        ),
      ),
    );
  }
}
