/**
 * Handles the add comment feature in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { dboAction, handleValidationErrors } from "../../Ajax";
import * as UiScroll from "../../Ui/Scroll";
import * as UiNotification from "../../Ui/Notification";
import { getPhrase } from "../../Language";
import * as EventHandler from "../../Event/Handler";
import DomUtil from "../../Dom/Util";
import { showGuestDialog } from "./GuestDialog";
import * as Core from "../../Core";
import { CKEditor, getCkeditor } from "../Ckeditor";
import { listenToCkeditor } from "../Ckeditor/Event";

type ResponseAddComment = {
  guestDialog?: string;
  template?: string;
};

type CallbackInsertComment = (template: string) => void;

export class CommentAdd {
  readonly #container: HTMLElement;
  readonly #content: HTMLElement;
  readonly #editorContainer: HTMLElement;
  readonly #textarea: HTMLTextAreaElement;
  readonly #objectTypeId: number;
  readonly #objectId: number;
  readonly #placeholder: HTMLButtonElement;
  readonly #callback: CallbackInsertComment;
  #editor?: CKEditor;

  constructor(container: HTMLElement, objectTypeId: number, objectId: number, callback: CallbackInsertComment) {
    this.#container = container;
    this.#content = this.#container.querySelector(".commentAdd__content") as HTMLElement;
    this.#editorContainer = this.#container.querySelector(".commentAdd__editor") as HTMLElement;
    this.#textarea = this.#container.querySelector(".wysiwygTextarea") as HTMLTextAreaElement;
    this.#objectTypeId = objectTypeId;
    this.#objectId = objectId;
    this.#placeholder = this.#container.querySelector(".commentAdd__placeholder") as HTMLButtonElement;
    this.#callback = callback;

    this.#initEvents();
  }

  #initEvents(): void {
    this.#placeholder.addEventListener("click", (event) => {
      if (this.#content.classList.contains("commentAdd__content--collapsed")) {
        event.preventDefault();

        this.#content.classList.remove("commentAdd__content--collapsed");
        this.#container.classList.remove("commentAdd--collapsed");
        this.#placeholder.hidden = true;
        this.#editorContainer.hidden = false;

        this.#focusEditor();
      }
    });

    const submitButton = this.#container.querySelector('button[data-type="save"]') as HTMLButtonElement;
    submitButton.addEventListener("click", (event) => {
      event.preventDefault();

      void this.#submit();
    });

    setCommentEditorFeatures(this.#textarea);
  }

  /**
   * Scrolls the editor into view and sets the caret to the end of the editor.
   */
  #focusEditor(): void {
    window.setTimeout(() => {
      UiScroll.element(this.#container, () => {
        this.#getEditor().focus();
      });
    }, 0);
  }

  /**
   * Validates the message and invokes listeners to perform additional validation.
   */
  #validate(): boolean {
    // remove all existing error elements
    this.#container.querySelectorAll(".innerError").forEach((el) => el.remove());

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
  async #submit(additionalParameters: Record<string, unknown> = {}): Promise<void> {
    if (!this.#validate()) {
      return;
    }

    this.#showLoadingOverlay();

    const parameters = this.#getParameters();

    EventHandler.fire("com.woltlab.wcf.ckeditor5", "submit_text", parameters.data as any);

    let response: ResponseAddComment;

    try {
      response = (await dboAction("addComment", "wcf\\data\\comment\\CommentAction")
        .payload(Core.extend(parameters, additionalParameters) as ArbitraryObject)
        .disableLoadingIndicator()
        .dispatch()) as ResponseAddComment;
    } catch (error) {
      await handleValidationErrors(error, (returnValues) => {
        this.#throwError(this.#getEditor().element, returnValues.errorType);

        this.#hideLoadingOverlay();

        return true;
      });

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

    this.#callback(response!.template!);
    UiNotification.show(getPhrase("wcf.global.success.add"));
    this.#reset();
    this.#hideLoadingOverlay();
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
   * Displays a loading spinner while the request is processed by the server.
   */
  #showLoadingOverlay(): void {
    if (this.#content.classList.contains("commentAdd__content--loading")) {
      return;
    }

    const loadingOverlay = document.createElement("div");
    loadingOverlay.className = "commentAdd__loading";
    loadingOverlay.innerHTML = '<woltlab-core-loading-indicator size="96" hide-text></woltlab-core-loading-indicator>';
    this.#content.classList.add("commentAdd__content--loading");
    this.#content.appendChild(loadingOverlay);
  }

  /**
   * Throws an error by adding an inline error to target element.
   */
  #throwError(element: HTMLElement, message: string): void {
    DomUtil.innerError(element, message === "empty" ? getPhrase("wcf.global.form.error.empty") : message);
  }

  /**
   * Returns the request parameters to add a comment.
   */
  #getParameters(): ArbitraryObject {
    return {
      data: {
        message: this.#getEditor().getHtml(),
        objectID: this.#objectId,
        objectTypeID: this.#objectTypeId,
      },
    };
  }

  /**
   * Resets the editor contents and notifies event listeners.
   */
  #reset(): void {
    this.#getEditor().reset();

    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }

    this.#content.classList.add("commentAdd__content--collapsed");
    this.#container.classList.add("commentAdd--collapsed");
    this.#editorContainer.hidden = true;
    this.#placeholder.hidden = false;
  }

  /**
   * Hides the loading spinner.
   */
  #hideLoadingOverlay(): void {
    this.#content.classList.remove("commentAdd__content--loading");

    const loadingOverlay = this.#content.querySelector(".commentAdd__loading");
    if (loadingOverlay !== null) {
      loadingOverlay.remove();
    }
  }
}

export function setCommentEditorFeatures(textarea: HTMLTextAreaElement): void {
  listenToCkeditor(textarea).setupFeatures(({ features }) => {
    features.heading = false;
    features.quoteBlock = false;
    features.spoiler = false;
    features.table = false;
  });
}
