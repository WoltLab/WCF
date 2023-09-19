/**
 * Provides the media manager dialog for selecting media for input elements.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "./Base", "../../Core", "../../Dom/Traverse", "../../FileUtil", "../../Language", "../../Ui/Dialog"], function (require, exports, tslib_1, Base_1, Core, DomTraverse, FileUtil, Language, UiDialog) {
    "use strict";
    Base_1 = tslib_1.__importDefault(Base_1);
    Core = tslib_1.__importStar(Core);
    DomTraverse = tslib_1.__importStar(DomTraverse);
    FileUtil = tslib_1.__importStar(FileUtil);
    Language = tslib_1.__importStar(Language);
    UiDialog = tslib_1.__importStar(UiDialog);
    class MediaManagerSelect extends Base_1.default {
        _activeButton = null;
        _buttons;
        _storeElements = new WeakMap();
        constructor(options) {
            super(options);
            this._buttons = document.getElementsByClassName(this._options.buttonClass || "jsMediaSelectButton");
            Array.from(this._buttons).forEach((button) => {
                // only consider buttons with a proper store specified
                const store = button.dataset.store;
                if (store) {
                    const storeElement = document.getElementById(store);
                    if (storeElement && storeElement.tagName === "INPUT") {
                        button.addEventListener("click", (ev) => this._click(ev));
                        this._storeElements.set(button, storeElement);
                        const removeButton = document.createElement("button");
                        removeButton.type = "button";
                        removeButton.classList.add("button", "jsTooltip");
                        removeButton.title = Language.getPhrase("wcf.global.button.delete");
                        removeButton.innerHTML = '<fa-icon name="xmark"></fa-icon>';
                        if (button.parentElement.tagName === "LI") {
                            const listItem = document.createElement("li");
                            listItem.append(removeButton);
                            button.parentElement.insertAdjacentElement("afterend", listItem);
                            if (!storeElement.value) {
                                listItem.hidden = true;
                            }
                        }
                        else {
                            button.insertAdjacentElement("afterend", removeButton);
                            if (!storeElement.value) {
                                removeButton.hidden = true;
                            }
                        }
                        removeButton.addEventListener("click", () => this._removeMedia(button, removeButton));
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
                            fileIcon = "file-" + fileIcon;
                        }
                        else {
                            fileIcon = "file";
                        }
                        displayElement.innerHTML = `
            <div class="box48" style="margin-bottom: 10px;">
              <fa-icon size="48" name="${fileIcon}"></fa-icon>
              <div class="containerHeadline">
                <h3>${media.filename}</h3>
                <p>${media.formattedFilesize}</p>
              </div>
            </div>`;
                    }
                }
            }
            // show remove button
            if (this._activeButton.parentElement.tagName === "LI") {
                const removeButton = this._activeButton.parentElement.nextElementSibling;
                removeButton.hidden = false;
            }
            else {
                const removeButton = this._activeButton.nextElementSibling;
                removeButton.hidden = false;
            }
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
            listItem.innerHTML = `
        <a class="jsTooltip" title="${Language.get("wcf.media.button.select")}">
          <fa-icon name="check"></fa-icon>
          <span class="invisible">${Language.get("wcf.media.button.select")}</span>
        </a>
      `;
        }
        /**
         * Handles clicking on the remove button.
         */
        _removeMedia(selectButton, removeButton) {
            if (removeButton.parentElement.tagName === "LI") {
                removeButton.parentElement.hidden = true;
            }
            else {
                removeButton.hidden = true;
            }
            const input = document.getElementById(selectButton.dataset.store);
            input.value = "";
            Core.triggerEvent(input, "change");
            const display = selectButton.dataset.display;
            if (display) {
                const displayElement = document.getElementById(display);
                if (displayElement) {
                    displayElement.innerHTML = "";
                }
            }
        }
    }
    return MediaManagerSelect;
});
