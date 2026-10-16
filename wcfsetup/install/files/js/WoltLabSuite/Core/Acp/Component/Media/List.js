/**
 * Reloads the list of media files after new files have been uploaded.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle all
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setup(uploadButton, listViewItems) {
        let pendingUploads = 0;
        uploadButton.addEventListener("uploadStart", (event) => {
            pendingUploads++;
            void event.detail.ready
                .catch(() => {
                // The upload has failed, the error is shown by the upload button.
            })
                .finally(() => {
                pendingUploads--;
                // Reload the list only once after a batch of files has been uploaded.
                if (pendingUploads === 0) {
                    listViewItems.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
                }
            });
        });
    }
});
