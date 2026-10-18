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

/**
 * Asks the user for the size in which images are inserted. Resolves with
 * `original` without asking if there is no image or no image offers a
 * thumbnail and with `undefined` if the user canceled the dialog.
 */
export async function chooseImageSize(media: MediaData[]): Promise<MediaSize | undefined> {
  const availableSizes = sizes.filter((size) => media.some((item) => item.isImage && item.editorLinks[size]));
  if (availableSizes.length < 2) {
    return "original";
  }

  const options = availableSizes
    .map(
      (size) =>
        `<option value="${size}"${size === "medium" ? " selected" : ""}>${getPhrase(`wcf.media.insert.imageSize.${size}`)}</option>`,
    )
    .join("");

  const dialog = dialogFactory()
    .fromHtml(
      `<dl>
        <dt><label for="mediaInsertImageSize">${getPhrase("wcf.media.insert.imageSize")}</label></dt>
        <dd><select id="mediaInsertImageSize">${options}</select></dd>
      </dl>`,
    )
    .asPrompt({ primary: getPhrase("wcf.global.button.insert") });

  return new Promise((resolve) => {
    dialog.addEventListener("primary", () => {
      resolve(dialog.content.querySelector<HTMLSelectElement>("select")!.value as MediaSize);
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

export function insertIntoCkeditor(ckeditor: CKEditor, media: MediaData[], size: MediaSize): void {
  for (const item of media) {
    if (item.isImage) {
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
