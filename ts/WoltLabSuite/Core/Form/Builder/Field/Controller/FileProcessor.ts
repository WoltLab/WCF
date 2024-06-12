/**
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */

import WoltlabCoreFileElement from "WoltLabSuite/Core/Component/File/woltlab-core-file";
import DomUtil from "WoltLabSuite/Core/Dom/Util";

export class FileProcessor {
  readonly #container: HTMLElement;
  readonly #uploadButton: WoltlabCoreFileUploadElement;
  readonly #files = new Map<number, WoltlabCoreFileElement>();
  readonly #fieldId: string;

  constructor(fieldId: string, values: string[]) {
    this.#fieldId = fieldId;

    this.#container = document.getElementById(fieldId + "Container")!;
    if (this.#container === null) {
      throw new Error("Unknown field with id '" + fieldId + "'");
    }

    values.forEach((html) => {
      const element = document.createElement("template");
      DomUtil.setInnerHtml(element, html);

      void this.#registerFile(element.content.querySelector<WoltlabCoreFileElement>("woltlab-core-file")!);
    });

    this.#uploadButton = this.#container.querySelector("woltlab-core-file-upload") as WoltlabCoreFileUploadElement;
    this.#uploadButton.addEventListener("uploadStart", (event: CustomEvent<WoltlabCoreFileElement>) => {
      void this.#registerFile(event.detail);
    });
  }

  async #registerFile(element: WoltlabCoreFileElement): Promise<void> {
    this.#uploadButton.insertAdjacentElement("beforebegin", element);

    await element.ready;
    this.#files.set(element.fileId!, element);
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = this.#fieldId + (this.#uploadButton.maximumCount === 1 ? "" : "[]");
    input.value = element.fileId!.toString();
    element.insertAdjacentElement("afterend", input);

    if (this.#uploadButton.maximumCount === 1) {
      // single file upload
      element.dataset.previewUrl = element.link!;
      element.unbounded = true;
    }
  }
}
