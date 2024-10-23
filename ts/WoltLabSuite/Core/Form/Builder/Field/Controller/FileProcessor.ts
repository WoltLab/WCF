/**
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */

import WoltlabCoreFileElement from "WoltLabSuite/Core/Component/File/woltlab-core-file";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { deleteFile } from "WoltLabSuite/Core/Api/Files/DeleteFile";
import DomChangeListener from "WoltLabSuite/Core/Dom/Change/Listener";
import {
  fileInitializationFailed,
  insertFileInformation,
  removeUploadProgress,
  trackUploadProgress,
} from "WoltLabSuite/Core/Component/File/Helper";
import { clearPreviousErrors } from "WoltLabSuite/Core/Component/File/Upload";

type FileId = string;
const fileProcessors = new Map<FileId, FileProcessor>();

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
  readonly #useBigPreview: boolean;
  readonly #singleFileUpload: boolean;
  readonly #extraButtons: ExtraButton[];
  #uploadResolve: undefined | (() => void);

  constructor(
    fieldId: string,
    singleFileUpload: boolean = false,
    useBigPreview: boolean = false,
    extraButtons: ExtraButton[] = [],
  ) {
    this.#fieldId = fieldId;
    this.#useBigPreview = useBigPreview;
    this.#singleFileUpload = singleFileUpload;
    this.#extraButtons = extraButtons;

    this.#container = document.getElementById(fieldId + "Container")!;
    if (this.#container === null) {
      throw new Error("Unknown field with id '" + fieldId + "'");
    }

    this.#uploadButton = this.#container.querySelector("woltlab-core-file-upload") as WoltlabCoreFileUploadElement;
    this.#uploadButton.addEventListener("uploadStart", (event: CustomEvent<WoltlabCoreFileElement>) => {
      if (this.#uploadResolve !== undefined) {
        this.#uploadResolve();
      }

      this.#registerFile(event.detail);
    });
    this.#fileInput = this.#uploadButton.querySelector<HTMLInputElement>('input[type="file"]')!;

    this.#container.querySelectorAll<WoltlabCoreFileElement>("woltlab-core-file").forEach((element) => {
      this.#registerFile(element, element.parentElement);
    });

    fileProcessors.set(fieldId, this);
  }

  get classPrefix(): string {
    return this.#useBigPreview ? "fileUpload__preview__" : "fileList__";
  }

  protected addButtons(container: HTMLElement, element: WoltlabCoreFileElement): void {
    const buttons = document.createElement("ul");
    buttons.classList.add("buttonList");
    buttons.classList.add(this.classPrefix + "item__buttons");

    let listItem = document.createElement("li");
    listItem.append(this.getDeleteButton(element));
    buttons.append(listItem);

    if (this.#singleFileUpload) {
      listItem = document.createElement("li");
      listItem.append(this.getReplaceButton(element));
      buttons.append(listItem);
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
        element.dispatchEvent(
          new CustomEvent("fileProcessorCustomAction", {
            detail: button.actionName,
            bubbles: true,
          }),
        );
      });

      const listItem = document.createElement("li");
      listItem.append(extraButton);
      buttons.append(listItem);
    });

    container.append(buttons);
  }

  #markElementUploadHasFailed(container: HTMLElement, element: WoltlabCoreFileElement, reason: unknown): void {
    fileInitializationFailed(container, element, reason);

    container.classList.add("innerError");
  }

  protected getDeleteButton(element: WoltlabCoreFileElement): HTMLButtonElement {
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.classList.add("button", "small");
    deleteButton.textContent = getPhrase("wcf.global.button.delete");
    deleteButton.addEventListener("click", async () => {
      await deleteFile(element.fileId!);

      this.#unregisterFile(element);
    });

    return deleteButton;
  }

  protected getReplaceButton(element: WoltlabCoreFileElement): HTMLButtonElement {
    const replaceButton = document.createElement("button");
    replaceButton.type = "button";
    replaceButton.classList.add("button", "small");
    replaceButton.textContent = getPhrase("wcf.global.button.replace");
    replaceButton.addEventListener("click", () => {
      // Add to context an extra attribute that the replace button is clicked.
      // After the dialog is closed or the file is selected, the context will be reset to his old value.
      // This is necessary as the serverside validation will otherwise fail.
      const oldContext = this.#uploadButton.dataset.context!;
      const context = JSON.parse(oldContext);
      context.__replace = true;
      this.#uploadButton.dataset.context = JSON.stringify(context);

      this.#replaceElement = element;
      this.#unregisterFile(element);

      clearPreviousErrors(this.#uploadButton);

      const changeEventListener = () => {
        this.#fileInput.removeEventListener("cancel", cancelEventListener);

        // Wait until the upload starts,
        // the request to the server is not synchronized with the end of the `change` event.
        // Otherwise, we would swap the context too soon.
        void new Promise<void>((resolve) => {
          this.#uploadResolve = resolve;
        }).then(() => {
          this.#uploadResolve = undefined;
          this.#uploadButton.dataset.context = oldContext;
        });
      };
      const cancelEventListener = () => {
        this.#uploadButton.dataset.context = oldContext;
        this.#registerFile(this.#replaceElement!);
        this.#replaceElement = undefined;
        this.#fileInput.removeEventListener("change", changeEventListener);
      };

      this.#fileInput.addEventListener("cancel", cancelEventListener, { once: true });
      this.#fileInput.addEventListener("change", changeEventListener, { once: true });
      this.#fileInput.click();
    });

    return replaceButton;
  }

  #unregisterFile(element: WoltlabCoreFileElement): void {
    if (this.#useBigPreview) {
      element.parentElement!.innerHTML = "";
    } else {
      element.parentElement!.parentElement!.remove();
    }
  }

  #registerFile(element: WoltlabCoreFileElement, container: HTMLElement | null = null): void {
    if (container === null) {
      if (this.#useBigPreview) {
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

    if (!this.#useBigPreview) {
      insertFileInformation(container, element);
    }

    trackUploadProgress(container, element);

    element.ready
      .then(() => {
        if (this.#replaceElement !== undefined) {
          void deleteFile(this.#replaceElement.fileId!);
          this.#replaceElement = undefined;
        }
        this.#fileInitializationCompleted(element, container!);
      })
      .catch((reason) => {
        if (this.#replaceElement !== undefined) {
          this.#registerFile(this.#replaceElement);
          this.#replaceElement = undefined;

          if (this.#useBigPreview) {
            // `this.#replaceElement` need a new container, otherwise the element will be marked as erroneous, too.
            const tmpContainer = document.createElement("div");
            tmpContainer.append(element);
            this.#uploadButton.insertAdjacentElement("afterend", tmpContainer);

            container = tmpContainer;
          }
        }
        this.#markElementUploadHasFailed(container!, element, reason);
      })
      .finally(() => {
        removeUploadProgress(container!);
      });
  }

  #fileInitializationCompleted(element: WoltlabCoreFileElement, container: HTMLElement): void {
    if (this.#useBigPreview) {
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
          filenameLink.dataset.fancybox = "";

          // Insert a hidden image element that will be used by the image viewer as the preview image
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

  get values(): undefined | number | Set<number> {
    if (this.#singleFileUpload) {
      const input = this.#container.querySelector<HTMLInputElement>('input[type="hidden"]');
      if (input === null) {
        return undefined;
      }

      return parseInt(input.value);
    }

    return new Set(
      Array.from(this.#container.querySelectorAll<HTMLInputElement>('input[type="hidden"]')).map((input) =>
        parseInt(input.value),
      ),
    );
  }
}

export function getValues(fieldId: string): undefined | number | Set<number> {
  const field = fileProcessors.get(fieldId);
  if (field === undefined) {
    throw new Error("Unknown field with id '" + fieldId + "'");
  }

  return field.values;
}
