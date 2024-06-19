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
  #replaceElement: WoltlabCoreFileElement | undefined = undefined;
  readonly #fileInput: HTMLInputElement;

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
    this.#fileInput = this.#uploadButton.shadowRoot!.querySelector<HTMLInputElement>('input[type="file"]')!;

    this.#container.querySelectorAll<WoltlabCoreFileElement>("woltlab-core-file").forEach((element) => {
      void this.#registerFile(element);
    });
  }

  get isSingleFileUpload(): boolean {
    // TODO check if only images are allowed
    return this.#uploadButton.maximumCount === 1;
  }

  async #registerFile(element: WoltlabCoreFileElement): Promise<void> {
    let elementContainer: HTMLElement | null;

    if (this.isSingleFileUpload) {
      elementContainer = this.#container.querySelector(".fileUpload__preview");
      if (elementContainer === null) {
        elementContainer = document.createElement("div");
        elementContainer.classList.add("fileUpload__preview");
        this.#uploadButton.insertAdjacentElement("beforebegin", elementContainer);
      }
    } else {
      elementContainer = document.createElement("li");
      elementContainer.classList.add("fileUpload__fileList__item");
      this.#container.querySelector(".fileUpload__fileList")!.append(elementContainer);
    }

    elementContainer.append(element);

    try {
      await element.ready;

      if (this.#replaceElement !== undefined) {
        await deleteFile(this.#replaceElement.fileId!);
        this.#replaceElement = undefined;
      }
    } catch (reason) {
      // reinsert the element and show an error message
      if (this.#replaceElement !== undefined) {
        await this.#registerFile(this.#replaceElement);
        this.#replaceElement = undefined;
      }
      this.#markElementUploadHasFailed(elementContainer, element, reason);
      return;
    }

    if (this.isSingleFileUpload) {
      element.dataset.previewUrl = element.link!;
      element.unbounded = true;
    }

    const input = document.createElement("input");
    input.type = "hidden";
    input.name = this.isSingleFileUpload ? this.#fieldId : this.#fieldId + "[]";
    input.value = element.fileId!.toString();
    elementContainer.append(input);

    this.#addButtons(element);
  }

  #markElementUploadHasFailed(container: HTMLElement, element: WoltlabCoreFileElement, reason: unknown): void {
    if (reason instanceof Error) {
      throw reason;
    }
    if (element.apiError === undefined) {
      return;
    }
    let errorMessage: string;

    const validationError = element.apiError.getValidationError();
    if (validationError !== undefined) {
      switch (validationError.param) {
        case "preflight":
          errorMessage = getPhrase(`wcf.upload.error.${validationError.code}`);
          break;

        default:
          errorMessage = "Unrecognized error type: " + JSON.stringify(validationError);
          break;
      }
    } else {
      errorMessage = `Unexpected server error: [${element.apiError.type}] ${element.apiError.message}`;
    }

    container.classList.add("innerError");

    const errorElement = document.createElement("div");
    errorElement.classList.add("fileUpload__fileList__item__errorMessage");
    errorElement.textContent = errorMessage;

    element.append(errorElement);
  }

  #addButtons(element: WoltlabCoreFileElement): void {
    const buttons = document.createElement("ul");
    buttons.classList.add("buttonList");
    if (this.isSingleFileUpload) {
      buttons.classList.add("fileUpload__preview__buttons");
    } else {
      buttons.classList.add("fileUpload__fileList__buttons");
    }

    this.#addDeleteButton(element, buttons);
    this.#addReplaceButton(element, buttons);

    element.parentElement!.append(buttons);
  }

  #addDeleteButton(element: WoltlabCoreFileElement, buttons: HTMLUListElement): void {
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.classList.add("button", "small");
    deleteButton.textContent = getPhrase("wcf.global.button.delete");
    deleteButton.addEventListener("click", async () => {
      await deleteFile(element.fileId!);

      this.#unregisterFile(element);
    });

    const listItem = document.createElement("li");
    listItem.append(deleteButton);
    buttons.append(listItem);
  }

  #unregisterFile(element: WoltlabCoreFileElement): void {
    if (this.isSingleFileUpload) {
      element.parentElement!.innerHTML = "";
    } else {
      element.parentElement!.remove();
    }
  }

  #addReplaceButton(element: WoltlabCoreFileElement, buttons: HTMLUListElement): void {
    const replaceButton = document.createElement("button");
    replaceButton.type = "button";
    replaceButton.classList.add("button", "small");
    replaceButton.textContent = getPhrase("wcf.global.button.replace");
    replaceButton.addEventListener("click", () => {
      // TODO show dialog if the user really wants to replace the file.
      //  the old will be deleted
      this.#replaceElement = element;

      // add to context an extra attribute that the replace button is clicked.
      // after the dialog is closed or the file is selected, the context will be reset to his old value.
      // this is necessary as the serverside validation will otherwise fail.
      const oldContext = this.#uploadButton.dataset.context!;
      const context = JSON.parse(oldContext);
      context.__replace = true;
      this.#uploadButton.dataset.context = JSON.stringify(context);

      // remove the element and all buttons from the dom, but keep them stored in a variable.
      // if the user cancels the dialog or the upload fails, reinsert the old elements and show an error message.
      // if the upload is successful, delete the old file.
      this.#unregisterFile(element);

      this.#fileInput.addEventListener(
        "cancel",
        () => {
          this.#uploadButton.dataset.context = oldContext;
          void this.#registerFile(this.#replaceElement!);
          this.#replaceElement = undefined;
        },
        { once: true },
      );
      this.#fileInput.addEventListener(
        "change",
        () => {
          this.#uploadButton.dataset.context = oldContext;
        },
        { once: true },
      );
      this.#fileInput.click();
    });

    const listItem = document.createElement("li");
    listItem.append(replaceButton);
    buttons.append(listItem);
  }
}
