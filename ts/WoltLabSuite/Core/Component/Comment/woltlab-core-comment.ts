import { dboAction } from "../../Ajax";
import UiDropdownSimple from "../../Ui/Dropdown/Simple";
import { confirmationFactory } from "../Confirmation";

export class WoltlabCoreCommentElement extends HTMLElement {
  connectedCallback() {
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
    const result = await confirmationFactory().delete("todo");
    if (result) {
      await dboAction("delete", "wcf\\data\\comment\\CommentAction").objectIds([this.commentId]).dispatch();

      this.dispatchEvent(new CustomEvent("delete"));
    }
  }

  get commentId(): number {
    return parseInt(this.getAttribute("comment-id")!);
  }

  get menu(): HTMLElement | undefined {
    return UiDropdownSimple.getDropdownMenu(`commentOptions${this.commentId}`);
  }
}

window.customElements.define("woltlab-core-comment", WoltlabCoreCommentElement);

export default WoltlabCoreCommentElement;
