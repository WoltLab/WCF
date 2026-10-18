/**
 * Helper functions to insert media files into editors.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/StringUtil"], function (require, exports, Dialog_1, Language_1, StringUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.chooseImageSize = chooseImageSize;
    exports.getEffectiveSize = getEffectiveSize;
    exports.insertIntoCkeditor = insertIntoCkeditor;
    const sizes = ["small", "medium", "large", "original"];
    /**
     * Asks the user for the size in which images are inserted. Resolves with
     * `original` without asking if there is no image or no image offers a
     * thumbnail and with `undefined` if the user canceled the dialog.
     */
    async function chooseImageSize(media) {
        const availableSizes = sizes.filter((size) => media.some((item) => item.isImage && item.editorLinks[size]));
        if (availableSizes.length < 2) {
            return "original";
        }
        const options = availableSizes
            .map((size) => `<option value="${size}"${size === "medium" ? " selected" : ""}>${(0, Language_1.getPhrase)(`wcf.media.insert.imageSize.${size}`)}</option>`)
            .join("");
        const dialog = (0, Dialog_1.dialogFactory)()
            .fromHtml(`<dl>
        <dt><label for="mediaInsertImageSize">${(0, Language_1.getPhrase)("wcf.media.insert.imageSize")}</label></dt>
        <dd><select id="mediaInsertImageSize">${options}</select></dd>
      </dl>`)
            .asPrompt({ primary: (0, Language_1.getPhrase)("wcf.global.button.insert") });
        return new Promise((resolve) => {
            dialog.addEventListener("primary", () => {
                resolve(dialog.content.querySelector("select").value);
            });
            dialog.addEventListener("cancel", () => {
                resolve(undefined);
            });
            dialog.show((0, Language_1.getPhrase)("wcf.media.insert"));
        });
    }
    /**
     * Returns the largest size of the image that does not exceed the requested size.
     */
    function getEffectiveSize(media, size) {
        for (let i = sizes.indexOf(size); i >= 0; i--) {
            if (media.editorLinks[sizes[i]]) {
                return sizes[i];
            }
        }
        return "original";
    }
    function insertIntoCkeditor(ckeditor, media, size) {
        for (const item of media) {
            if (item.isImage) {
                const effectiveSize = getEffectiveSize(item, size);
                ckeditor.insertHtml(`<img src="${(0, StringUtil_1.escapeHTML)(item.editorLinks[effectiveSize])}" class="image woltlabSuiteMedia" data-media-id="${item.mediaID}" data-media-size="${effectiveSize}">`);
            }
            else {
                ckeditor.insertText(`[wsm='${item.mediaID}'][/wsm]`);
            }
        }
    }
});
