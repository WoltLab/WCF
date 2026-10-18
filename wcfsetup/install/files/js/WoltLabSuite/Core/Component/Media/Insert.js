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
    exports.chooseInsertOptions = chooseInsertOptions;
    exports.getEffectiveSize = getEffectiveSize;
    exports.insertIntoCkeditor = insertIntoCkeditor;
    const sizes = ["small", "medium", "large", "original"];
    function getAvailableSizes(media) {
        return sizes.filter((size) => media.some((item) => item.isImage && item.editorLinks[size]));
    }
    function renderSizeSelect(availableSizes) {
        const options = availableSizes
            .map((size) => `<option value="${size}"${size === "medium" ? " selected" : ""}>${(0, Language_1.getPhrase)(`wcf.media.insert.imageSize.${size}`)}</option>`)
            .join("");
        return `<dl>
    <dt><label for="mediaInsertImageSize">${(0, Language_1.getPhrase)("wcf.media.insert.imageSize")}</label></dt>
    <dd><select id="mediaInsertImageSize">${options}</select></dd>
  </dl>`;
    }
    /**
     * Asks the user for the size in which images are inserted. Resolves with
     * `original` without asking if there is no image or no image offers a
     * thumbnail and with `undefined` if the user canceled the dialog.
     */
    async function chooseImageSize(media) {
        const options = await chooseInsertOptions(media, false);
        return options?.size;
    }
    /**
     * Asks the user for the size in which images are inserted and whether
     * multiple images are inserted as a gallery. Resolves without asking if
     * there is nothing to choose and with `undefined` if the user canceled the
     * dialog.
     */
    async function chooseInsertOptions(media, allowGallery = true) {
        const availableSizes = getAvailableSizes(media);
        const canInsertGallery = allowGallery && media.filter((item) => item.isImage).length >= 2;
        if (availableSizes.length < 2 && !canInsertGallery) {
            return { size: "original", gallery: false };
        }
        let html = "";
        if (canInsertGallery) {
            html += `<dl>
      <dt>${(0, Language_1.getPhrase)("wcf.media.insert.type")}</dt>
      <dd>
        <label><input type="radio" name="mediaInsertType" value="separate" checked> ${(0, Language_1.getPhrase)("wcf.media.insert.type.separate")}</label>
        <label><input type="radio" name="mediaInsertType" value="gallery"> ${(0, Language_1.getPhrase)("wcf.media.insert.type.gallery")}</label>
      </dd>
    </dl>`;
        }
        if (availableSizes.length >= 2) {
            html += renderSizeSelect(availableSizes);
        }
        const dialog = (0, Dialog_1.dialogFactory)()
            .fromHtml(html)
            .asPrompt({ primary: (0, Language_1.getPhrase)("wcf.global.button.insert") });
        const sizeSelect = dialog.content.querySelector("select");
        const isGallery = () => dialog.content.querySelector('input[name="mediaInsertType"]:checked')?.value === "gallery";
        // Galleries always use the same thumbnail size.
        dialog.content.querySelectorAll('input[name="mediaInsertType"]').forEach((input) => {
            input.addEventListener("change", () => {
                if (sizeSelect !== null) {
                    sizeSelect.disabled = isGallery();
                }
            });
        });
        return new Promise((resolve) => {
            dialog.addEventListener("primary", () => {
                resolve({
                    size: sizeSelect !== null ? sizeSelect.value : "original",
                    gallery: isGallery(),
                });
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
    function insertIntoCkeditor(ckeditor, media, size, gallery = false) {
        if (gallery) {
            const imageIds = media.filter((item) => item.isImage).map((item) => item.mediaID);
            if (imageIds.length > 0) {
                ckeditor.insertText(`[wsmg='${imageIds.join(",")}'][/wsmg]`);
            }
        }
        for (const item of media) {
            if (item.isImage) {
                if (gallery) {
                    continue;
                }
                const effectiveSize = getEffectiveSize(item, size);
                ckeditor.insertHtml(`<img src="${(0, StringUtil_1.escapeHTML)(item.editorLinks[effectiveSize])}" class="image woltlabSuiteMedia" data-media-id="${item.mediaID}" data-media-size="${effectiveSize}">`);
            }
            else {
                ckeditor.insertText(`[wsm='${item.mediaID}'][/wsm]`);
            }
        }
    }
});
