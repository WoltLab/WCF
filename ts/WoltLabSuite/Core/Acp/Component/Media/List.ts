/**
 * Reloads the grid view of media files after new files have been uploaded. A
 * single uploaded file is opened in the edit dialog, multiple uploaded files
 * are confirmed by a notification, because they might not be visible on the
 * current page of the grid view.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle all
 */

import { editMedia, setupMediaUpload } from "WoltLabSuite/Core/Component/Media/Upload";
import { showSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { getPhrase } from "WoltLabSuite/Core/Language";

export function setup(uploadButton: HTMLElement, container: HTMLElement, editEndpoint: string): void {
  setupMediaUpload(uploadButton, {
    // Reload the grid view only once after a batch of files has been uploaded.
    allUploadsCompleted: async (mediaIds) => {
      container.dispatchEvent(new CustomEvent("interaction:invalidate-all"));

      if (mediaIds.length > 1) {
        showSuccessSnackbar(getPhrase("wcf.media.upload.success.multiple", { count: mediaIds.length }));

        return;
      }

      // A single uploaded file is edited right away.
      if (mediaIds.length === 1 && (await editMedia(editEndpoint, mediaIds[0]))) {
        container.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
      }
    },
  });
}
