/**
 * Handles editing media files via dialog.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "../Component/Dialog", "../Component/Snackbar"], function (require, exports, Dialog_1, Snackbar_1) {
    "use strict";
    let endpoint = "";
    class MediaEditor {
        _callbackObject;
        _media = null;
        constructor(callbackObject) {
            this._callbackObject = callbackObject || {};
            if (this._callbackObject._editorClose && typeof this._callbackObject._editorClose !== "function") {
                throw new TypeError("Callback object has no function '_editorClose'.");
            }
            if (this._callbackObject._editorSuccess && typeof this._callbackObject._editorSuccess !== "function") {
                throw new TypeError("Callback object has no function '_editorSuccess'.");
            }
        }
        /**
         * Sets the link of the edit dialog, `%s` is replaced with the id of the
         * media file.
         *
         * @since 6.3
         */
        static setEndpoint(link) {
            endpoint = link;
        }
        /**
         * Edits the media with the given data or id.
         */
        async edit(editedMedia) {
            const media = typeof editedMedia === "object" ? editedMedia : { mediaID: editedMedia, categoryID: 0 };
            if (this._media !== null) {
                throw new Error(`Cannot edit media with id ${media.mediaID} while editing media with id '${this._media.mediaID}'.`);
            }
            if (endpoint === "") {
                throw new Error("The endpoint of the media editor has not been set.");
            }
            this._media = media;
            const { ok, result } = await (0, Dialog_1.dialogFactory)()
                .usingFormBuilder()
                .fromEndpoint(endpoint.replace("%s", media.mediaID.toString()));
            this._media = null;
            if (!ok) {
                this._callbackObject._editorClose?.();
                return;
            }
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
            this._callbackObject._editorSuccess?.({ ...media, ...result }, media.categoryID);
        }
        /**
         * Updates the data of the currently edited media file.
         */
        updateData(media) {
            if (this._callbackObject._editorSuccess) {
                this._callbackObject._editorSuccess(media, undefined, false);
            }
        }
    }
    return MediaEditor;
});
