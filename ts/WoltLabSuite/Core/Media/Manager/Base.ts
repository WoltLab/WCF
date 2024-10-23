/**
 * Provides the media manager dialog.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import * as Core from "../../Core";
import { Media, MediaManagerOptions, MediaEditorCallbackObject, MediaUploadSuccessEventData } from "../Data";
import * as Language from "../../Language";
import * as Permission from "../../Permission";
import * as DomChangeListener from "../../Dom/Change/Listener";
import * as EventHandler from "../../Event/Handler";
import * as DomTraverse from "../../Dom/Traverse";
import * as DomUtil from "../../Dom/Util";
import * as UiDialog from "../../Ui/Dialog";
import { DialogCallbackSetup, DialogCallbackObject } from "../../Ui/Dialog/Data";
import * as Clipboard from "../../Controller/Clipboard";
import UiPagination from "../../Ui/Pagination";
import * as UiNotification from "../../Ui/Notification";
import * as StringUtil from "../../StringUtil";
import MediaManagerSearch from "./Search";
import MediaUpload from "../Upload";
import MediaEditor from "../Editor";
import * as MediaClipboard from "../Clipboard";
import { ObjectActionData } from "../../Ui/Object/Data";

let mediaManagerCounter = 0;

interface DialogInitAjaxResponseData {
  returnValues: {
    hasMarkedItems: number;
    media: object;
    pageCount: number;
  };
}

interface SetMediaAdditionalData {
  pageCount: number;
  pageNo: number;
}

abstract class MediaManager<TOptions extends MediaManagerOptions = MediaManagerOptions>
  implements DialogCallbackObject, MediaEditorCallbackObject
{
  protected _forceClipboard = false;
  protected _hadInitiallyMarkedItems = false;
  protected readonly _id: string;
  protected readonly _listItems = new Map<number, HTMLLIElement>();
  protected _media = new Map<number, Media>();
  protected _mediaCategorySelect: HTMLSelectElement | null;
  protected readonly _mediaEditor: MediaEditor | null = null;
  protected _mediaManagerMediaList: HTMLElement | null = null;
  protected _pagination: UiPagination | null = null;
  protected _search: MediaManagerSearch | null = null;
  protected _upload: MediaUpload | null = null;
  protected readonly _options: TOptions;

  constructor(options: Partial<TOptions>) {
    this._options = Core.extend(
      {
        dialogTitle: Language.get("wcf.media.manager"),
        imagesOnly: false,
        minSearchLength: 3,
      },
      options,
    ) as TOptions;

    this._id = `mediaManager${mediaManagerCounter++}`;

    if (Permission.get("admin.content.cms.canManageMedia")) {
      this._mediaEditor = new MediaEditor(this);
    }

    DomChangeListener.add("WoltLabSuite/Core/Media/Manager", () => this._addButtonEventListeners());

    EventHandler.add("com.woltlab.wcf.media.upload", "success", (data: MediaUploadSuccessEventData) =>
      this._openEditorAfterUpload(data),
    );
  }

  /**
   * Adds click event listeners to media buttons.
   */
  protected _addButtonEventListeners(): void {
    if (!this._mediaManagerMediaList || !Permission.get("admin.content.cms.canManageMedia")) return;

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
  protected _categoryChange(): void {
    this._search!.search();
  }

  /**
   * Handles clicks on the media manager button.
   */
  protected _click(event?: Event): void {
    event?.preventDefault();

    UiDialog.open(this);
  }

  /**
   * Is called if the media manager dialog is closed.
   */
  protected _dialogClose(): void {
    // only show media clipboard if editor is open
    if (Permission.get("admin.content.cms.canManageMedia") || this._forceClipboard) {
      Clipboard.hideEditor("com.woltlab.wcf.media");
    }
  }

  /**
   * Initializes the dialog when first loaded.
   */
  protected _dialogInit(content: HTMLElement, data: DialogInitAjaxResponseData): void {
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
  public _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: this._id,
      options: {
        onClose: () => this._dialogClose(),
        onShow: () => this._dialogShow(),
        title: this._options.dialogTitle,
      },
      source: {
        after: (content: HTMLElement, data: DialogInitAjaxResponseData) => this._dialogInit(content, data),
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
  protected _dialogShow(): void {
    if (!this._mediaManagerMediaList) {
      const dialog = this.getDialog();

      this._mediaManagerMediaList = dialog.querySelector(".mediaManagerMediaList");

      this._mediaCategorySelect = dialog.querySelector(".mediaManagerCategoryList > select");
      if (this._mediaCategorySelect) {
        this._mediaCategorySelect.addEventListener("change", () => this._categoryChange());
      }

      // store list items locally
      const listItems = DomTraverse.childrenByTag(this._mediaManagerMediaList!, "LI");
      listItems.forEach((listItem: HTMLLIElement) => {
        this._listItems.set(~~listItem.dataset.objectId!, listItem);
      });

      if (Permission.get("admin.content.cms.canManageMedia")) {
        const uploadButton = UiDialog.getDialog(this)!.dialog.querySelector(".mediaManagerMediaUploadButton")!;
        this._upload = new MediaUpload(DomUtil.identify(uploadButton), DomUtil.identify(this._mediaManagerMediaList!), {
          mediaManager: this,
        });

        EventHandler.add("WoltLabSuite/Core/Ui/Object/Action", "delete", (data: ObjectActionData) =>
          this.removeMedia(~~data.objectElement.dataset.objectId!),
        );
      }

      if (Permission.get("admin.content.cms.canManageMedia") || this._forceClipboard) {
        MediaClipboard.init("menuManagerDialog-" + this.getMode(), this._hadInitiallyMarkedItems ? true : false, this);
      } else {
        this._removeClipboardCheckboxes();
      }

      this._search = new MediaManagerSearch(this);

      if (!listItems.length) {
        this._search.hideSearch();
      }
    } else {
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
  protected _editMedia(event: Event): void {
    if (!Permission.get("admin.content.cms.canManageMedia")) {
      throw new Error("You are not allowed to edit media files.");
    }

    UiDialog.close(this);

    const target = event.currentTarget as HTMLElement;

    this._mediaEditor!.edit(this._media.get(~~target.dataset.objectId!)!);
  }

  /**
   * Re-opens the manager dialog after closing the editor dialog.
   */
  _editorClose(): void {
    UiDialog.open(this);
  }

  /**
   * Re-opens the manager dialog and updates the media data after successfully editing a media file.
   */
  _editorSuccess(media: Media, oldCategoryId?: number, closedEditorDialog = true): void {
    // if the category changed of media changed and category
    // is selected, check if media list needs to be refreshed
    if (this._mediaCategorySelect) {
      const selectedCategoryId = ~~this._mediaCategorySelect.value;

      if (selectedCategoryId) {
        const newCategoryId = ~~media.categoryID;

        if (
          oldCategoryId != newCategoryId &&
          (oldCategoryId == selectedCategoryId || newCategoryId == selectedCategoryId)
        ) {
          this._search!.search();
        }
      }
    }

    if (closedEditorDialog) {
      UiDialog.open(this);
    }

    this._media.set(~~media.mediaID, media);

    const listItem = this._listItems.get(~~media.mediaID)!;
    const p = listItem.querySelector(".mediaTitle")!;
    if (media.isMultilingual) {
      if (media.title && media.title[window.LANGUAGE_ID]) {
        p.textContent = media.title[window.LANGUAGE_ID];
      } else {
        p.textContent = media.filename;
      }
    } else {
      if (media.title && media.title[media.languageID!]) {
        p.textContent = media.title[media.languageID!];
      } else {
        p.textContent = media.filename;
      }
    }

    const thumbnail = listItem.querySelector(".mediaThumbnail")!;
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
  protected _initPagination(pageCount: number, pageNo?: number): void {
    if (pageNo === undefined) pageNo = 1;

    if (pageCount > 1) {
      const newPagination = document.createElement("div");
      newPagination.className = "paginationBottom jsPagination";

      const oldPagination = UiDialog.getDialog(this)!.content.querySelector(".jsPagination") as HTMLElement;
      oldPagination.insertAdjacentElement("beforebegin", newPagination);
      oldPagination.remove();

      this._pagination = new UiPagination(newPagination, {
        activePage: pageNo,
        callbackSwitch: (pageNo: number) => this._search!.search(pageNo),
        maxPage: pageCount,
      });
    } else if (this._pagination) {
      DomUtil.hide(this._pagination.getElement());
    }
  }

  /**
   * Removes all media clipboard checkboxes.
   */
  _removeClipboardCheckboxes(): void {
    this._mediaManagerMediaList!.querySelectorAll(".mediaCheckbox").forEach((el) => el.remove());
  }

  /**
   * Opens the media editor after uploading a single file.
   *
   * @since 5.2
   */
  _openEditorAfterUpload(data: MediaUploadSuccessEventData): void {
    if (data.upload === this._upload && !data.isMultiFileUpload && !this._upload.hasPendingUploads()) {
      const keys = Object.keys(data.media);

      if (keys.length) {
        UiDialog.close(this);

        this._mediaEditor!.edit(this._media.get(~~data.media[keys[0]].mediaID)!);
      }
    }
  }

  /**
   * Sets the displayed media (after a search).
   */
  _setMedia(media: object): void {
    this._media = new Map<number, Media>(Object.entries(media).map(([mediaId, media]) => [~~mediaId, media]));

    let info = DomTraverse.nextByClass(this._mediaManagerMediaList!, "info") as HTMLElement;

    if (this._media.size) {
      if (info) {
        DomUtil.hide(info);
      }
    } else {
      if (info === null) {
        info = document.createElement("p");
        info.className = "info";
        info.textContent = Language.get("wcf.media.search.noResults");
      }

      DomUtil.show(info);
      DomUtil.insertAfter(info, this._mediaManagerMediaList!);
    }

    DomTraverse.childrenByTag(this._mediaManagerMediaList!, "LI").forEach((listItem) => {
      if (!this._media.has(~~listItem.dataset.objectId!)) {
        DomUtil.hide(listItem);
      } else {
        DomUtil.show(listItem);
      }
    });

    DomChangeListener.trigger();

    if (Permission.get("admin.content.cms.canManageMedia") || this._forceClipboard) {
      Clipboard.reload();
    } else {
      this._removeClipboardCheckboxes();
    }
  }

  /**
   * Adds a media file to the manager.
   */
  public addMedia(media: Media, listItem: HTMLLIElement): void {
    if (!media.languageID) media.isMultilingual = 1;

    this._media.set(~~media.mediaID, media);
    this._listItems.set(~~media.mediaID, listItem);

    if (this._listItems.size === 1) {
      this._search!.showSearch();
    }
  }

  /**
   * Is called after the media files with the given ids have been deleted via clipboard.
   */
  public clipboardDeleteMedia(mediaIds: number[]): void {
    mediaIds.forEach((mediaId) => {
      this.removeMedia(~~mediaId);
    });

    UiNotification.show();
  }

  /**
   * Returns the id of the currently selected category or `0` if no category is selected.
   */
  public getCategoryId(): number {
    if (this._mediaCategorySelect) {
      return ~~this._mediaCategorySelect.value;
    }

    return 0;
  }

  /**
   * Returns the media manager dialog element.
   */
  getDialog(): HTMLElement {
    return UiDialog.getDialog(this)!.dialog;
  }

  /**
   * Returns the mode of the media manager.
   */
  public getMode(): string {
    return "";
  }

  /**
   * Returns the media manager option with the given name.
   */
  public getOption(name: string): any {
    if (this._options[name]) {
      return this._options[name];
    }

    return null;
  }

  /**
   * Removes a media file.
   */
  public removeMedia(mediaId: number): void {
    if (this._listItems.has(mediaId)) {
      // remove list item
      try {
        this._listItems.get(mediaId)!.remove();
      } catch {
        // ignore errors if item has already been removed by other code
      }

      this._listItems.delete(mediaId);
      this._media.delete(mediaId);
    }
  }

  /**
   * Changes the displayed media to the previously displayed media.
   */
  public resetMedia(): void {
    // calling WoltLabSuite/Core/Media/Manager/Search.search() reloads the first page of the dialog
    this._search!.search();
  }

  /**
   * Sets the media files currently displayed.
   */
  setMedia(media: object, template: string, additionalData: SetMediaAdditionalData): void {
    const hasMedia = Object.entries(media).length > 0;

    if (hasMedia) {
      const ul = document.createElement("ul");
      ul.innerHTML = template;

      DomTraverse.childrenByTag(ul, "LI").forEach((listItem) => {
        if (!this._listItems.has(~~listItem.dataset.objectId!)) {
          this._listItems.set(~~listItem.dataset.objectId!, listItem);

          this._mediaManagerMediaList!.appendChild(listItem);
        }
      });
    }

    this._initPagination(additionalData.pageCount, additionalData.pageNo);

    this._setMedia(media);
  }

  /**
   * Sets up a new media element.
   */
  public setupMediaElement(media: Media, mediaElement: HTMLElement): void {
    const mediaInformation = DomTraverse.childByClass(mediaElement, "mediaInformation")!;

    const buttonGroupNavigation = document.createElement("nav");
    buttonGroupNavigation.className = "jsMobileNavigation buttonGroupNavigation";
    mediaInformation.parentNode!.appendChild(buttonGroupNavigation);

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
      deleteButton.dataset.confirmMessage = StringUtil.unescapeHTML(
        Language.get("wcf.media.delete.confirmMessage", {
          title: uuid,
        }),
      ).replace(uuid, StringUtil.escapeHTML(media.filename));
      buttons.appendChild(deleteButton);

      deleteButton.innerHTML = `
        <a class="jsTooltip" title="${Language.get("wcf.global.button.delete")}">
          <fa-icon name="xmark"></fa-icon>
          <span class="invisible">${Language.get("wcf.global.button.delete")}</span>
        </a>`;
    }
  }
}

export = MediaManager;
