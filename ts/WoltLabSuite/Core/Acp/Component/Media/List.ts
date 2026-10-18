/**
 * Reloads the grid view of media files after new files have been uploaded.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle all
 */

import type WoltlabCoreFileElement from "WoltLabSuite/Core/Component/File/woltlab-core-file";

export function setup(uploadButton: HTMLElement, container: HTMLElement): void {
  let pendingUploads = 0;

  uploadButton.addEventListener("uploadStart", (event: CustomEvent<WoltlabCoreFileElement>) => {
    pendingUploads++;

    void event.detail.ready
      .catch(() => {
        // The upload has failed, the error is shown by the upload button.
      })
      .finally(() => {
        pendingUploads--;

        // Reload the grid view only once after a batch of files has been uploaded.
        if (pendingUploads === 0) {
          container.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
        }
      });
  });
}
