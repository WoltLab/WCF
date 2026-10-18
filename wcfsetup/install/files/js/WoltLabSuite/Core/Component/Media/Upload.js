/**
 * Notifies the caller once media uploads have been completed.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Component/File/Helper", "WoltLabSuite/Core/Dom/Util"], function (require, exports, Helper_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupMediaUpload = setupMediaUpload;
    function setupMediaUpload(uploadButton, callbacks) {
        let pendingUploads = 0;
        uploadButton.addEventListener("uploadStart", (event) => {
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
                    (0, Util_1.innerError)(uploadButton, (0, Helper_1.getErrorMessageFromFile)(file));
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
});
