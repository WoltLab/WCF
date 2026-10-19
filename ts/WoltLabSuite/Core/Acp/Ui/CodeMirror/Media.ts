/**
 * Inserts media files into a CodeMirror editor.
 *
 * @author Matthias Schmidt, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { openMediaManager } from "../../../Component/Media/Manager";
import { chooseImageSize, getEffectiveSize } from "../../../Component/Media/Insert";
import { promiseMutex } from "../../../Helper/PromiseMutex";

class AcpUiCodeMirrorMedia {
  protected readonly element: HTMLElement;

  constructor(elementId: string) {
    this.element = document.getElementById(elementId) as HTMLElement;

    const button = document.getElementById(`codemirror-${elementId}-media`)!;
    button.addEventListener(
      "click",
      promiseMutex(() => this.insert()),
    );
  }

  protected async insert(): Promise<void> {
    const media = await openMediaManager({ mode: "editor" });
    if (media.length === 0) {
      return;
    }

    const size = await chooseImageSize(media);
    if (size === undefined) {
      return;
    }

    const content = media
      .map((item) => {
        const effectiveSize = item.isImage ? getEffectiveSize(item, size) : "original";
        const sizeArgument = effectiveSize !== "original" ? ` size="${effectiveSize}"` : "";

        return `{{ media="${item.mediaID}"${sizeArgument} }}`;
      })
      .join("");

    (this.element as any).codemirror.replaceSelection(content);
  }
}

export = AcpUiCodeMirrorMedia;
