/**
 * Uploads file via AJAX.
 *
 * @author  Joshua Ruesweg, Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since  5.2
 * @woltlabExcludeBundle tiny
 */

import { ResponseData } from "../../Ajax/Data";
import * as Core from "../../Core";
import { FileCollection, FileLikeObject, UploadId, UploadOptions } from "../../Upload/Data";
import { default as DeleteHandler } from "./Delete";
import DomUtil from "../../Dom/Util";
import * as Language from "../../Language";
import Upload from "../../Upload";
import { FileUploadHandler } from "./Data";

interface FileUploadOptions extends UploadOptions {
  // image preview
  imagePreview: boolean;
  // max files
  maxFiles: number | null;

  internalId: string;
}

interface FileData {
  filesize: number;
  icon: string;
  image: string | null;
  imageHeight: number | null;
  imageWidth: number | null;
  uniqueFileId: string;
}

interface ErrorData {
  errorMessage: string;
}

interface AjaxResponse {
  error: ErrorData[];
  files: FileData[];
}

class FileUpload extends Upload<FileUploadOptions> implements FileUploadHandler {
  protected readonly _deleteHandler: DeleteHandler;

  constructor(buttonContainerId: string, targetId: string, options: Partial<FileUploadOptions>) {
    options = options || {};

    if (options.internalId === undefined) {
      throw new Error("Missing internal id.");
    }

    // set default options
    options = Core.extend(
      {
        // image preview
        imagePreview: false,
        // max files
        maxFiles: null,
        // Dummy value, because it is checked in the base method, without using it with this upload handler.
        className: "invalid",
        // url
        url: `index.php?ajax-file-upload/&t=${Core.getXsrfToken()}`,
      },
      options,
    );

    options.multiple = options.maxFiles === null || (options.maxFiles as number) > 1;

    super(buttonContainerId, targetId, options);

    this.checkMaxFiles();

    this._deleteHandler = new DeleteHandler(buttonContainerId, targetId, this._options.imagePreview, this);
  }

  protected _createFileElement(file: File | FileLikeObject): HTMLElement {
    const element = super._createFileElement(file);
    element.classList.add("box64", "uploadedFile");

    const progress = element.querySelector("progress") as HTMLProgressElement;

    const icon = document.createElement("fa-icon");
    icon.size = 64;
    icon.setIcon("spinner");

    const fileName = element.textContent;
    element.textContent = "";
    element.append(icon);

    const innerDiv = document.createElement("div");
    const fileNameP = document.createElement("p");
    fileNameP.textContent = fileName; // file.name

    const smallProgress = document.createElement("small");
    smallProgress.appendChild(progress);

    innerDiv.appendChild(fileNameP);
    innerDiv.appendChild(smallProgress);

    const div = document.createElement("div");
    div.appendChild(innerDiv);

    const ul = document.createElement("ul");
    ul.className = "buttonGroup";
    div.appendChild(ul);

    // reset element textContent and replace with own element style
    element.append(div);

    return element;
  }

  protected _failure(uploadId: number, data: ResponseData): boolean {
    this._fileElements[uploadId].forEach((fileElement) => {
      fileElement.classList.add("uploadFailed");

      const small = fileElement.querySelector("small") as HTMLElement;
      small.innerHTML = "";

      const icon = fileElement.querySelector("fa-icon")!;
      icon.setIcon("ban");

      const innerError = document.createElement("span");
      innerError.className = "innerError";
      innerError.textContent = Language.get("wcf.upload.error.uploadFailed");
      small.insertAdjacentElement("afterend", innerError);
    });

    throw new Error(`Upload failed: ${data.message as string}`);
  }

  protected _upload(event: Event): UploadId;
  protected _upload(event: null, file: File): UploadId;
  protected _upload(event: null, file: null, blob: Blob): UploadId;
  protected _upload(event: Event | null, file?: File | null, blob?: Blob | null): UploadId {
    const parent = this._buttonContainer.parentElement!;
    const innerError = parent.querySelector("small.innerError:not(.innerFileError)");
    if (innerError) {
      innerError.remove();
    }

    return super._upload(event, file, blob);
  }

  protected _success(uploadId: number, data: AjaxResponse): void {
    this._fileElements[uploadId].forEach((fileElement, index) => {
      if (data.files[index] !== undefined) {
        const fileData = data.files[index];

        if (this._options.imagePreview) {
          if (fileData.image === null) {
            throw new Error("Expect image for uploaded file. None given.");
          }

          fileElement.remove();

          const previewImage = this._target.querySelector("img.previewImage") as HTMLImageElement;
          if (previewImage !== null) {
            previewImage.src = fileData.image;
          } else {
            const image = document.createElement("img");
            image.classList.add("previewImage");
            image.src = fileData.image;
            image.style.setProperty("max-width", "100%", "");
            image.dataset.uniqueFileId = fileData.uniqueFileId;
            this._target.appendChild(image);
          }
        } else {
          fileElement.dataset.uniqueFileId = fileData.uniqueFileId;
          fileElement.querySelector("small")!.textContent = fileData.filesize.toString();
          const icon = fileElement.querySelector("fa-icon")!;

          if (fileData.image !== null) {
            const a = document.createElement("a");
            a.dataset.fancybox = "";
            a.href = fileData.image;
            const image = document.createElement("img");
            image.classList.add("formUploadHandlerContentListImage");
            image.src = fileData.image;
            image.width = fileData.imageWidth!;
            image.height = fileData.imageHeight!;
            a.appendChild(image);
            icon.replaceWith(a);
          } else {
            icon.setIcon(fileData.icon, fileData.icon === "paperclip");
          }
        }
      } else if (data.error[index] !== undefined) {
        const errorData = data["error"][index];

        fileElement.classList.add("uploadFailed");

        const small = fileElement.querySelector("small") as HTMLElement;
        small.innerHTML = "";

        const icon = fileElement.querySelector("fa-icon")!;
        icon.setIcon("ban");

        let innerError = fileElement.querySelector(".innerError") as HTMLElement;
        if (innerError === null) {
          innerError = document.createElement("span");
          innerError.className = "innerError";
          innerError.textContent = errorData.errorMessage;

          small.insertAdjacentElement("afterend", innerError);
        } else {
          innerError.textContent = errorData.errorMessage;
        }
      } else {
        throw new Error(`Unknown uploaded file for uploadId ${uploadId}.`);
      }
    });

    // create delete buttons
    this._deleteHandler.rebuild();
    this.checkMaxFiles();
    Core.triggerEvent(this._target, "change");
  }

  protected _getFormData(): ArbitraryObject {
    return {
      internalId: this._options.internalId,
    };
  }

  validateUpload(files: FileCollection): boolean {
    if (this._options.maxFiles === null || files.length + this.countFiles() <= this._options.maxFiles) {
      return true;
    } else {
      const parent = this._buttonContainer.parentElement!;

      let innerError = parent.querySelector("small.innerError:not(.innerFileError)");
      if (innerError === null) {
        innerError = document.createElement("small");
        innerError.className = "innerError";
        this._buttonContainer.insertAdjacentElement("afterend", innerError);
      }

      innerError.textContent = Language.get("wcf.upload.error.reachedRemainingLimit", {
        maxFiles: this._options.maxFiles - this.countFiles(),
      });

      return false;
    }
  }

  /**
   * Returns the count of the uploaded images.
   */
  countFiles(): number {
    if (this._options.imagePreview) {
      return this._target.querySelector("img") !== null ? 1 : 0;
    } else {
      return this._target.childElementCount;
    }
  }

  /**
   * Checks the maximum number of files and enables or disables the upload button.
   */
  checkMaxFiles(): void {
    if (this._options.maxFiles !== null && this.countFiles() >= this._options.maxFiles) {
      DomUtil.hide(this._button);
    } else {
      DomUtil.show(this._button);
    }
  }
}

export = FileUpload;
