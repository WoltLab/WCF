/**
 * Provides the media manager dialog for inserting media files.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 * @deprecated 6.3 Use `WoltLabSuite/Core/Component/Media/Manager` instead.
 */
define(["require", "exports", "../../Component/Media/Manager", "../../Component/Media/Insert", "../../Helper/PromiseMutex"], function (require, exports, Manager_1, Insert_1, PromiseMutex_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.MediaManagerEditor = void 0;
    class MediaManagerEditor {
        #options;
        constructor(options) {
            this.#options = options;
            if (options.ckeditor !== undefined) {
                // Editors are supported by `WoltLabSuite/Core/Component/Ckeditor/Media` out of the box.
                return;
            }
            if (typeof options.buttonClass === "string") {
                const insert = (0, PromiseMutex_1.promiseMutex)(() => this.#insert());
                document.querySelectorAll(`.${options.buttonClass}`).forEach((button) => {
                    button.addEventListener("click", () => {
                        insert();
                    });
                });
            }
        }
        async #insert() {
            const media = await (0, Manager_1.openMediaManager)({
                mode: "editor",
                imagesOnly: this.#options.imagesOnly,
                title: this.#options.dialogTitle,
            });
            if (media.length === 0 || this.#options.callbackInsert === undefined) {
                return;
            }
            const size = await (0, Insert_1.chooseImageSize)(media);
            if (size === undefined) {
                return;
            }
            this.#options.callbackInsert(new Map(media.map((item) => [item.mediaID, item])), "separate" /* MediaInsertType.Separate */, size === "original" ? undefined : size);
        }
    }
    exports.MediaManagerEditor = MediaManagerEditor;
    exports.default = MediaManagerEditor;
});
