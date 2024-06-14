/**
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */

import WoltlabCoreFileElement from "WoltLabSuite/Core/Component/File/woltlab-core-file";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { deleteFile } from "WoltLabSuite/Core/Api/Files/DeleteFile";

export class FileProcessor {
  readonly #container: HTMLElement;
  readonly #uploadButton: WoltlabCoreFileUploadElement;
  readonly #fieldId: string;

  constructor(fieldId: string) {
    this.#fieldId = fieldId;

    this.#container = document.getElementById(fieldId + "Container")!;
    if (this.#container === null) {
      throw new Error("Unknown field with id '" + fieldId + "'");
    }

    this.#uploadButton = this.#container.querySelector("woltlab-core-file-upload") as WoltlabCoreFileUploadElement;
    this.#uploadButton.addEventListener("uploadStart", (event: CustomEvent<WoltlabCoreFileElement>) => {
      void this.#registerFile(event.detail);
    });
  }

  async #registerFile(element: WoltlabCoreFileElement): Promise<void> {
    const singleFileUpload = this.#uploadButton.maximumCount === 1;
    let elementContainer: HTMLElement;

    if (singleFileUpload) {
      elementContainer = this.#container.querySelector(".fileUpload__preview")!;
    } else {
      elementContainer = document.createElement("li");
      elementContainer.classList.add("fileUpload__fileList__item");
      this.#container.querySelector(".fileUpload__fileList")!.append(elementContainer);
    }

    elementContainer.append(element);

    await element.ready;

    if (singleFileUpload) {
      element.dataset.previewUrl = element.link!;
      element.unbounded = true;
    }

    const input = document.createElement("input");
    input.type = "hidden";
    input.name = singleFileUpload ? this.#fieldId : this.#fieldId + "[]";
    input.value = element.fileId!.toString();
    elementContainer.append(input);

    this.#addButtons(element, singleFileUpload);
  }

  #addButtons(element: WoltlabCoreFileElement, singleFileUpload: boolean): void {
    const buttons = document.createElement("ul");
    buttons.classList.add("buttonList");
    if (singleFileUpload) {
      buttons.classList.add("fileUpload__preview__buttons");
    } else {
      buttons.classList.add("fileUpload__fileList__buttons");
    }

    this.#addDeleteButton(element, buttons);

    element.parentElement!.append(buttons);
  }

  #addDeleteButton(element: WoltlabCoreFileElement, buttons: HTMLUListElement): void {
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.classList.add("button", "small");
    deleteButton.textContent = getPhrase("wcf.global.button.delete");
    deleteButton.addEventListener("click", async () => {
      await deleteFile(element.fileId!);

      //TODO remove element from DOM
    });

    const listItem = document.createElement("li");
    listItem.append(deleteButton);
    buttons.append(listItem);
  }
}
