/**
 * Initializes modules required for media list view.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Controller/Media/List
 */

import MediaListUpload from "../../Media/List/Upload";
import * as MediaClipboard from "../../Media/Clipboard";
import * as EventHandler from "../../Event/Handler";
import MediaEditor from "../../Media/Editor";
import * as DomChangeListener from "../../Dom/Change/Listener";
import { Media, MediaUploadSuccessEventData } from "../../Media/Data";
import MediaManager from "../../Media/Manager/Base";

const _mediaEditor = new MediaEditor({
  _editorSuccess: (media: Media, oldCategoryId: number) => {
    if (media.categoryID != oldCategoryId) {
      window.setTimeout(() => {
        window.location.reload();
      }, 500);
    }
  },
});
const _tableBody = document.getElementById("mediaListTableBody")!;
let _upload: MediaListUpload;

interface MediaListOptions {
  categoryId?: number;
  hasMarkedItems?: boolean;
}

export function init(options: MediaListOptions): void {
  options = options || {};
  _upload = new MediaListUpload("uploadButton", "mediaListTableBody", {
    categoryId: options.categoryId,
    multiple: true,
    elementTagSize: 48,
  });

  MediaClipboard.init("wcf\\acp\\page\\MediaListPage", options.hasMarkedItems || false, {
    clipboardDeleteMedia: (mediaIds: number[]) => clipboardDeleteMedia(mediaIds),
  } as MediaManager);

  addButtonEventListeners();

  DomChangeListener.add("WoltLabSuite/Core/Controller/Media/List", () => addButtonEventListeners());

  EventHandler.add("com.woltlab.wcf.media.upload", "success", (data: MediaUploadSuccessEventData) =>
    openEditorAfterUpload(data),
  );
}

/**
 * Adds the `click` event listeners to the media edit icons in new media table rows.
 */
function addButtonEventListeners(): void {
  Array.from(_tableBody.getElementsByClassName("jsMediaEditButton")).forEach((button) => {
    button.classList.remove("jsMediaEditButton");
    button.addEventListener("click", (ev) => edit(ev));
  });
}

/**
 * Is called when a media edit icon is clicked.
 */
function edit(event: Event): void {
  _mediaEditor.edit(~~(event.currentTarget as HTMLElement).dataset.objectId!);
}

/**
 * Opens the media editor after uploading a single file.
 */
function openEditorAfterUpload(data: MediaUploadSuccessEventData) {
  if (data.upload === _upload && !data.isMultiFileUpload && !_upload.hasPendingUploads()) {
    const keys = Object.keys(data.media);

    if (keys.length) {
      _mediaEditor.edit(data.media[keys[0]]);
    }
  }
}

/**
 * Is called after the media files with the given ids have been deleted via clipboard.
 */
function clipboardDeleteMedia(mediaIds: number[]) {
  Array.from(document.getElementsByClassName("jsMediaRow")).forEach((media) => {
    const mediaID = ~~(media.querySelector(".jsClipboardItem") as HTMLElement).dataset.objectId!;

    if (mediaIds.indexOf(mediaID) !== -1) {
      media.remove();
    }
  });

  if (!document.getElementsByClassName("jsMediaRow").length) {
    window.location.reload();
  }
}
