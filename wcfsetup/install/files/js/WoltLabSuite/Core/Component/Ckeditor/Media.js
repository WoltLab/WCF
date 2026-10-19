/**
 * Forwards upload requests from the editor to the media system.
 *
 * @author Alexander Ebert, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "./Event", "./Filename", "../Media/Manager", "../Media/Insert", "../../Helper/PromiseMutex"], function (require, exports, Event_1, Filename_1, Manager_1, Insert_1, PromiseMutex_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function uploadMedia(element, file, abortController) {
        const payload = { abortController, file: (0, Filename_1.normalizePastedFilename)(file) };
        (0, Event_1.dispatchToCkeditor)(element).uploadMedia(payload);
        // The media system works differently compared to the
        // attachments, because uploading a file will offer
        // the user to insert the content in different formats.
        //
        // Rejecting the upload promise will cause CKEditor to
        // stop caring about the file so that we regain control.
        return Promise.reject();
    }
    /**
     * Opens the media manager and inserts the chosen media files. A file that was
     * pasted or dropped into the editor is uploaded right away and inserted once
     * the upload has been completed.
     */
    async function insertMedia(ckeditor, file) {
        const media = await (0, Manager_1.openMediaManager)({ mode: "editor", file });
        if (media.length === 0) {
            return;
        }
        const options = await (0, Insert_1.chooseInsertOptions)(media);
        if (options === undefined) {
            return;
        }
        (0, Insert_1.insertIntoCkeditor)(ckeditor, media, options.size, options.gallery);
    }
    function setup(element) {
        (0, Event_1.listenToCkeditor)(element)
            .setupConfiguration(({ configuration, features }) => {
            if (features.attachment || !features.media) {
                return;
            }
            // TODO: The typings do not include our custom plugins yet.
            configuration.woltlabUpload = {
                uploadImage: (file, abortController) => uploadMedia(element, file, abortController),
                uploadOther: (file) => uploadMedia(element, file),
            };
        })
            .ready(({ ckeditor }) => {
            if (!ckeditor.features.media) {
                return;
            }
            // The media manager is loaded asynchronously, repeated clicks must not open it twice.
            const openManager = (0, PromiseMutex_1.promiseMutex)(() => insertMedia(ckeditor));
            (0, Event_1.listenToCkeditor)(ckeditor.sourceElement).bbcode(({ bbcode }) => {
                if (bbcode !== "media") {
                    return false;
                }
                openManager();
                return true;
            });
            if (!ckeditor.features.attachment) {
                (0, Event_1.listenToCkeditor)(ckeditor.sourceElement).uploadMedia((payload) => {
                    void insertMedia(ckeditor, payload.file);
                });
            }
        });
    }
});
