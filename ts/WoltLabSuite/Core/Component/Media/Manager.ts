/**
 * Shows the media manager in a dialog to insert or select media files.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { getMedia, MediaData } from "WoltLabSuite/Core/Api/Media/GetMedia";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { editMedia, setupMediaUpload } from "./Upload";

export type MediaManagerMode = "editor" | "select";

export type MediaManagerOptions = {
  mode: MediaManagerMode;
  imagesOnly?: boolean;
  title?: string;
  /**
   * File that is uploaded right away, the resulting media file is returned
   * once the upload has been completed.
   */
  file?: File;
};

/**
 * Opens the media manager and resolves with the media files that the user has
 * inserted or selected, an empty list is returned if the dialog was closed.
 */
export async function openMediaManager(options: MediaManagerOptions): Promise<MediaData[]> {
  const dialog = await dialogFactory()
    .usingListView()
    .fromPreset(
      options.title || getPhrase(options.mode === "select" ? "wcf.media.chooseFile" : "wcf.media.manager"),
      "wcf\\system\\listView\\user\\MediaManagerListView",
      new Map([
        ["mode", options.mode],
        ["imagesOnly", options.imagesOnly ? "1" : "0"],
      ]),
      undefined,
      "uploadTime",
      "DESC",
    );

  const listViewItems = dialog.content.querySelector<HTMLElement>(".listView__items")!;
  const uploadButton = dialog.content.querySelector<HTMLElement>("woltlab-core-file-upload");

  return new Promise<MediaData[]>((resolve) => {
    let completed = false;

    const complete = async (mediaIds: number[]): Promise<void> => {
      if (completed) {
        return;
      }
      completed = true;

      const media = await Promise.all(mediaIds.map((mediaId) => getMedia(mediaId)));

      dialog.close();
      resolve(media);
    };

    dialog.content.addEventListener("click", (event) => {
      const button = (event.target as HTMLElement).closest<HTMLElement>(
        ".mediaManager__insertButton, .mediaManager__selectButton",
      );
      if (button !== null) {
        void complete([parseInt(button.dataset.objectId!)]);
      }
    });

    listViewItems.addEventListener("bulk-interaction", (event: CustomEvent<DOMStringMap>) => {
      if (event.detail.bulkInteraction === "insert") {
        void complete(JSON.parse(event.detail.objectIds!) as number[]);
      }
    });

    if (uploadButton !== null) {
      setupMediaUpload(uploadButton, {
        uploadCompleted: (mediaId) => {
          if (options.file !== undefined) {
            void complete([mediaId]);
          }
        },
        allUploadsCompleted: async (mediaIds) => {
          if (!listViewItems.isConnected) {
            return;
          }

          // Reload the list only once after a batch of files has been uploaded.
          listViewItems.dispatchEvent(new CustomEvent("interaction:invalidate-all"));

          // A single uploaded file is edited right away, unless it is inserted directly.
          const editEndpoint = uploadButton.closest<HTMLElement>(".mediaManager__upload")?.dataset.editEndpoint;
          if (options.file !== undefined || mediaIds.length !== 1 || editEndpoint === undefined) {
            return;
          }

          if ((await editMedia(editEndpoint, mediaIds[0])) && listViewItems.isConnected) {
            listViewItems.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
          }
        },
      });

      const file = options.file;
      if (file !== undefined) {
        // The upload button is initialized by a mutation observer.
        window.setTimeout(() => {
          uploadButton.dispatchEvent(
            new CustomEvent("upload:files", {
              detail: { files: [file] },
            }),
          );
        }, 0);
      }
    }

    dialog.addEventListener("afterClose", () => {
      // The selection is stored per list view and would otherwise be kept forever.
      listViewItems.dispatchEvent(new CustomEvent("interaction:reset-selection"));

      if (!completed) {
        completed = true;
        resolve([]);
      }
    });
  });
}
