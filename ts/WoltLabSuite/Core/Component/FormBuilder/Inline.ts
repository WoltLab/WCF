/**
 * Promise-based API to submit a form builder form that is embedded directly
 * into a page to a PSR-15 controller.
 *
 * The form is expected to be the only content of the container element. The
 * controller responds with `{ form }` if the validation failed and with
 * `{ form, result }` on success, the form is re-rendered in both cases.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "../../Ajax/Backend";
import * as DomUtil from "../../Dom/Util";
import * as FormBuilderManager from "../../Form/Builder/Manager";
import { getCkeditor } from "../Ckeditor";
import { clearQuotesForEditor } from "../Quote/Storage";

type ResponseSubmitForm =
  | {
      form: string;
      formId: string;
    }
  | {
      form: string;
      formId: string;
      result: unknown;
    };

type SuccessCallback<T> = (result: T) => void | Promise<void>;

class InlineForm<T> {
  readonly #container: HTMLElement;
  readonly #endpoint: string;
  readonly #formId: string;
  readonly #onSuccess: SuccessCallback<T>;
  #isSubmitting = false;

  constructor(container: HTMLElement, formId: string, endpoint: string, onSuccess: SuccessCallback<T>) {
    this.#container = container;
    this.#formId = formId;
    this.#endpoint = endpoint;
    this.#onSuccess = onSuccess;

    this.#initSubmitButton();
  }

  #initSubmitButton(): void {
    const submitButton = this.#getSubmitButton();
    submitButton.addEventListener("click", () => {
      void this.#submit();
    });
  }

  #getSubmitButton(): HTMLButtonElement {
    const submitButton = document.getElementById(`${this.#formId}_submitButton`);
    if (!(submitButton instanceof HTMLButtonElement)) {
      throw new Error(`Unable to find the submit button of the form '${this.#formId}'.`);
    }

    return submitButton;
  }

  async #submit(): Promise<void> {
    if (this.#isSubmitting) {
      return;
    }

    this.#isSubmitting = true;
    const submitButton = this.#getSubmitButton();
    submitButton.disabled = true;

    try {
      const data = await FormBuilderManager.getData(this.#formId);
      const response = (await prepareRequest(this.#endpoint).post(data).fetchAsJson()) as ResponseSubmitForm;

      if ("result" in response) {
        this.#resetEditors();
        this.#render(response.form);

        await this.#onSuccess(response.result as T);
      } else {
        this.#render(response.form);
        this.#scrollToFirstError();
      }
    } finally {
      this.#isSubmitting = false;

      // The button is replaced when the form is re-rendered.
      if (submitButton.isConnected) {
        submitButton.disabled = false;
      }
    }
  }

  /**
   * Resets the editors before the form is discarded, this removes the drafts
   * and the attachments of the editors.
   */
  #resetEditors(): void {
    this.#container.querySelectorAll<HTMLTextAreaElement>("textarea.wysiwygTextarea").forEach((textarea) => {
      const ckeditor = getCkeditor(textarea);
      if (ckeditor === undefined) {
        return;
      }

      ckeditor.reset();
      clearQuotesForEditor(textarea.id);
    });
  }

  #render(html: string): void {
    if (FormBuilderManager.hasForm(this.#formId)) {
      FormBuilderManager.unregisterForm(this.#formId);
    }

    DomUtil.setInnerHtml(this.#container, html);

    this.#initSubmitButton();
  }

  #scrollToFirstError(): void {
    const error = this.#container.querySelector('.formField__errors, woltlab-core-notice[type="error"]');
    error?.scrollIntoView({ behavior: "smooth", block: "center" });
  }
}

/**
 * Sets up the form with the given id that has been rendered into the given
 * container. The callback is invoked with the result of a successful
 * submission after the form has been reset.
 */
export function setupInlineForm<T = unknown>(
  container: HTMLElement,
  formId: string,
  endpoint: string,
  onSuccess: SuccessCallback<T>,
): void {
  new InlineForm<T>(container, formId, endpoint, onSuccess);
}
