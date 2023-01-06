/**
 * Handles the reply feature in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Component/Comment/Response/Add
 * @since 6.0
 */

import DomUtil from "../../../Dom/Util";
import { getPhrase } from "../../../Language";
import { RedactorEditor } from "../../../Ui/Redactor/Editor";
import * as EventHandler from "../../../Event/Handler";
import * as UiScroll from "../../../Ui/Scroll";
import { dboAction } from "../../../Ajax";
import * as Core from "../../../Core";
import * as UiNotification from "../../../Ui/Notification";
import { StatusNotOk } from "../../../Ajax/Error";
import { showGuestDialog } from "../GuestDialog";

type ResponseAddResponse = {
  guestDialog?: string;
  template?: string;
};

type CallbackInsertResponse = (commentId: number, template: string) => void;

export class CommentResponseAdd {
  readonly #container: HTMLElement;
  readonly #content: HTMLElement;
  readonly #textarea: HTMLTextAreaElement;
  readonly #callback: CallbackInsertResponse;
  readonly #messageCache = new Map<number, string>();
  #editor: RedactorEditor | null = null;
  #commentId: number;

  constructor(container: HTMLElement, callback: CallbackInsertResponse) {
    this.#container = container;
    this.#content = this.#container.querySelector(".commentResponseAdd__content") as HTMLElement;
    this.#textarea = this.#container.querySelector(".wysiwygTextarea") as HTMLTextAreaElement;
    this.#callback = callback;

    this.#initEvents();
  }

  #initEvents(): void {
    const submitButton = this.#container.querySelector('button[data-type="save"]') as HTMLButtonElement;
    submitButton.addEventListener("click", (event) => {
      event.preventDefault();

      void this.#submit();
    });
  }

  show(commentId: number): void {
    if (this.#commentId) {
      this.#messageCache.set(this.#commentId, this.#getContent());
    }

    this.#setContent(this.#messageCache.get(commentId) || "");
    this.#commentId = commentId;

    this.#container.hidden = false;
  }

  /**
   * Validates the message and invokes listeners to perform additional validation.
   */
  #validate(): boolean {
    // remove all existing error elements
    this.#container.querySelectorAll(".innerError").forEach((el) => el.remove());

    // check if editor contains actual content
    if (this.#getEditor().utils.isEmpty()) {
      this.#throwError(this.#textarea, getPhrase("wcf.global.form.error.empty"));
      return false;
    }

    const data = {
      api: this,
      editor: this.#getEditor(),
      message: this.#getEditor().code.get(),
      valid: true,
    };

    EventHandler.fire("com.woltlab.wcf.redactor2", "validate_text", data);

    return data.valid;
  }

  /**
   * Validates the message and submits it to the server.
   */
  async #submit(additionalParameters: Record<string, unknown> = {}): Promise<void> {
    if (!this.#validate()) {
      return;
    }

    this.#showLoadingOverlay();

    const parameters = this.#getParameters();

    EventHandler.fire("com.woltlab.wcf.redactor2", "submit_text", parameters.data as any);

    let response: ResponseAddResponse;

    try {
      response = (await dboAction("addResponse", "wcf\\data\\comment\\CommentAction")
        .objectIds([this.#commentId])
        .payload(Core.extend(parameters, additionalParameters) as ArbitraryObject)
        .disableLoadingIndicator()
        .dispatch()) as ResponseAddResponse;
    } catch (error) {
      if (error instanceof StatusNotOk) {
        const json = await error.response.json();
        if (json.code === 412 && json.returnValues) {
          this.#throwError(this.#textarea, json.returnValues.errorType);
        }
      } else {
        throw error;
      }

      this.#hideLoadingOverlay();
      return;
    }

    if (response!.guestDialog) {
      const additionalParameters = await showGuestDialog(response!.guestDialog);
      if (additionalParameters === undefined) {
        this.#hideLoadingOverlay();
      } else {
        void this.#submit(additionalParameters);
      }
      return;
    }

    this.#callback(this.#commentId, response!.template!);
    UiNotification.show(getPhrase("wcf.global.success.add"));
    this.#reset();
    this.#hideLoadingOverlay();
  }

  /**
   * Resets the editor contents and notifies event listeners.
   */
  #reset(): void {
    this.#getEditor().code.set("<p>\u200b</p>");

    EventHandler.fire("com.woltlab.wcf.redactor2", "reset_text");

    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }

    this.#messageCache.delete(this.#commentId);
    this.#container.hidden = true;
  }

  /**
   * Throws an error by adding an inline error to target element.
   */
  #throwError(element: HTMLElement, message: string): void {
    DomUtil.innerError(element, message === "empty" ? getPhrase("wcf.global.form.error.empty") : message);
  }

  /**
   * Returns the current editor instance.
   */
  #getEditor(): RedactorEditor {
    if (this.#editor === null) {
      if (typeof window.jQuery === "function") {
        this.#editor = window.jQuery(this.#textarea).data("redactor") as RedactorEditor;
      } else {
        throw new Error("Unable to access editor, jQuery has not been loaded yet.");
      }
    }

    return this.#editor;
  }

  /**
   * Retrieves the current content from the editor.
   */
  #getContent(): string {
    return window.jQuery(this.#textarea).redactor("code.get") as string;
  }

  /**
   * Sets the content and places the caret at the end of the editor.
   */
  #setContent(html: string): void {
    window.jQuery(this.#textarea).redactor("code.set", html);
    window.jQuery(this.#textarea).redactor("WoltLabCaret.endOfEditor");

    // the error message can appear anywhere in the container, not exclusively after the textarea
    const innerError = this.#textarea.parentElement!.querySelector(".innerError");
    if (innerError !== null) {
      innerError.remove();
    }

    this.#focusEditor();
  }

  /**
   * Scrolls the editor into view and sets the caret to the end of the editor.
   */
  #focusEditor(): void {
    window.setTimeout(() => {
      UiScroll.element(this.#container, () => {
        const element = window.jQuery(this.#textarea);
        const editor = (element.redactor("core.editor") as any)[0];
        if (editor !== document.activeElement) {
          element.redactor("WoltLabCaret.endOfEditor");
        }
      });
    }, 0);
  }

  /**
   * Returns the request parameters to add a response.
   */
  #getParameters(): ArbitraryObject {
    return {
      data: {
        message: this.#getEditor().code.get(),
      },
    };
  }

  /**
   * Displays a loading spinner while the request is processed by the server.
   */
  #showLoadingOverlay(): void {
    if (this.#content.classList.contains("commentResponseAdd__content--loading")) {
      return;
    }

    const loadingOverlay = document.createElement("div");
    loadingOverlay.className = "commentResponseAdd__loading";
    loadingOverlay.innerHTML = '<fa-icon size="96" name="spinner" solid></fa-icon>';
    this.#content.classList.add("commentResponseAdd__content--loading");
    this.#content.appendChild(loadingOverlay);
  }

  /**
   * Hides the loading spinner.
   */
  #hideLoadingOverlay(): void {
    this.#content.classList.remove("commentResponseAdd__content--loading");

    const loadingOverlay = this.#content.querySelector(".commentResponseAdd__loading");
    if (loadingOverlay !== null) {
      loadingOverlay.remove();
    }
  }
}
