/**
 * Shows the media manager in a dialog to insert or select media files.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Api/Media/GetMedia", "WoltLabSuite/Core/Language"], function (require, exports, Dialog_1, GetMedia_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.openMediaManager = openMediaManager;
    /**
     * Opens the media manager and resolves with the media files that the user has
     * inserted or selected, an empty list is returned if the dialog was closed.
     */
    async function openMediaManager(options) {
        const dialog = await (0, Dialog_1.dialogFactory)()
            .usingListView()
            .fromPreset(options.title || (0, Language_1.getPhrase)(options.mode === "select" ? "wcf.media.chooseFile" : "wcf.media.manager"), "wcf\\system\\listView\\user\\MediaManagerListView", new Map([
            ["mode", options.mode],
            ["imagesOnly", options.imagesOnly ? "1" : "0"],
        ]), undefined, "uploadTime", "DESC");
        const listViewItems = dialog.content.querySelector(".listView__items");
        const uploadButton = dialog.content.querySelector("woltlab-core-file-upload");
        return new Promise((resolve) => {
            let completed = false;
            const complete = async (mediaIds) => {
                if (completed) {
                    return;
                }
                completed = true;
                const media = await Promise.all(mediaIds.map((mediaId) => (0, GetMedia_1.getMedia)(mediaId)));
                dialog.close();
                resolve(media);
            };
            dialog.content.addEventListener("click", (event) => {
                const button = event.target.closest(".mediaManager__insertButton, .mediaManager__selectButton");
                if (button !== null) {
                    void complete([parseInt(button.dataset.objectId)]);
                }
            });
            listViewItems.addEventListener("bulk-interaction", (event) => {
                if (event.detail.bulkInteraction === "insert") {
                    void complete(JSON.parse(event.detail.objectIds));
                }
            });
            if (uploadButton !== null) {
                setupUpload(uploadButton, listViewItems, options.file !== undefined ? (mediaId) => complete([mediaId]) : null);
                const file = options.file;
                if (file !== undefined) {
                    // The upload button is initialized by a mutation observer.
                    window.setTimeout(() => {
                        uploadButton.dispatchEvent(new CustomEvent("upload:files", {
                            detail: { files: [file] },
                        }));
                    }, 0);
                }
            }
            dialog.addEventListener("afterClose", () => {
                // The selection is stored per list view and would otherwise be kept forever.
                listViewItems.dispatchEvent(new CustomEvent("interaction:reset-selection"));
                if (!completed) {
                    completed = true;
                    resolve([]);
                }
            });
        });
    }
    function setupUpload(uploadButton, listViewItems, onUploadCompleted) {
        let pendingUploads = 0;
        uploadButton.addEventListener("uploadStart", (event) => {
            pendingUploads++;
            const fileElement = event.detail;
            void fileElement.ready
                .then(() => {
                const mediaId = fileElement.data?.mediaID;
                if (onUploadCompleted !== null && typeof mediaId === "number") {
                    void onUploadCompleted(mediaId);
                }
            })
                .catch(() => {
                // The upload has failed, the error is shown by the upload button.
            })
                .finally(() => {
                pendingUploads--;
                // Reload the list only once after a batch of files has been uploaded.
                if (pendingUploads === 0 && listViewItems.isConnected) {
                    listViewItems.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
                }
            });
        });
    }
});
