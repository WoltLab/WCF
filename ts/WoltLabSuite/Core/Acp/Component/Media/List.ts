/**
 * Reloads the grid view of media files after new files have been uploaded.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle all
 */

import { setupMediaUpload } from "WoltLabSuite/Core/Component/Media/Upload";

export function setup(uploadButton: HTMLElement, container: HTMLElement): void {
  setupMediaUpload(uploadButton, {
    // Reload the grid view only once after a batch of files has been uploaded.
    allUploadsCompleted: () => {
      container.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
    },
  });
}
