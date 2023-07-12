/**
 * Forwards upload requests from the editor to the media system.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */

import { dispatchToCkeditor, listenToCkeditor } from "./Event";

type UploadResult = {
  [key: string]: unknown;
  urls: {
    default: string;
  };
};

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
  const payload: UploadMediaEventPayload = { abortController, file };

  dispatchToCkeditor(element).uploadMedia(payload);

  // The media system works differently compared to the
  // attachments, because uploading a file will offer
  // the user to insert the content in different formats.
  //
  // Rejecting the upload promise will cause CKEditor to
  // stop caring about the file so that we regain control.
  return Promise.reject();
}

export function setup(element: HTMLElement): void {
  listenToCkeditor(element)
    .setupConfiguration(({ configuration, features }) => {
      (configuration as any).woltlabMedia = {
        resolveMediaUrl(mediaId: number, mediaSize: string) {
          let thumbnail = "";
          if (mediaSize !== "original") {
            thumbnail = `&thumbnail=${mediaSize}`;
          }

          return `${window.WSC_API_URL}index.php?media/${mediaId}/${thumbnail}`;
        },
      };

      if (features.attachment || !features.media) {
        return;
      }

      // TODO: The typings do not include our custom plugins yet.
      (configuration as any).woltlabUpload = {
        uploadImage: (file: File, abortController: AbortController) => uploadMedia(element, file, abortController),
        uploadOther: (file: File) => uploadMedia(element, file),
      };
    })
    .ready(({ ckeditor }) => {
      if (!ckeditor.features.media) {
        return;
      }

      void import("../../Media/Manager/Editor").then(({ MediaManagerEditor }) => {
        new MediaManagerEditor({
          ckeditor,
        });
      });
    });
}
