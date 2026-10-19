/**
 * Notifies the caller once media uploads have been completed.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Component/File/Helper", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Language"], function (require, exports, Helper_1, Dialog_1, Snackbar_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.editMedia = editMedia;
    exports.setupMediaUpload = setupMediaUpload;
    function showError(message) {
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(`<p>${message}</p>`).asAlert();
        dialog.show((0, Language_1.getPhrase)("wcf.global.error.title"));
    }
    /**
     * Opens the dialog to edit the media file with the given id. The endpoint
     * contains `%s` as the placeholder for the id.
     */
    async function editMedia(endpoint, mediaId) {
        const { ok } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(endpoint.replace("%s", mediaId.toString()));
        if (ok) {
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
        }
        return ok;
    }
    function setupMediaUpload(uploadButton, callbacks) {
        let pendingUploads = 0;
        let mediaIds = [];
        // Errors that occur before the upload has started are shown in a dialog
        // instead of below the upload button.
        uploadButton.addEventListener("upload:error", (event) => {
            event.preventDefault();
            showError(event.detail.message);
        });
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
                    mediaIds.push(mediaId);
                    callbacks.uploadCompleted?.(mediaId);
                }
            })
                .catch(() => {
                if (file.apiError !== undefined) {
                    showError((0, Helper_1.getErrorMessageFromFile)(file));
                }
            })
                .finally(() => {
                file.remove();
                pendingUploads--;
                if (pendingUploads === 0) {
                    // All files of a selection start uploading before the first one is completed.
                    const completedMediaIds = mediaIds;
                    mediaIds = [];
                    callbacks.allUploadsCompleted?.(completedMediaIds);
                }
            });
        });
    }
});
