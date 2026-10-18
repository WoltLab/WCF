/**
 * Reloads the grid view of media files after new files have been uploaded and
 * opens the edit dialog if a single file has been uploaded.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle all
 */

import { editMedia, setupMediaUpload } from "WoltLabSuite/Core/Component/Media/Upload";

export function setup(uploadButton: HTMLElement, container: HTMLElement, editEndpoint: string): void {
  setupMediaUpload(uploadButton, {
    // Reload the grid view only once after a batch of files has been uploaded.
    allUploadsCompleted: async (mediaIds) => {
      container.dispatchEvent(new CustomEvent("interaction:invalidate-all"));

      // A single uploaded file is edited right away.
      if (mediaIds.length === 1 && (await editMedia(editEndpoint, mediaIds[0]))) {
        container.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
      }
    },
  });
}
