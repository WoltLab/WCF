/**
 * Handles user interaction with the quick reply feature.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Reply
 * @woltlabExcludeBundle tiny
 */

import * as Ajax from "../../Ajax";
import { AjaxCallbackSetup, ResponseData } from "../../Ajax/Data";
import * as Core from "../../Core";
import * as EventHandler from "../../Event/Handler";
import * as Language from "../../Language";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import UiDialog from "../Dialog";
import * as UiNotification from "../Notification";
import User from "../../User";
import ControllerCaptcha from "../../Controller/Captcha";
import { RedactorEditor } from "../Redactor/Editor";
import * as UiScroll from "../Scroll";

interface MessageReplyOptions {
  ajax: {
    className: string;
  };
  quoteManager: any;
  successMessage: string;
}

interface AjaxResponse {
  returnValues: {
    guestDialog?: string;
    guestDialogID?: string;
    lastPostTime: number;
    template?: string;
    url?: string;
  };
}

class UiMessageReply {
  protected readonly _container: HTMLElement;
  protected readonly _content: HTMLElement;
  protected _editor: RedactorEditor | null = null;
  protected _guestDialogId = "";
  protected _loadingOverlay: HTMLElement | null = null;
  protected readonly _options: MessageReplyOptions;
  protected readonly _textarea: HTMLTextAreaElement;

  /**
   * Initializes a new quick reply field.
   */
  constructor(opts: Partial<MessageReplyOptions>) {
    this._options = Core.extend(
      {
        ajax: {
          className: "",
        },
        quoteManager: null,
        successMessage: "wcf.global.success.add",
      },
      opts,
    ) as MessageReplyOptions;

    this._container = document.getElementById("messageQuickReply") as HTMLElement;
    this._content = this._container.querySelector(".messageContent") as HTMLElement;
    this._textarea = document.getElementById("text") as HTMLTextAreaElement;

    // prevent marking of text for quoting
    this._container.querySelector(".message")!.classList.add("jsInvalidQuoteTarget");

    // handle submit button
    const submitButton = this._container.querySelector('button[data-type="save"]') as HTMLButtonElement;
    submitButton.addEventListener("click", (ev) => this._submit(ev));

    // bind reply button
    document.querySelectorAll(".jsQuickReply").forEach((replyButton: HTMLAnchorElement) => {
      replyButton.addEventListener("click", (event) => {
        event.preventDefault();

        this._getEditor().WoltLabReply.showEditor(true);

        UiScroll.element(this._container, () => {
          this._getEditor().WoltLabCaret.endOfEditor();
        });
      });
    });
  }

  /**
   * Submits the guest dialog.
   */
  protected _submitGuestDialog(event: KeyboardEvent | MouseEvent): void {
    // only submit when enter key is pressed
    if (event instanceof KeyboardEvent && event.key !== "Enter") {
      return;
    }

    const target = event.currentTarget as HTMLElement;
    const dialogContent = target.closest(".dialogContent")!;
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

    const captchaId = target.dataset.captchaId!;
    if (ControllerCaptcha.has(captchaId)) {
      const data = ControllerCaptcha.getData(captchaId);
      ControllerCaptcha.delete(captchaId);
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

    // Ignore requests to submit the message while a previous request is still pending.
    if (this._content.classList.contains("loading")) {
      if (!this._guestDialogId || !UiDialog.isOpen(this._guestDialogId)) {
        return;
      }
    }

    if (!this._validate()) {
      // validation failed, bail out
      return;
    }

    this._showLoadingOverlay();

    // build parameters
    const parameters: ArbitraryObject = {};
    Object.entries(this._container.dataset).forEach(([key, value]) => {
      parameters[key.replace(/Id$/, "ID")] = value;
    });

    parameters.data = { message: this._getEditor().code.get() };
    parameters.removeQuoteIDs = this._options.quoteManager
      ? this._options.quoteManager.getQuotesMarkedForRemoval()
      : [];

    // add any available settings
    const settingsContainer = document.getElementById("settings_text");
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
   *
   * @param       {Element}       element         erroneous element
   * @param       {string}        message         error message
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
      this._loadingOverlay.className = "messageContentLoadingOverlay";
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

    const loadingOverlay = this._content.querySelector(".messageContentLoadingOverlay");
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

    // Opera on Android does not properly blur the editor after submitting the message,
    // causing the keyboard to vanish, but the focus remains inside the editor.
    window.setTimeout(() => {
      const editor = document.activeElement?.closest(".redactor-layer");
      if (editor && editor instanceof HTMLElement) {
        editor.blur();
      }
    }, 50);
  }

  /**
   * Handles errors occurred during server processing.
   */
  protected _handleError(data: ResponseData): void {
    const parameters = {
      api: this,
      cancel: false,
      returnValues: data.returnValues,
    };
    EventHandler.fire("com.woltlab.wcf.redactor2", "handleError_text", parameters);

    if (!parameters.cancel) {
      this.throwError(this._textarea, data.returnValues.realErrorMessage);
    }
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
   * Inserts the rendered message into the post list, unless the post is on the next
   * page in which case a redirect will be performed instead.
   */
  protected _insertMessage(data: AjaxResponse): void {
    this._getEditor().WoltLabAutosave.reset();

    // redirect to new page
    if (data.returnValues.url) {
      if (window.location.href == data.returnValues.url) {
        window.location.reload();
      }
      window.location.href = data.returnValues.url;
    } else {
      if (data.returnValues.template) {
        let elementId: string;

        // insert HTML
        if (this._container.dataset.sortOrder === "DESC") {
          DomUtil.insertHtml(data.returnValues.template, this._container, "after");
          elementId = DomUtil.identify(this._container.nextElementSibling!);
        } else {
          let insertBefore = this._container;
          if (
            insertBefore.previousElementSibling &&
            insertBefore.previousElementSibling.classList.contains("messageListPagination")
          ) {
            insertBefore = insertBefore.previousElementSibling as HTMLElement;
          }

          DomUtil.insertHtml(data.returnValues.template, insertBefore, "before");
          elementId = DomUtil.identify(insertBefore.previousElementSibling!);
        }

        // update last post time
        this._container.dataset.lastPostTime = data.returnValues.lastPostTime.toString();

        window.history.replaceState(undefined, "", `#${elementId}`);
        UiScroll.element(document.getElementById(elementId)!);
      }

      UiNotification.show(Language.get(this._options.successMessage));

      if (this._options.quoteManager) {
        this._options.quoteManager.countQuotes();
      }

      DomChangeListener.trigger();
    }
  }

  /**
   * @param {{returnValues:{guestDialog:string,guestDialogID:string}}} data
   * @protected
   */
  _ajaxSuccess(data: AjaxResponse): void {
    if (!User.userId && !data.returnValues.guestDialogID) {
      throw new Error("Missing 'guestDialogID' return value for guest.");
    }

    if (!User.userId && data.returnValues.guestDialog) {
      const guestDialogId = data.returnValues.guestDialogID!;

      UiDialog.openStatic(guestDialogId, data.returnValues.guestDialog, {
        closable: false,
        onClose: function () {
          if (ControllerCaptcha.has(guestDialogId)) {
            ControllerCaptcha.delete(guestDialogId);
          }
        },
        title: Language.get("wcf.global.confirmation.title"),
      });

      const dialog = UiDialog.getDialog(guestDialogId)!;
      const submit = dialog.content.querySelector("input[type=submit]") as HTMLInputElement;
      submit.addEventListener("click", (ev) => this._submitGuestDialog(ev));
      const input = dialog.content.querySelector("input[type=text]") as HTMLInputElement;
      input.addEventListener("keypress", (ev) => this._submitGuestDialog(ev));

      this._guestDialogId = guestDialogId;
    } else {
      this._insertMessage(data);

      if (!User.userId) {
        UiDialog.close(data.returnValues.guestDialogID!);
      }

      this._reset();

      this._hideLoadingOverlay();
    }
  }

  _ajaxFailure(data: ResponseData): boolean {
    this._hideLoadingOverlay();

    if (data === null || data.returnValues === undefined || data.returnValues.realErrorMessage === undefined) {
      return true;
    }

    this._handleError(data);

    return false;
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "quickReply",
        className: this._options.ajax.className,
        interfaceName: "wcf\\data\\IMessageQuickReplyAction",
      },
      silent: true,
    };
  }
}

Core.enableLegacyInheritance(UiMessageReply);

export = UiMessageReply;
