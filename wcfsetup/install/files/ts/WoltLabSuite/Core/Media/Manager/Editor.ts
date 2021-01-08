/**
 * Provides the media manager dialog for selecting media for Redactor editors.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/Manager/Editor
 */

import MediaManager from "./Base";
import * as Core from "../../Core";
import { Media, MediaManagerEditorOptions, MediaUploadSuccessEventData } from "../Data";
import * as EventHandler from "../../Event/Handler";
import * as DomTraverse from "../../Dom/Traverse";
import * as Language from "../../Language";
import * as UiDialog from "../../Ui/Dialog";
import * as Clipboard from "../../Controller/Clipboard";
import { OnDropPayload } from "../../Ui/Redactor/DragAndDrop";
import DomUtil from "../../Dom/Util";

interface PasteFromClipboard {
  blob: Blob;
}

class MediaManagerEditor extends MediaManager<MediaManagerEditorOptions> {
  protected _activeButton;
  protected readonly _buttons: HTMLCollectionOf<HTMLElement>;
  protected _mediaToInsert: Map<number, Media>;
  protected _mediaToInsertByClipboard: boolean;
  protected _uploadData: OnDropPayload | PasteFromClipboard | null;
  protected _uploadId: number | null;

  constructor(options: Partial<MediaManagerEditorOptions>) {
    options = Core.extend(
      {
        callbackInsert: null,
      },
      options,
    );

    super(options);

    this._forceClipboard = true;
    this._activeButton = null;
    const context = this._options.editor ? this._options.editor.core.toolbar()[0] : undefined;
    this._buttons = (context || window.document).getElementsByClassName(
      this._options.buttonClass || "jsMediaEditorButton",
    ) as HTMLCollectionOf<HTMLElement>;
    Array.from(this._buttons).forEach((button) => {
      button.addEventListener("click", (ev) => this._click(ev));
    });
    this._mediaToInsert = new Map<number, Media>();
    this._mediaToInsertByClipboard = false;
    this._uploadData = null;
    this._uploadId = null;

    if (this._options.editor && !this._options.editor.opts.woltlab.attachments) {
      const editorId = this._options.editor.$editor[0].dataset.elementId as string;

      const uuid1 = EventHandler.add("com.woltlab.wcf.redactor2", `dragAndDrop_${editorId}`, (data: OnDropPayload) =>
        this._editorUpload(data),
      );
      const uuid2 = EventHandler.add(
        "com.woltlab.wcf.redactor2",
        `pasteFromClipboard_${editorId}`,
        (data: OnDropPayload) => this._editorUpload(data),
      );

      EventHandler.add("com.woltlab.wcf.redactor2", `destroy_${editorId}`, () => {
        EventHandler.remove("com.woltlab.wcf.redactor2", `dragAndDrop_${editorId}`, uuid1);
        EventHandler.remove("com.woltlab.wcf.redactor2", `dragAndDrop_${editorId}`, uuid2);
      });

      EventHandler.add("com.woltlab.wcf.media.upload", "success", (data) => this._mediaUploaded(data));
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
        <button class="buttonPrimary">${Language.get("wcf.global.button.insert")}</button>
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

  protected _click(event: Event): void {
    this._activeButton = event.currentTarget;

    super._click(event);
  }

  protected _dialogShow(): void {
    super._dialogShow();

    // check if data needs to be uploaded
    if (this._uploadData) {
      const fileUploadData = this._uploadData as OnDropPayload;
      if (fileUploadData.file) {
        this._upload.uploadFile(fileUploadData.file);
      } else {
        const blobUploadData = this._uploadData as PasteFromClipboard;
        this._uploadId = this._upload.uploadBlob(blobUploadData.blob);
      }

      this._uploadData = null;
    }
  }

  /**
   * Handles pasting and dragging and dropping files into the editor.
   */
  protected _editorUpload(data: OnDropPayload): void {
    this._uploadData = data;

    UiDialog.open(this);
  }

  /**
   * Returns the id of the insert dialog based on the media files to be inserted.
   */
  protected _getInsertDialogId(): string {
    return ["mediaInsert", ...this._mediaToInsert.keys()].join("-");
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

    const insertType = "separate";

    // update insert options with selected values if method is called by clicking on 'insert' button
    // in dialog
    if (event) {
      UiDialog.close(this._getInsertDialogId());

      const dialogContent = (event.currentTarget as HTMLElement).closest(".dialogContent")!;
      const thumbnailSizeSelect = dialogContent.querySelector("select[name=thumbnailSize]") as HTMLSelectElement;
      thumbnailSize = thumbnailSizeSelect.value;
    }

    if (this._options.callbackInsert !== null) {
      this._options.callbackInsert(this._mediaToInsert, insertType, thumbnailSize!);
    } else {
      if (insertType === "separate") {
        this._options.editor!.buffer.set();

        this._mediaToInsert.forEach((media) => this._insertMediaItem(thumbnailSize!, media));
      } else {
        this._insertMediaGallery();
      }
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
   * Inserts a series of uploaded images into the editor using a slider.
   */
  protected _insertMediaGallery(): void {
    const mediaIds = Array.from(this._mediaToInsert.keys());

    this._options.editor!.buffer.set();
    this._options.editor!.insert.text("[wsmg='" + mediaIds.join(",") + "'][/wsmg]");
  }

  /**
   * Inserts a single media item into the editor.
   */
  protected _insertMediaItem(thumbnailSize: string, media: Media): void {
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

      this._options.editor!.insert.html(
        `<img src="${link}" class="woltlabSuiteMedia" data-media-id="${media.mediaID}" data-media-size="${thumbnailSize}">`,
      );
    } else {
      this._options.editor!.insert.text(`[wsm='${media.mediaID}'][/wsm]`);
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
      <a>
        <span class="icon icon16 fa-plus jsTooltip" title="${Language.get("wcf.global.button.insert")}"></span>
        <span class="invisible">${Language.get("wcf.global.button.insert")}</span>
      </a>`;
  }
}

Core.enableLegacyInheritance(MediaManagerEditor);

export = MediaManagerEditor;
