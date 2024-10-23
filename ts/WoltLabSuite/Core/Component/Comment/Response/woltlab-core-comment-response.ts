/**
 * The `<woltlab-core-comment-response>` element represents a response in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import DomUtil from "../../../Dom/Util";
import UiDropdownSimple from "../../../Ui/Dropdown/Simple";
import * as UiNotification from "../../../Ui/Notification";
import { confirmationFactory } from "../../Confirmation";
import * as EventHandler from "../../../Event/Handler";
import * as UiScroll from "../../../Ui/Scroll";
import { getPhrase } from "../../../Language";
import { getCkeditorById } from "../../Ckeditor";
import { enableResponse } from "WoltLabSuite/Core/Api/Comments/Responses/EnableResponse";
import { deleteResponse } from "WoltLabSuite/Core/Api/Comments/Responses/DeleteResponse";
import { editResponse } from "WoltLabSuite/Core/Api/Comments/Responses/EditResponse";
import { renderResponse } from "WoltLabSuite/Core/Api/Comments/Responses/RenderResponse";
import { updateResponse } from "WoltLabSuite/Core/Api/Comments/Responses/UpdateResponse";

export class WoltlabCoreCommentResponseElement extends HTMLParsedElement {
  parsedCallback() {
    if (this.menu) {
      const enableButton = this.menu.querySelector(".commentResponse__option--enable");
      enableButton?.addEventListener("click", (event) => {
        event.preventDefault();
        void this.#enable();
      });

      const deleteButton = this.menu.querySelector(".commentResponse__option--delete");
      deleteButton?.addEventListener("click", (event) => {
        event.preventDefault();
        void this.#delete();
      });

      const editButton = this.menu.querySelector(".commentResponse__option--edit");
      editButton?.addEventListener("click", (event) => {
        event.preventDefault();
        void this.#startEdit();
      });
    }
  }

  async #enable(): Promise<void> {
    (await enableResponse(this.responseId)).unwrap();

    this.querySelector<HTMLElement>(".commentResponse__status--disabled")!.hidden = true;
    if (this.menu) {
      this.menu.querySelector<HTMLElement>(".commentResponse__option--enable")!.hidden = true;
    }
  }

  async #delete(): Promise<void> {
    const result = await confirmationFactory().delete();
    if (result) {
      (await deleteResponse(this.responseId)).unwrap();

      UiNotification.show();

      this.dispatchEvent(new CustomEvent("delete"));
    }
  }

  async #startEdit(): Promise<void> {
    this.menu!.querySelector<HTMLElement>(".commentResponse__option--edit")!.hidden = true;
    const { template } = (await editResponse(this.responseId)).unwrap();
    this.#showEditor(template);
  }

  #showEditor(template: string): void {
    this.querySelector<HTMLElement>(".htmlContent")!.hidden = true;

    DomUtil.insertHtml(template, this.#editorContainer, "append");

    const buttonSave = this.querySelector('button[data-type="save"]') as HTMLButtonElement;
    buttonSave.addEventListener("click", () => {
      void this.#saveEdit();
    });

    const buttonCancel = this.querySelector('button[data-type="cancel"]') as HTMLButtonElement;
    buttonCancel.addEventListener("click", () => {
      this.#cancelEdit();
    });

    EventHandler.add("com.woltlab.wcf.ckeditor5", `submitEditor_${this.#editorId}`, (data) => {
      data.cancel = true;
      void this.#saveEdit();
    });

    window.setTimeout(() => {
      UiScroll.element(this);
    }, 250);
  }

  async #saveEdit(): Promise<void> {
    const ckeditor = getCkeditorById(this.#editorId)!;
    const parameters = {
      data: {
        message: ckeditor.getHtml(),
      },
    };

    if (!this.#validateEdit(parameters)) {
      return;
    }

    EventHandler.fire("com.woltlab.wcf.ckeditor5", `submit_${this.#editorId}`, parameters);

    this.#showLoadingIndicator();

    const response = await updateResponse(this.responseId, ckeditor.getHtml());
    if (!response.ok) {
      const validationError = response.error.getValidationError();
      if (validationError === undefined) {
        throw new Error("Unexpected validation error", { cause: response.error });
      }
      DomUtil.innerError(document.getElementById(this.#editorId)!, validationError.code);
      this.#hideLoadingIndicator();

      return;
    }

    const { template } = (await renderResponse(this.responseId, true)).unwrap();
    DomUtil.setInnerHtml(this.querySelector<HTMLElement>(".htmlContent")!, template);
    this.#hideLoadingIndicator();
    this.#cancelEdit();
    UiNotification.show();
  }

  #showLoadingIndicator(): void {
    let div = this.querySelector<HTMLElement>(".commentResponse__loading");
    if (!div) {
      div = document.createElement("div");
      div.classList.add("commentResponse__loading");
      div.innerHTML = '<woltlab-core-loading-indicator size="96" hide-text></woltlab-core-loading-indicator>';
      this.querySelector(".commentResponse__message")!.append(div);
    }

    this.#editorContainer.hidden = true;
    div.hidden = false;
  }

  #hideLoadingIndicator(): void {
    this.#editorContainer.hidden = false;

    const div = this.querySelector<HTMLElement>(".commentResponse__loading");
    if (div) {
      div.hidden = true;
    }
  }

  /**
   * Validates the message and invokes listeners to perform additional validation.
   */
  #validateEdit(parameters: ArbitraryObject): boolean {
    this.querySelectorAll(".innerError").forEach((el) => el.remove());

    // check if editor contains actual content
    const editor = getCkeditorById(this.#editorId)!;
    if (editor.getHtml() === "") {
      DomUtil.innerError(editor.element, getPhrase("wcf.global.form.error.empty"));
      return false;
    }

    const data = {
      api: this,
      parameters: parameters,
      valid: true,
    };

    EventHandler.fire("com.woltlab.wcf.ckeditor5", `validate_${this.#editorId}`, data);

    return data.valid;
  }

  #cancelEdit(): void {
    void getCkeditorById(this.#editorId)!.destroy();

    this.#editorContainer.remove();

    this.menu!.querySelector<HTMLElement>(".commentResponse__option--edit")!.hidden = false;
    this.querySelector<HTMLElement>(".htmlContent")!.hidden = false;
  }

  get #editorContainer(): HTMLElement {
    let div = this.querySelector<HTMLElement>(".commentResponse__editor");
    if (!div) {
      div = document.createElement("div");
      div.classList.add("commentResponse__editor");
      this.querySelector(".commentResponse__message")!.append(div);
    }

    return div;
  }

  get responseId(): number {
    return parseInt(this.getAttribute("response-id")!);
  }

  get menu(): HTMLElement | undefined {
    let menu = UiDropdownSimple.getDropdownMenu(`commentResponseOptions${this.responseId}`);

    // The initialization of the menu can taken place after
    // `parsedCallback()` is called.
    if (menu === undefined) {
      menu = this.querySelector<HTMLElement>(".commentResponse__menu .dropdownMenu") || undefined;
    }

    return menu;
  }

  get #editorId(): string {
    return `commentResponseEditor${this.responseId}`;
  }
}

window.customElements.define("woltlab-core-comment-response", WoltlabCoreCommentResponseElement);

export default WoltlabCoreCommentResponseElement;
