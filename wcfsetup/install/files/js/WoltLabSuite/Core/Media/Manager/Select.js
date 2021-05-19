/**
 * Provides the media manager dialog for selecting media for input elements.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/Manager/Select
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "./Base", "../../Core", "../../Dom/Traverse", "../../FileUtil", "../../Language", "../../Ui/Dialog", "../../Dom/Util"], function (require, exports, tslib_1, Base_1, Core, DomTraverse, FileUtil, Language, UiDialog, Util_1) {
    "use strict";
    Base_1 = tslib_1.__importDefault(Base_1);
    Core = tslib_1.__importStar(Core);
    DomTraverse = tslib_1.__importStar(DomTraverse);
    FileUtil = tslib_1.__importStar(FileUtil);
    Language = tslib_1.__importStar(Language);
    UiDialog = tslib_1.__importStar(UiDialog);
    Util_1 = tslib_1.__importDefault(Util_1);
    class MediaManagerSelect extends Base_1.default {
        constructor(options) {
            super(options);
            this._activeButton = null;
            this._storeElements = new WeakMap();
            this._buttons = document.getElementsByClassName(this._options.buttonClass || "jsMediaSelectButton");
            Array.from(this._buttons).forEach((button) => {
                // only consider buttons with a proper store specified
                const store = button.dataset.store;
                if (store) {
                    const storeElement = document.getElementById(store);
                    if (storeElement && storeElement.tagName === "INPUT") {
                        button.addEventListener("click", (ev) => this._click(ev));
                        this._storeElements.set(button, storeElement);
                        // add remove button
                        const removeButton = document.createElement("p");
                        removeButton.className = "button";
                        button.insertAdjacentElement("afterend", removeButton);
                        const icon = document.createElement("span");
                        icon.className = "icon icon16 fa-times";
                        removeButton.appendChild(icon);
                        if (!storeElement.value) {
                            Util_1.default.hide(removeButton);
                        }
                        removeButton.addEventListener("click", (ev) => this._removeMedia(ev));
                    }
                }
            });
        }
        _addButtonEventListeners() {
            super._addButtonEventListeners();
            if (!this._mediaManagerMediaList)
                return;
            DomTraverse.childrenByTag(this._mediaManagerMediaList, "LI").forEach((listItem) => {
                const chooseIcon = listItem.querySelector(".jsMediaSelectButton");
                if (chooseIcon) {
                    chooseIcon.classList.remove("jsMediaSelectButton");
                    chooseIcon.addEventListener("click", (ev) => this._chooseMedia(ev));
                }
            });
        }
        /**
         * Handles clicking on a media choose icon.
         */
        _chooseMedia(event) {
            if (this._activeButton === null) {
                throw new Error("Media cannot be chosen if no button is active.");
            }
            const target = event.currentTarget;
            const media = this._media.get(~~target.dataset.objectId);
            // save selected media in store element
            const input = document.getElementById(this._activeButton.dataset.store);
            input.value = media.mediaID.toString();
            Core.triggerEvent(input, "change");
            // display selected media
            const display = this._activeButton.dataset.display;
            if (display) {
                const displayElement = document.getElementById(display);
                if (displayElement) {
                    if (media.isImage) {
                        const thumbnailLink = media.smallThumbnailLink ? media.smallThumbnailLink : media.link;
                        const altText = media.altText && media.altText[window.LANGUAGE_ID] ? media.altText[window.LANGUAGE_ID] : "";
                        displayElement.innerHTML = `<img src="${thumbnailLink}" alt="${altText}" />`;
                    }
                    else {
                        let fileIcon = FileUtil.getIconNameByFilename(media.filename);
                        if (fileIcon) {
                            fileIcon = "-" + fileIcon;
                        }
                        displayElement.innerHTML = `
            <div class="box48" style="margin-bottom: 10px;">
              <span class="icon icon48 fa-file${fileIcon}-o"></span>
              <div class="containerHeadline">
                <h3>${media.filename}</h3>
                <p>${media.formattedFilesize}</p>
              </div>
            </div>`;
                    }
                }
            }
            // show remove button
            this._activeButton.nextElementSibling.style.removeProperty("display");
            UiDialog.close(this);
        }
        _click(event) {
            event.preventDefault();
            this._activeButton = event.currentTarget;
            super._click(event);
            if (!this._mediaManagerMediaList) {
                return;
            }
            const storeElement = this._storeElements.get(this._activeButton);
            DomTraverse.childrenByTag(this._mediaManagerMediaList, "LI").forEach((listItem) => {
                if (storeElement.value && storeElement.value == listItem.dataset.objectId) {
                    listItem.classList.add("jsSelected");
                }
                else {
                    listItem.classList.remove("jsSelected");
                }
            });
        }
        getMode() {
            return "select";
        }
        setupMediaElement(media, mediaElement) {
            super.setupMediaElement(media, mediaElement);
            // add media insertion icon
            const buttons = mediaElement.querySelector("nav.buttonGroupNavigation > ul");
            const listItem = document.createElement("li");
            listItem.className = "jsMediaSelectButton";
            listItem.dataset.objectId = media.mediaID.toString();
            buttons.appendChild(listItem);
            listItem.innerHTML =
                '<a><span class="icon icon16 fa-check jsTooltip" title="' +
                    Language.get("wcf.media.button.select") +
                    '"></span> <span class="invisible">' +
                    Language.get("wcf.media.button.select") +
                    "</span></a>";
        }
        /**
         * Handles clicking on the remove button.
         */
        _removeMedia(event) {
            event.preventDefault();
            const removeButton = event.currentTarget;
            const button = removeButton.previousElementSibling;
            removeButton.remove();
            const input = document.getElementById(button.dataset.store);
            input.value = "";
            Core.triggerEvent(input, "change");
            const display = button.dataset.display;
            if (display) {
                const displayElement = document.getElementById(display);
                if (displayElement) {
                    displayElement.innerHTML = "";
                }
            }
        }
    }
    Core.enableLegacyInheritance(MediaManagerSelect);
    return MediaManagerSelect;
});
