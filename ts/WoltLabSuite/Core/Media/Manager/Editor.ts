/**
 * Provides the media manager dialog for inserting media files.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 * @deprecated 6.3 Use `WoltLabSuite/Core/Component/Media/Manager` instead.
 */

import { Media, MediaInsertType, MediaManagerEditorOptions } from "../Data";
import { openMediaManager } from "../../Component/Media/Manager";
import { chooseImageSize } from "../../Component/Media/Insert";
import { promiseMutex } from "../../Helper/PromiseMutex";

export class MediaManagerEditor {
  readonly #options: Partial<MediaManagerEditorOptions>;

  constructor(options: Partial<MediaManagerEditorOptions>) {
    this.#options = options;

    if (options.ckeditor !== undefined) {
      // Editors are supported by `WoltLabSuite/Core/Component/Ckeditor/Media` out of the box.
      return;
    }

    if (typeof options.buttonClass === "string") {
      const insert = promiseMutex(() => this.#insert());
      document.querySelectorAll<HTMLElement>(`.${options.buttonClass}`).forEach((button) => {
        button.addEventListener("click", () => {
          insert();
        });
      });
    }
  }

  async #insert(): Promise<void> {
    const media = await openMediaManager({
      mode: "editor",
      imagesOnly: this.#options.imagesOnly,
      title: this.#options.dialogTitle,
    });
    if (media.length === 0 || this.#options.callbackInsert === undefined) {
      return;
    }

    const size = await chooseImageSize(media);
    if (size === undefined) {
      return;
    }

    this.#options.callbackInsert(
      new Map(media.map((item) => [item.mediaID, item as Media])),
      MediaInsertType.Separate,
      size === "original" ? undefined : size,
    );
  }
}

export default MediaManagerEditor;
