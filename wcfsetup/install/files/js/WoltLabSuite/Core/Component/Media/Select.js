/**
 * Selects a media file for an input element using the media manager.
 *
 * The button references the id of the hidden input that stores the id of the
 * media file via `data-store` and optionally the id of the element that shows
 * a preview of the selected media file via `data-display`.
 *
 * @author Matthias Schmidt, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "./Manager", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/StringUtil", "WoltLabSuite/Core/FileUtil"], function (require, exports, tslib_1, Manager_1, PromiseMutex_1, Language_1, StringUtil_1, FileUtil) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    FileUtil = tslib_1.__importStar(FileUtil);
    function renderPreview(displayElement, media) {
        if (media.isImage) {
            const link = (0, StringUtil_1.escapeHTML)(media.smallThumbnailLink || media.link);
            displayElement.innerHTML = `<img src="${link}" alt="${(0, StringUtil_1.escapeHTML)(media.altText)}">`;
            return;
        }
        const iconName = FileUtil.getIconNameByFilename(media.filename);
        displayElement.innerHTML = `
    <div class="box48" style="margin-bottom: 10px;">
      <fa-icon size="48" name="${iconName ? `file-${iconName}` : "file"}"></fa-icon>
      <div class="containerHeadline">
        <h3>${(0, StringUtil_1.escapeHTML)(media.filename)}</h3>
        <p>${(0, StringUtil_1.escapeHTML)(media.formattedFilesize)}</p>
      </div>
    </div>`;
    }
    function createRemoveButton(button, onRemove) {
        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.classList.add("button", "jsTooltip");
        removeButton.title = (0, Language_1.getPhrase)("wcf.global.button.delete");
        removeButton.innerHTML = '<fa-icon name="xmark"></fa-icon>';
        removeButton.addEventListener("click", () => onRemove());
        // The buttons are usually part of a button group.
        if (button.parentElement.tagName === "LI") {
            const listItem = document.createElement("li");
            listItem.append(removeButton);
            button.parentElement.insertAdjacentElement("afterend", listItem);
            return listItem;
        }
        button.insertAdjacentElement("afterend", removeButton);
        return removeButton;
    }
    function setup(button, options = {}) {
        const input = document.getElementById(button.dataset.store || "");
        if (!(input instanceof HTMLInputElement)) {
            throw new Error("The button must reference an input element via `data-store`.", { cause: { button } });
        }
        const getDisplayElement = () => button.dataset.display ? document.getElementById(button.dataset.display) : null;
        const removeButton = createRemoveButton(button, () => {
            input.value = "";
            input.dispatchEvent(new Event("change", { bubbles: true }));
            const displayElement = getDisplayElement();
            if (displayElement !== null) {
                displayElement.innerHTML = "";
            }
            removeButton.hidden = true;
        });
        removeButton.hidden = input.value === "";
        button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(async (event) => {
            event.preventDefault();
            const [media] = await (0, Manager_1.openMediaManager)({
                mode: "select",
                imagesOnly: options.imagesOnly,
                title: options.title,
            });
            if (media === undefined) {
                return;
            }
            input.value = media.mediaID.toString();
            input.dispatchEvent(new Event("change", { bubbles: true }));
            const displayElement = getDisplayElement();
            if (displayElement !== null) {
                renderPreview(displayElement, media);
            }
            removeButton.hidden = false;
        }));
    }
});
