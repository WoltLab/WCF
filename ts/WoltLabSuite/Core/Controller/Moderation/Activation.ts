/**
 * Handles the button on the moderation activation page.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 * @since 6.2
 */

import { deleteContent } from "WoltLabSuite/Core/Api/ModerationQueues/DeleteContent";
import { enableContent } from "WoltLabSuite/Core/Api/ModerationQueues/EnableContent";
import { confirmationFactory } from "WoltLabSuite/Core/Component/Confirmation";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { getPhrase } from "WoltLabSuite/Core/Language";

async function handleEnableContent(queueId: number, redirectUrl: string): Promise<void> {
  const result = await confirmationFactory()
    .custom(getPhrase("wcf.moderation.activation.enableContent.confirmMessage"))
    .withoutMessage();

  if (result) {
    await enableContent(queueId);

    showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
      window.location.href = redirectUrl;
    });
  }
}

async function handleRemoveContent(queueId: number, objectName: string, redirectUrl: string): Promise<void> {
  const { result, reason } = await confirmationFactory().softDelete(objectName, true);

  if (result) {
    await deleteContent(queueId, reason);

    showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
      window.location.href = redirectUrl;
    });
  }
}

export function setup(enableContentButton: HTMLElement, removeContentButton: HTMLElement): void {
  enableContentButton.addEventListener(
    "click",
    promiseMutex(() =>
      handleEnableContent(parseInt(removeContentButton.dataset.objectId!), removeContentButton.dataset.redirectUrl!),
    ),
  );

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
