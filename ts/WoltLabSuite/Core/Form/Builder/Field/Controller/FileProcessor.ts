/**
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */

import WoltlabCoreFileElement from "WoltLabSuite/Core/Component/File/woltlab-core-file";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { deleteFile } from "WoltLabSuite/Core/Api/Files/DeleteFile";
import { formatFilesize } from "WoltLabSuite/Core/FileUtil";
import DomChangeListener from "WoltLabSuite/Core/Dom/Change/Listener";

export interface ExtraButton {
  title: string;
  icon?: string;
  actionName: string;
}

export class FileProcessor {
  readonly #container: HTMLElement;
  readonly #uploadButton: WoltlabCoreFileUploadElement;
  readonly #fieldId: string;
  #replaceElement: WoltlabCoreFileElement | undefined = undefined;
  readonly #fileInput: HTMLInputElement;
  readonly #imageOnly: boolean;
  readonly #singleFileUpload: boolean;
  readonly #extraButtons: ExtraButton[];

  constructor(
    fieldId: string,
    singleFileUpload: boolean = false,
    imageOnly: boolean = false,
    extraButtons: ExtraButton[] = [],
  ) {
    this.#fieldId = fieldId;
    this.#imageOnly = imageOnly;
    this.#singleFileUpload = singleFileUpload;
    this.#extraButtons = extraButtons;

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
      void this.#registerFile(element, element.parentElement);
    });
  }

  get classPrefix(): string {
    return this.showBigPreview ? "fileUpload__preview__" : "fileList__";
  }

  get showBigPreview(): boolean {
    return this.#singleFileUpload && this.#imageOnly;
  }

  protected addButtons(container: HTMLElement, element: WoltlabCoreFileElement): void {
    const buttons = document.createElement("ul");
    buttons.classList.add("buttonList");
    buttons.classList.add(this.classPrefix + "item__buttons");

    this.addDeleteButton(element, buttons);

    if (this.#singleFileUpload) {
      this.addReplaceButton(element, buttons);
    }

    this.#extraButtons.forEach((button) => {
      const extraButton = document.createElement("button");
      extraButton.type = "button";
      extraButton.classList.add("button", "small");
      if (button.icon === undefined) {
        extraButton.textContent = button.title;
      } else {
        extraButton.classList.add("jsTooltip");
        extraButton.title = button.title;
        extraButton.innerHTML = button.icon;
      }
      extraButton.addEventListener("click", () => {
        element.dispatchEvent(new CustomEvent("fileProcessorCustomAction", { detail: button.actionName }));
      });

      const listItem = document.createElement("li");
      listItem.append(extraButton);
      buttons.append(listItem);
    });

    container.append(buttons);
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
    errorElement.classList.add(this.classPrefix + "item__errorMessage");
    errorElement.textContent = errorMessage;

    element.append(errorElement);
  }

  protected addDeleteButton(element: WoltlabCoreFileElement, buttons: HTMLUListElement): void {
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

  protected addReplaceButton(element: WoltlabCoreFileElement, buttons: HTMLUListElement): void {
    const replaceButton = document.createElement("button");
    replaceButton.type = "button";
    replaceButton.classList.add("button", "small");
    replaceButton.textContent = getPhrase("wcf.global.button.replace");
    replaceButton.addEventListener("click", () => {
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

      const changeEventListener = () => {
        this.#uploadButton.dataset.context = oldContext;
        this.#fileInput.removeEventListener("cancel", cancelEventListener);
      };
      const cancelEventListener = () => {
        this.#uploadButton.dataset.context = oldContext;
        void this.#registerFile(this.#replaceElement!);
        this.#replaceElement = undefined;
        this.#fileInput.removeEventListener("change", changeEventListener);
      };

      this.#fileInput.addEventListener("cancel", cancelEventListener, { once: true });
      this.#fileInput.addEventListener("change", changeEventListener, { once: true });
      this.#fileInput.click();
    });

    const listItem = document.createElement("li");
    listItem.append(replaceButton);
    buttons.append(listItem);
  }

  #unregisterFile(element: WoltlabCoreFileElement): void {
    if (this.showBigPreview) {
      element.parentElement!.innerHTML = "";
    } else {
      element.parentElement!.remove();
    }
  }

  async #registerFile(element: WoltlabCoreFileElement, container: HTMLElement | null = null): Promise<void> {
    if (container === null) {
      if (this.showBigPreview) {
        container = this.#container.querySelector(".fileUpload__preview");
        if (container === null) {
          container = document.createElement("div");
          container.classList.add("fileUpload__preview");
          this.#uploadButton.insertAdjacentElement("beforebegin", container);
        }
        container.append(element);
      } else {
        container = document.createElement("li");
        container.classList.add("fileList__item");
        this.#container.querySelector(".fileList")!.append(container);
      }
    }

    if (!this.showBigPreview) {
      // create a new container for the file element
      const fileContainer = document.createElement("div");
      fileContainer.classList.add(this.classPrefix + "item__file");
      fileContainer.append(element);
      container.append(fileContainer);

      // add filename and filesize information
      const filename = document.createElement("div");
      filename.classList.add(this.classPrefix + "item__filename");
      filename.textContent = element.filename || element.dataset.filename!;

      container.append(filename);

      const fileSize = document.createElement("div");
      fileSize.classList.add(this.classPrefix + "item__fileSize");
      fileSize.textContent = formatFilesize(element.fileSize || parseInt(element.dataset.fileSize!));

      container.append(fileSize);
    }

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

        if (this.showBigPreview) {
          // move the new uploaded file to his own container
          // otherwise the file under `this.#replaceElement` will be marked as failed, too
          const tmpContainer = document.createElement("div");
          tmpContainer.append(element);
          this.#uploadButton.insertAdjacentElement("afterend", tmpContainer);

          container = tmpContainer;
        }
      }
      this.#markElementUploadHasFailed(container, element, reason);
      return;
    }

    if (this.showBigPreview) {
      element.dataset.previewUrl = element.link!;
      element.unbounded = true;
    } else {
      if (element.isImage()) {
        const thumbnail = element.thumbnails.find((thumbnail) => thumbnail.identifier === "tiny");
        if (thumbnail !== undefined) {
          element.thumbnail = thumbnail;
        } else {
          element.dataset.previewUrl = element.link!;
          element.unbounded = false;
        }

        if (element.link !== undefined && element.filename !== undefined) {
          const filenameLink = document.createElement("a");
          filenameLink.href = element.link;
          filenameLink.title = element.filename;
          filenameLink.textContent = element.filename;
          filenameLink.classList.add("jsImageViewer");

          // insert a hidden image element that will be used by the image viewer as the preview image
          const previewImage = document.createElement("img");
          previewImage.src = thumbnail !== undefined ? thumbnail.link : element.link;
          previewImage.alt = element.filename;
          previewImage.style.display = "none";
          previewImage.loading = "lazy";
          previewImage.classList.add(this.classPrefix + "item__previewImage");
          filenameLink.append(previewImage);

          const filenameContainer = container.querySelector("." + this.classPrefix + "item__filename")!;
          filenameContainer.innerHTML = "";
          filenameContainer.append(filenameLink);

          DomChangeListener.trigger();
        }
      }
    }

    const input = document.createElement("input");
    input.type = "hidden";
    input.name = this.#singleFileUpload ? this.#fieldId : this.#fieldId + "[]";
    input.value = element.fileId!.toString();
    container.append(input);

    this.addButtons(container, element);
  }
}
