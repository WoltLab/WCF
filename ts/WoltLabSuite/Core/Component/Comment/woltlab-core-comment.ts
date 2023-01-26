/**
 * The `<woltlab-core-comment>` element represents a comment in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { dboAction, handleValidationErrors } from "../../Ajax";
import DomUtil from "../../Dom/Util";
import UiDropdownSimple from "../../Ui/Dropdown/Simple";
import * as UiNotification from "../../Ui/Notification";
import { confirmationFactory } from "../Confirmation";
import * as UiScroll from "../../Ui/Scroll";
import * as Environment from "../../Environment";
import * as EventHandler from "../../Event/Handler";
import { RedactorEditor } from "../../Ui/Redactor/Editor";
import { getPhrase } from "../../Language";

type ResponseBeginEdit = {
  template: string;
};

type ResponseSave = {
  message: string;
};

export class WoltlabCoreCommentElement extends HTMLParsedElement {
  parsedCallback() {
    if (this.menu) {
      const enableButton = this.menu.querySelector(".comment__option--enable");
      enableButton?.addEventListener("click", (event) => {
        event.preventDefault();
        void this.#enable();
      });

      const deleteButton = this.menu.querySelector(".comment__option--delete");
      deleteButton?.addEventListener("click", (event) => {
        event.preventDefault();
        void this.#delete();
      });

      const editButton = this.menu.querySelector(".comment__option--edit");
      editButton?.addEventListener("click", (event) => {
        event.preventDefault();
        void this.#startEdit();
      });
    }

    const replyButton = this.querySelector(".comment__button--reply");
    replyButton?.addEventListener("click", () => {
      this.dispatchEvent(new CustomEvent("reply"));
    });
  }

  async #enable(): Promise<void> {
    await dboAction("enable", "wcf\\data\\comment\\CommentAction").objectIds([this.commentId]).dispatch();

    this.querySelector<HTMLElement>(".comment__status--disabled")!.hidden = true;
    if (this.menu) {
      this.menu.querySelector<HTMLElement>(".comment__option--enable")!.hidden = true;
    }
  }

  async #delete(): Promise<void> {
    const result = await confirmationFactory().delete();
    if (result) {
      await dboAction("delete", "wcf\\data\\comment\\CommentAction").objectIds([this.commentId]).dispatch();

      UiNotification.show();

      this.dispatchEvent(new CustomEvent("delete"));
    }
  }

  async #startEdit(): Promise<void> {
    this.menu!.querySelector<HTMLElement>(".comment__option--edit")!.hidden = true;

    const { template } = (await dboAction("beginEdit", "wcf\\data\\comment\\CommentAction")
      .objectIds([this.commentId])
      .dispatch()) as ResponseBeginEdit;

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

    EventHandler.add("com.woltlab.wcf.redactor", `submitEditor_${this.#editorId}`, (data) => {
      data.cancel = true;
      void this.#saveEdit();
    });

    const editorElement = document.getElementById(this.#editorId) as HTMLElement;
    if (Environment.editor() === "redactor") {
      window.setTimeout(() => {
        UiScroll.element(this);
      }, 250);
    } else {
      editorElement.focus();
    }
  }

  async #saveEdit(): Promise<void> {
    const parameters = {
      data: {
        message: "",
      },
    };

    EventHandler.fire("com.woltlab.wcf.redactor2", `getText_${this.#editorId}`, parameters.data);

    if (!this.#validateEdit(parameters)) {
      return;
    }

    EventHandler.fire("com.woltlab.wcf.redactor2", `submit_${this.#editorId}`, parameters);

    this.#showLoadingIndicator();

    let response: ResponseSave;

    try {
      response = (await dboAction("save", "wcf\\data\\comment\\CommentAction")
        .objectIds([this.commentId])
        .payload(parameters)
        .dispatch()) as ResponseSave;
    } catch (error) {
      await handleValidationErrors(error, ({ errorType }) => {
        DomUtil.innerError(document.getElementById(this.#editorId)!, errorType);

        return true;
      });

      this.#hideLoadingIndicator();
      return;
    }

    DomUtil.setInnerHtml(this.querySelector<HTMLElement>(".htmlContent")!, response!.message);
    this.#hideLoadingIndicator();
    this.#cancelEdit();
    UiNotification.show();
  }

  #showLoadingIndicator(): void {
    let div = this.querySelector<HTMLElement>(".comment__loading");
    if (!div) {
      div = document.createElement("div");
      div.classList.add("comment__loading");
      div.innerHTML = '<woltlab-core-loading-indicator size="96" hide-text></woltlab-core-loading-indicator>';
      this.querySelector(".comment__message")!.append(div);
    }

    this.#editorContainer.hidden = true;
    div.hidden = false;
  }

  #hideLoadingIndicator(): void {
    this.#editorContainer.hidden = false;

    const div = this.querySelector<HTMLElement>(".comment__loading");
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
    const editorElement = document.getElementById(this.#editorId)!;
    const redactor: RedactorEditor = window.jQuery(editorElement).data("redactor");
    if (redactor.utils.isEmpty()) {
      DomUtil.innerError(editorElement, getPhrase("wcf.global.form.error.empty"));
      return false;
    }

    const data = {
      api: this,
      parameters: parameters,
      valid: true,
    };

    EventHandler.fire("com.woltlab.wcf.redactor2", `validate_${this.#editorId}`, data);

    return data.valid;
  }

  #cancelEdit(): void {
    this.#destroyEditor();

    this.#editorContainer.remove();

    this.menu!.querySelector<HTMLElement>(".comment__option--edit")!.hidden = false;
    this.querySelector<HTMLElement>(".htmlContent")!.hidden = false;
  }

  #destroyEditor(): void {
    EventHandler.fire("com.woltlab.wcf.redactor2", `autosaveDestroy_${this.#editorId}`);
    EventHandler.fire("com.woltlab.wcf.redactor2", `destroy_${this.#editorId}`);
  }

  get #editorContainer(): HTMLElement {
    let div = this.querySelector<HTMLElement>(".comment__editor");
    if (!div) {
      div = document.createElement("div");
      div.classList.add("comment__editor");
      this.querySelector(".comment__message")!.append(div);
    }

    return div;
  }

  get commentId(): number {
    return parseInt(this.getAttribute("comment-id")!);
  }

  get menu(): HTMLElement | undefined {
    let menu = UiDropdownSimple.getDropdownMenu(`commentOptions${this.commentId}`);

    // The initialization of the menu can taken place after
    // `parsedCallback()` is called.
    if (menu === undefined) {
      menu = this.querySelector<HTMLElement>(".comment__menu .dropdownMenu") || undefined;
    }

    return menu;
  }

  get #editorId(): string {
    return `commentEditor${this.commentId}`;
  }
}

window.customElements.define("woltlab-core-comment", WoltlabCoreCommentElement);

export default WoltlabCoreCommentElement;
