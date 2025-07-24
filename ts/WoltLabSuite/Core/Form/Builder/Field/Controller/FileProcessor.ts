/**
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */

import WoltlabCoreFileElement, { Thumbnail } from "WoltLabSuite/Core/Component/File/woltlab-core-file";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { deleteFile } from "WoltLabSuite/Core/Api/Files/DeleteFile";
import DomChangeListener from "WoltLabSuite/Core/Dom/Change/Listener";
import {
  getErrorMessageFromFile,
  insertFileInformation,
  removeUploadProgress,
  trackUploadProgress,
  updateFileInformation,
} from "WoltLabSuite/Core/Component/File/Helper";
import { clearPreviousErrors } from "WoltLabSuite/Core/Component/File/Upload";
import { innerError } from "WoltLabSuite/Core/Dom/Util";
import { confirmationFactory } from "WoltLabSuite/Core/Component/Confirmation";

type FileId = string;
const fileProcessors = new Map<FileId, FileProcessor>();
const callbacks = new Map<FileId, ((values: undefined | number | Set<number>) => void)[]>();

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
  readonly #simpleReplace: boolean;
  readonly #hideDeleteButton: boolean;
  readonly #thumbnailSize?: string;
  readonly #extraButtons: ExtraButton[];
  #uploadResolve: undefined | (() => void);

  constructor(
    fieldId: string,
    singleFileUpload: boolean = false,
    useBigPreview: boolean = false,
    simpleReplace: boolean = false,
    hideDeleteButton: boolean = false,
    thumbnailSize?: string,
    extraButtons: ExtraButton[] = [],
  ) {
    this.#fieldId = fieldId;
    this.#useBigPreview = useBigPreview;
    this.#singleFileUpload = singleFileUpload;
    this.#simpleReplace = simpleReplace;
    this.#hideDeleteButton = hideDeleteButton;
    this.#extraButtons = extraButtons;
    this.#thumbnailSize = thumbnailSize;

    this.#container = document.getElementById(fieldId + "Container")!;
    if (this.#container === null) {
      throw new Error("Unknown field with id '" + fieldId + "'");
    }

    this.#uploadButton = this.#container.querySelector("woltlab-core-file-upload") as WoltlabCoreFileUploadElement;

    if (this.#simpleReplace) {
      this.#uploadButton.addEventListener("shouldUpload", () => {
        const file =
          this.#uploadButton.parentElement!.querySelector<WoltlabCoreFileElement>("woltlab-core-file[file-id]");
        if (!file) {
          return;
        }

        this.#simpleFileReplace(file);
      });
    }

    this.#uploadButton.addEventListener("uploadStart", (event: CustomEvent<WoltlabCoreFileElement>) => {
      if (this.#uploadResolve !== undefined) {
        this.#uploadResolve();
      }

      this.#registerFile(event.detail);
    });
    this.#fileInput = this.#uploadButton.querySelector<HTMLInputElement>('input[type="file"]')!;

    this.#container.querySelectorAll<WoltlabCoreFileElement>("woltlab-core-file").forEach((element) => {
      this.#registerFile(element, element.parentElement, false);
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

    if (!this.#hideDeleteButton) {
      const listItem = document.createElement("li");
      listItem.append(this.getDeleteButton(element));
      buttons.append(listItem);
    }

    if (this.#singleFileUpload && !this.#simpleReplace) {
      const listItem = document.createElement("li");
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

  protected getReplaceButton(element: WoltlabCoreFileElement): HTMLButtonElement {
    const replaceButton = document.createElement("button");
    replaceButton.type = "button";
    replaceButton.classList.add("button", "small");
    replaceButton.textContent = getPhrase("wcf.global.button.replace");
    replaceButton.addEventListener("click", () => {
      const oldContext = this.#startReplaceFile(element);

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
        this.#registerFile(this.#replaceElement!, null, false);
        this.#replaceElement = undefined;
        this.#uploadResolve = undefined;
        this.#fileInput.removeEventListener("change", changeEventListener);
      };

      this.#fileInput.addEventListener("cancel", cancelEventListener, { once: true });
      this.#fileInput.addEventListener("change", changeEventListener, { once: true });

      this.#fileInput.click();
    });

    return replaceButton;
  }

  #markElementUploadHasFailed(element: WoltlabCoreFileElement, reason: unknown): void {
    let errorMessage: string;
    if (reason instanceof Error) {
      errorMessage = reason.message;
    } else {
      errorMessage = getErrorMessageFromFile(element);
    }

    innerError(this.#uploadButton, errorMessage);

    element.remove();

    if (reason instanceof Error) {
      throw reason;
    }
  }

  protected getDeleteButton(element: WoltlabCoreFileElement): HTMLButtonElement {
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.classList.add("button", "small");
    deleteButton.textContent = getPhrase("wcf.global.button.delete");
    deleteButton.addEventListener("click", async () => {
      if (!(await confirmationFactory().delete())) {
        return;
      }

      const result = await deleteFile(element.fileId!);
      if (result.ok) {
        this.#unregisterFile(element);

        notifyValueChange(this.#fieldId, this.values);
      } else {
        let container: HTMLElement = element;
        if (!this.#useBigPreview) {
          container = container.parentElement!;
        }

        if (result.error.code === "permission_denied") {
          innerError(container, getPhrase("wcf.upload.error.delete.permissionDenied"), true);
        } else {
          innerError(container, result.error.message ?? getPhrase("wcf.upload.error.delete.unknownError"));
        }
      }
    });

    return deleteButton;
  }

  #simpleFileReplace(oldFile: WoltlabCoreFileElement) {
    const oldContext = this.#startReplaceFile(oldFile);

    const cropCancelledEvent = () => {
      this.#uploadResolve = undefined;
      this.#uploadButton.dataset.context = oldContext;
      this.#registerFile(this.#replaceElement!, null, false);
      this.#replaceElement = undefined;
    };

    this.#uploadButton.addEventListener("cancel", cropCancelledEvent, { once: true });

    void new Promise<void>((resolve) => {
      this.#uploadResolve = resolve;
    }).then(() => {
      this.#uploadResolve = undefined;
      this.#uploadButton.dataset.context = oldContext;
      this.#uploadButton.removeEventListener("cancel", cropCancelledEvent);
    });
  }

  #startReplaceFile(element: WoltlabCoreFileElement): string {
    // Add to context an extra attribute that the replace button is clicked.
    // After the dialog is closed or the file is selected, the context will be reset to his old value.
    // This is necessary as the serverside validation will otherwise fail.
    const oldContext = this.#uploadButton.dataset.context!;
    const context = JSON.parse(oldContext);
    context.__replace = true;
    this.#uploadButton.dataset.context = JSON.stringify(context);

    this.#replaceElement = element;
    this.#unregisterFile(element);

    return oldContext;
  }

  #unregisterFile(element: WoltlabCoreFileElement): void {
    if (this.#useBigPreview) {
      element.parentElement!.innerHTML = "";
    } else {
      element.parentElement!.parentElement!.remove();
    }
  }

  #registerFile(
    element: WoltlabCoreFileElement,
    container: HTMLElement | null = null,
    notifyCallback: boolean = true,
  ): void {
    if (container === null) {
      if (this.#useBigPreview) {
        container = this.#container.querySelector(".fileUpload__preview");
        if (container === null) {
          container = document.createElement("div");
          container.classList.add("fileUpload__preview");
          this.#uploadButton.insertAdjacentElement("afterbegin", container);
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

      element.addEventListener("file:update-data", () => {
        updateFileInformation(container!, element);
      });
    }

    trackUploadProgress(container, element);

    element.ready
      .then(() => {
        if (this.#replaceElement !== undefined) {
          void deleteFile(this.#replaceElement.fileId!);
          this.#replaceElement = undefined;
        }
        this.#fileInitializationCompleted(element, container!, notifyCallback);
      })
      .catch((reason) => {
        if (this.#replaceElement !== undefined) {
          this.#registerFile(this.#replaceElement, null, false);
          this.#replaceElement = undefined;

          if (this.#useBigPreview) {
            // `this.#replaceElement` need a new container, otherwise the element will be marked as erroneous, too.
            const tmpContainer = document.createElement("div");
            tmpContainer.append(element);
            this.#uploadButton.insertAdjacentElement("afterend", tmpContainer);

            container = tmpContainer;
          }
        }
        this.#markElementUploadHasFailed(element, reason);
      })
      .finally(() => {
        removeUploadProgress(container!);
      });
  }

  #fileInitializationCompleted(
    element: WoltlabCoreFileElement,
    container: HTMLElement,
    notifyCallback: boolean = true,
  ): void {
    if (this.#useBigPreview) {
      setThumbnail(
        element,
        element.thumbnails.find((thumbnail) => thumbnail.identifier === this.#thumbnailSize),
        true,
      );
    } else {
      if (element.isImage()) {
        const thumbnailSize = this.#thumbnailSize ?? "tiny";

        const thumbnail = element.thumbnails.find((thumbnail) => thumbnail.identifier === thumbnailSize);
        setThumbnail(element, thumbnail);

        if (element.link !== undefined && element.filename !== undefined) {
          const filenameLink = document.createElement("a");
          filenameLink.href = element.link;
          filenameLink.title = element.filename;
          filenameLink.textContent = element.filename;
          filenameLink.dataset.fancybox = "";
          filenameLink.dataset.caption = element.filename;

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

    if (notifyCallback) {
      notifyValueChange(this.#fieldId, this.values);
    }
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

function setThumbnail(element: WoltlabCoreFileElement, thumbnail?: Thumbnail, unbounded: boolean = false) {
  if (unbounded) {
    element.dataset.previewUrl = thumbnail !== undefined ? thumbnail.link : element.link;
  } else if (thumbnail !== undefined) {
    element.thumbnail = thumbnail;
  }

  element.unbounded = unbounded;
}

export function getValues(fieldId: string): undefined | number | Set<number> {
  const field = fileProcessors.get(fieldId);
  if (field === undefined) {
    throw new Error("Unknown field with id '" + fieldId + "'");
  }

  return field.values;
}

/**
 * Registers a callback that will be called when the value of the field changes.
 *
 * @since 6.2
 */
export function registerCallback(fieldId: string, callback: (values: undefined | number | Set<number>) => void): void {
  if (!callbacks.has(fieldId)) {
    callbacks.set(fieldId, []);
  }

  callbacks.get(fieldId)!.push(callback);
}

/**
 * @since 6.2
 */
export function unregisterCallback(
  fieldId: string,
  callback: (values: undefined | number | Set<number>) => void,
): void {
  callbacks.set(fieldId, callbacks.get(fieldId)?.filter((registeredCallback) => registeredCallback !== callback) ?? []);
}

function notifyValueChange(fieldId: string, values: undefined | number | Set<number>): void {
  callbacks.get(fieldId)?.forEach((callback) => callback(values));
}
