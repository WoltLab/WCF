/**
 * Provides the media manager dialog for selecting media for Redactor editors.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import MediaManager from "./Base";
import * as Core from "../../Core";
import { Media, MediaInsertType, MediaManagerEditorOptions, MediaUploadSuccessEventData } from "../Data";
import * as EventHandler from "../../Event/Handler";
import * as DomTraverse from "../../Dom/Traverse";
import * as Language from "../../Language";
import * as UiDialog from "../../Ui/Dialog";
import * as Clipboard from "../../Controller/Clipboard";
import DomUtil from "../../Dom/Util";
import type { UploadMediaEventPayload } from "../../Component/Ckeditor/Media";
import { listenToCkeditor } from "../../Component/Ckeditor/Event";
import { escapeHTML } from "WoltLabSuite/Core/StringUtil";

export class MediaManagerEditor extends MediaManager<MediaManagerEditorOptions> {
  protected _mediaToInsert = new Map<number, Media>();
  protected _mediaToInsertByClipboard = false;
  protected _uploadData?: UploadMediaEventPayload;
  protected _uploadId: number | null = null;

  constructor(options: Partial<MediaManagerEditorOptions>) {
    options = Core.extend(
      {
        callbackInsert: null,
      },
      options,
    );

    super(options);

    this._forceClipboard = true;

    if (this._options.ckeditor === undefined) {
      if (typeof this._options.buttonClass === "string") {
        document.querySelectorAll<HTMLElement>(`.${this._options.buttonClass}`).forEach((button) => {
          button.addEventListener("click", (event) => {
            this._click(event);
          });
        });
      }
    } else {
      const ckeditor = this._options.ckeditor;
      listenToCkeditor(ckeditor.sourceElement).bbcode(({ bbcode }) => {
        if (bbcode !== "media") {
          return false;
        }

        this._click();

        return true;
      });

      if (!ckeditor.features.attachment) {
        listenToCkeditor(ckeditor.sourceElement).uploadMedia((payload) => {
          this._editorUpload(payload);
        });
      }
    }
  }

  protected _addButtonEventListeners(): void {
    super._addButtonEventListeners();

    if (!this._mediaManagerMediaList) {
      return;
    }

    DomTraverse.childrenByTag(this._mediaManagerMediaList, "LI").forEach((listItem) => {
      const insertIcon = listItem.querySelector(".jsMediaInsertButton");
      if (insertIcon) {
        insertIcon.classList.remove("jsMediaInsertButton");
        insertIcon.addEventListener("click", (ev) => this._openInsertDialog(ev));
      }
    });
  }

  /**
   * Builds the dialog to setup inserting media files.
   */
  protected _buildInsertDialog(): void {
    let thumbnailOptions = "";

    this._getThumbnailSizes().forEach((thumbnailSize) => {
      thumbnailOptions +=
        '<option value="' +
        thumbnailSize +
        '">' +
        Language.get("wcf.media.insert.imageSize." + thumbnailSize) +
        "</option>";
    });
    thumbnailOptions += '<option value="original">' + Language.get("wcf.media.insert.imageSize.original") + "</option>";

    const dialog = `
      <div class="section">
        <dl class="thumbnailSizeSelection">
          <dt>${Language.get("wcf.media.insert.imageSize")}</dt>
          <dd>
            <select name="thumbnailSize">
              ${thumbnailOptions}
            </select>
          </dd>
        </dl>
      </div>
      <div class="formSubmit">
        <button type="button" class="button buttonPrimary">${Language.get("wcf.global.button.insert")}</button>
      </div>`;

    UiDialog.open({
      _dialogSetup: () => {
        return {
          id: this._getInsertDialogId(),
          options: {
            onClose: () => this._editorClose(),
            onSetup: (content) => {
              content.querySelector(".buttonPrimary")!.addEventListener("click", (ev) => this._insertMedia(ev));

              DomUtil.show(content.querySelector(".thumbnailSizeSelection") as HTMLElement);
            },
            title: Language.get("wcf.media.insert"),
          },
          source: dialog,
        };
      },
    });
  }

  protected _dialogShow(): void {
    super._dialogShow();

    // check if data needs to be uploaded
    if (this._uploadData) {
      if (this._upload !== null) {
        const uploadId = this._upload.uploadFile(this._uploadData.file);
        this._uploadData.promise = new Promise((resolve) => {
          const uuid = EventHandler.add(
            "com.woltlab.wcf.media.upload",
            "success",
            (data: MediaUploadSuccessEventData) => {
              if (data.uploadId !== uploadId) {
                return;
              }

              EventHandler.remove("com.woltlab.wcf.media.upload", "success", uuid);

              resolve({
                mediaId: data.media[0].mediaID,
                mediaSize: "original",
                url: data.media[0].link,
              });
            },
          );
        });
      }

      this._uploadData = undefined;
    }
  }

  /**
   * Handles pasting and dragging and dropping files into the editor.
   */
  protected _editorUpload(data: UploadMediaEventPayload): void {
    this._uploadData = data;

    UiDialog.open(this);
  }

  /**
   * Returns the id of the insert dialog based on the media files to be inserted.
   */
  protected _getInsertDialogId(): string {
    return [this._id + "Insert", ...this._mediaToInsert.keys()].join("-");
  }

  /**
   * Returns the supported thumbnail sizes (excluding `original`) for all media images to be inserted.
   */
  protected _getThumbnailSizes(): string[] {
    return ["small", "medium", "large"]
      .map((size) => {
        const sizeSupported = Array.from(this._mediaToInsert.values()).every((media) => {
          return media[size + "ThumbnailType"] !== null;
        });

        if (sizeSupported) {
          return size;
        }

        return null;
      })
      .filter((s) => s !== null) as string[];
  }

  /**
   * Inserts media files into the editor.
   */
  protected _insertMedia(event?: Event | null, thumbnailSize?: string, closeEditor = false): void {
    if (closeEditor === undefined) closeEditor = true;

    // update insert options with selected values if method is called by clicking on 'insert' button
    // in dialog
    if (event) {
      UiDialog.close(this._getInsertDialogId());

      const dialogContent = (event.currentTarget as HTMLElement).closest(".dialogContent")!;
      const thumbnailSizeSelect = dialogContent.querySelector("select[name=thumbnailSize]") as HTMLSelectElement;
      thumbnailSize = thumbnailSizeSelect.value;
    }

    if (this._options.callbackInsert !== null) {
      this._options.callbackInsert(this._mediaToInsert, MediaInsertType.Separate, thumbnailSize);
    } else {
      this._mediaToInsert.forEach((media) => this._insertMediaItem(thumbnailSize, media));
    }

    if (this._mediaToInsertByClipboard) {
      Clipboard.unmark("com.woltlab.wcf.media", Array.from(this._mediaToInsert.keys()));
    }

    this._mediaToInsert = new Map<number, Media>();
    this._mediaToInsertByClipboard = false;

    // close manager dialog
    if (closeEditor) {
      UiDialog.close(this);
    }
  }

  /**
   * Inserts a single media item into the editor.
   */
  protected _insertMediaItem(thumbnailSize: string | undefined, media: Media): void {
    const ckeditor = this._options.ckeditor!;

    if (media.isImage) {
      let available = "";
      ["small", "medium", "large", "original"].some((size) => {
        if (media[size + "ThumbnailHeight"] != 0) {
          available = size;

          if (thumbnailSize == size) {
            return true;
          }
        }

        return false;
      });

      thumbnailSize = available;

      if (!thumbnailSize) {
        thumbnailSize = "original";
      }

      let link = media.link;
      if (thumbnailSize !== "original") {
        link = media[thumbnailSize + "ThumbnailLink"];
      }

      ckeditor.insertHtml(
        `<img src="${escapeHTML(link)}" class="woltlabSuiteMedia" data-media-id="${
          media.mediaID
        }" data-media-size="${escapeHTML(thumbnailSize)}">`,
      );
    } else {
      ckeditor.insertText(`[wsm='${media.mediaID}'][/wsm]`);
    }
  }

  /**
   * Is called after media files are successfully uploaded to insert copied media.
   */
  protected _mediaUploaded(data: MediaUploadSuccessEventData): void {
    if (this._uploadId !== null && this._upload === data.upload) {
      if (
        this._uploadId === data.uploadId ||
        (Array.isArray(this._uploadId) && this._uploadId.indexOf(data.uploadId) !== -1)
      ) {
        this._mediaToInsert = new Map<number, Media>(data.media.entries());
        this._insertMedia(null, "medium", false);

        this._uploadId = null;
      }
    }
  }

  /**
   * Handles clicking on the insert button.
   */
  protected _openInsertDialog(event: Event): void {
    const target = event.currentTarget as HTMLElement;

    this.insertMedia([~~target.dataset.objectId!]);
  }

  /**
   * Is called to insert the media files with the given ids into an editor.
   */
  public clipboardInsertMedia(mediaIds: number[]): void {
    this.insertMedia(mediaIds, true);
  }

  /**
   * Prepares insertion of the media files with the given ids.
   */
  public insertMedia(mediaIds: number[], insertedByClipboard?: boolean): void {
    this._mediaToInsert = new Map<number, Media>();
    this._mediaToInsertByClipboard = insertedByClipboard || false;

    // open the insert dialog if all media files are images
    let imagesOnly = true;
    mediaIds.forEach((mediaId) => {
      const media = this._media.get(mediaId)!;
      this._mediaToInsert.set(media.mediaID, media);

      if (!media.isImage) {
        imagesOnly = false;
      }
    });

    if (imagesOnly) {
      const thumbnailSizes = this._getThumbnailSizes();
      if (thumbnailSizes.length) {
        UiDialog.close(this);
        const dialogId = this._getInsertDialogId();
        if (UiDialog.getDialog(dialogId)) {
          UiDialog.openStatic(dialogId, null);
        } else {
          this._buildInsertDialog();
        }
      } else {
        this._insertMedia(undefined, "original");
      }
    } else {
      this._insertMedia();
    }
  }

  public getMode(): string {
    return "editor";
  }

  public setupMediaElement(media: Media, mediaElement: HTMLElement): void {
    super.setupMediaElement(media, mediaElement);

    // add media insertion icon
    const buttons = mediaElement.querySelector("nav.buttonGroupNavigation > ul")!;

    const listItem = document.createElement("li");
    listItem.className = "jsMediaInsertButton";
    listItem.dataset.objectId = media.mediaID.toString();
    buttons.appendChild(listItem);

    listItem.innerHTML = `
      <a class="jsTooltip" title="${Language.get("wcf.global.button.insert")}">
        <fa-icon name="plus"></fa-icon>
        <span class="invisible">${Language.get("wcf.global.button.insert")}</span>
      </a>`;
  }
}

export default MediaManagerEditor;
