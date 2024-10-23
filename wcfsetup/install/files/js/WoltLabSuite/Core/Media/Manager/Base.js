/**
 * Provides the media manager dialog.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Core", "../../Language", "../../Permission", "../../Dom/Change/Listener", "../../Event/Handler", "../../Dom/Traverse", "../../Dom/Util", "../../Ui/Dialog", "../../Controller/Clipboard", "../../Ui/Pagination", "../../Ui/Notification", "../../StringUtil", "./Search", "../Upload", "../Editor", "../Clipboard"], function (require, exports, tslib_1, Core, Language, Permission, DomChangeListener, EventHandler, DomTraverse, DomUtil, UiDialog, Clipboard, Pagination_1, UiNotification, StringUtil, Search_1, Upload_1, Editor_1, MediaClipboard) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    Permission = tslib_1.__importStar(Permission);
    DomChangeListener = tslib_1.__importStar(DomChangeListener);
    EventHandler = tslib_1.__importStar(EventHandler);
    DomTraverse = tslib_1.__importStar(DomTraverse);
    DomUtil = tslib_1.__importStar(DomUtil);
    UiDialog = tslib_1.__importStar(UiDialog);
    Clipboard = tslib_1.__importStar(Clipboard);
    Pagination_1 = tslib_1.__importDefault(Pagination_1);
    UiNotification = tslib_1.__importStar(UiNotification);
    StringUtil = tslib_1.__importStar(StringUtil);
    Search_1 = tslib_1.__importDefault(Search_1);
    Upload_1 = tslib_1.__importDefault(Upload_1);
    Editor_1 = tslib_1.__importDefault(Editor_1);
    MediaClipboard = tslib_1.__importStar(MediaClipboard);
    let mediaManagerCounter = 0;
    class MediaManager {
        _forceClipboard = false;
        _hadInitiallyMarkedItems = false;
        _id;
        _listItems = new Map();
        _media = new Map();
        _mediaCategorySelect;
        _mediaEditor = null;
        _mediaManagerMediaList = null;
        _pagination = null;
        _search = null;
        _upload = null;
        _options;
        constructor(options) {
            this._options = Core.extend({
                dialogTitle: Language.get("wcf.media.manager"),
                imagesOnly: false,
                minSearchLength: 3,
            }, options);
            this._id = `mediaManager${mediaManagerCounter++}`;
            if (Permission.get("admin.content.cms.canManageMedia")) {
                this._mediaEditor = new Editor_1.default(this);
            }
            DomChangeListener.add("WoltLabSuite/Core/Media/Manager", () => this._addButtonEventListeners());
            EventHandler.add("com.woltlab.wcf.media.upload", "success", (data) => this._openEditorAfterUpload(data));
        }
        /**
         * Adds click event listeners to media buttons.
         */
        _addButtonEventListeners() {
            if (!this._mediaManagerMediaList || !Permission.get("admin.content.cms.canManageMedia"))
                return;
            DomTraverse.childrenByTag(this._mediaManagerMediaList, "LI").forEach((listItem) => {
                const editIcon = listItem.querySelector(".jsMediaEditButton");
                if (editIcon) {
                    editIcon.classList.remove("jsMediaEditButton");
                    editIcon.addEventListener("click", (ev) => this._editMedia(ev));
                }
            });
        }
        /**
         * Is called when a new category is selected.
         */
        _categoryChange() {
            this._search.search();
        }
        /**
         * Handles clicks on the media manager button.
         */
        _click(event) {
            event?.preventDefault();
            UiDialog.open(this);
        }
        /**
         * Is called if the media manager dialog is closed.
         */
        _dialogClose() {
            // only show media clipboard if editor is open
            if (Permission.get("admin.content.cms.canManageMedia") || this._forceClipboard) {
                Clipboard.hideEditor("com.woltlab.wcf.media");
            }
        }
        /**
         * Initializes the dialog when first loaded.
         */
        _dialogInit(content, data) {
            // store media data locally
            Object.entries(data.returnValues.media || {}).forEach(([mediaId, media]) => {
                this._media.set(~~mediaId, media);
            });
            this._initPagination(~~data.returnValues.pageCount);
            this._hadInitiallyMarkedItems = data.returnValues.hasMarkedItems > 0;
        }
        /**
         * Returns all data to setup the media manager dialog.
         */
        _dialogSetup() {
            return {
                id: this._id,
                options: {
                    onClose: () => this._dialogClose(),
                    onShow: () => this._dialogShow(),
                    title: this._options.dialogTitle,
                },
                source: {
                    after: (content, data) => this._dialogInit(content, data),
                    data: {
                        actionName: "getManagementDialog",
                        className: "wcf\\data\\media\\MediaAction",
                        parameters: {
                            mode: this.getMode(),
                            imagesOnly: this._options.imagesOnly,
                        },
                    },
                },
            };
        }
        /**
         * Is called if the media manager dialog is shown.
         */
        _dialogShow() {
            if (!this._mediaManagerMediaList) {
                const dialog = this.getDialog();
                this._mediaManagerMediaList = dialog.querySelector(".mediaManagerMediaList");
                this._mediaCategorySelect = dialog.querySelector(".mediaManagerCategoryList > select");
                if (this._mediaCategorySelect) {
                    this._mediaCategorySelect.addEventListener("change", () => this._categoryChange());
                }
                // store list items locally
                const listItems = DomTraverse.childrenByTag(this._mediaManagerMediaList, "LI");
                listItems.forEach((listItem) => {
                    this._listItems.set(~~listItem.dataset.objectId, listItem);
                });
                if (Permission.get("admin.content.cms.canManageMedia")) {
                    const uploadButton = UiDialog.getDialog(this).dialog.querySelector(".mediaManagerMediaUploadButton");
                    this._upload = new Upload_1.default(DomUtil.identify(uploadButton), DomUtil.identify(this._mediaManagerMediaList), {
                        mediaManager: this,
                    });
                    EventHandler.add("WoltLabSuite/Core/Ui/Object/Action", "delete", (data) => this.removeMedia(~~data.objectElement.dataset.objectId));
                }
                if (Permission.get("admin.content.cms.canManageMedia") || this._forceClipboard) {
                    MediaClipboard.init("menuManagerDialog-" + this.getMode(), this._hadInitiallyMarkedItems ? true : false, this);
                }
                else {
                    this._removeClipboardCheckboxes();
                }
                this._search = new Search_1.default(this);
                if (!listItems.length) {
                    this._search.hideSearch();
                }
            }
            else {
                MediaClipboard.setMediaManager(this);
            }
            // only show media clipboard if editor is open
            if (Permission.get("admin.content.cms.canManageMedia") || this._forceClipboard) {
                Clipboard.showEditor();
            }
        }
        /**
         * Opens the media editor for a media file.
         */
        _editMedia(event) {
            if (!Permission.get("admin.content.cms.canManageMedia")) {
                throw new Error("You are not allowed to edit media files.");
            }
            UiDialog.close(this);
            const target = event.currentTarget;
            this._mediaEditor.edit(this._media.get(~~target.dataset.objectId));
        }
        /**
         * Re-opens the manager dialog after closing the editor dialog.
         */
        _editorClose() {
            UiDialog.open(this);
        }
        /**
         * Re-opens the manager dialog and updates the media data after successfully editing a media file.
         */
        _editorSuccess(media, oldCategoryId, closedEditorDialog = true) {
            // if the category changed of media changed and category
            // is selected, check if media list needs to be refreshed
            if (this._mediaCategorySelect) {
                const selectedCategoryId = ~~this._mediaCategorySelect.value;
                if (selectedCategoryId) {
                    const newCategoryId = ~~media.categoryID;
                    if (oldCategoryId != newCategoryId &&
                        (oldCategoryId == selectedCategoryId || newCategoryId == selectedCategoryId)) {
                        this._search.search();
                    }
                }
            }
            if (closedEditorDialog) {
                UiDialog.open(this);
            }
            this._media.set(~~media.mediaID, media);
            const listItem = this._listItems.get(~~media.mediaID);
            const p = listItem.querySelector(".mediaTitle");
            if (media.isMultilingual) {
                if (media.title && media.title[window.LANGUAGE_ID]) {
                    p.textContent = media.title[window.LANGUAGE_ID];
                }
                else {
                    p.textContent = media.filename;
                }
            }
            else {
                if (media.title && media.title[media.languageID]) {
                    p.textContent = media.title[media.languageID];
                }
                else {
                    p.textContent = media.filename;
                }
            }
            const thumbnail = listItem.querySelector(".mediaThumbnail");
            thumbnail.innerHTML = media.elementTag;
            // Bust browser cache by adding additional parameter.
            const img = thumbnail.querySelector("img");
            if (img) {
                img.src += `&refresh=${Date.now()}`;
            }
        }
        /**
         * Initializes the dialog pagination.
         */
        _initPagination(pageCount, pageNo) {
            if (pageNo === undefined)
                pageNo = 1;
            if (pageCount > 1) {
                const newPagination = document.createElement("div");
                newPagination.className = "paginationBottom jsPagination";
                const oldPagination = UiDialog.getDialog(this).content.querySelector(".jsPagination");
                oldPagination.insertAdjacentElement("beforebegin", newPagination);
                oldPagination.remove();
                this._pagination = new Pagination_1.default(newPagination, {
                    activePage: pageNo,
                    callbackSwitch: (pageNo) => this._search.search(pageNo),
                    maxPage: pageCount,
                });
            }
            else if (this._pagination) {
                DomUtil.hide(this._pagination.getElement());
            }
        }
        /**
         * Removes all media clipboard checkboxes.
         */
        _removeClipboardCheckboxes() {
            this._mediaManagerMediaList.querySelectorAll(".mediaCheckbox").forEach((el) => el.remove());
        }
        /**
         * Opens the media editor after uploading a single file.
         *
         * @since 5.2
         */
        _openEditorAfterUpload(data) {
            if (data.upload === this._upload && !data.isMultiFileUpload && !this._upload.hasPendingUploads()) {
                const keys = Object.keys(data.media);
                if (keys.length) {
                    UiDialog.close(this);
                    this._mediaEditor.edit(this._media.get(~~data.media[keys[0]].mediaID));
                }
            }
        }
        /**
         * Sets the displayed media (after a search).
         */
        _setMedia(media) {
            this._media = new Map(Object.entries(media).map(([mediaId, media]) => [~~mediaId, media]));
            let info = DomTraverse.nextByClass(this._mediaManagerMediaList, "info");
            if (this._media.size) {
                if (info) {
                    DomUtil.hide(info);
                }
            }
            else {
                if (info === null) {
                    info = document.createElement("p");
                    info.className = "info";
                    info.textContent = Language.get("wcf.media.search.noResults");
                }
                DomUtil.show(info);
                DomUtil.insertAfter(info, this._mediaManagerMediaList);
            }
            DomTraverse.childrenByTag(this._mediaManagerMediaList, "LI").forEach((listItem) => {
                if (!this._media.has(~~listItem.dataset.objectId)) {
                    DomUtil.hide(listItem);
                }
                else {
                    DomUtil.show(listItem);
                }
            });
            DomChangeListener.trigger();
            if (Permission.get("admin.content.cms.canManageMedia") || this._forceClipboard) {
                Clipboard.reload();
            }
            else {
                this._removeClipboardCheckboxes();
            }
        }
        /**
         * Adds a media file to the manager.
         */
        addMedia(media, listItem) {
            if (!media.languageID)
                media.isMultilingual = 1;
            this._media.set(~~media.mediaID, media);
            this._listItems.set(~~media.mediaID, listItem);
            if (this._listItems.size === 1) {
                this._search.showSearch();
            }
        }
        /**
         * Is called after the media files with the given ids have been deleted via clipboard.
         */
        clipboardDeleteMedia(mediaIds) {
            mediaIds.forEach((mediaId) => {
                this.removeMedia(~~mediaId);
            });
            UiNotification.show();
        }
        /**
         * Returns the id of the currently selected category or `0` if no category is selected.
         */
        getCategoryId() {
            if (this._mediaCategorySelect) {
                return ~~this._mediaCategorySelect.value;
            }
            return 0;
        }
        /**
         * Returns the media manager dialog element.
         */
        getDialog() {
            return UiDialog.getDialog(this).dialog;
        }
        /**
         * Returns the mode of the media manager.
         */
        getMode() {
            return "";
        }
        /**
         * Returns the media manager option with the given name.
         */
        getOption(name) {
            if (this._options[name]) {
                return this._options[name];
            }
            return null;
        }
        /**
         * Removes a media file.
         */
        removeMedia(mediaId) {
            if (this._listItems.has(mediaId)) {
                // remove list item
                try {
                    this._listItems.get(mediaId).remove();
                }
                catch {
                    // ignore errors if item has already been removed by other code
                }
                this._listItems.delete(mediaId);
                this._media.delete(mediaId);
            }
        }
        /**
         * Changes the displayed media to the previously displayed media.
         */
        resetMedia() {
            // calling WoltLabSuite/Core/Media/Manager/Search.search() reloads the first page of the dialog
            this._search.search();
        }
        /**
         * Sets the media files currently displayed.
         */
        setMedia(media, template, additionalData) {
            const hasMedia = Object.entries(media).length > 0;
            if (hasMedia) {
                const ul = document.createElement("ul");
                ul.innerHTML = template;
                DomTraverse.childrenByTag(ul, "LI").forEach((listItem) => {
                    if (!this._listItems.has(~~listItem.dataset.objectId)) {
                        this._listItems.set(~~listItem.dataset.objectId, listItem);
                        this._mediaManagerMediaList.appendChild(listItem);
                    }
                });
            }
            this._initPagination(additionalData.pageCount, additionalData.pageNo);
            this._setMedia(media);
        }
        /**
         * Sets up a new media element.
         */
        setupMediaElement(media, mediaElement) {
            const mediaInformation = DomTraverse.childByClass(mediaElement, "mediaInformation");
            const buttonGroupNavigation = document.createElement("nav");
            buttonGroupNavigation.className = "jsMobileNavigation buttonGroupNavigation";
            mediaInformation.parentNode.appendChild(buttonGroupNavigation);
            const buttons = document.createElement("ul");
            buttons.className = "buttonList iconList";
            buttonGroupNavigation.appendChild(buttons);
            const listItem = document.createElement("li");
            listItem.className = "mediaCheckbox";
            buttons.appendChild(listItem);
            const a = document.createElement("a");
            listItem.appendChild(a);
            const label = document.createElement("label");
            a.appendChild(label);
            const checkbox = document.createElement("input");
            checkbox.className = "jsClipboardItem";
            checkbox.type = "checkbox";
            checkbox.dataset.objectId = media.mediaID.toString();
            label.appendChild(checkbox);
            if (Permission.get("admin.content.cms.canManageMedia")) {
                const editButton = document.createElement("li");
                editButton.className = "jsMediaEditButton";
                editButton.dataset.objectId = media.mediaID.toString();
                buttons.appendChild(editButton);
                editButton.innerHTML = `
        <a class="jsTooltip" title="${Language.get("wcf.global.button.edit")}">
          <fa-icon name="pencil"></fa-icon>
          <span class="invisible">${Language.get("wcf.global.button.edit")}</span>
        </a>`;
                const deleteButton = document.createElement("li");
                deleteButton.classList.add("jsObjectAction");
                deleteButton.dataset.objectAction = "delete";
                // use temporary title to not unescape html in filename
                const uuid = Core.getUuid();
                deleteButton.dataset.confirmMessage = StringUtil.unescapeHTML(Language.get("wcf.media.delete.confirmMessage", {
                    title: uuid,
                })).replace(uuid, StringUtil.escapeHTML(media.filename));
                buttons.appendChild(deleteButton);
                deleteButton.innerHTML = `
        <a class="jsTooltip" title="${Language.get("wcf.global.button.delete")}">
          <fa-icon name="xmark"></fa-icon>
          <span class="invisible">${Language.get("wcf.global.button.delete")}</span>
        </a>`;
            }
        }
    }
    return MediaManager;
});
