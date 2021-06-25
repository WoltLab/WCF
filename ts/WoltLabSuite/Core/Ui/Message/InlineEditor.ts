/**
 * Flexible message inline editor.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/InlineEditor
 */

import * as Ajax from "../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup, ResponseData } from "../../Ajax/Data";
import * as Core from "../../Core";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import * as Environment from "../../Environment";
import * as EventHandler from "../../Event/Handler";
import * as Language from "../../Language";
import { NotificationAction } from "../Dropdown/Data";
import * as UiDropdownReusable from "../Dropdown/Reusable";
import * as UiNotification from "../Notification";
import * as UiScroll from "../Scroll";
import {
  AjaxResponseEditor,
  AjaxResponseMessage,
  ElementVisibility,
  ItemData,
  MessageInlineEditorOptions,
} from "./InlineEditor/Data";

interface ElementData {
  button: HTMLAnchorElement;
  messageBody: HTMLElement;
  messageBodyEditor: HTMLElement | null;
  messageFooter: HTMLElement;
  messageFooterButtons: HTMLUListElement;
  messageHeader: HTMLElement;
  messageText: HTMLElement;
}

interface ValidationData {
  api: UiMessageInlineEditor;
  parameters: ArbitraryObject;
  valid: boolean;
  promises: Promise<void>[];
}

class UiMessageInlineEditor implements AjaxCallbackObject {
  protected _activeDropdownElement: HTMLElement | null;
  protected _activeElement: HTMLElement | null;
  protected _dropdownMenu: HTMLUListElement | null;
  protected _elements: WeakMap<HTMLElement, ElementData>;
  protected _options: MessageInlineEditorOptions;

  /**
   * Initializes the message inline editor.
   */
  constructor(opts: Partial<MessageInlineEditorOptions>) {
    this.init(opts);
  }

  /**
   * Helper initialization method for legacy inheritance support.
   */
  protected init(opts: Partial<MessageInlineEditorOptions>): void {
    // Define the properties again, the constructor might not be
    // called in legacy implementations.
    this._activeDropdownElement = null;
    this._activeElement = null;
    this._dropdownMenu = null;
    this._elements = new WeakMap<HTMLElement, ElementData>();

    this._options = Core.extend(
      {
        canEditInline: false,

        className: "",
        containerId: 0,
        dropdownIdentifier: "",
        editorPrefix: "messageEditor",

        messageSelector: ".jsMessage",

        quoteManager: null,
      },
      opts,
    ) as MessageInlineEditorOptions;

    this.rebuild();

    DomChangeListener.add(`Ui/Message/InlineEdit_${this._options.className}`, () => this.rebuild());
  }

  /**
   * Initializes each applicable message, should be called whenever new
   * messages are being displayed.
   */
  rebuild(): void {
    document.querySelectorAll(this._options.messageSelector).forEach((element: HTMLElement) => {
      if (this._elements.has(element)) {
        return;
      }

      const button = element.querySelector(".jsMessageEditButton") as HTMLAnchorElement;
      if (button !== null) {
        const canEdit = Core.stringToBool(element.dataset.canEdit || "");
        const canEditInline = Core.stringToBool(element.dataset.canEditInline || "");

        if (this._options.canEditInline || canEditInline) {
          button.addEventListener("click", (ev) => this._clickDropdown(element, ev));
          button.classList.add("jsDropdownEnabled");

          if (canEdit) {
            button.addEventListener("dblclick", (ev) => this._click(element, ev));
          }
        } else if (canEdit) {
          button.addEventListener("click", (ev) => this._click(element, ev));
        }
      }

      const messageBody = element.querySelector(".messageBody") as HTMLElement;
      const messageFooter = element.querySelector(".messageFooter") as HTMLElement;
      const messageFooterButtons = messageFooter.querySelector(".messageFooterButtons") as HTMLUListElement;
      const messageHeader = element.querySelector(".messageHeader") as HTMLElement;
      const messageText = messageBody.querySelector(".messageText") as HTMLElement;

      this._elements.set(element, {
        button,
        messageBody,
        messageBodyEditor: null,
        messageFooter,
        messageFooterButtons,
        messageHeader,
        messageText,
      });
    });
  }

  /**
   * Handles clicks on the edit button or the edit dropdown item.
   */
  protected _click(element: HTMLElement | null, event: MouseEvent | null): void {
    if (element === null) {
      element = this._activeDropdownElement;
    }
    if (event) {
      event.preventDefault();
    }

    if (this._activeElement === null) {
      this._activeElement = element;

      this._prepare();

      Ajax.api(this, {
        actionName: "beginEdit",
        parameters: {
          containerID: this._options.containerId,
          objectID: this._getObjectId(element!),
        },
      });
    } else {
      UiNotification.show("wcf.message.error.editorAlreadyInUse", undefined, "warning");
    }
  }

  /**
   * Creates and opens the dropdown on first usage.
   */
  protected _clickDropdown(element: HTMLElement, event: MouseEvent): void {
    event.preventDefault();

    const button = event.currentTarget as HTMLElement;
    if (button.classList.contains("dropdownToggle")) {
      return;
    }

    button.classList.add("dropdownToggle");
    button.parentElement!.classList.add("dropdown");
    button.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();

      this._activeDropdownElement = element;
      UiDropdownReusable.toggleDropdown(this._options.dropdownIdentifier, button);
    });

    // build dropdown
    if (this._dropdownMenu === null) {
      this._dropdownMenu = document.createElement("ul");
      this._dropdownMenu.className = "dropdownMenu";

      const items = this._dropdownGetItems();

      EventHandler.fire("com.woltlab.wcf.inlineEditor", `dropdownInit_${this._options.dropdownIdentifier}`, {
        items: items,
      });

      this._dropdownBuild(items);

      UiDropdownReusable.init(this._options.dropdownIdentifier, this._dropdownMenu);
      UiDropdownReusable.registerCallback(this._options.dropdownIdentifier, (containerId, action) =>
        this._dropdownToggle(containerId, action),
      );
    }

    setTimeout(() => button.click(), 10);
  }

  /**
   * Creates the dropdown menu on first usage.
   */
  protected _dropdownBuild(items: ItemData[]): void {
    items.forEach((item) => {
      const listItem = document.createElement("li");
      listItem.dataset.item = item.item;

      if (item.item === "divider") {
        listItem.className = "dropdownDivider";
      } else {
        const label = document.createElement("span");
        label.textContent = Language.get(item.label!);
        listItem.appendChild(label);

        if (item.item === "editItem") {
          listItem.addEventListener("click", (ev) => this._click(null, ev));
        } else {
          listItem.addEventListener("click", (ev) => this._clickDropdownItem(ev));
        }
      }

      this._dropdownMenu!.appendChild(listItem);
    });
  }

  /**
   * Callback for dropdown toggle.
   */
  protected _dropdownToggle(containerId: string, action: NotificationAction): void {
    const elementData = this._elements.get(this._activeDropdownElement!)!;
    const buttonParent = elementData.button.parentElement!;

    if (action === "close") {
      buttonParent.classList.remove("dropdownOpen");
      elementData.messageFooterButtons.classList.remove("forceVisible");

      return;
    }

    buttonParent.classList.add("dropdownOpen");
    elementData.messageFooterButtons.classList.add("forceVisible");

    const visibility = new Map<string, boolean>(Object.entries(this._dropdownOpen()));

    EventHandler.fire("com.woltlab.wcf.inlineEditor", `dropdownOpen_${this._options.dropdownIdentifier}`, {
      element: this._activeDropdownElement,
      visibility,
    });

    const dropdownMenu = this._dropdownMenu!;

    let visiblePredecessor = false;
    const children = Array.from(dropdownMenu.children);
    children.forEach((listItem: HTMLElement, index) => {
      const item = listItem.dataset.item!;

      if (item === "divider") {
        if (visiblePredecessor) {
          DomUtil.show(listItem);

          visiblePredecessor = false;
        } else {
          DomUtil.hide(listItem);
        }
      } else {
        if (visibility.get(item) === false) {
          DomUtil.hide(listItem);

          // check if previous item was a divider
          if (index > 0 && index + 1 === children.length) {
            const previousElementSibling = listItem.previousElementSibling as HTMLElement;
            if (previousElementSibling.dataset.item === "divider") {
              DomUtil.hide(previousElementSibling);
            }
          }
        } else {
          DomUtil.show(listItem);

          visiblePredecessor = true;
        }
      }
    });
  }

  /**
   * Returns the list of dropdown items for this type.
   */
  protected _dropdownGetItems(): ItemData[] {
    // This should be an abstract method, but cannot be marked as such for backwards compatibility.
    return [];
  }

  /**
   * Invoked once the dropdown for this type is shown, expects a list of type name and a boolean value
   * to represent the visibility of each item. Items that do not appear in this list will be considered
   * visible.
   */
  protected _dropdownOpen(): ElementVisibility {
    // This should be an abstract method, but cannot be marked as such for backwards compatibility.
    return {};
  }

  /**
   * Invoked whenever the user selects an item from the dropdown menu, the selected item is passed as argument.
   */
  protected _dropdownSelect(_item: string): void {
    // This should be an abstract method, but cannot be marked as such for backwards compatibility.
  }

  /**
   * Handles clicks on a dropdown item.
   */
  protected _clickDropdownItem(event: MouseEvent): void {
    event.preventDefault();

    const target = event.currentTarget as HTMLElement;
    const item = target.dataset.item!;
    const data = {
      cancel: false,
      element: this._activeDropdownElement,
      item,
    };
    EventHandler.fire("com.woltlab.wcf.inlineEditor", `dropdownItemClick_${this._options.dropdownIdentifier}`, data);

    if (data.cancel) {
      event.preventDefault();
    } else {
      this._dropdownSelect(item);
    }
  }

  /**
   * Prepares the message for editor display.
   */
  protected _prepare(): void {
    const data = this._elements.get(this._activeElement!)!;

    const messageBodyEditor = document.createElement("div");
    messageBodyEditor.className = "messageBody editor";
    data.messageBodyEditor = messageBodyEditor;

    const icon = document.createElement("span");
    icon.className = "icon icon48 fa-spinner";
    messageBodyEditor.appendChild(icon);

    data.messageBody.insertAdjacentElement("afterend", messageBodyEditor);

    DomUtil.hide(data.messageBody);
  }

  /**
   * Shows the message editor.
   */
  protected _showEditor(data: AjaxResponseEditor): void {
    const id = this._getEditorId();
    const activeElement = this._activeElement!;
    const elementData = this._elements.get(activeElement)!;

    activeElement.classList.add("jsInvalidQuoteTarget");
    const icon = elementData.messageBodyEditor!.querySelector(".icon") as HTMLElement;
    icon.remove();

    const messageBody = elementData.messageBodyEditor!;
    const editor = document.createElement("div");
    editor.className = "editorContainer";
    DomUtil.setInnerHtml(editor, data.returnValues.template);
    messageBody.appendChild(editor);

    // bind buttons
    const formSubmit = editor.querySelector(".formSubmit") as HTMLElement;

    const buttonSave = formSubmit.querySelector('button[data-type="save"]') as HTMLButtonElement;
    buttonSave.addEventListener("click", () => this._save());

    const buttonCancel = formSubmit.querySelector('button[data-type="cancel"]') as HTMLButtonElement;
    buttonCancel.addEventListener("click", () => this._restoreMessage());

    EventHandler.add("com.woltlab.wcf.redactor", `submitEditor_${id}`, (data: { cancel: boolean }) => {
      data.cancel = true;

      this._save();
    });

    // hide message header and footer
    DomUtil.hide(elementData.messageHeader);
    DomUtil.hide(elementData.messageFooter);

    if (Environment.editor() === "redactor") {
      window.setTimeout(() => {
        if (this._options.quoteManager) {
          this._options.quoteManager.setAlternativeEditor(id);
        }

        UiScroll.element(activeElement);
      }, 250);
    } else {
      const editorElement = document.getElementById(id) as HTMLElement;
      editorElement.focus();
    }
  }

  /**
   * Restores the message view.
   */
  protected _restoreMessage(): void {
    const activeElement = this._activeElement!;
    const elementData = this._elements.get(activeElement)!;

    this._destroyEditor();

    elementData.messageBodyEditor!.remove();
    elementData.messageBodyEditor = null;

    DomUtil.show(elementData.messageBody);
    DomUtil.show(elementData.messageFooter);
    DomUtil.show(elementData.messageHeader);
    activeElement.classList.remove("jsInvalidQuoteTarget");

    this._activeElement = null;

    if (this._options.quoteManager) {
      this._options.quoteManager.clearAlternativeEditor();
    }
  }

  /**
   * Saves the editor message.
   */
  protected _save(): void {
    const parameters = {
      containerID: this._options.containerId,
      data: {
        message: "",
      },
      objectID: this._getObjectId(this._activeElement!),
      removeQuoteIDs: this._options.quoteManager ? this._options.quoteManager.getQuotesMarkedForRemoval() : [],
    };

    const id = this._getEditorId();

    // add any available settings
    const settingsContainer = document.getElementById(`settings_${id}`);
    if (settingsContainer) {
      settingsContainer
        .querySelectorAll("input, select, textarea")
        .forEach((element: HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement) => {
          if (element.nodeName === "INPUT" && (element.type === "checkbox" || element.type === "radio")) {
            if (!(element as HTMLInputElement).checked) {
              return;
            }
          }

          const name = element.name;
          if (Object.prototype.hasOwnProperty.call(parameters, name)) {
            throw new Error(`Variable overshadowing, key '${name}' is already present.`);
          }

          parameters[name] = element.value.trim();
        });
    }

    EventHandler.fire("com.woltlab.wcf.redactor2", `getText_${id}`, parameters.data);

    let validateResult: unknown = this._validate(parameters);

    // Legacy validation methods returned a plain boolean.
    if (!(validateResult instanceof Promise)) {
      if (validateResult === false) {
        validateResult = Promise.reject();
      } else {
        validateResult = Promise.resolve();
      }
    }

    (validateResult as Promise<void[]>).then(
      () => {
        EventHandler.fire("com.woltlab.wcf.redactor2", `submit_${id}`, parameters);

        Ajax.api(this, {
          actionName: "save",
          parameters: parameters,
        });

        this._hideEditor();
      },
      (e) => {
        const errorMessage = (e as Error).message;
        console.log(`Validation of post edit failed: ${errorMessage}`);
      },
    );
  }

  /**
   * Validates the message and invokes listeners to perform additional validation.
   */
  protected _validate(parameters: ArbitraryObject): Promise<void[]> {
    // remove all existing error elements
    this._activeElement!.querySelectorAll(".innerError").forEach((el) => el.remove());

    const data: ValidationData = {
      api: this,
      parameters: parameters,
      valid: true,
      promises: [],
    };

    EventHandler.fire("com.woltlab.wcf.redactor2", `validate_${this._getEditorId()}`, data);

    if (data.valid) {
      data.promises.push(Promise.resolve());
    } else {
      data.promises.push(Promise.reject());
    }

    return Promise.all(data.promises);
  }

  /**
   * Throws an error by showing an inline error for the target element.
   */
  throwError(element: HTMLElement, message: string): void {
    DomUtil.innerError(element, message);
  }

  /**
   * Shows the update message.
   */
  protected _showMessage(data: AjaxResponseMessage): void {
    const activeElement = this._activeElement!;
    const editorId = this._getEditorId();
    const elementData = this._elements.get(activeElement)!;

    // set new content
    DomUtil.setInnerHtml(elementData.messageBody.querySelector(".messageText")!, data.returnValues.message);

    // handle attachment list
    if (typeof data.returnValues.attachmentList === "string") {
      elementData.messageFooter
        .querySelectorAll(".attachmentThumbnailList, .attachmentFileList")
        .forEach((el) => el.remove());

      const element = document.createElement("div");
      DomUtil.setInnerHtml(element, data.returnValues.attachmentList);

      let node;
      while (element.childNodes.length) {
        node = element.childNodes[element.childNodes.length - 1];
        elementData.messageFooter.insertBefore(node, elementData.messageFooter.firstChild);
      }
    }

    if (typeof data.returnValues.poll === "string") {
      const poll = elementData.messageBody.querySelector(".pollContainer");
      if (poll !== null) {
        // The poll container is wrapped inside `.jsInlineEditorHideContent`.
        poll.parentElement!.remove();
      }

      if (data.returnValues.poll !== "") {
        const pollContainer = document.createElement("div");
        pollContainer.className = "jsInlineEditorHideContent";
        DomUtil.setInnerHtml(pollContainer, data.returnValues.poll);

        elementData.messageBody.insertAdjacentElement("afterbegin", pollContainer);
      }
    }

    this._restoreMessage();

    this._updateHistory(this._getHash(this._getObjectId(activeElement)));

    EventHandler.fire("com.woltlab.wcf.redactor", `autosaveDestroy_${editorId}`);

    UiNotification.show();

    if (this._options.quoteManager) {
      this._options.quoteManager.clearAlternativeEditor();
      this._options.quoteManager.countQuotes();
    }
  }

  /**
   * Hides the editor from view.
   */
  protected _hideEditor(): void {
    const elementData = this._elements.get(this._activeElement!)!;
    const editorContainer = elementData.messageBodyEditor!.querySelector(".editorContainer") as HTMLElement;
    DomUtil.hide(editorContainer);

    const icon = document.createElement("span");
    icon.className = "icon icon48 fa-spinner";
    elementData.messageBodyEditor!.appendChild(icon);
  }

  /**
   * Restores the previously hidden editor.
   */
  protected _restoreEditor(): void {
    const elementData = this._elements.get(this._activeElement!)!;
    const messageBodyEditor = elementData.messageBodyEditor!;

    const icon = messageBodyEditor.querySelector(".fa-spinner") as HTMLElement;
    icon.remove();

    const editorContainer = messageBodyEditor.querySelector(".editorContainer") as HTMLElement;
    if (editorContainer !== null) {
      DomUtil.show(editorContainer);
    }
  }

  /**
   * Destroys the editor instance.
   */
  protected _destroyEditor(): void {
    EventHandler.fire("com.woltlab.wcf.redactor2", `autosaveDestroy_${this._getEditorId()}`);
    EventHandler.fire("com.woltlab.wcf.redactor2", `destroy_${this._getEditorId()}`);
  }

  /**
   * Returns the hash added to the url after successfully editing a message.
   */
  protected _getHash(objectId: string): string {
    return `#message${objectId}`;
  }

  /**
   * Updates the history to avoid old content when going back in the browser
   * history.
   */
  protected _updateHistory(hash: string): void {
    window.location.hash = hash;
  }

  /**
   * Returns the unique editor id.
   */
  protected _getEditorId(): string {
    return this._options.editorPrefix + this._getObjectId(this._activeElement!).toString();
  }

  /**
   * Returns the element's `data-object-id` value.
   */
  protected _getObjectId(element: HTMLElement): string {
    return element.dataset.objectId || "";
  }

  _ajaxFailure(data: ResponseData): boolean {
    const elementData = this._elements.get(this._activeElement!)!;
    const editor = elementData.messageBodyEditor!.querySelector(".redactor-layer") as HTMLElement;

    // handle errors occurring on editor load
    if (editor === null) {
      this._restoreMessage();

      return true;
    }

    this._restoreEditor();

    if (!data || data.returnValues === undefined || data.returnValues.realErrorMessage === undefined) {
      return true;
    }

    DomUtil.innerError(editor, data.returnValues.realErrorMessage);

    return false;
  }

  _ajaxSuccess(data: ResponseData): void {
    switch (data.actionName) {
      case "beginEdit":
        this._showEditor(data as AjaxResponseEditor);
        break;

      case "save":
        this._showMessage(data as AjaxResponseMessage);
        break;
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        className: this._options.className,
        interfaceName: "wcf\\data\\IMessageInlineEditorAction",
      },
      silent: true,
    };
  }

  /** @deprecated  3.0 - used only for backward compatibility with `WCF.Message.InlineEditor` */
  legacyEdit(containerId: string): void {
    this._click(document.getElementById(containerId), null);
  }
}

Core.enableLegacyInheritance(UiMessageInlineEditor);

export = UiMessageInlineEditor;
