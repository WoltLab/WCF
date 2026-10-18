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

import { openMediaManager } from "./Manager";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { escapeHTML } from "WoltLabSuite/Core/StringUtil";
import * as FileUtil from "WoltLabSuite/Core/FileUtil";
import type { MediaData } from "WoltLabSuite/Core/Api/Media/GetMedia";

export type MediaSelectOptions = {
  imagesOnly?: boolean;
  title?: string;
};

function renderPreview(displayElement: HTMLElement, media: MediaData): void {
  if (media.isImage) {
    const link = escapeHTML(media.smallThumbnailLink || media.link);
    displayElement.innerHTML = `<img src="${link}" alt="${escapeHTML(media.altText)}">`;

    return;
  }

  const iconName = FileUtil.getIconNameByFilename(media.filename);
  displayElement.innerHTML = `
    <div class="box48" style="margin-bottom: 10px;">
      <fa-icon size="48" name="${iconName ? `file-${iconName}` : "file"}"></fa-icon>
      <div class="containerHeadline">
        <h3>${escapeHTML(media.filename)}</h3>
        <p>${escapeHTML(media.formattedFilesize)}</p>
      </div>
    </div>`;
}

function createRemoveButton(button: HTMLElement, onRemove: () => void): HTMLElement {
  const removeButton = document.createElement("button");
  removeButton.type = "button";
  removeButton.classList.add("button", "jsTooltip");
  removeButton.title = getPhrase("wcf.global.button.delete");
  removeButton.innerHTML = '<fa-icon name="xmark"></fa-icon>';
  removeButton.addEventListener("click", () => onRemove());

  // The buttons are usually part of a button group.
  if (button.parentElement!.tagName === "LI") {
    const listItem = document.createElement("li");
    listItem.append(removeButton);
    button.parentElement!.insertAdjacentElement("afterend", listItem);

    return listItem;
  }

  button.insertAdjacentElement("afterend", removeButton);

  return removeButton;
}

export function setup(button: HTMLElement, options: MediaSelectOptions = {}): void {
  const input = document.getElementById(button.dataset.store || "");
  if (!(input instanceof HTMLInputElement)) {
    throw new Error("The button must reference an input element via `data-store`.", { cause: { button } });
  }

  const getDisplayElement = (): HTMLElement | null =>
    button.dataset.display ? document.getElementById(button.dataset.display) : null;

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

  button.addEventListener(
    "click",
    promiseMutex(async (event: MouseEvent) => {
      event.preventDefault();

      const [media] = await openMediaManager({
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
    }),
  );
}
