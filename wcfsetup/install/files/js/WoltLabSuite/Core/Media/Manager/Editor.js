/**
 * Provides the media manager dialog for selecting media for Redactor editors.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "./Base", "../../Core", "../../Event/Handler", "../../Dom/Traverse", "../../Language", "../../Ui/Dialog", "../../Controller/Clipboard", "../../Dom/Util", "../../Component/Ckeditor/Event", "WoltLabSuite/Core/StringUtil"], function (require, exports, tslib_1, Base_1, Core, EventHandler, DomTraverse, Language, UiDialog, Clipboard, Util_1, Event_1, StringUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.MediaManagerEditor = void 0;
    Base_1 = tslib_1.__importDefault(Base_1);
    Core = tslib_1.__importStar(Core);
    EventHandler = tslib_1.__importStar(EventHandler);
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Language = tslib_1.__importStar(Language);
    UiDialog = tslib_1.__importStar(UiDialog);
    Clipboard = tslib_1.__importStar(Clipboard);
    Util_1 = tslib_1.__importDefault(Util_1);
    class MediaManagerEditor extends Base_1.default {
        _mediaToInsert = new Map();
        _mediaToInsertByClipboard = false;
        _uploadData;
        _uploadId = null;
        constructor(options) {
            options = Core.extend({
                callbackInsert: null,
            }, options);
            super(options);
            this._forceClipboard = true;
            if (this._options.ckeditor === undefined) {
                if (typeof this._options.buttonClass === "string") {
                    document.querySelectorAll(`.${this._options.buttonClass}`).forEach((button) => {
                        button.addEventListener("click", (event) => {
                            this._click(event);
                        });
                    });
                }
            }
            else {
                const ckeditor = this._options.ckeditor;
                (0, Event_1.listenToCkeditor)(ckeditor.sourceElement).bbcode(({ bbcode }) => {
                    if (bbcode !== "media") {
                        return false;
                    }
                    this._click();
                    return true;
                });
                if (!ckeditor.features.attachment) {
                    (0, Event_1.listenToCkeditor)(ckeditor.sourceElement).uploadMedia((payload) => {
                        this._editorUpload(payload);
                    });
                }
            }
        }
        _addButtonEventListeners() {
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
        _buildInsertDialog() {
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
                                content.querySelector(".buttonPrimary").addEventListener("click", (ev) => this._insertMedia(ev));
                                Util_1.default.show(content.querySelector(".thumbnailSizeSelection"));
                            },
                            title: Language.get("wcf.media.insert"),
                        },
                        source: dialog,
                    };
                },
            });
        }
        _dialogShow() {
            super._dialogShow();
            // check if data needs to be uploaded
            if (this._uploadData) {
                if (this._upload !== null) {
                    const uploadId = this._upload.uploadFile(this._uploadData.file);
                    this._uploadData.promise = new Promise((resolve) => {
                        const uuid = EventHandler.add("com.woltlab.wcf.media.upload", "success", (data) => {
                            if (data.uploadId !== uploadId) {
                                return;
                            }
                            EventHandler.remove("com.woltlab.wcf.media.upload", "success", uuid);
                            resolve({
                                mediaId: data.media[0].mediaID,
                                mediaSize: "original",
                                url: data.media[0].link,
                            });
                        });
                    });
                }
                this._uploadData = undefined;
            }
        }
        /**
         * Handles pasting and dragging and dropping files into the editor.
         */
        _editorUpload(data) {
            this._uploadData = data;
            UiDialog.open(this);
        }
        /**
         * Returns the id of the insert dialog based on the media files to be inserted.
         */
        _getInsertDialogId() {
            return [this._id + "Insert", ...this._mediaToInsert.keys()].join("-");
        }
        /**
         * Returns the supported thumbnail sizes (excluding `original`) for all media images to be inserted.
         */
        _getThumbnailSizes() {
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
                .filter((s) => s !== null);
        }
        /**
         * Inserts media files into the editor.
         */
        _insertMedia(event, thumbnailSize, closeEditor = false) {
            if (closeEditor === undefined)
                closeEditor = true;
            // update insert options with selected values if method is called by clicking on 'insert' button
            // in dialog
            if (event) {
                UiDialog.close(this._getInsertDialogId());
                const dialogContent = event.currentTarget.closest(".dialogContent");
                const thumbnailSizeSelect = dialogContent.querySelector("select[name=thumbnailSize]");
                thumbnailSize = thumbnailSizeSelect.value;
            }
            if (this._options.callbackInsert !== null) {
                this._options.callbackInsert(this._mediaToInsert, "separate" /* MediaInsertType.Separate */, thumbnailSize);
            }
            else {
                this._mediaToInsert.forEach((media) => this._insertMediaItem(thumbnailSize, media));
            }
            if (this._mediaToInsertByClipboard) {
                Clipboard.unmark("com.woltlab.wcf.media", Array.from(this._mediaToInsert.keys()));
            }
            this._mediaToInsert = new Map();
            this._mediaToInsertByClipboard = false;
            // close manager dialog
            if (closeEditor) {
                UiDialog.close(this);
            }
        }
        /**
         * Inserts a single media item into the editor.
         */
        _insertMediaItem(thumbnailSize, media) {
            const ckeditor = this._options.ckeditor;
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
                ckeditor.insertHtml(`<img src="${(0, StringUtil_1.escapeHTML)(link)}" class="woltlabSuiteMedia" data-media-id="${media.mediaID}" data-media-size="${(0, StringUtil_1.escapeHTML)(thumbnailSize)}">`);
            }
            else {
                ckeditor.insertText(`[wsm='${media.mediaID}'][/wsm]`);
            }
        }
        /**
         * Is called after media files are successfully uploaded to insert copied media.
         */
        _mediaUploaded(data) {
            if (this._uploadId !== null && this._upload === data.upload) {
                if (this._uploadId === data.uploadId ||
                    (Array.isArray(this._uploadId) && this._uploadId.indexOf(data.uploadId) !== -1)) {
                    this._mediaToInsert = new Map(data.media.entries());
                    this._insertMedia(null, "medium", false);
                    this._uploadId = null;
                }
            }
        }
        /**
         * Handles clicking on the insert button.
         */
        _openInsertDialog(event) {
            const target = event.currentTarget;
            this.insertMedia([~~target.dataset.objectId]);
        }
        /**
         * Is called to insert the media files with the given ids into an editor.
         */
        clipboardInsertMedia(mediaIds) {
            this.insertMedia(mediaIds, true);
        }
        /**
         * Prepares insertion of the media files with the given ids.
         */
        insertMedia(mediaIds, insertedByClipboard) {
            this._mediaToInsert = new Map();
            this._mediaToInsertByClipboard = insertedByClipboard || false;
            // open the insert dialog if all media files are images
            let imagesOnly = true;
            mediaIds.forEach((mediaId) => {
                const media = this._media.get(mediaId);
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
                    }
                    else {
                        this._buildInsertDialog();
                    }
                }
                else {
                    this._insertMedia(undefined, "original");
                }
            }
            else {
                this._insertMedia();
            }
        }
        getMode() {
            return "editor";
        }
        setupMediaElement(media, mediaElement) {
            super.setupMediaElement(media, mediaElement);
            // add media insertion icon
            const buttons = mediaElement.querySelector("nav.buttonGroupNavigation > ul");
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
    exports.MediaManagerEditor = MediaManagerEditor;
    exports.default = MediaManagerEditor;
});
