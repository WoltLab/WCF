/**
 * Handles the reply feature in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import DomUtil from "../../../Dom/Util";
import { getPhrase } from "../../../Language";
import * as EventHandler from "../../../Event/Handler";
import * as UiScroll from "../../../Ui/Scroll";
import * as UiNotification from "../../../Ui/Notification";
import { CKEditor, getCkeditor } from "../../Ckeditor";
import { listenToCkeditor } from "../../Ckeditor/Event";
import User from "WoltLabSuite/Core/User";
import { getGuestToken } from "../../GuestTokenDialog";
import { createResponse } from "WoltLabSuite/Core/Api/Comments/Responses/CreateResponse";

type CallbackInsertResponse = (commentId: number, responseId: number) => void;

export class CommentResponseAdd {
  readonly container: HTMLElement;
  readonly #content: HTMLElement;
  readonly #textarea: HTMLTextAreaElement;
  readonly #callback: CallbackInsertResponse;
  readonly #messageCache = new Map<number, string>();
  #editor?: CKEditor;
  #commentId: number;

  constructor(container: HTMLElement, callback: CallbackInsertResponse) {
    this.container = container;
    this.#content = this.container.querySelector(".commentResponseAdd__content") as HTMLElement;
    this.#textarea = this.container.querySelector(".wysiwygTextarea") as HTMLTextAreaElement;
    this.#callback = callback;

    this.#initEvents();
  }

  #initEvents(): void {
    const submitButton = this.container.querySelector('button[data-type="save"]') as HTMLButtonElement;
    submitButton.addEventListener("click", (event) => {
      event.preventDefault();

      void this.#submit();
    });

    listenToCkeditor(this.#textarea).setupFeatures(({ features }) => {
      features.heading = false;
      features.spoiler = false;
      features.table = false;
    });
  }

  show(commentId: number): void {
    if (this.#commentId) {
      this.#messageCache.set(this.#commentId, this.#getContent());
    }

    this.#setContent(this.#messageCache.get(commentId) || "");
    this.#commentId = commentId;

    this.container.hidden = false;
  }

  /**
   * Validates the message and invokes listeners to perform additional validation.
   */
  #validate(): boolean {
    // remove all existing error elements
    this.container.querySelectorAll(".innerError").forEach((el) => el.remove());

    const message = this.#getEditor().getHtml();
    if (message === "") {
      this.#throwError(this.#getEditor().element, getPhrase("wcf.global.form.error.empty"));
      return false;
    }

    const data = {
      api: this,
      editor: this.#getEditor(),
      message,
      valid: true,
    };

    EventHandler.fire("com.woltlab.wcf.ckeditor5", "validate_text", data);

    return data.valid;
  }

  /**
   * Validates the message and submits it to the server.
   */
  async #submit(): Promise<void> {
    if (!this.#validate()) {
      return;
    }

    this.#showLoadingOverlay();

    let token: string | undefined = "";
    if (!User.userId) {
      token = await getGuestToken();
      if (token === undefined) {
        this.#hideLoadingOverlay();

        return;
      }
    }

    const response = await createResponse(this.#commentId, this.#getEditor().getHtml(), token);
    if (!response.ok) {
      const validationError = response.error.getValidationError();
      if (validationError === undefined) {
        throw response.error;
      }
      this.#throwError(this.#getEditor().element, validationError.code);
      this.#hideLoadingOverlay();

      return;
    }

    this.#callback(this.#commentId, response.value.responseID);
    UiNotification.show(getPhrase("wcf.global.success.add"));
    this.#reset();
    this.#hideLoadingOverlay();
  }

  /**
   * Resets the editor contents and notifies event listeners.
   */
  #reset(): void {
    this.#getEditor().reset();

    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }

    this.#messageCache.delete(this.#commentId);
    this.container.hidden = true;
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
  #getEditor(): CKEditor {
    if (this.#editor === undefined) {
      this.#editor = getCkeditor(this.#textarea)!;
    }

    return this.#editor;
  }

  /**
   * Retrieves the current content from the editor.
   */
  #getContent(): string {
    return this.#getEditor().getHtml();
  }

  /**
   * Sets the content and places the caret at the end of the editor.
   */
  #setContent(html: string): void {
    this.#getEditor().setHtml(html);

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
      UiScroll.element(this.container, () => {
        this.#getEditor().focus();
      });
    }, 0);
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
    loadingOverlay.innerHTML = '<woltlab-core-loading-indicator size="96" hide-text></woltlab-core-loading-indicator>';
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
