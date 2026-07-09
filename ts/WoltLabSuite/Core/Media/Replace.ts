/**
 * Uploads replacemnts for media files.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.3
 * @woltlabExcludeBundle tiny
 */

import * as Core from "../Core";
import { MediaUploadAjaxResponseData, MediaUploadError, MediaUploadOptions } from "./Data";
import MediaUpload from "./Upload";
import { getPhrase } from "WoltLabSuite/Core/Language";
import DomUtil from "../Dom/Util";
import * as DomChangeListener from "../Dom/Change/Listener";
import { showDefaultSuccessSnackbar } from "../Component/Snackbar";

class MediaReplace extends MediaUpload {
  protected readonly _mediaID: number;

  constructor(mediaID: number, buttonContainerId: string, targetId: string, options: Partial<MediaUploadOptions>) {
    super(
      buttonContainerId,
      targetId,
      Core.extend(options, {
        action: "replaceFile",
      }),
    );

    this._mediaID = mediaID;
  }

  protected _createButton(): void {
    super._createButton();

    this._button.classList.add("small");

    this._button.querySelector("span")!.textContent = getPhrase("wcf.media.button.replaceFile");
  }

  protected _createFileElement(): HTMLElement {
    return this._target;
  }

  protected _getFormData(): ArbitraryObject {
    return {
      objectIDs: [this._mediaID],
    };
  }

  protected _success(uploadId: number, data: MediaUploadAjaxResponseData): void {
    this._fileElements[uploadId].forEach((file) => {
      const internalFileId = file.dataset.internalFileId!;
      const media = data.returnValues.media[internalFileId];

      if (media) {
        if (media.isImage) {
          this._target.innerHTML = media.smallThumbnailTag;
        }

        document.getElementById("mediaFilename")!.textContent = media.filename;
        document.getElementById("mediaFilesize")!.textContent = media.formattedFilesize;
        if (media.isImage) {
          document.getElementById("mediaImageDimensions")!.textContent = media.imageDimensions;
        }
        document.getElementById("mediaUploader")!.innerHTML = media.userLinkElement;

        this._options.mediaEditor!.updateData(media);

        // Remove existing error messages.
        DomUtil.innerError(this._buttonContainer, "");

        showDefaultSuccessSnackbar();
      } else {
        let error: MediaUploadError = data.returnValues.errors[internalFileId];
        if (!error) {
          error = {
            errorType: "uploadFailed",
            filename: file.dataset.filename!,
          };
        }

        DomUtil.innerError(
          this._buttonContainer,
          getPhrase("wcf.media.upload.error." + error.errorType, {
            filename: error.filename,
          }),
        );
      }

      DomChangeListener.trigger();
    });
  }
}

export = MediaReplace;
