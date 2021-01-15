/**
 * Provides editing support for comments.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Comment/Edit
 */

import * as Ajax from "../../Ajax";
import { AjaxCallbackSetup, ResponseData } from "../../Ajax/Data";
import * as Core from "../../Core";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import * as Environment from "../../Environment";
import * as EventHandler from "../../Event/Handler";
import * as Language from "../../Language";
import { RedactorEditor } from "../Redactor/Editor";
import * as UiScroll from "../Scroll";
import * as UiNotification from "../Notification";

interface AjaxResponse {
  actionName: string;
  returnValues: {
    message: string;
    template: string;
  };
}

class UiCommentEdit {
  protected _activeElement: HTMLElement | null = null;
  protected readonly _comments = new WeakSet<HTMLElement>();
  protected readonly _container: HTMLElement;
  protected _editorContainer: HTMLElement | null = null;

  /**
   * Initializes the comment edit manager.
   */
  constructor(container: HTMLElement) {
    this._container = container;

    this.rebuild();

    DomChangeListener.add("Ui/Comment/Edit_" + DomUtil.identify(this._container), this.rebuild.bind(this));
  }

  /**
   * Initializes each applicable message, should be called whenever new
   * messages are being displayed.
   */
  rebuild(): void {
    this._container.querySelectorAll(".comment").forEach((comment: HTMLElement) => {
      if (this._comments.has(comment)) {
        return;
      }

      if (Core.stringToBool(comment.dataset.canEdit || "")) {
        const button = comment.querySelector(".jsCommentEditButton") as HTMLAnchorElement;
        if (button !== null) {
          button.addEventListener("click", (ev) => this._click(ev));
        }
      }

      this._comments.add(comment);
    });
  }

  /**
   * Handles clicks on the edit button.
   */
  protected _click(event: MouseEvent): void {
    event.preventDefault();

    if (this._activeElement === null) {
      const target = event.currentTarget as HTMLElement;
      this._activeElement = target.closest(".comment") as HTMLElement;

      this._prepare();

      Ajax.api(this, {
        actionName: "beginEdit",
        objectIDs: [this._getObjectId(this._activeElement)],
      });
    } else {
      UiNotification.show("wcf.message.error.editorAlreadyInUse", null, "warning");
    }
  }

  /**
   * Prepares the message for editor display.
   */
  protected _prepare(): void {
    this._editorContainer = document.createElement("div");
    this._editorContainer.className = "commentEditorContainer";
    this._editorContainer.innerHTML = '<span class="icon icon48 fa-spinner"></span>';

    const content = this._activeElement!.querySelector(".commentContentContainer")!;
    content.insertBefore(this._editorContainer, content.firstChild);
  }

  /**
   * Shows the message editor.
   */
  protected _showEditor(data: AjaxResponse): void {
    const id = this._getEditorId();
    const editorContainer = this._editorContainer!;

    const icon = editorContainer.querySelector(".icon")!;
    icon.remove();

    const editor = document.createElement("div");
    editor.className = "editorContainer";
    DomUtil.setInnerHtml(editor, data.returnValues.template);
    editorContainer.appendChild(editor);

    // bind buttons
    const formSubmit = editorContainer.querySelector(".formSubmit") as HTMLElement;

    const buttonSave = formSubmit.querySelector('button[data-type="save"]') as HTMLButtonElement;
    buttonSave.addEventListener("click", () => this._save());

    const buttonCancel = formSubmit.querySelector('button[data-type="cancel"]') as HTMLButtonElement;
    buttonCancel.addEventListener("click", () => this._restoreMessage());

    EventHandler.add("com.woltlab.wcf.redactor", `submitEditor_${id}`, (data) => {
      data.cancel = true;

      this._save();
    });

    const editorElement = document.getElementById(id) as HTMLElement;
    if (Environment.editor() === "redactor") {
      window.setTimeout(() => {
        UiScroll.element(this._activeElement!);
      }, 250);
    } else {
      editorElement.focus();
    }
  }

  /**
   * Restores the message view.
   */
  protected _restoreMessage(): void {
    this._destroyEditor();

    this._editorContainer!.remove();

    this._activeElement = null;
  }

  /**
   * Saves the editor message.
   */
  protected _save(): void {
    const parameters = {
      data: {
        message: "",
      },
    };

    const id = this._getEditorId();

    EventHandler.fire("com.woltlab.wcf.redactor2", `getText_${id}`, parameters.data);

    if (!this._validate(parameters)) {
      // validation failed
      return;
    }

    EventHandler.fire("com.woltlab.wcf.redactor2", `submit_${id}`, parameters);

    Ajax.api(this, {
      actionName: "save",
      objectIDs: [this._getObjectId(this._activeElement!)],
      parameters: parameters,
    });

    this._hideEditor();
  }

  /**
   * Validates the message and invokes listeners to perform additional validation.
   */
  protected _validate(parameters: ArbitraryObject): boolean {
    // remove all existing error elements
    this._activeElement!.querySelectorAll(".innerError").forEach((el) => el.remove());

    // check if editor contains actual content
    const editorElement = document.getElementById(this._getEditorId())!;
    const redactor: RedactorEditor = window.jQuery(editorElement).data("redactor");
    if (redactor.utils.isEmpty()) {
      this.throwError(editorElement, Language.get("wcf.global.form.error.empty"));
      return false;
    }

    const data = {
      api: this,
      parameters: parameters,
      valid: true,
    };

    EventHandler.fire("com.woltlab.wcf.redactor2", "validate_" + this._getEditorId(), data);

    return data.valid;
  }

  /**
   * Throws an error by adding an inline error to target element.
   */
  throwError(element: HTMLElement, message: string): void {
    DomUtil.innerError(element, message);
  }

  /**
   * Shows the update message.
   */
  protected _showMessage(data: AjaxResponse): void {
    // set new content
    const container = this._editorContainer!.parentElement!.querySelector(
      ".commentContent .userMessage",
    ) as HTMLElement;
    DomUtil.setInnerHtml(container, data.returnValues.message);

    this._restoreMessage();

    UiNotification.show();
  }

  /**
   * Hides the editor from view.
   */
  protected _hideEditor(): void {
    const editorContainer = this._editorContainer!.querySelector(".editorContainer") as HTMLElement;
    DomUtil.hide(editorContainer);

    const icon = document.createElement("span");
    icon.className = "icon icon48 fa-spinner";
    this._editorContainer!.appendChild(icon);
  }

  /**
   * Restores the previously hidden editor.
   */
  protected _restoreEditor(): void {
    const icon = this._editorContainer!.querySelector(".fa-spinner")!;
    icon.remove();

    const editorContainer = this._editorContainer!.querySelector(".editorContainer") as HTMLElement;
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
   * Returns the unique editor id.
   */
  protected _getEditorId(): string {
    return `commentEditor${this._getObjectId(this._activeElement!)}`;
  }

  /**
   * Returns the element's `data-object-id` value.
   */
  protected _getObjectId(element: HTMLElement): number {
    return ~~element.dataset.objectId!;
  }

  _ajaxFailure(data: ResponseData): boolean {
    const editor = this._editorContainer!.querySelector(".redactor-layer") as HTMLElement;

    // handle errors occurring on editor load
    if (editor === null) {
      this._restoreMessage();

      return true;
    }

    this._restoreEditor();

    if (!data || data.returnValues === undefined || data.returnValues.errorType === undefined) {
      return true;
    }

    DomUtil.innerError(editor, data.returnValues.errorType);

    return false;
  }

  _ajaxSuccess(data: AjaxResponse): void {
    switch (data.actionName) {
      case "beginEdit":
        this._showEditor(data);
        break;

      case "save":
        this._showMessage(data);
        break;
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    const objectTypeId = ~~this._container.dataset.objectTypeId!;

    return {
      data: {
        className: "wcf\\data\\comment\\CommentAction",
        parameters: {
          data: {
            objectTypeID: objectTypeId,
          },
        },
      },
      silent: true,
    };
  }
}

Core.enableLegacyInheritance(UiCommentEdit);

export = UiCommentEdit;
