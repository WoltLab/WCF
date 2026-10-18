/**
 * Helper functions to insert media files into editors.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { escapeHTML } from "WoltLabSuite/Core/StringUtil";
import type { MediaData, MediaSize } from "WoltLabSuite/Core/Api/Media/GetMedia";
import type { CKEditor } from "WoltLabSuite/Core/Component/Ckeditor";

const sizes: MediaSize[] = ["small", "medium", "large", "original"];

export type InsertOptions = {
  size: MediaSize;
  gallery: boolean;
};

function getAvailableSizes(media: MediaData[]): MediaSize[] {
  return sizes.filter((size) => media.some((item) => item.isImage && item.editorLinks[size]));
}

function renderSizeSelect(availableSizes: MediaSize[]): string {
  const options = availableSizes
    .map(
      (size) =>
        `<option value="${size}"${size === "medium" ? " selected" : ""}>${getPhrase(`wcf.media.insert.imageSize.${size}`)}</option>`,
    )
    .join("");

  return `<dl>
    <dt><label for="mediaInsertImageSize">${getPhrase("wcf.media.insert.imageSize")}</label></dt>
    <dd><select id="mediaInsertImageSize">${options}</select></dd>
  </dl>`;
}

/**
 * Asks the user for the size in which images are inserted. Resolves with
 * `original` without asking if there is no image or no image offers a
 * thumbnail and with `undefined` if the user canceled the dialog.
 */
export async function chooseImageSize(media: MediaData[]): Promise<MediaSize | undefined> {
  const options = await chooseInsertOptions(media, false);

  return options?.size;
}

/**
 * Asks the user for the size in which images are inserted and whether
 * multiple images are inserted as a gallery. Resolves without asking if
 * there is nothing to choose and with `undefined` if the user canceled the
 * dialog.
 */
export async function chooseInsertOptions(media: MediaData[], allowGallery = true): Promise<InsertOptions | undefined> {
  const availableSizes = getAvailableSizes(media);
  const canInsertGallery = allowGallery && media.filter((item) => item.isImage).length >= 2;
  if (availableSizes.length < 2 && !canInsertGallery) {
    return { size: "original", gallery: false };
  }

  let html = "";
  if (canInsertGallery) {
    html += `<dl>
      <dt>${getPhrase("wcf.media.insert.type")}</dt>
      <dd>
        <label><input type="radio" name="mediaInsertType" value="separate" checked> ${getPhrase("wcf.media.insert.type.separate")}</label>
        <label><input type="radio" name="mediaInsertType" value="gallery"> ${getPhrase("wcf.media.insert.type.gallery")}</label>
      </dd>
    </dl>`;
  }
  if (availableSizes.length >= 2) {
    html += renderSizeSelect(availableSizes);
  }

  const dialog = dialogFactory()
    .fromHtml(html)
    .asPrompt({ primary: getPhrase("wcf.global.button.insert") });

  const sizeSelect = dialog.content.querySelector<HTMLSelectElement>("select");
  const isGallery = () =>
    dialog.content.querySelector<HTMLInputElement>('input[name="mediaInsertType"]:checked')?.value === "gallery";

  // Galleries always use the same thumbnail size.
  dialog.content.querySelectorAll<HTMLInputElement>('input[name="mediaInsertType"]').forEach((input) => {
    input.addEventListener("change", () => {
      if (sizeSelect !== null) {
        sizeSelect.disabled = isGallery();
      }
    });
  });

  return new Promise((resolve) => {
    dialog.addEventListener("primary", () => {
      resolve({
        size: sizeSelect !== null ? (sizeSelect.value as MediaSize) : "original",
        gallery: isGallery(),
      });
    });
    dialog.addEventListener("cancel", () => {
      resolve(undefined);
    });

    dialog.show(getPhrase("wcf.media.insert"));
  });
}

/**
 * Returns the largest size of the image that does not exceed the requested size.
 */
export function getEffectiveSize(media: MediaData, size: MediaSize): MediaSize {
  for (let i = sizes.indexOf(size); i >= 0; i--) {
    if (media.editorLinks[sizes[i]]) {
      return sizes[i];
    }
  }

  return "original";
}

export function insertIntoCkeditor(ckeditor: CKEditor, media: MediaData[], size: MediaSize, gallery = false): void {
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

      ckeditor.insertHtml(
        `<img src="${escapeHTML(item.editorLinks[effectiveSize]!)}" class="image woltlabSuiteMedia" data-media-id="${
          item.mediaID
        }" data-media-size="${effectiveSize}">`,
      );
    } else {
      ckeditor.insertText(`[wsm='${item.mediaID}'][/wsm]`);
    }
  }
}
