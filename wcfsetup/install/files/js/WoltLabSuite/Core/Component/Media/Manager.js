/**
 * Shows the media manager in a dialog to insert or select media files.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Api/Media/GetMedia", "WoltLabSuite/Core/Language", "./Upload"], function (require, exports, Dialog_1, GetMedia_1, Language_1, Upload_1) {
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
                // The upload element is hidden and triggered by the primary button of the list view.
                const proxyButton = dialog.content.querySelector(".mediaManager__uploadButton");
                if (proxyButton !== null) {
                    proxyButton.addEventListener("click", () => {
                        uploadButton.click();
                    });
                    uploadButton.addEventListener("uploadStart", () => {
                        proxyButton.disabled = true;
                    });
                }
                (0, Upload_1.setupMediaUpload)(uploadButton, {
                    uploadCompleted: (mediaId) => {
                        if (options.file !== undefined) {
                            void complete([mediaId]);
                        }
                    },
                    allUploadsCompleted: async (mediaIds) => {
                        if (proxyButton !== null) {
                            proxyButton.disabled = false;
                        }
                        if (!listViewItems.isConnected) {
                            return;
                        }
                        // Reload the list only once after a batch of files has been uploaded.
                        listViewItems.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
                        // A single uploaded file is edited right away, unless it is inserted directly.
                        const editEndpoint = uploadButton.closest(".mediaManager__upload")?.dataset.editEndpoint;
                        if (options.file !== undefined || mediaIds.length !== 1 || editEndpoint === undefined) {
                            return;
                        }
                        if ((await (0, Upload_1.editMedia)(editEndpoint, mediaIds[0])) && listViewItems.isConnected) {
                            listViewItems.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
                        }
                    },
                });
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
});
