/**
 * Notifies the caller once media uploads have been completed.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import type WoltlabCoreFileElement from "WoltLabSuite/Core/Component/File/woltlab-core-file";
import { getErrorMessageFromFile } from "WoltLabSuite/Core/Component/File/Helper";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";

export type MediaUploadCallbacks = {
  /**
   * Is called for every media file that has been created by an upload.
   */
  uploadCompleted?: (mediaId: number) => void;
  /**
   * Is called once there are no more pending uploads.
   */
  allUploadsCompleted?: () => void;
};

function showError(message: string): void {
  const dialog = dialogFactory().fromHtml(`<p>${message}</p>`).asAlert();
  dialog.show(getPhrase("wcf.global.error.title"));
}

export function setupMediaUpload(uploadButton: HTMLElement, callbacks: MediaUploadCallbacks): void {
  let pendingUploads = 0;

  // Errors that occur before the upload has started are shown in a dialog
  // instead of below the upload button.
  uploadButton.addEventListener("upload:error", (event: CustomEvent<{ message: string }>) => {
    event.preventDefault();

    showError(event.detail.message);
  });

  uploadButton.addEventListener("uploadStart", (event: CustomEvent<WoltlabCoreFileElement>) => {
    pendingUploads++;

    // The file element only tracks the state of the upload while it is part
    // of the document, but there is no need to display it.
    const file = event.detail;
    file.hidden = true;
    document.body.append(file);

    void file.ready
      .then(() => {
        const mediaId = file.data?.mediaID;
        if (typeof mediaId === "number") {
          callbacks.uploadCompleted?.(mediaId);
        }
      })
      .catch(() => {
        if (file.apiError !== undefined) {
          showError(getErrorMessageFromFile(file));
        }
      })
      .finally(() => {
        file.remove();

        pendingUploads--;
        if (pendingUploads === 0) {
          callbacks.allUploadsCompleted?.();
        }
      });
  });
}
