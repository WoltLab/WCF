/**
 * Handles the comment add feature.
 *
 * Warning: This implementation is also used for responses, but in a slightly
 *          modified version. Changes made to this class need to be verified
 *          against the response implementation.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Comment/Add
 */

import * as Ajax from "../../Ajax";
import { AjaxCallbackSetup, ResponseData } from "../../Ajax/Data";
import ControllerCaptcha from "../../Controller/Captcha";
import * as Core from "../../Core";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import * as EventHandler from "../../Event/Handler";
import * as Language from "../../Language";
import UiDialog from "../Dialog";
import { RedactorEditor } from "../Redactor/Editor";
import * as UiScroll from "../Scroll";
import User from "../../User";
import * as UiNotification from "../Notification";

interface AjaxResponse {
  returnValues: {
    guestDialog?: string;
    template: string;
  };
}

class UiCommentAdd {
  protected readonly _container: HTMLElement;
  protected readonly _content: HTMLElement;
  protected readonly _textarea: HTMLTextAreaElement;
  protected _editor: RedactorEditor | null = null;
  protected _loadingOverlay: HTMLElement | null = null;

  /**
   * Initializes a new quick reply field.
   */
  constructor(container: HTMLElement) {
    this._container = container;
    this._content = this._container.querySelector(".jsOuterEditorContainer") as HTMLElement;
    this._textarea = this._container.querySelector(".wysiwygTextarea") as HTMLTextAreaElement;

    this._content.addEventListener("click", (event) => {
      if (this._content.classList.contains("collapsed")) {
        event.preventDefault();

        this._content.classList.remove("collapsed");

        this._focusEditor();
      }
    });

    // handle submit button
    const submitButton = this._container.querySelector('button[data-type="save"]') as HTMLButtonElement;
    submitButton.addEventListener("click", (ev) => this._submit(ev));
  }

  /**
   * Scrolls the editor into view and sets the caret to the end of the editor.
   */
  protected _focusEditor(): void {
    UiScroll.element(this._container, () => {
      window.jQuery(this._textarea).redactor("WoltLabCaret.endOfEditor");
    });
  }

  /**
   * Submits the guest dialog.
   */
  protected _submitGuestDialog(event: MouseEvent | KeyboardEvent): void {
    // only submit when enter key is pressed
    if (event instanceof KeyboardEvent && event.key !== "Enter") {
      return;
    }

    const target = event.currentTarget as HTMLInputElement;
    const dialogContent = target.closest(".dialogContent") as HTMLElement;
    const usernameInput = dialogContent.querySelector("input[name=username]") as HTMLInputElement;
    if (usernameInput.value === "") {
      DomUtil.innerError(usernameInput, Language.get("wcf.global.form.error.empty"));
      usernameInput.closest("dl")!.classList.add("formError");

      return;
    }

    let parameters: ArbitraryObject = {
      parameters: {
        data: {
          username: usernameInput.value,
        },
      },
    };

    if (ControllerCaptcha.has("commentAdd")) {
      const data = ControllerCaptcha.getData("commentAdd");
      if (data instanceof Promise) {
        void data.then((data) => {
          parameters = Core.extend(parameters, data) as ArbitraryObject;
          this._submit(undefined, parameters);
        });
      } else {
        parameters = Core.extend(parameters, data as ArbitraryObject) as ArbitraryObject;
        this._submit(undefined, parameters);
      }
    } else {
      this._submit(undefined, parameters);
    }
  }

  /**
   * Validates the message and submits it to the server.
   */
  protected _submit(event: MouseEvent | undefined, additionalParameters?: ArbitraryObject): void {
    if (event) {
      event.preventDefault();
    }

    if (!this._validate()) {
      // validation failed, bail out
      return;
    }

    this._showLoadingOverlay();

    // build parameters
    const parameters = this._getParameters();

    EventHandler.fire("com.woltlab.wcf.redactor2", "submit_text", parameters.data as any);

    if (!User.userId && !additionalParameters) {
      parameters.requireGuestDialog = true;
    }

    Ajax.api(
      this,
      Core.extend(
        {
          parameters: parameters,
        },
        additionalParameters as ArbitraryObject,
      ),
    );
  }

  /**
   * Returns the request parameters to add a comment.
   */
  protected _getParameters(): ArbitraryObject {
    const commentList = this._container.closest(".commentList") as HTMLElement;

    return {
      data: {
        message: this._getEditor().code.get(),
        objectID: ~~commentList.dataset.objectId!,
        objectTypeID: ~~commentList.dataset.objectTypeId!,
      },
    };
  }

  /**
   * Validates the message and invokes listeners to perform additional validation.
   */
  protected _validate(): boolean {
    // remove all existing error elements
    this._container.querySelectorAll(".innerError").forEach((el) => el.remove());

    // check if editor contains actual content
    if (this._getEditor().utils.isEmpty()) {
      this.throwError(this._textarea, Language.get("wcf.global.form.error.empty"));
      return false;
    }

    const data = {
      api: this,
      editor: this._getEditor(),
      message: this._getEditor().code.get(),
      valid: true,
    };

    EventHandler.fire("com.woltlab.wcf.redactor2", "validate_text", data);

    return data.valid;
  }

  /**
   * Throws an error by adding an inline error to target element.
   */
  throwError(element: HTMLElement, message: string): void {
    DomUtil.innerError(element, message === "empty" ? Language.get("wcf.global.form.error.empty") : message);
  }

  /**
   * Displays a loading spinner while the request is processed by the server.
   */
  protected _showLoadingOverlay(): void {
    if (this._loadingOverlay === null) {
      this._loadingOverlay = document.createElement("div");
      this._loadingOverlay.className = "commentLoadingOverlay";
      this._loadingOverlay.innerHTML = '<span class="icon icon96 fa-spinner"></span>';
    }

    this._content.classList.add("loading");
    this._content.appendChild(this._loadingOverlay);
  }

  /**
   * Hides the loading spinner.
   */
  protected _hideLoadingOverlay(): void {
    this._content.classList.remove("loading");

    const loadingOverlay = this._content.querySelector(".commentLoadingOverlay");
    if (loadingOverlay !== null) {
      loadingOverlay.remove();
    }
  }

  /**
   * Resets the editor contents and notifies event listeners.
   */
  protected _reset(): void {
    this._getEditor().code.set("<p>\u200b</p>");

    EventHandler.fire("com.woltlab.wcf.redactor2", "reset_text");

    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }

    this._content.classList.add("collapsed");
  }

  /**
   * Handles errors occurred during server processing.
   */
  protected _handleError(data: ResponseData): void {
    this.throwError(this._textarea, data.returnValues.errorType);
  }

  /**
   * Returns the current editor instance.
   */
  protected _getEditor(): RedactorEditor {
    if (this._editor === null) {
      if (typeof window.jQuery === "function") {
        this._editor = window.jQuery(this._textarea).data("redactor") as RedactorEditor;
      } else {
        throw new Error("Unable to access editor, jQuery has not been loaded yet.");
      }
    }

    return this._editor;
  }

  /**
   * Inserts the rendered message.
   */
  protected _insertMessage(data: AjaxResponse): HTMLElement {
    // insert HTML
    DomUtil.insertHtml(data.returnValues.template, this._container, "after");

    UiNotification.show(Language.get("wcf.global.success.add"));

    DomChangeListener.trigger();

    return this._container.nextElementSibling as HTMLElement;
  }

  _ajaxSuccess(data: AjaxResponse): void {
    if (!User.userId && data.returnValues.guestDialog) {
      UiDialog.openStatic("jsDialogGuestComment", data.returnValues.guestDialog, {
        closable: false,
        onClose: () => {
          if (ControllerCaptcha.has("commentAdd")) {
            ControllerCaptcha.delete("commentAdd");
          }
        },
        title: Language.get("wcf.global.confirmation.title"),
      });

      const dialog = UiDialog.getDialog("jsDialogGuestComment")!;

      const submitButton = dialog.content.querySelector("input[type=submit]") as HTMLButtonElement;
      submitButton.addEventListener("click", (ev) => this._submitGuestDialog(ev));
      const cancelButton = dialog.content.querySelector('button[data-type="cancel"]') as HTMLButtonElement;
      cancelButton.addEventListener("click", () => this._cancelGuestDialog());

      const input = dialog.content.querySelector("input[type=text]") as HTMLInputElement;
      input.addEventListener("keypress", (ev) => this._submitGuestDialog(ev));
    } else {
      const scrollTarget = this._insertMessage(data);

      if (!User.userId) {
        UiDialog.close("jsDialogGuestComment");
      }

      this._reset();

      this._hideLoadingOverlay();

      window.setTimeout(() => {
        UiScroll.element(scrollTarget);
      }, 100);
    }
  }

  _ajaxFailure(data: ResponseData): boolean {
    this._hideLoadingOverlay();

    if (data === null || data.returnValues === undefined || data.returnValues.errorType === undefined) {
      return true;
    }

    this._handleError(data);

    return false;
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "addComment",
        className: "wcf\\data\\comment\\CommentAction",
      },
      silent: true,
    };
  }

  /**
   * Cancels the guest dialog and restores the comment editor.
   */
  protected _cancelGuestDialog(): void {
    UiDialog.close("jsDialogGuestComment");

    this._hideLoadingOverlay();
  }
}

Core.enableLegacyInheritance(UiCommentAdd);

export = UiCommentAdd;
