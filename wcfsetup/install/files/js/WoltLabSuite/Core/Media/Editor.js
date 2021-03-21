/**
 * Handles editing media files via dialog.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/Editor
 */
define(["require", "exports", "tslib", "../Core", "../Ui/Notification", "../Ui/Dialog", "../Language/Chooser", "../Language/Input", "../Dom/Util", "../Dom/Traverse", "../Dom/Change/Listener", "../Language", "../Ajax", "./Replace"], function (require, exports, tslib_1, Core, UiNotification, UiDialog, LanguageChooser, LanguageInput, DomUtil, DomTraverse, Listener_1, Language, Ajax, Replace_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    UiNotification = tslib_1.__importStar(UiNotification);
    UiDialog = tslib_1.__importStar(UiDialog);
    LanguageChooser = tslib_1.__importStar(LanguageChooser);
    LanguageInput = tslib_1.__importStar(LanguageInput);
    DomUtil = tslib_1.__importStar(DomUtil);
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Language = tslib_1.__importStar(Language);
    Ajax = tslib_1.__importStar(Ajax);
    Replace_1 = tslib_1.__importDefault(Replace_1);
    class MediaEditor {
        constructor(callbackObject) {
            this._availableLanguageCount = 1;
            this._categoryIds = [];
            this._dialogs = new Map();
            this._media = null;
            this._oldCategoryId = 0;
            this._callbackObject = callbackObject || {};
            if (this._callbackObject._editorClose && typeof this._callbackObject._editorClose !== "function") {
                throw new TypeError("Callback object has no function '_editorClose'.");
            }
            if (this._callbackObject._editorSuccess && typeof this._callbackObject._editorSuccess !== "function") {
                throw new TypeError("Callback object has no function '_editorSuccess'.");
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "update",
                    className: "wcf\\data\\media\\MediaAction",
                },
            };
        }
        _ajaxSuccess() {
            UiNotification.show();
            if (this._callbackObject._editorSuccess) {
                this._callbackObject._editorSuccess(this._media, this._oldCategoryId);
                this._oldCategoryId = 0;
            }
            UiDialog.close(`mediaEditor_${this._media.mediaID}`);
            this._media = null;
        }
        /**
         * Is called if an editor is manually closed by the user.
         */
        _close() {
            this._media = null;
            if (this._callbackObject._editorClose) {
                this._callbackObject._editorClose();
            }
        }
        /**
         * Initializes the editor dialog.
         *
         * @since 5.3
         */
        _initEditor(content, data) {
            this._availableLanguageCount = ~~data.returnValues.availableLanguageCount;
            this._categoryIds = data.returnValues.categoryIDs.map((number) => ~~number);
            if (data.returnValues.mediaData) {
                this._media = data.returnValues.mediaData;
            }
            const media = this._media;
            const mediaId = media.mediaID;
            // make sure that the language chooser is initialized first
            setTimeout(() => {
                if (this._availableLanguageCount > 1) {
                    LanguageChooser.setLanguageId(`mediaEditor_${mediaId}_languageID`, media.languageID || window.LANGUAGE_ID);
                }
                if (this._categoryIds.length) {
                    const categoryID = content.querySelector("select[name=categoryID]");
                    if (media.categoryID) {
                        categoryID.value = media.categoryID.toString();
                    }
                    else {
                        categoryID.value = "0";
                    }
                }
                const title = content.querySelector("input[name=title]");
                const altText = content.querySelector("input[name=altText]");
                const caption = content.querySelector("textarea[name=caption]");
                if (this._availableLanguageCount > 1 && media.isMultilingual) {
                    if (document.getElementById(`altText_${mediaId}`)) {
                        LanguageInput.setValues(`altText_${mediaId}`, (media.altText || {}));
                    }
                    if (document.getElementById(`caption_${mediaId}`)) {
                        LanguageInput.setValues(`caption_${mediaId}`, (media.caption || {}));
                    }
                    LanguageInput.setValues(`title_${mediaId}`, (media.title || {}));
                }
                else {
                    title.value = media.title ? media.title[media.languageID || window.LANGUAGE_ID] : "";
                    if (altText) {
                        altText.value = media.altText ? media.altText[media.languageID || window.LANGUAGE_ID] : "";
                    }
                    if (caption) {
                        caption.value = media.caption ? media.caption[media.languageID || window.LANGUAGE_ID] : "";
                    }
                }
                if (this._availableLanguageCount > 1) {
                    const isMultilingual = content.querySelector("input[name=isMultilingual]");
                    isMultilingual.addEventListener("change", (ev) => this._updateLanguageFields(ev));
                    this._updateLanguageFields(null, isMultilingual);
                }
                if (altText) {
                    altText.addEventListener("keypress", (ev) => this._keyPress(ev));
                }
                title.addEventListener("keypress", (ev) => this._keyPress(ev));
                content.querySelector("button[data-type=submit]").addEventListener("click", () => this._saveData());
                // remove focus from input elements and scroll dialog to top
                document.activeElement.blur();
                document.getElementById(`mediaEditor_${mediaId}`).parentNode.scrollTop = 0;
                // Initialize button to replace media file.
                const uploadButton = content.querySelector(".mediaManagerMediaReplaceButton");
                let target = content.querySelector(".mediaThumbnail");
                if (!target) {
                    target = document.createElement("div");
                    content.appendChild(target);
                }
                new Replace_1.default(mediaId, DomUtil.identify(uploadButton), 
                // Pass an anonymous element for non-images which is required internally
                // but not needed in this case.
                DomUtil.identify(target), {
                    mediaEditor: this,
                });
                Listener_1.default.trigger();
            }, 200);
        }
        /**
         * Handles the `[ENTER]` key to submit the form.
         */
        _keyPress(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                this._saveData();
            }
        }
        /**
         * Saves the data of the currently edited media.
         */
        _saveData() {
            const content = UiDialog.getDialog(`mediaEditor_${this._media.mediaID}`).content;
            const categoryId = content.querySelector("select[name=categoryID]");
            const altText = content.querySelector("input[name=altText]");
            const caption = content.querySelector("textarea[name=caption]");
            const captionEnableHtml = content.querySelector("input[name=captionEnableHtml]");
            const title = content.querySelector("input[name=title]");
            let hasError = false;
            const altTextError = altText ? DomTraverse.childByClass(altText.parentNode, "innerError") : false;
            const captionError = caption ? DomTraverse.childByClass(caption.parentNode, "innerError") : false;
            const titleError = DomTraverse.childByClass(title.parentNode, "innerError");
            // category
            this._oldCategoryId = this._media.categoryID;
            if (this._categoryIds.length) {
                this._media.categoryID = ~~categoryId.value;
                // if the selected category id not valid (manipulated DOM), ignore
                if (this._categoryIds.indexOf(this._media.categoryID) === -1) {
                    this._media.categoryID = 0;
                }
            }
            // language and multilingualism
            if (this._availableLanguageCount > 1) {
                const isMultilingual = content.querySelector("input[name=isMultilingual]");
                this._media.isMultilingual = ~~isMultilingual.checked;
                this._media.languageID = this._media.isMultilingual
                    ? null
                    : LanguageChooser.getLanguageId(`mediaEditor_${this._media.mediaID}_languageID`);
            }
            else {
                this._media.languageID = window.LANGUAGE_ID;
            }
            // altText, caption and title
            this._media.altText = {};
            this._media.caption = {};
            this._media.title = {};
            if (this._availableLanguageCount > 1 && this._media.isMultilingual) {
                if (altText && !LanguageInput.validate(altText.id, true)) {
                    hasError = true;
                    if (!altTextError) {
                        DomUtil.innerError(altText, Language.get("wcf.global.form.error.multilingual"));
                    }
                }
                if (caption && !LanguageInput.validate(caption.id, true)) {
                    hasError = true;
                    if (!captionError) {
                        DomUtil.innerError(caption, Language.get("wcf.global.form.error.multilingual"));
                    }
                }
                if (!LanguageInput.validate(title.id, true)) {
                    hasError = true;
                    if (!titleError) {
                        DomUtil.innerError(title, Language.get("wcf.global.form.error.multilingual"));
                    }
                }
                this._media.altText = altText ? this.mapToI18nValues(LanguageInput.getValues(altText.id)) : "";
                this._media.caption = caption ? this.mapToI18nValues(LanguageInput.getValues(caption.id)) : "";
                this._media.title = this.mapToI18nValues(LanguageInput.getValues(title.id));
            }
            else {
                this._media.altText[this._media.languageID] = altText ? altText.value : "";
                this._media.caption[this._media.languageID] = caption ? caption.value : "";
                this._media.title[this._media.languageID] = title.value;
            }
            // captionEnableHtml
            if (captionEnableHtml) {
                this._media.captionEnableHtml = ~~captionEnableHtml.checked;
            }
            else {
                this._media.captionEnableHtml = 0;
            }
            const aclValues = {
                allowAll: ~~document.getElementById(`mediaEditor_${this._media.mediaID}_aclAllowAll`)
                    .checked,
                group: Array.from(content.querySelectorAll(`input[name="mediaEditor_${this._media.mediaID}_aclValues[group][]"]`)).map((aclGroup) => ~~aclGroup.value),
                user: Array.from(content.querySelectorAll(`input[name="mediaEditor_${this._media.mediaID}_aclValues[user][]"]`)).map((aclUser) => ~~aclUser.value),
            };
            if (!hasError) {
                if (altTextError) {
                    altTextError.remove();
                }
                if (captionError) {
                    captionError.remove();
                }
                if (titleError) {
                    titleError.remove();
                }
                Ajax.api(this, {
                    actionName: "update",
                    objectIDs: [this._media.mediaID],
                    parameters: {
                        aclValues: aclValues,
                        altText: this._media.altText,
                        caption: this._media.caption,
                        data: {
                            captionEnableHtml: this._media.captionEnableHtml,
                            categoryID: this._media.categoryID,
                            isMultilingual: this._media.isMultilingual,
                            languageID: this._media.languageID,
                        },
                        title: this._media.title,
                    },
                });
            }
        }
        mapToI18nValues(values) {
            const obj = {};
            values.forEach((value, key) => (obj[key] = value));
            return obj;
        }
        /**
         * Updates language-related input fields depending on whether multilingualis is enabled.
         */
        _updateLanguageFields(event, element) {
            if (event) {
                element = event.currentTarget;
            }
            const mediaId = this._media.mediaID;
            const languageChooserContainer = document.getElementById(`mediaEditor_${mediaId}_languageIDContainer`)
                .parentNode;
            if (element.checked) {
                LanguageInput.enable(`title_${mediaId}`);
                if (document.getElementById(`caption_${mediaId}`)) {
                    LanguageInput.enable(`caption_${mediaId}`);
                }
                if (document.getElementById(`altText_${mediaId}`)) {
                    LanguageInput.enable(`altText_${mediaId}`);
                }
                DomUtil.hide(languageChooserContainer);
            }
            else {
                LanguageInput.disable(`title_${mediaId}`);
                if (document.getElementById(`caption_${mediaId}`)) {
                    LanguageInput.disable(`caption_${mediaId}`);
                }
                if (document.getElementById(`altText_${mediaId}`)) {
                    LanguageInput.disable(`altText_${mediaId}`);
                }
                DomUtil.show(languageChooserContainer);
            }
        }
        /**
         * Edits the media with the given data or id.
         */
        edit(editedMedia) {
            let media;
            let mediaId = 0;
            if (typeof editedMedia === "object") {
                media = editedMedia;
                mediaId = media.mediaID;
            }
            else {
                media = {
                    mediaID: editedMedia,
                };
                mediaId = editedMedia;
            }
            if (this._media !== null) {
                throw new Error(`Cannot edit media with id ${mediaId} while editing media with id '${this._media.mediaID}'.`);
            }
            this._media = media;
            if (!this._dialogs.has(`mediaEditor_${mediaId}`)) {
                this._dialogs.set(`mediaEditor_${mediaId}`, {
                    _dialogSetup: () => {
                        return {
                            id: `mediaEditor_${mediaId}`,
                            options: {
                                backdropCloseOnClick: false,
                                onClose: () => this._close(),
                                title: Language.get("wcf.media.edit"),
                            },
                            source: {
                                after: (content, responseData) => this._initEditor(content, responseData),
                                data: {
                                    actionName: "getEditorDialog",
                                    className: "wcf\\data\\media\\MediaAction",
                                    objectIDs: [mediaId],
                                },
                            },
                        };
                    },
                });
            }
            UiDialog.open(this._dialogs.get(`mediaEditor_${mediaId}`));
        }
        /**
         * Updates the data of the currently edited media file.
         */
        updateData(media) {
            if (this._callbackObject._editorSuccess) {
                this._callbackObject._editorSuccess(media);
            }
        }
    }
    Core.enableLegacyInheritance(MediaEditor);
    return MediaEditor;
});
