/**
 * Handles editing media files via dialog.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/Editor
 */

import * as Core from "../Core";
import { Media, MediaEditorCallbackObject } from "./Data";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../Ajax/Data";
import * as UiNotification from "../Ui/Notification";
import * as UiDialog from "../Ui/Dialog";
import { DialogCallbackObject } from "../Ui/Dialog/Data";
import * as LanguageChooser from "../Language/Chooser";
import * as LanguageInput from "../Language/Input";
import * as DomUtil from "../Dom/Util";
import * as DomTraverse from "../Dom/Traverse";
import DomChangeListener from "../Dom/Change/Listener";
import * as Language from "../Language";
import * as Ajax from "../Ajax";
import MediaReplace from "./Replace";
import { I18nValues } from "../Language/Input";

interface InitEditorData {
  returnValues: {
    availableLanguageCount: number;
    categoryIDs: number[];
    mediaData?: Media;
  };
}

class MediaEditor implements AjaxCallbackObject {
  protected _availableLanguageCount = 1;
  protected _categoryIds: number[] = [];
  protected _dialogs = new Map<string, DialogCallbackObject>();
  protected readonly _callbackObject: MediaEditorCallbackObject;
  protected _media: Media | null = null;
  protected _oldCategoryId = 0;

  constructor(callbackObject: MediaEditorCallbackObject) {
    this._callbackObject = callbackObject || {};

    if (this._callbackObject._editorClose && typeof this._callbackObject._editorClose !== "function") {
      throw new TypeError("Callback object has no function '_editorClose'.");
    }
    if (this._callbackObject._editorSuccess && typeof this._callbackObject._editorSuccess !== "function") {
      throw new TypeError("Callback object has no function '_editorSuccess'.");
    }
  }

  public _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "update",
        className: "wcf\\data\\media\\MediaAction",
      },
    };
  }

  public _ajaxSuccess(): void {
    UiNotification.show();

    if (this._callbackObject._editorSuccess) {
      this._callbackObject._editorSuccess(this._media, this._oldCategoryId);
      this._oldCategoryId = 0;
    }

    UiDialog.close(`mediaEditor_${this._media!.mediaID}`);

    this._media = null;
  }

  /**
   * Is called if an editor is manually closed by the user.
   */
  protected _close(): void {
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
  protected _initEditor(content: HTMLElement, data: InitEditorData): void {
    this._availableLanguageCount = ~~data.returnValues.availableLanguageCount;
    this._categoryIds = data.returnValues.categoryIDs.map((number) => ~~number);

    if (data.returnValues.mediaData) {
      this._media = data.returnValues.mediaData;
    }
    const media = this._media!;
    const mediaId = media.mediaID;

    // make sure that the language chooser is initialized first
    setTimeout(() => {
      if (this._availableLanguageCount > 1) {
        LanguageChooser.setLanguageId(`mediaEditor_${mediaId}_languageID`, media.languageID || window.LANGUAGE_ID);
      }

      if (this._categoryIds.length) {
        const categoryID = content.querySelector("select[name=categoryID]") as HTMLSelectElement;
        if (media.categoryID) {
          categoryID.value = media.categoryID.toString();
        } else {
          categoryID.value = "0";
        }
      }

      const title = content.querySelector("input[name=title]") as HTMLInputElement;
      const altText = content.querySelector("input[name=altText]") as HTMLInputElement;
      const caption = content.querySelector("textarea[name=caption]") as HTMLInputElement;

      if (this._availableLanguageCount > 1 && media.isMultilingual) {
        if (document.getElementById(`altText_${mediaId}`)) {
          LanguageInput.setValues(`altText_${mediaId}`, (media.altText || {}) as I18nValues);
        }

        if (document.getElementById(`caption_${mediaId}`)) {
          LanguageInput.setValues(`caption_${mediaId}`, (media.caption || {}) as I18nValues);
        }

        LanguageInput.setValues(`title_${mediaId}`, (media.title || {}) as I18nValues);
      } else {
        title.value = media.title ? media.title[media.languageID || window.LANGUAGE_ID] : "";
        if (altText) {
          altText.value = media.altText ? media.altText[media.languageID || window.LANGUAGE_ID] : "";
        }
        if (caption) {
          caption.value = media.caption ? media.caption[media.languageID || window.LANGUAGE_ID] : "";
        }
      }

      if (this._availableLanguageCount > 1) {
        const isMultilingual = content.querySelector("input[name=isMultilingual]") as HTMLInputElement;
        isMultilingual.addEventListener("change", (ev) => this._updateLanguageFields(ev));

        this._updateLanguageFields(null, isMultilingual);
      }

      if (altText) {
        altText.addEventListener("keypress", (ev) => this._keyPress(ev));
      }
      title.addEventListener("keypress", (ev) => this._keyPress(ev));

      content.querySelector("button[data-type=submit]")!.addEventListener("click", () => this._saveData());

      // remove focus from input elements and scroll dialog to top
      (document.activeElement! as HTMLElement).blur();
      (document.getElementById(`mediaEditor_${mediaId}`)!.parentNode as HTMLElement).scrollTop = 0;

      // Initialize button to replace media file.
      const uploadButton = content.querySelector(".mediaManagerMediaReplaceButton")!;
      let target = content.querySelector(".mediaThumbnail");
      if (!target) {
        target = document.createElement("div");
        content.appendChild(target);
      }
      new MediaReplace(
        mediaId,
        DomUtil.identify(uploadButton),
        // Pass an anonymous element for non-images which is required internally
        // but not needed in this case.
        DomUtil.identify(target),
        {
          mediaEditor: this,
        },
      );

      DomChangeListener.trigger();
    }, 200);
  }

  /**
   * Handles the `[ENTER]` key to submit the form.
   */
  protected _keyPress(event: KeyboardEvent): void {
    if (event.key === "Enter") {
      event.preventDefault();

      this._saveData();
    }
  }

  /**
   * Saves the data of the currently edited media.
   */
  protected _saveData(): void {
    const content = UiDialog.getDialog(`mediaEditor_${this._media!.mediaID}`)!.content;

    const categoryId = content.querySelector("select[name=categoryID]") as HTMLSelectElement;
    const altText = content.querySelector("input[name=altText]") as HTMLInputElement;
    const caption = content.querySelector("textarea[name=caption]") as HTMLTextAreaElement;
    const captionEnableHtml = content.querySelector("input[name=captionEnableHtml]") as HTMLInputElement;
    const title = content.querySelector("input[name=title]") as HTMLInputElement;

    let hasError = false;
    const altTextError = altText ? DomTraverse.childByClass(altText.parentNode! as HTMLElement, "innerError") : false;
    const captionError = caption ? DomTraverse.childByClass(caption.parentNode! as HTMLElement, "innerError") : false;
    const titleError = DomTraverse.childByClass(title.parentNode! as HTMLElement, "innerError");

    // category
    this._oldCategoryId = this._media!.categoryID;
    if (this._categoryIds.length) {
      this._media!.categoryID = ~~categoryId.value;

      // if the selected category id not valid (manipulated DOM), ignore
      if (this._categoryIds.indexOf(this._media!.categoryID) === -1) {
        this._media!.categoryID = 0;
      }
    }

    // language and multilingualism
    if (this._availableLanguageCount > 1) {
      const isMultilingual = content.querySelector("input[name=isMultilingual]") as HTMLInputElement;
      this._media!.isMultilingual = ~~isMultilingual.checked;
      this._media!.languageID = this._media!.isMultilingual
        ? null
        : LanguageChooser.getLanguageId(`mediaEditor_${this._media!.mediaID}_languageID`);
    } else {
      this._media!.languageID = window.LANGUAGE_ID;
    }

    // altText, caption and title
    this._media!.altText = {};
    this._media!.caption = {};
    this._media!.title = {};
    if (this._availableLanguageCount > 1 && this._media!.isMultilingual) {
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

      this._media!.altText = altText ? this.mapToI18nValues(LanguageInput.getValues(altText.id)) : "";
      this._media!.caption = caption ? this.mapToI18nValues(LanguageInput.getValues(caption.id)) : "";
      this._media!.title = this.mapToI18nValues(LanguageInput.getValues(title.id));
    } else {
      this._media!.altText[this._media!.languageID!] = altText ? altText.value : "";
      this._media!.caption[this._media!.languageID!] = caption ? caption.value : "";
      this._media!.title[this._media!.languageID!] = title.value;
    }

    // captionEnableHtml
    if (captionEnableHtml) {
      this._media!.captionEnableHtml = ~~captionEnableHtml.checked;
    } else {
      this._media!.captionEnableHtml = 0;
    }

    const aclValues = {
      allowAll: ~~(document.getElementById(`mediaEditor_${this._media!.mediaID}_aclAllowAll`)! as HTMLInputElement)
        .checked,
      group: Array.from(
        content.querySelectorAll(`input[name="mediaEditor_${this._media!.mediaID}_aclValues[group][]"]`),
      ).map((aclGroup: HTMLInputElement) => ~~aclGroup.value),
      user: Array.from(
        content.querySelectorAll(`input[name="mediaEditor_${this._media!.mediaID}_aclValues[user][]"]`),
      ).map((aclUser: HTMLInputElement) => ~~aclUser.value),
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
        objectIDs: [this._media!.mediaID],
        parameters: {
          aclValues: aclValues,
          altText: this._media!.altText,
          caption: this._media!.caption,
          data: {
            captionEnableHtml: this._media!.captionEnableHtml,
            categoryID: this._media!.categoryID,
            isMultilingual: this._media!.isMultilingual,
            languageID: this._media!.languageID,
          },
          title: this._media!.title,
        },
      });
    }
  }

  private mapToI18nValues(values: Map<number, string>): I18nValues {
    const obj = {};
    values.forEach((value, key) => (obj[key] = value));

    return obj;
  }

  /**
   * Updates language-related input fields depending on whether multilingualis is enabled.
   */
  protected _updateLanguageFields(event: Event | null, element?: HTMLInputElement): void {
    if (event) {
      element = event.currentTarget as HTMLInputElement;
    }

    const mediaId = this._media!.mediaID;
    const languageChooserContainer = document.getElementById(`mediaEditor_${mediaId}_languageIDContainer`)!
      .parentNode! as HTMLElement;

    if (element!.checked) {
      LanguageInput.enable(`title_${mediaId}`);
      if (document.getElementById(`caption_${mediaId}`)) {
        LanguageInput.enable(`caption_${mediaId}`);
      }
      if (document.getElementById(`altText_${mediaId}`)) {
        LanguageInput.enable(`altText_${mediaId}`);
      }

      DomUtil.hide(languageChooserContainer);
    } else {
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
  public edit(editedMedia: Media | number): void {
    let media: Media;
    let mediaId = 0;
    if (typeof editedMedia === "object") {
      media = editedMedia;
      mediaId = media.mediaID;
    } else {
      media = {
        mediaID: editedMedia,
      } as Media;
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
              after: (content: HTMLElement, responseData: InitEditorData) => this._initEditor(content, responseData),
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

    UiDialog.open(this._dialogs.get(`mediaEditor_${mediaId}`)!);
  }

  /**
   * Updates the data of the currently edited media file.
   */
  public updateData(media: Media): void {
    if (this._callbackObject._editorSuccess) {
      this._callbackObject._editorSuccess(media);
    }
  }
}

Core.enableLegacyInheritance(MediaEditor);

export = MediaEditor;
