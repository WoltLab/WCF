/**
 * Forwards upload requests from the editor to the media system.
 *
 * @author Alexander Ebert, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */

import { dispatchToCkeditor, listenToCkeditor } from "./Event";
import { normalizePastedFilename } from "./Filename";
import { openMediaManager } from "../Media/Manager";
import { chooseInsertOptions, insertIntoCkeditor } from "../Media/Insert";
import { promiseMutex } from "../../Helper/PromiseMutex";
import type { CKEditor } from "../Ckeditor";

type UploadResult = {
  [key: string]: unknown;
  urls: {
    default: string;
  };
};

/**
 * @deprecated 6.3 The media files are inserted by the media manager.
 */
type MediaData = {
  mediaId: number;
  mediaSize: string;
  url: string;
};

export type UploadMediaEventPayload = {
  abortController?: AbortController;
  file: File;
  promise?: Promise<MediaData>;
};

function uploadMedia(element: HTMLElement, file: File, abortController?: AbortController): Promise<UploadResult> {
  const payload: UploadMediaEventPayload = { abortController, file: normalizePastedFilename(file) };

  dispatchToCkeditor(element).uploadMedia(payload);

  // The media system works differently compared to the
  // attachments, because uploading a file will offer
  // the user to insert the content in different formats.
  //
  // Rejecting the upload promise will cause CKEditor to
  // stop caring about the file so that we regain control.
  return Promise.reject();
}

/**
 * Opens the media manager and inserts the chosen media files. A file that was
 * pasted or dropped into the editor is uploaded right away and inserted once
 * the upload has been completed.
 */
async function insertMedia(ckeditor: CKEditor, file?: File): Promise<void> {
  const media = await openMediaManager({ mode: "editor", file });
  if (media.length === 0) {
    return;
  }

  const options = await chooseInsertOptions(media);
  if (options === undefined) {
    return;
  }

  insertIntoCkeditor(ckeditor, media, options.size, options.gallery);
}

export function setup(element: HTMLElement): void {
  listenToCkeditor(element)
    .setupConfiguration(({ configuration, features }) => {
      if (features.attachment || !features.media) {
        return;
      }

      // TODO: The typings do not include our custom plugins yet.
      configuration.woltlabUpload = {
        uploadImage: (file: File, abortController: AbortController) => uploadMedia(element, file, abortController),
        uploadOther: (file: File) => uploadMedia(element, file),
      };
    })
    .ready(({ ckeditor }) => {
      if (!ckeditor.features.media) {
        return;
      }

      // The media manager is loaded asynchronously, repeated clicks must not open it twice.
      const openManager = promiseMutex(() => insertMedia(ckeditor));
      listenToCkeditor(ckeditor.sourceElement).bbcode(({ bbcode }) => {
        if (bbcode !== "media") {
          return false;
        }

        openManager();

        return true;
      });

      if (!ckeditor.features.attachment) {
        listenToCkeditor(ckeditor.sourceElement).uploadMedia((payload) => {
          void insertMedia(ckeditor, payload.file);
        });
      }
    });
}
