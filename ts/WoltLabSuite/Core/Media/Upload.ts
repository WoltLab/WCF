/**
 * Uploads media files.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/Upload
 * @woltlabExcludeBundle tiny
 */

import Upload from "../Upload";
import * as Core from "../Core";
import * as DomUtil from "../Dom/Util";
import * as Language from "../Language";
import User from "../User";
import * as DateUtil from "../Date/Util";
import * as FileUtil from "../FileUtil";
import * as DomChangeListener from "../Dom/Change/Listener";
import {
  Media,
  MediaUploadOptions,
  MediaUploadSuccessEventData,
  MediaUploadError,
  MediaUploadAjaxResponseData,
} from "./Data";
import * as EventHandler from "../Event/Handler";
import MediaManager from "./Manager/Base";

class MediaUpload<TOptions extends MediaUploadOptions = MediaUploadOptions> extends Upload<TOptions> {
  protected _categoryId: number | null = null;
  protected readonly _elementTagSize: number;
  protected readonly _mediaManager: MediaManager | null;

  constructor(buttonContainerId: string, targetId: string, options: Partial<TOptions>) {
    super(
      buttonContainerId,
      targetId,
      Core.extend(
        {
          className: "wcf\\data\\media\\MediaAction",
          multiple: options.mediaManager ? true : false,
          singleFileRequests: true,
        },
        options || {},
      ),
    );

    options = options || {};

    this._elementTagSize = 144;
    if (this._options.elementTagSize) {
      this._elementTagSize = this._options.elementTagSize;
    }

    this._mediaManager = null;
    if (this._options.mediaManager) {
      this._mediaManager = this._options.mediaManager;
      delete this._options.mediaManager;
    }
  }

  protected _createFileElement(file: File): HTMLElement {
    let fileElement: HTMLElement;
    if (this._target.nodeName === "OL" || this._target.nodeName === "UL") {
      fileElement = document.createElement("li");
    } else if (this._target.nodeName === "TBODY") {
      const firstTr = this._target.getElementsByTagName("TR")[0] as HTMLTableRowElement;
      const tableContainer = this._target.parentNode!.parentNode! as HTMLElement;
      if (tableContainer.style.getPropertyValue("display") === "none") {
        fileElement = firstTr;

        tableContainer.style.removeProperty("display");

        document.getElementById(this._target.dataset.noItemsInfo!)!.remove();
      } else {
        fileElement = firstTr.cloneNode(true) as HTMLTableRowElement;

        // regenerate id of table row
        fileElement.removeAttribute("id");
        DomUtil.identify(fileElement);
      }

      Array.from(fileElement.getElementsByTagName("TD")).forEach((cell: HTMLTableDataCellElement) => {
        if (cell.classList.contains("columnMark")) {
          cell.querySelectorAll("[data-object-id]").forEach((el: HTMLElement) => DomUtil.hide(el));
        } else if (cell.classList.contains("columnIcon")) {
          cell.querySelectorAll("[data-object-id]").forEach((el: HTMLElement) => DomUtil.hide(el));

          cell.querySelector(".mediaEditButton")!.classList.add("jsMediaEditButton");
          (cell.querySelector(".jsObjectAction[data-object-action='delete']") as HTMLElement).dataset.confirmMessage =
            Language.get("wcf.media.delete.confirmMessage", {
              title: file.name,
            });
        } else if (cell.classList.contains("columnFilename")) {
          // replace copied image with spinner
          let image = cell.querySelector("img");
          if (!image) {
            image = cell.querySelector(".icon48");
          }

          const spinner = document.createElement("span");
          spinner.innerHTML = '<fa-icon size="48" name="spinner"></fa-icon>';
          spinner.classList.add("mediaThumbnail");

          DomUtil.replaceElement(image!, spinner);

          // replace title and uploading user
          const ps = cell.querySelectorAll(".box48 > div > p");
          ps[0].textContent = file.name;

          let userLink = ps[1].getElementsByTagName("A")[0];
          if (!userLink) {
            userLink = document.createElement("a");
            ps[1].getElementsByTagName("SMALL")[0].appendChild(userLink);
          }

          userLink.setAttribute("href", User.getLink());
          userLink.textContent = User.username;
        } else if (cell.classList.contains("columnUploadTime")) {
          cell.innerHTML = "";
          cell.appendChild(DateUtil.getTimeElement(new Date()));
        } else if (cell.classList.contains("columnFilesize")) {
          cell.textContent = FileUtil.formatFilesize(file.size);
        } else if (cell.classList.contains("columnDownloads")) {
          cell.textContent = "0";
        } else {
          // empty the other cells
          cell.innerHTML = "";
        }
      });

      DomUtil.prepend(fileElement, this._target);

      return fileElement;
    } else {
      fileElement = document.createElement("p");
    }

    const thumbnail = document.createElement("div");
    thumbnail.classList.add("mediaThumbnail");
    fileElement.appendChild(thumbnail);

    const fileIcon = document.createElement("fa-icon");
    fileIcon.size = 144;
    fileIcon.setIcon("spinner");
    thumbnail.appendChild(fileIcon);

    const mediaInformation = document.createElement("div");
    mediaInformation.className = "mediaInformation";
    fileElement.appendChild(mediaInformation);

    const p = document.createElement("p");
    p.className = "mediaTitle";
    p.textContent = file.name;
    mediaInformation.appendChild(p);

    const progress = document.createElement("progress");
    progress.max = 100;
    mediaInformation.appendChild(progress);

    DomUtil.prepend(fileElement, this._target);

    DomChangeListener.trigger();

    return fileElement;
  }

  protected _getParameters(): ArbitraryObject {
    const parameters: ArbitraryObject = {
      elementTagSize: this._elementTagSize,
    };
    if (this._mediaManager) {
      parameters.imagesOnly = this._mediaManager.getOption("imagesOnly");

      const categoryId = this._mediaManager.getCategoryId();
      if (categoryId) {
        parameters.categoryID = categoryId;
      }
    }

    return Core.extend(super._getParameters() as object, parameters as object) as ArbitraryObject;
  }

  protected _replaceFileIcon(fileIcon: FaIcon, media: Media, size: number): void {
    if (media.elementTag) {
      fileIcon.outerHTML = media.elementTag;
    } else if (media.tinyThumbnailType) {
      const img = document.createElement("img");
      img.src = media.tinyThumbnailLink;
      img.alt = "";
      img.style.setProperty("width", `${size}px`);
      img.style.setProperty("height", `${size}px`);

      DomUtil.replaceElement(fileIcon, img);
    } else {
      let fileIconName = FileUtil.getIconNameByFilename(media.filename);
      if (fileIconName) {
        fileIconName = `file-${fileIconName}`;
      } else {
        fileIconName = "file";
      }

      fileIcon.setIcon(fileIconName, false);
    }
  }

  protected _success(uploadId: number, data: MediaUploadAjaxResponseData): void {
    const files = this._fileElements[uploadId];
    files.forEach((file) => {
      const internalFileId = file.dataset.internalFileId!;
      const media: Media = data.returnValues.media[internalFileId];

      if (file.tagName === "TR") {
        if (media) {
          // update object id
          file.dataset.objectId = media.mediaID.toString();
          file.querySelectorAll("[data-object-id]").forEach((el: HTMLElement) => {
            el.dataset.objectId = media.mediaID.toString();
            el.style.removeProperty("display");
          });

          file.querySelector(".columnMediaID")!.textContent = media.mediaID.toString();

          // update icon
          this._replaceFileIcon(file.querySelector("fa-icon")!, media, 48);
        } else {
          let error: MediaUploadError = data.returnValues.errors[internalFileId];
          if (!error) {
            error = {
              errorType: "uploadFailed",
              filename: file.dataset.filename!,
            };
          }

          const deleteButton = document.createElement("button");
          deleteButton.classList.add("jsTooltip");
          deleteButton.title = Language.get("wcf.global.button.delete");
          deleteButton.addEventListener("click", () => {
            deleteButton.closest(".mediaFile")!.remove();

            EventHandler.fire("com.woltlab.wcf.media.upload", "removedErroneousUploadRow");
          });

          const fileIcon = file.querySelector("fa-icon")!;
          fileIcon.setIcon("xmark");
          fileIcon.insertAdjacentElement("beforebegin", deleteButton);
          deleteButton.append(fileIcon);

          file.classList.add("uploadFailed");

          const p = file.querySelectorAll(".columnFilename .box48 > div > p")[1] as HTMLElement;

          DomUtil.innerError(
            p,
            Language.get(`wcf.media.upload.error.${error.errorType}`, {
              filename: error.filename,
            }),
          );

          p.remove();
        }
      } else {
        file.querySelector(".mediaInformation progress")!.remove();

        if (media) {
          const fileIcon = file.querySelector(".mediaThumbnail fa-icon") as FaIcon;
          this._replaceFileIcon(fileIcon, media, 144);

          file.classList.add("jsClipboardObject", "mediaFile", "jsObjectActionObject");
          file.dataset.objectId = media.mediaID.toString();

          if (this._mediaManager) {
            this._mediaManager.setupMediaElement(media, file);
            this._mediaManager.addMedia(media, file as HTMLLIElement);
          }
        } else {
          let error: MediaUploadError = data.returnValues.errors[internalFileId];
          if (!error) {
            error = {
              errorType: "uploadFailed",
              filename: file.dataset.filename!,
            };
          }

          const fileIcon = file.querySelector(".mediaThumbnail fa-icon") as FaIcon;
          fileIcon.setIcon("xmark");

          file.classList.add("uploadFailed", "pointer", "jsTooltip");
          file.title = Language.get("wcf.global.button.delete");
          file.addEventListener("click", () => file.remove());

          const title = file.querySelector(".mediaInformation .mediaTitle") as HTMLElement;
          title.textContent = Language.get(`wcf.media.upload.error.${error.errorType}`, {
            filename: error.filename,
          });
        }
      }

      DomChangeListener.trigger();
    });

    EventHandler.fire("com.woltlab.wcf.media.upload", "success", {
      files: files,
      isMultiFileUpload: this._multiFileUploadIds.indexOf(uploadId) !== -1,
      media: data.returnValues.media,
      upload: this,
      uploadId: uploadId,
    } as MediaUploadSuccessEventData);
  }
}

Core.enableLegacyInheritance(MediaUpload);

export = MediaUpload;
