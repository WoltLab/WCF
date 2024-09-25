/**
 * Initializes modules required for media list view.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Media/List/Upload", "../../Media/Clipboard", "../../Event/Handler", "../../Media/Editor", "../../Dom/Change/Listener"], function (require, exports, tslib_1, Upload_1, MediaClipboard, EventHandler, Editor_1, DomChangeListener) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Upload_1 = tslib_1.__importDefault(Upload_1);
    MediaClipboard = tslib_1.__importStar(MediaClipboard);
    EventHandler = tslib_1.__importStar(EventHandler);
    Editor_1 = tslib_1.__importDefault(Editor_1);
    DomChangeListener = tslib_1.__importStar(DomChangeListener);
    const _mediaEditor = new Editor_1.default({
        _editorSuccess: (media, oldCategoryId, closedEditorDialog = true) => {
            if (media.categoryID != oldCategoryId || closedEditorDialog) {
                window.setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        },
    });
    const _tableBody = document.getElementById("mediaListTableBody");
    let _upload;
    function init(options) {
        options = options || {};
        _upload = new Upload_1.default("uploadButton", "mediaListTableBody", {
            categoryId: options.categoryId,
            multiple: true,
            elementTagSize: 48,
        });
        MediaClipboard.init("wcf\\acp\\page\\MediaListPage", options.hasMarkedItems || false, {
            clipboardDeleteMedia: (mediaIds) => clipboardDeleteMedia(mediaIds),
        });
        addButtonEventListeners();
        DomChangeListener.add("WoltLabSuite/Core/Controller/Media/List", () => addButtonEventListeners());
        EventHandler.add("com.woltlab.wcf.media.upload", "success", (data) => openEditorAfterUpload(data));
    }
    /**
     * Adds the `click` event listeners to the media edit icons in new media table rows.
     */
    function addButtonEventListeners() {
        Array.from(_tableBody.getElementsByClassName("jsMediaEditButton")).forEach((button) => {
            button.classList.remove("jsMediaEditButton");
            button.addEventListener("click", (ev) => edit(ev));
        });
    }
    /**
     * Is called when a media edit icon is clicked.
     */
    function edit(event) {
        _mediaEditor.edit(~~event.currentTarget.dataset.objectId);
    }
    /**
     * Opens the media editor after uploading a single file.
     */
    function openEditorAfterUpload(data) {
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
    function clipboardDeleteMedia(mediaIds) {
        Array.from(document.getElementsByClassName("jsMediaRow")).forEach((media) => {
            const mediaID = ~~media.querySelector(".jsClipboardItem").dataset.objectId;
            if (mediaIds.indexOf(mediaID) !== -1) {
                media.remove();
            }
        });
        if (!document.getElementsByClassName("jsMediaRow").length) {
            window.location.reload();
        }
    }
});
