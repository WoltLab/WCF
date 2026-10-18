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
import { innerError } from "WoltLabSuite/Core/Dom/Util";

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

export function setupMediaUpload(uploadButton: HTMLElement, callbacks: MediaUploadCallbacks): void {
  let pendingUploads = 0;

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
          innerError(uploadButton, getErrorMessageFromFile(file));
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
